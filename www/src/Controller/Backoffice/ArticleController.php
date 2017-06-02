<?php
namespace App\Controller\Backoffice;

use App\Entity\Article;
use App\Entity\Tag;
use App\Entity\User;
use App\Helper\StringHelper;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormBuilder;

class ArticleController extends BackofficeController
{
    /**
     * @param Article $entity
     */
    private function checkObjectPermissions($entity)
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->hasRole(User::ROLES['admin'])) {
            if ($entity->getUserId() !== $user->getId() || $entity->isDeleted()) {
                $this->denyAccessUnlessGranted(
                    $user->getId(), $this->entity, 'You do not have access to this article.'
                );
            }
        }
    }

    /**
     * @return Article
     */
    public function createNewEntity()
    {
        /** @var Article $entity */
        $entity = parent::createNewEntity();
        $entity->setUser($this->getUser());
        return $entity;
    }

    /**
     * @param Article $entity
     * @param array  $entityProperties
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function createEditForm($entity, array $entityProperties)
    {
        $this->checkObjectPermissions($entity);
        return parent::createEditForm($entity, $entityProperties);
    }

    /**
     * @param Article $entity
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

    /**
     * @param Article $entity
     */
    public function prePersistEntity($entity)
    {
        $this->fillImageUrl($entity);
        $this->addNewTags($entity);

        parent::prePersistEntity($entity);
    }

    /**
     * @param Article $entity
     */
    public function preUpdateEntity($entity)
    {
        $this->checkObjectPermissions($entity);
        $this->fillImageUrl($entity);
        $this->addNewTags($entity);

        parent::preUpdateEntity($entity);
    }

    /**
     * @param Article $entity
     */
    protected function preRemoveEntity($entity)
    {
        $this->checkObjectPermissions($entity);
        return parent::preRemoveEntity($entity);
    }

    /**
     * @param Article $article
     */
    private function fillImageUrl($article)
    {
        if ($article->getVideoUrl() && !$article->getImageUrl()) {
            parse_str(parse_url($article->getVideoUrl(), PHP_URL_QUERY), $vars);
            if (isset($vars['v'])) {
                $article->setImageUrl('http://img.youtube.com/vi/' . $vars['v'] . '/mqdefault.jpg');
            }
        }
    }

    /**
     * @param Article $article
     */
    private function addNewTags($article)
    {
        $newTags = $article->getNewTags();
        if (count($newTags) > 0) {
            $existedAddedTags = [];
            $notExistedTags = [];
            /** @var Tag $tag */
            foreach ($article->getTags() as $tag) {
                $existedAddedTags[] = $tag->getSlug();
            }

            foreach ($newTags as $tag) {
                if (!in_array(StringHelper::slugify($tag), $existedAddedTags)) {
                    $notExistedTags[] = $tag;
                }
            }

            $manager = $this->getDoctrine()->getManager();

            foreach ($notExistedTags as $tag) {
                $exitedTag = $manager->getRepository('App:Tag')
                    ->findOneBy(['slug' => StringHelper::slugify($tag)]);

                if (!$exitedTag) {
                    $exitedTag = new Tag();
                    $exitedTag->setName($tag);

                    $manager->persist($exitedTag);
                    $manager->flush();
                }

                $article->addTag($exitedTag);
            }
        }
    }

    protected function createListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null)
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var QueryBuilder $query */
        $query = parent::createListQueryBuilder($entityClass, $sortDirection, $sortField, $dqlFilter);
        if (!$user->hasRole(User::ROLES['admin'])) {
            $query->andWhere('entity.userId = :userId')->setParameter('userId', $user->getId());
        }
        return $query;
    }

    protected function createSearchQueryBuilder(
        $entityClass,
        $searchQuery,
        array $searchableFields,
        $sortField = null,
        $sortDirection = null,
        $dqlFilter = null
    ) {
        /** @var User $user */
        $user = $this->getUser();
        /** @var QueryBuilder $query */
        $query = parent::createSearchQueryBuilder(
            $entityClass, $searchQuery, $searchableFields, $sortField, $sortDirection);
        if (!$user->hasRole(User::ROLES['admin'])) {
            $query->andWhere('entity.userId = :userId')->setParameter('userId', $user->getId());
        }

        return $query;
    }

    public function undeleteAction()
    {
        $id = $this->request->query->get('id');
        /** @var Article $entity */
        $entity = $this->em->getRepository('App:Article')->find($id);
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
