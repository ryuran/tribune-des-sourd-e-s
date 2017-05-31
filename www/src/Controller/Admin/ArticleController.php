<?php
namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\Tag;
use App\Helper\StringHelper;
use JavierEguiluz\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;

class ArticleController extends BaseAdminController
{
    public function createNewEntity()
    {
        /** @var Article $article */
        $article = parent::createNewEntity();
        $article->setUserId(1);
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
