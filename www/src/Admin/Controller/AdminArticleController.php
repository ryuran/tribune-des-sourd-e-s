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
        $this->fillImageUrl($article);

        parent::prePersistEntity($article);
    }

    /**
     * @param Article $article
     */
    public function preUpdateEntity($article)
    {
        $this->fillImageUrl($article);

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
}
