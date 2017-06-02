<?php
namespace App\Controller\Backoffice;

use JavierEguiluz\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BackofficeController extends BaseAdminController
{
    private function checkEntityPermissions(Request $request)
    {
        $easyAdmin = isset($this->request) ? $this->request->attributes->get('easyadmin') : $request->attributes->get('easyadmin');
        if (isset($easyAdmin['entity']['role'])) {
            $requiredPermission = $easyAdmin['entity']['role'];
            $this->denyAccessUnlessGranted(
                $requiredPermission, null, $requiredPermission . ' permission required'
            );
        }
    }

    protected function initialize(Request $request)
    {
        $response = parent::initialize($request);
        $this->checkEntityPermissions($request);
        return $response;
    }
}
