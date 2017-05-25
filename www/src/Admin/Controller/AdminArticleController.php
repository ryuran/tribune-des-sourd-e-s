<?php
namespace App\Admin\Controller;

use App\Entity\Article;
use JavierEguiluz\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;

class AdminArticleController extends BaseAdminController
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
        if (!$article->getImageUrl()) {
            $this->fillImageUrl($article);
        }

        parent::prePersistEntity($article);
    }

    /**
     * @param Article $article
     */
    public function preUpdateEntity($article)
    {
        if (!$article->getImageUrl()) {
            $this->fillImageUrl($article);
        }

        parent::preUpdateEntity($article);
    }

    /**
     * @param Article $article
     */
    private function fillImageUrl($article)
    {
        $article->setImageUrl('http://i3.ytimg.com/vi/SomeVideoIDHere/0.jpg');
    }
}
