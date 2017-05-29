<?php
namespace App\Service;

use Symfony\Component\Routing\Router;
use OAuth\Common\Service\ServiceInterface;
use OAuth\ServiceFactory;
use OAuth\OAuth2\Service\Facebook;
use OAuth\OAuth2\Service\Google;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use App\Utils\ApiResponse;

class Oauth
{
    private $router;
    private $session;
    private $serviceFactory;
    private $params = [];

    public function __construct(Router $router, $params_auth)
    {
        $this->router = $router;
        $this->params['oauth'] = $params_auth;

        $this->session = new Session();
        $this->serviceFactory = new ServiceFactory();
    }

    private function getService($serviceName): ServiceInterface
    {
        if (!isset($this->params['oauth'][$serviceName])) {
            return null;
        }

        $credentials = new Credentials(
            $this->params['oauth'][$serviceName]['key'],
            $this->params['oauth'][$serviceName]['secret'],
            $this->router->generate('user_oauth_callback', ['service' => $serviceName], Router::ABSOLUTE_URL)
        );

        $scope = [];
        switch ($serviceName) {
            case 'facebook':
                $scope = [Facebook::SCOPE_PUBLIC_PROFILE, Facebook::SCOPE_EMAIL];
                break;
            case 'google':
                $scope = [Google::SCOPE_PROFILE, Google::SCOPE_EMAIL];//'userinfo_email', 'userinfo_profile'
                break;
        }

        return $this->serviceFactory->createService($serviceName, $credentials, $this->session, $scope);
    }

    public function getServiceUrl($serviceName): ApiResponse
    {
        if (!isset($this->params['oauth'][$serviceName])) {
            return ApiResponse::returnErrorsResponse([
                '#' => ['This service is not configured.']
            ]);
        }

        if (isset($this->params['oauth'][$serviceName])) {
            $url = $this->getService($serviceName)->getAuthorizationUri();
            return ApiResponse::returnValidDataResponse(['url' => $url]);
        }
        return ApiResponse::returnErrorsResponse([
            '#' => ['Unable to connect to this service.']
        ]);
    }

    //public function getUserData($serviceName, $code, $state): ApiResponse
    public function getUserData($serviceName): ApiResponse
    {
        $service = $this->getService($serviceName);

        if (!$service) {
            return ApiResponse::returnErrorsResponse([
                '#' => ['This service is not configured.']
            ]);
        }

        $user = [];

        try {
            //$token = $service->requestAccessToken($code, $state);

            switch ($serviceName) {
                case 'facebook':
                    $user = json_decode($service->request('/me?fields=id,email'), true);
                    break;

                case 'google':
                    $user = json_decode($service->request('userinfo'), true);
                    break;
            }
        } catch (\Exception $e) {
            return ApiResponse::returnErrorsResponse([
                '#' => [$e->getMessage()]
            ]);
        }

        return ApiResponse::returnValidDataResponse(['user' => $user]);
    }
}
