<?php
namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Favorite;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ArticleController extends Controller
{
    /**
     * Renvoi les derniers reportages les plus récents
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $articles = $this->getDoctrine()->getManager()->getRepository('App:Article')->findBy(
            [], ['updatedAt' => 'DESC']
        );

        return $this->render('Article/index.html.twig', ['articles' => $articles]);
    }

    /**
     * Réenvoi les reportages les plus vues, favorisés et récents
     *
     * @param $number
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function topAction($number)
    {
        if (!is_int($number)) {
            $this->redirectToRoute('article_top');
        }

        $articles = $this->getDoctrine()->getManager()->getRepository('App:Article')->findBy(
            [], ['viewCount' => 'DESC', 'favoriteCount' => 'DESC', 'updatedAt' => 'DESC'], $number
        );

        return $this->render('Article/top.html.twig', ['number' => $number, 'articles' => $articles]);
    }

    /**
     * Renvoi la catégorie et ses reportages
     *
     * @param Category $category
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function categoryAction(Category $category)
    {
        return $this->render('Article/category.html.twig', ['category' => $category]);
    }

    /**
     * Renvoi la liste des catégories
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function categoriesAction()
    {
        $categories = $this->getDoctrine()->getManager()->getRepository('App:Category')->findBy(
            [], ['name' => 'ASC']
        );

        return $this->render('Article/categories.html.twig', ['categories' => $categories]);
    }

    /**
     * Renvoi un article
     *
     * @param Article $article
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function articleAction(Article $article)
    {
        /** @var ObjectManager $em */
        $em = $this->getDoctrine()->getManager();

        $article->increaseViewCount();
        $em->persist($article);
        $em->flush();

        return $this->render('Article/article.html.twig', ['article' => $article]);
    }

    /**
     * Renvoi les reportages favoris
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function favoritesAction()
    {
        return $this->render('Article/favorites.html.twig');
    }

    /**
     * Met en favori un reportage
     *
     * @param Article $article
     */
    public function favoriteAction(Article $article)
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var ObjectManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var UserRepository $userRepository */
        $favoriteRepository = $em->getRepository('App:Favorite');
        /** @var Favorite $favorite */
        $favorite = $favoriteRepository->exist($user, $article)->getQuery()->getOneOrNullResult();

        if ($favorite !== null) {
            exit;
        }

        $favorite = new Favorite();
        $favorite->setUser($user);
        $favorite->setArticle($article);

        $article->increaseFavoriteCount();

        $em->persist($favorite);
        $em->persist($article);
        $em->flush();
    }

    /**
     * Enlève un reportage des favoris
     *
     * @param Article $article
     */
    public function unfavoriteAction(Article $article)
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var ObjectManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var UserRepository $userRepository */
        $favoriteRepository = $em->getRepository('App:Favorite');
        /** @var Favorite $favorite */
        $favorite = $favoriteRepository->exist($user, $article)->getQuery()->getOneOrNullResult();

        if ($favorite === null) {
            exit;
        }

        $article->decreaseFavoriteCount();

        $em->remove($favorite);
        $em->persist($article);
        $em->flush();
    }
}
