<?php

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\Form;
use App\Utils\ApiResponse;
use App\Entity\User;
use App\Form\User\EditType;
use App\Form\User\RenewType;
use App\Model\UserModel;

class LoggedController extends Controller
{
    public function editAction(Request $request)
    {
        $viewData = [];
        /** @var User $userLogged */
        $userLogged = clone $this->getUser();
        /** @var UserModel $userModel */
        $userModel = $this->get(UserModel::class);
        $formData = $request->request->get(EditType::NAME) ?? [];

        /** @var ApiResponse $apiResponse */
        $apiResponse = $userModel->edit($formData);

        if ($apiResponse->code === ApiResponse::SUCCESS) {
            /** @var User $user */
            $user = $apiResponse->embedded['user'];

            if ($user->getEmail() && $user->getEmail() !== $userLogged->getEmail()) {
                if (isset($apiResponse->embedded['messages'])) {
                    /** @var Session $session */
                    $session = $this->get('session');
                    foreach ($apiResponse->embedded['messages'] as $message) {
                        $session->getFlashBag()->add('info', $message);
                    }
                }

                return $this->redirectToRoute($this->getParameter('user_logout'));
            }
            return $this->redirectToRoute($this->getParameter('user_update'));
        } else {
            /** @var Form $form */
            $form = $apiResponse->embedded['form'];
            $viewData['form'] = $form->createView();
        }

        return $this->render('User/Logged/edit.html.twig', $viewData);
    }

    public function resetAction(Request $request, $user_token)
    {
        $viewData = [];
        $formData = $request->request->get(RenewType::NAME) ?? [];
        $formData['_user_token'] = $user_token;

        /** @var UserModel $userModel */
        $userModel = $this->get(UserModel::class);
        /** @var ApiResponse $apiResponse */
        $apiResponse = $userModel->reset($formData);

        if (isset($apiResponse->embedded['form'])) {
            if ($apiResponse->code === ApiResponse::SUCCESS) {
                return $this->redirectToRoute($this->getParameter("user_reset"));
            } else {
                /** @var Form $form */
                $form = $apiResponse->embedded['form'];
                $viewData['form'] = $form->createView();
            }

            return $this->render('User/Logged/reset.html.twig', $viewData);
        } else {
            throw $this->createNotFoundException();
        }
    }

    public function unsubscribeAction()
    {
        /** @var UserModel $userModel */
        $userModel = $this->get(UserModel::class);
        $userModel->unsubscribe();
        /** @var Session $session */
        $session = $this->get('session');

        $session->invalidate();
        $session->getFlashBag()->clear();
        $session->getFlashBag()->add('info', "Your account has been deleted.");

        return $this->redirectToRoute($this->getParameter('user_unsubscribe'));
    }

    public function logoutAction()
    {
        /** @var UserModel $userModel */
        $userModel = $this->get(UserModel::class);
        $userModel->forceLogout();
        $this->get('session')->invalidate();

        return $this->redirectToRoute($this->getParameter('user_logout'));
    }
}
