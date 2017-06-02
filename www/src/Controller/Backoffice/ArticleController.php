<?php
namespace App\Controller\Backoffice;

use App\Entity\Article;
use App\Entity\Tag;
use App\Entity\User;
use App\Helper\StringHelper;
use Doctrine\ORM\QueryBuilder;

class ArticleController extends BackofficeController
{
    /**
     * @param Article $article
     */
    private function checkObjectPermissions($article)
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->hasRole(User::ROLES['admin'])) {
            if ($article->getUserId() !== $user->getId()) {
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
        /** @var Article $article */
        $article = parent::createNewEntity();
        $article->setUser($this->getUser());
        return $article;
    }

    /**
     * @param Article $article
     * @param array  $entityProperties
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function createEditForm($article, array $entityProperties)
    {
        $this->checkObjectPermissions($article);
        return parent::createEditForm($article, $entityProperties);
    }

    /**
     * @param Article $article
     */
    public function prePersistEntity($article)
    {
        $this->fillImageUrl($article);
        $this->addNewTags($article);

        parent::prePersistEntity($article);
    }

    /**
     * @param Article $article
     */
    public function preUpdateEntity($article)
    {
        $this->checkObjectPermissions($article);
        $this->fillImageUrl($article);
        $this->addNewTags($article);

        parent::preUpdateEntity($article);
    }

    /**
     * @param Article $article
     */
    protected function preRemoveEntity($article)
    {
        $this->checkObjectPermissions($article);
        return parent::preRemoveEntity($article);
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
}
