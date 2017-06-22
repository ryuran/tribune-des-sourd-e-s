<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FavoriteRepository")
 * @ORM\Table(name="favorite", uniqueConstraints={@ORM\UniqueConstraint(name="favorite_unique", columns={"user_id", "article_id"})})
 */
class Favorite
{
    public function __construct()
    {
    }

    /**
     * @var integer
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @return null|integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="User", inversedBy="favorites")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
    /**
     * @return User|null
     */
    public function getUser()
    {
        return $this->user;
    }
    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;
        if ($user !== null) {
            $this->userId = $user->getId();
        }
        return $this;
    }

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    private $userId;
    /**
     * @return null|int
     */
    public function getUserId()
    {
        return $this->userId;
    }
    /**
     * @param integer $userId
     *
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @var Article|null
     * @ORM\ManyToOne(targetEntity="Article", inversedBy="favorites")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     */
    protected $article;
    /**
     * @return Article|null
     */
    public function getArticle()
    {
        return $this->article;
    }
    /**
     * @param Article $article
     *
     * @return $this
     */
    public function setArticle($article)
    {
        $this->article = $article;
        return $this;
    }

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    private $articleId;
    /**
     * @return null|int
     */
    public function getArticleId()
    {
        return $this->articleId;
    }
    /**
     * @param integer $articleId
     *
     * @return $this
     */
    public function setArticleId($articleId)
    {
        $this->articleId = $articleId;
        return $this;
    }

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $createdAt;
    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
    /**
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
