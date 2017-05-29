<?php

namespace App\Controller\User;

use App\Utils\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Form\Form;
use App\Form\User\RegisterType;
use UserBundle\Model\UserModel;

class RegistrationController extends Controller
{
    public function registerAction(Request $request)
    {
        $viewData = [];
        $formData = $request->request->get(RegisterType::NAME);

        if (count($formData) > 0) {
            $formData['locale'] = $request->getLocale();
        }

        /** @var UserModel $modelUser */
        $userModel = $this->get('user.model.user');
        /** @var ApiResponse $apiResponse */
        $apiResponse = $userModel->register($formData);

        if ($apiResponse->code === ApiResponse::SUCCESS) {
            $this->get('session')->getFlashBag()->add(
                'info',
                "We have sent an email an email to validate your account."
            );
            return $this->redirectToRoute($this->getParameter('user_register'));
        } else {
            /** @var Form $form */
            $form = $apiResponse->embedded['form'];
            $viewData['form'] = $form->createView();
        }

        return $this->render('UserBundle:Registration:register.html.twig', $viewData);
    }

    public function validateAction($user_token)
    {
        /** @var UserModel $userModel */
        $userModel = $this->get('user.model.user');
        $responseData = $userModel->validate($user_token);

        if ($responseData->code === ApiResponse::SUCCESS) {
            $this->get('session')->getFlashBag()->add('info', "You account has been validated.");

            return $this->redirectToRoute($this->getParameter("user_register"));
        }

        throw new TokenNotFoundException("Link expired.");
    }
}
