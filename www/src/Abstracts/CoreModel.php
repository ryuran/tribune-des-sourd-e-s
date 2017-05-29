<?php

namespace App\Abstracts;

use Symfony\Component\Form\Form;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use App\Utils\ApiResponse;
use App\Interfaces\CoreEntityInterface;
use App\Interfaces\UserPropertyInterface;
use App\Entity\User;

abstract class CoreModel
{
    protected $entityManager;
    protected $tokenStorage;
    protected $authorizationChecker;
    protected $translator;

    public function __construct(
        EntityManager $entityManager,
        TokenStorage $tokenStorage,
        AuthorizationChecker $authorizationChecker,
        Translator $translator
    ) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->translator = $translator;
    }

    public function getLoggedUser(): User
    {
        /** @var TokenInterface $token */
        $token = $this->tokenStorage->getToken();
        if ($token && is_object($token->getUser())) {
            return $token->getUser();
        }
        return new User();
    }

    public function getLoggedUserId(): int
    {
        $loggedUser = $this->getLoggedUser();
        return $loggedUser->getId() ?? 0;
    }

    public function editConnected(Form $form, array $formData): ApiResponse
    {
        $apiResponse = ApiResponse::checkConnectedAndValid($this->getLoggedUserId(), $form, $formData);
        if ($apiResponse->code !== ApiResponse::CONTINUE) {
            return $apiResponse;
        }

        /** @var CoreEntityInterface $entity */
        $entity = $form->getData();
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return ApiResponse::returnValidEntityResponse($entity);
    }

    public function editWithUserValidation(Form $form, array $formData)
    {
        $loggedUserId = $this->getLoggedUserId();
        $apiResponseVerification = ApiResponse::checkConnectedAndValid($this->getLoggedUserId(), $form, $formData);
        if ($apiResponseVerification instanceof ApiResponse) {
            return $apiResponseVerification;
        }

        /** @var UserPropertyInterface $entity */
        $entity = $form->getData();

        if ($loggedUserId === $entity->getUserId() || $this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            return ApiResponse::returnValidEntityResponse($entity);
        }
        return ApiResponse::returnForbiddenResponse();
    }

    public function deleteConnected(CoreEntityInterface $entity)
    {
        $apiResponse = ApiResponse::checkConnected($this->getLoggedUserId());
        if ($apiResponse->code !== ApiResponse::CONTINUE) {
            return $apiResponse;
        }

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return new ApiResponse(204);
    }

    public function deleteWithUserValidation(UserPropertyInterface $entity)
    {
        $loggedUserId = $this->getLoggedUserId();

        $apiResponse = ApiResponse::checkConnected($loggedUserId);
        if ($apiResponse->code !== ApiResponse::CONTINUE) {
            return $apiResponse;
        }

        if ($entity->getUserId() === $loggedUserId || $this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();

            return ApiResponse::returnValidEntityResponse($entity);
        }
        return ApiResponse::returnForbiddenResponse();
    }
}
