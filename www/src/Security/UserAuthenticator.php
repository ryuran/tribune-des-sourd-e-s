<?php

namespace App\Security;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManager;
use App\Entity\User;

class UserAuthenticator extends AbstractGuardAuthenticator
{
    private $entityManager;
    private $userPasswordEncoder;
    private $router;
    private $session;
    private $params = [];

    public function __construct(
        $user_login,
        EntityManager $entityManager,
        UserPasswordEncoder $userPasswordEncoder,
        Router $router
    ) {
        $this->entityManager = $entityManager;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->router = $router;
        $this->params['user_login'] = $user_login;
    }

    /**
     * Get the authentication credentials from the request. If you return null,
     * authentication will be skipped.
     * @param Request $request
     * @return array|null
     */
    public function getCredentials(Request $request)
    {
        $token = $request->headers->get('X-API-TOKEN');

        if ($token) {
            return ['token' => $token];
        } elseif ($request->get('_route') == 'user_login') {
            $data = $request->request->get('login');

            $username = isset($data['username']) ? $data['username'] : '';
            $password = isset($data['plainPassword']) ? $data['plainPassword'] : '';

            if ($username && $password) {
                $request->getSession()->set(Security::LAST_USERNAME, $username);

                return [
                    'username' => $username,
                    'plainPassword' => $password
                ];
            }
        }

        return null;
    }

    /**
     * Return a UserInterface object based on the credentials returned by getCredentials()
     * if null, authentication will fail
     * if a User object, checkCredentials() is called
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return null|object|UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (isset($credentials['token'])) {
            return $this->entityManager->getRepository('App:User')
                ->findOneBy(array('apiToken' => $credentials['token']));
        } elseif (isset($credentials['username']) && isset($credentials['plainPassword'])) {
            return $userProvider->loadUserByUsername($credentials['username']);
        }

        return null;
    }

    /**
     * Throw an AuthenticationException if the credentials returned by
     * getCredentials() are invalid.
     * @param mixed $credentials
     * @param UserInterface $userInterface
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $userInterface): bool
    {
        /** @var User $user */
        $user = $userInterface;

        if (isset($credentials['token'])) {
            return true;
        } elseif (isset($credentials['username']) && isset($credentials['plainPassword'])) {
            if (!$this->userPasswordEncoder->isPasswordValid($user, $credentials['plainPassword'])) {
                throw new BadCredentialsException("Bad credentials.");
            } else {
                switch ($user->getState()) {
                    case User::STATES['disabled']:
                        throw new DisabledException("Your account is disabled. Please contact the administrator.");
                        break;
                    case User::STATES['wait_validation']:
                        throw new LockedException("Your account is locked. Check and valid your email account.");
                        break;
                    case User::STATES['active']:
                        return true;
                        break;
                }
            }
        }
        return false;
    }

    /**
     * Create an authenticated token for the given user. You can skip this
     * method by extending the AbstractGuardAuthenticator class from your
     * authenticator.
     * @param UserInterface $user
     * @param string $providerKey
     * @return PostAuthenticationGuardToken
     */
    public function createAuthenticatedToken(UserInterface $user, $providerKey)
    {
        return parent::createAuthenticatedToken($user, $providerKey);
    }

    /**
     * Called when authentication executed, but failed (e.g. wrong username password).
     * @param Request $request
     * @param AuthenticationException $exception
     * @return RedirectResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        /** @var Session $session */
        $session = $request->getSession();
        $session->getFlashBag()->add('error', $exception->getMessage());

        return new RedirectResponse($this->router->generate('user_login'), 302);
    }

    /**
     * Called when authentication executed and was successful (for example a
     * RedirectResponse to the last page they visited)
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return RedirectResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $redirect = $request->getSession()->get('_target_path');
        $request->getSession()->set('_target_path', '');

        $redirect = $redirect ? $redirect : $this->router->generate($this->params['user_login']);

        return new RedirectResponse($redirect, 302);
    }

    /**
     * Does this method support remember me cookies?
     * @return bool
     */
    public function supportsRememberMe()
    {
        return false;
    }

    /**
     * Called when authentication is needed, but it's not sent
     * @param Request $request
     * @param AuthenticationException|null $exception
     * @return RedirectResponse
     */
    public function start(Request $request, AuthenticationException $exception = null)
    {
        $session = new Session();
        $session->set('_target_path', $request->getUri());
        if ($exception->getMessage()) {
            $session->getFlashBag()->add('error', $exception->getMessage());
        }

        $request->setSession($session);
        return new RedirectResponse($this->router->generate('user_login'), '302');
    }
}
