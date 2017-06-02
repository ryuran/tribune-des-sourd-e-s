<?php
namespace App\Controller\Backoffice;

use App\Entity\User;

class UserController extends BackofficeController
{
    /**
     * @param User $entity
     */
    public function prePersistEntity($entity)
    {
        if ($entity->getPlainPassword()) {
            $entity->setPassword(
                $this->get('security.password_encoder')->encodePassword($entity, $entity->getPlainPassword())
            );
        }
        parent::prePersistEntity($entity);
    }

    /**
     * @param User $entity
     * @param string $view
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function createEntityFormBuilder($entity, $view)
    {
        /** @var FormBuilder $form */
        $form = parent::createEntityFormBuilder($entity, $view);

        return $form;
    }

    public function undeleteAction()
    {
        $id = $this->request->query->get('id');
        /** @var User $entity */
        $entity = $this->em->getRepository('App:User')->find($id);
        $entity->setDeletedAt();
        $this->em->flush();

        $refererUrl = $this->request->query->get('referer', '');
        return !empty($refererUrl)
            ? $this->redirect(urldecode($refererUrl))
            : $this->redirect($this->generateUrl(
                'easyadmin', [
                    'action' => 'search',
                    'entity' => $this->request->query->get('entity'),
                    'query' => $this->request->query->get('query'),
                    'sortField' => $this->request->query->get('sortField'),
                    'sortDirection' => $this->request->query->get('sortDirection'),
                    'page' => $this->request->query->get('page'),
                ]));
    }
}
