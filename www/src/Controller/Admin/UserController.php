<?php
namespace App\Controller\Admin;

use App\Entity\User;
use JavierEguiluz\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;

class UserController extends BaseAdminController
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