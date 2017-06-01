<?php
namespace App\Controller\Backoffice;

use App\Entity\Article;
use App\Entity\Tag;
use App\Entity\User;
use App\Helper\StringHelper;
use Symfony\Component\HttpFoundation\Request;

class ArticleController extends BackofficeController
{
    private function checkPermissions()
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->hasRole(User::ROLES['admin'])) {
            /** @var Article $article */
            $article = $this->entity;

            if (is_object($article) && $article->getUserId() !== $user->getId()) {
                $this->denyAccessUnlessGranted(
                    $user->getId(), $article, 'You do not have access to this article.'
                );
            }
        }
    }

    protected function initialize(Request $request)
    {
        $response = parent::initialize($request);
        $this->checkPermissions($request);
        return $response;
    }

    public function createNewEntity()
    {
        /** @var Article $article */
        $article = parent::createNewEntity();
        $article->setUser($this->getUser());
        return $article;
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
        $this->fillImageUrl($article);
        $this->addNewTags($article);

        parent::preUpdateEntity($article);
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
                    ->findOneBySlug(StringHelper::slugify($tag));

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
}
