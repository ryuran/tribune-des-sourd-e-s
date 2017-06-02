<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArticleRepository")
 * @ORM\Table(name="article",indexes={@ORM\Index(name="search_idx", columns={"slug", "title"})})
 */
class Article
{
    public function __construct()
    {
        $this->favorites = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->newTags = [];
        $this->viewCount = 0;
        $this->favoriteCount = 0;
    }

    public function __toString()
    {
        return $this->getTitle();
    }

    /**
     * @var integer
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    public function getId()
    {
        return $this->id;
    }

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="User", inversedBy="articles")
     * @ORM\JoinColumn(name="userId", referencedColumnName="id")
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
     * @Assert\NotBlank()
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
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Favorite", mappedBy="article")
     */
    protected $favorites;
    /**
     * @return ArrayCollection
     */
    public function getFavorites()
    {
        return $this->favorites;
    }
    /**
     * @param Favorite $favorite
     *
     * @return $this
     */
    public function addFavorite(Favorite $favorite)
    {
        $this->favorites[] = $favorite;
        return $this;
    }
    /**
     * @param Favorite $favorite
     *
     * @return $this
     */
    public function removeFavorite(Favorite $favorite)
    {
        $this->favorites->removeElement($favorite);
        return $this;
    }

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Category", inversedBy="articles")
     * @ORM\JoinTable(name="articles_categories",
     *      joinColumns={@ORM\JoinColumn(name="article_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")}
     * )
     */
    private $categories;
    /**
     * @return ArrayCollection
     */
    public function getCategories()
    {
        return $this->categories;
    }
    /**
     * @param Category $category
     *
     * @return $this
     */
    public function addCategory(Category $category)
    {
        $this->categories[] = $category;
        return $this;
    }
    /**
     * @param Category $category
     *
     * @return $this
     */
    public function removeCategory(Category $category)
    {
        $this->categories->removeElement($category);
        return $this;
    }

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="articles")
     * @ORM\JoinTable(name="articles_tags",
     *      joinColumns={@ORM\JoinColumn(name="article_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")}
     * )
     */
    private $tags;
    /**
     * @return ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
    }
    /**
     * @param Tag $tag
     *
     * @return $this
     */
    public function addTag(Tag $tag)
    {
        $this->tags[] = $tag;
        return $this;
    }
    /**
     * @param Tag $tag
     *
     * @return $this
     */
    public function removeTag(Tag $tag)
    {
        $this->tags->removeElement($tag);
        return $this;
    }
    /** @var array */
    private $newTags;
    public function getNewTags()
    {
        return $this->newTags;
    }
    public function setNewTags($newTags)
    {
        $this->newTags = $newTags;
        return $this;
    }

    /**
     * @var null|string
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $title;
    /**
     * @return null|string
     */
    public function getTitle()
    {
        return $this->title;
    }
    /**
     * @param null|string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @var null|string
     * @ORM\Column(type="string", length=255, unique=true)
     * @Gedmo\Slug(fields={"title"})
     */
    protected $slug;
    /**
     * @return null|string
     */
    public function getSlug()
    {
        return $this->slug;
    }
    /**
     * @param string $slug
     *
     * @return $this
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;
    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }
    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @var null|string
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Url()
     */
    protected $videoUrl;
    /**
     * @return null|string
     */
    public function getVideoUrl()
    {
        return $this->videoUrl;
    }
    /**
     * @param string $videoUrl
     *
     * @return $this
     */
    public function setVideoUrl($videoUrl)
    {
        $this->videoUrl = $videoUrl;
        return $this;
    }

    /**
     * @var null|string
     * @ORM\Column(type="string", length=255)
     */
    protected $imageUrl;
    /**
     * @return null|string
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }
    /**
     * @param string $imageUrl
     *
     * @return $this
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    /**
     * @var null|string
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Url()
     */
    protected $sourceUrl;
    /**
     * @return null|string
     */
    public function getSourceUrl()
    {
        return $this->sourceUrl;
    }
    /**
     * @param string $sourceUrl
     *
     * @return $this
     */
    public function setSourceUrl($sourceUrl)
    {
        $this->sourceUrl = $sourceUrl;
        return $this;
    }

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    protected $viewCount;
    /**
     * @return integer
     */
    public function getViewCount()
    {
        return $this->viewCount;
    }
    /**
     * @param integer $viewCount
     *
     * @return $this
     */
    public function setViewCount($viewCount)
    {
        $this->viewCount = $viewCount;
        return $this;
    }
    /**
     * Ajoute un décompte de vue
     * @return $this
     */
    public function increaseViewCount()
    {
        $this->viewCount++;
        return $this;
    }

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    protected $favoriteCount;
    /**
     * @return integer
     */
    public function getFavoriteCount()
    {
        return $this->favoriteCount;
    }
    /**
     * @param integer $favoriteCount
     *
     * @return $this
     */
    public function setFavoriteCount($favoriteCount)
    {
        $this->favoriteCount = $favoriteCount;
        return $this;
    }
    /**
     * Ajoute un décompte de favori
     * @return $this
     */
    public function increaseFavoriteCount()
    {
        $this->favoriteCount++;
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

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    protected $updatedAt;
    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
    /**
     * @param $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
