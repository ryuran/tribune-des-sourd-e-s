<?php

namespace App\Security;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use App\Entity\User;

/**
 * UserProvider
 */
class UserProvider implements UserProviderInterface
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function loadUserByUsername($username): User
    {
        $criteria = new Criteria();
        $criteria->where(new Comparison("username", Comparison::EQ, $username));
        $criteria->orWhere(new Comparison("email", Comparison::EQ, $username));
        $criteria->setMaxResults(1);

        $user = $this->entityManager->getRepository("UserBundle:User")->matching($criteria)->first();

        if ($user) {
            return $user;
        }

        //throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        throw new BadCredentialsException("Bad credentials.");
    }

    public function refreshUser(UserInterface $user): User
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class): bool
    {
        return $class === User::class;
    }
}
