<?php
namespace App\Controller\Backoffice;

use App\Entity\User;

class UserController extends BackofficeController
{
    /**
     * @param User $user
     */
    public function prePersistEntity($user)
    {
        if ($user->getPlainPassword()) {
            $user->setPassword(
                $this->get('security.password_encoder')->encodePassword($user, $user->getPlainPassword())
            );
        }
        parent::prePersistEntity($user);
    }
}
