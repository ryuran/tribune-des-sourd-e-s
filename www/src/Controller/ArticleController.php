<?php
namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ArticleController extends Controller
{
    public function indexAction()
    {
        return $this->render('Article/index.html.twig');
    }

    public function topAction($number)
    {
        return $this->render('Article/top.html.twig', ['number' => $number]);
    }

    public function categoryAction(Category $category)
    {
        return $this->render('Article/category.html.twig', ['category' => $category]);
    }

    public function categoriesAction()
    {
        return $this->render('Article/categories.html.twig');
    }

    public function articleAction(Article $article)
    {
        return $this->render('Article/article.html.twig', ['article' => $article]);
    }

    public function favoritesAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        $articles = $user->getFavorites();

        return $this->render('Article/favorites.html.twig', ['articles' => $articles]);
    }

    public function favoriteAction(Article $article)
    {
    }

    public function unfavoriteAction(Article $article)
    {
    }
}
