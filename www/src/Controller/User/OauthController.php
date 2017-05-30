<?php

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Utils\ApiResponse;
use App\Model\UserModel;

class OauthController extends Controller
{
    private function redirectWithErrors(Request $request, $errors)
    {
        foreach ($errors as $field => $errors_field) {
            foreach ($errors_field as $error) {
                $this->get('session')->getFlashBag()->add('error', $error);
            }
        }

        $referer = $request->headers->get('referer');
        if ($referer && parse_url($referer, PHP_URL_HOST) == $this->getParameter('app_domain')) {
            return new RedirectResponse($referer);
        }

        return $this->redirectToRoute('user_login');
    }

    public function connectAction(Request $request, $service)
    {
        /** @var ApiResponse $apiResponse */
        $apiResponse = $this->get('core.oauth.model')->getServiceUrl($service);

        if ($apiResponse->code === ApiResponse::SUCCESS) {
            header('Location:' . $apiResponse->embedded['url']);
            exit;
        }

        return $this->redirectWithErrors($request, $apiResponse->embedded['errors']);
    }

    public function callbackAction(Request $request, $service)
    {
        $parameters = $request->query->all();
        $errors = ['#' => ['An error has occurred.']];

        if (isset($parameters['code'])) {
            /** @var ApiResponse $apiResponse */
            $apiResponse = $this->get('core.oauth.model')->getUserData(
                $service,
                $parameters['code'],
                isset($parameters['state']) ?? null
            );

            if ($apiResponse->code === ApiResponse::SUCCESS) {
                $userData = $apiResponse->embedded['user'];

                if (isset($userData['id']) && isset($userData['email'])) {
                    $responseDataUser = $this->get(UserModel::class)->oauth($service, $userData);

                    if ($responseDataUser['valid']) {
                        //return $this->render('User/Oauth/popup.html.twig', ['errors' => []]);
                        return $this->redirectToRoute($this->getParameter('user_login'));
                    } else {
                        $errors = $responseDataUser['errors'];
                    }
                } else {
                    $errors = ['#' => ['Permissions are not sufficient.']];
                }
            } else {
                $errors = ['#' => ['Connection was interrupted.']];
            }
        } elseif (isset($parameters['error'])) {
            $parameters['error']; //access_denied
            $parameters['error_description']; //Permissions error
            $parameters['error_reason']; //user_denied

            $errors = ['#' => ['Connection was canceled.']];
        }

        //return $this->render('User/Oauth/popup.html.twig', ['errors' => $errors]);
        return $this->redirectWithErrors($request, $errors);
    }
}
