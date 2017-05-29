<?php

namespace App\Model;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use App\Abstracts\CoreModel;
use App\Utils\ApiResponse;
use App\Security\Authenticator;
use App\Security\Provider;
use App\Helper\UserHelper;
use App\Repository\UserRepository;
use App\Service\Mailer;
use App\Entity\User;
use App\Form\User\RegisterType;
use App\Form\User\LoginType;
use App\Form\User\ForgetType;
use App\Form\User\EditType;
use App\Form\User\AdminType;
use App\Form\User\RenewType;

class UserModel extends CoreModel
{
    private $userPasswordEncoder;
    private $mailer;
    private $formFactory;
    private $authenticator;
    private $provider;
    private $params = [];

    public function __construct(
        $locale,
        EntityManager $entityManager,
        TokenStorage $tokenStorage,
        AuthorizationChecker $authorizationChecker,
        Translator $translator,
        UserPasswordEncoder $userPasswordEncoder,
        Mailer $mailer,
        FormFactory $formFactory,
        Authenticator $authenticator,
        Provider $provider
    ) {
        parent::__construct($entityManager, $tokenStorage, $authorizationChecker, $translator);
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->mailer = $mailer;
        $this->formFactory = $formFactory;
        $this->authenticator = $authenticator;
        $this->provider = $provider;

        $this->params['locale'] = $locale;
    }

    public function find(string $username): User
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository('UserBundle:User');
        return $userRepository->getByUsernameOrEmail($username);
    }

    public function forceLogin(User $user)
    {
        $token = new UsernamePasswordToken($user, null, "main", $user->getRoles());
        $this->tokenStorage->setToken($token);
    }

    public function forceLogout()
    {
        $this->tokenStorage->setToken(null);
    }

    public function forceRegister(
        string $email,
        string $username = null,
        string $password = null,
        string $role = null
    ): User {
        if (!$username) {
            $username = $email;
        }
        if (!$password) {
            $password = UserHelper::randomPassword();
        }
        if (!$role) {
            $role = "ROLE_USER";
        }

        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setPlainPassword($password);
        $user->setPassword($this->userPasswordEncoder->encodePassword($user, $user->getPlainPassword()));
        $user->setLocale($this->params['locale']);
        $user->setStatus(User::ACTIVE);
        $user->setRoles([$role]);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function register(array $formData, bool $skipValidation = false): ApiResponse
    {
        $user = new User();
        $form = $this->formFactory->create(RegisterType::class, $user);

        $apiResponse = ApiResponse::checkValid($form, $formData);
        if ($apiResponse->code !== ApiResponse::CONTINUE) {
            return $apiResponse;
        }

        $user->setPassword($this->userPasswordEncoder->encodePassword($user, $user->getPlainPassword()));

        if ($skipValidation) {
            $user->setStatus(User::ACTIVE);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        if (!$skipValidation) {
            $this->mailer->sendToUser(
                'UserBundle:Email:register.' . $user->getLocale() . '.html.twig',
                $user,
                ['token' => $user->getToken()]
            );
        }
        return ApiResponse::returnValidEntityResponse($user);
    }

    public function login(array $formData): ApiResponse
    {
        $form = $this->formFactory->create(LoginType::class, new User());

        $apiResponse = ApiResponse::checkValid($form, $formData);
        if ($apiResponse->code !== ApiResponse::CONTINUE) {
            return $apiResponse;
        }

        try {
            /** @var User $user */
            $user = $this->authenticator->getUser($formData, $this->provider);

            if ($user === null || !$this->authenticator->checkCredentials($formData, $user)) {
                return ApiResponse::returnNotValidFormResponse($form, [
                    '#' => [$this->translator->trans("Bad credentials.")]
                ]);
            }

            $this->forceLogin($user);
            return ApiResponse::returnValidEntityResponse($user);
        } catch (\Exception $e) {
            return ApiResponse::returnNotValidFormResponse($form, [
                '#' => [$this->translator->trans($e->getMessage())]
            ]);
        }
    }

    public function validate($token): ApiResponse
    {
        /** @var User $user */
        $user = $this->entityManager->getRepository('UserBundle:User')->findOneBy(['token' => $token]);

        if (!$user instanceof User) {
            return ApiResponse::returnErrorsResponse([
                'token' => [$this->translator->trans("Link expired.")]
            ]);
        }

        $user->setStatus(User::ACTIVE);
        $user->initToken();
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->forceLogin($user);

        return ApiResponse::returnValidEntityResponse($user);
    }

    public function forget($formData = []): ApiResponse
    {
        /** @var Form $form */
        $form = $this->formFactory->create(ForgetType::class, new User());

        $apiResponse = ApiResponse::checkValid($form, $formData);
        if ($apiResponse->code !== ApiResponse::CONTINUE) {
            return $apiResponse;
        }

        try {
            $user = $this->provider->loadUserByUsername($formData['username']);

            if (!$user) {
                return ApiResponse::returnNotValidFormResponse($form, [
                    'token' => [
                        $this->translator->trans('No user exists with this username/email.', [], 'validators')
                    ]
                ]);
            }

            $user->initToken();
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->mailer->sendToUser(
                'UserBundle:Email:forget.' . $user->getLocale() . '.html.twig',
                $user,
                ['token' => $user->getToken()]
            );

            return ApiResponse::returnValidEntityResponse($user);
        } catch (\Exception $e) {
            return ApiResponse::returnNotValidFormResponse($form, [
                '#' => [$this->translator->trans($e->getMessage())]
            ]);
        }
    }

    public function get($username = null): ApiResponse
    {
        $loggedUser = $this->getLoggedUser();

        if ($username !== null) {
            /** @var User $user */
            $user = $this->entityManager->getRepository('UserBundle:User')->findOneBy(['username' => $username]);

            if ($user) {
                return ApiResponse::returnValidEntityResponse($user);
            }
            return ApiResponse::returnErrorsResponse([
                'username' => [$this->translator->trans('No user exists with this username.')]
            ]);
        }

        if ($loggedUser->getId()) {
            return ApiResponse::returnValidEntityResponse($loggedUser);
        }

        return ApiResponse::returnUnauthorizedResponse();
    }

    public function admin($user, $formData = []): ApiResponse
    {
        $form = $this->formFactory->create(AdminType::class, $user);

        $apiResponse = ApiResponse::checkValid($form, $formData);
        if ($apiResponse->code !== ApiResponse::CONTINUE) {
            return $apiResponse;
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return ApiResponse::returnValidEntityResponse($user);
    }

    public function edit($formData = []): ApiResponse
    {
        $loggedUser = $this->getLoggedUser();
        $user = new User();

        $form = $this->formFactory->create(EditType::class, $user, [
            'token_storage' => $this->tokenStorage
        ]);

        if (isset($formData['username']) && $formData['username'] === $loggedUser->getUsername()) {
            unset($formData['username']);
        }
        if (isset($formData['email']) && $formData['email'] === $loggedUser->getEmail()) {
            unset($formData['email']);
        }

        $apiResponse = ApiResponse::checkConnectedAndValid($loggedUser->getId(), $form, $formData);
        if ($apiResponse->code !== ApiResponse::CONTINUE) {
            return $apiResponse;
        }

        $loggedUser->setEnabledEmails($user->getEnabledEmails());
        if ($user->getPlainPassword()) {
            $loggedUser->setPassword(
                $this->userPasswordEncoder->encodePassword($loggedUser, $user->getPlainPassword())
            );
        }
        if ($user->getUsername()) {
            $loggedUser->setUsername($user->getUsername());
        }

        if ($user->getEmail()) {
            $loggedUser->setEmail($user->getEmail());
            $loggedUser->setStatus(User::WAIT_VALIDATION);
            $loggedUser->initToken();

            $this->mailer->sendToUser(
                'UserBundle:Email:update.' . $loggedUser->getLocale() . '.html.twig',
                $loggedUser,
                ['token' => $loggedUser->getToken()]
            );

            $this->forceLogout();
        }

        $loggedUser->setLastname($user->getLastname());
        $loggedUser->setFirstname($user->getFirstname());

        $this->entityManager->persist($loggedUser);
        $this->entityManager->flush();

        if ($user->getEmail()) {
            return ApiResponse::returnValidDataResponse([
                'user' => $user,
                'messages' => ["We have sent an email an email to validate your account."]
            ]);
        } else {
            return ApiResponse::returnValidEntityResponse($user);
        }
    }

    public function reset($formData = []): ApiResponse
    {
        if (!isset($formData['_user_token'])) {
            return ApiResponse::returnErrorsResponse(['#' => ['Link expired.']]);
        }

        $token = $formData['_user_token'];
        unset($formData['_user_token']);

        $user = $this->entityManager->getRepository('UserBundle:User')->findOneBy(['token' => $token]);

        if (!$user) {
            return ApiResponse::returnErrorsResponse(['#' => ['Link not valid.']]);
        }

        $form = $this->formFactory->create(RenewType::class, $user);

        $apiResponse = ApiResponse::checkValid($form, $formData);
        if ($apiResponse->code !== ApiResponse::CONTINUE) {
            return $apiResponse;
        }

        $user->initToken();
        $user->setPassword($this->userPasswordEncoder->encodePassword($user, $user->getPlainPassword()));

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->forceLogin($user);

        return ApiResponse::returnValidEntityResponse($user);
    }

    public function oauth($service, $formData): ApiResponse
    {
        if (!isset($formData['email']) || isset($formData['id'])) {
            return ApiResponse::returnEmptyResponse();
        }

        $userRepository = $this->entityManager->getRepository('UserBundle:User');

        $userByEmail = $userRepository->findOneBy(['email' => $formData['email']]);
        $userById = $userRepository->findOneBy([$service . 'Id' => $formData['id']]);
        $user = $userById ?? $userByEmail;

        if ($userByEmail && $userById && $userByEmail->getId() !== $userById->getId()) {
            return ApiResponse::returnErrorsResponse([
                '#' => ['This account is already associated with another user.']
            ]);
        }

        if (!$user) {
            $user = $this->forceRegister($formData['email']);
            $method = 'set' . ucfirst($service) . 'Id';
            $user->$method($formData['id']);

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        $this->forceLogin($user);
        return ApiResponse::returnValidEntityResponse($user);
    }

    public function unsubscribe(): ApiResponse
    {
        $loggedUser = $this->getLoggedUser();

        $apiResponse = ApiResponse::checkConnected($loggedUser->getId());
        if ($apiResponse->code !== ApiResponse::CONTINUE) {
            return $apiResponse;
        }

        $this->entityManager->remove($loggedUser);
        $this->entityManager->flush();

        return ApiResponse::returnValidEntityResponse($loggedUser);
    }
}
