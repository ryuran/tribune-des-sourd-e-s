<?php

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use App\Utils\ApiResponse;
use App\Form\User\LoginType;
use App\Entity\User;
use App\Form\User\ForgetType;
use App\Model\UserModel;

class SecurityController extends Controller
{
    public function loginAction()
    {
        $viewData = [];

        $authenticationUtils = $this->get('security.authentication_utils');

        $user = new User();
        $user->setUsername($authenticationUtils->getLastUsername());

        /** @var Form $userForm */
        $userForm = $this->get('form.factory')->create(LoginType::class, $user);
        $viewData['form'] = $userForm->createView();

        return $this->render('User/Security/login.html.twig', $viewData);
    }

    public function forgetAction(Request $request)
    {
        $viewData = [];
        $formData = $request->request->get(ForgetType::NAME) ?? [];

        /** @var UserModel $userModel */
        $userModel = $this->get(UserModel::class);
        /** @var ApiResponse $responseData */
        $apiResponse = $userModel->forget($formData);

        if ($apiResponse->code === ApiResponse::SUCCESS) {
            $this->get('session')->getFlashBag()->add('info', "A email was sent with a link to reset your password.");
            return $this->redirectToRoute($this->getParameter("user_forget"));
        } else {
            /** @var Form $form */
            $form = $apiResponse->embedded['form'];
            $viewData['form'] = $form->createView();
        }

        return $this->render('User/Security/forget.html.twig', $viewData);
    }
}
