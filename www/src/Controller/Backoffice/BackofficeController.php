<?php
namespace App\Controller\Backoffice;

use App\Entity\User;
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

    private function disableSoftDeletable(Request $request)
    {
        $action = $request->query->get('action');
        $entity = $request->query->get('entity');

        if ($action !== 'list' && in_array($entity, ['Article', 'User'])) {
            /** @var User $user */
            $user = $this->getUser();
            if ($user->hasRole(User::ROLES['admin'])) {
                $this->get("doctrine.orm.default_entity_manager")->getFilters()->enable('softdeleteable')
                    ->disableForEntity('App\Entity\\' . $entity);
            }
        }
    }

    protected function initialize(Request $request)
    {
        $this->disableSoftDeletable($request);
        $response = parent::initialize($request);
        $this->checkEntityPermissions($request);
        return $response;
    }
}
