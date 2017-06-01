<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="category",indexes={@ORM\Index(name="search_idx", columns={"slug", "name"})})
 */
class Category
{
    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
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
     * @ORM\ManyToMany(targetEntity="Article", mappedBy="categories")
     */
    private $articles;
    /**
     * @return ArrayCollection
     */
    public function getArticles()
    {
        return $this->articles;
    }
    /**
     * @param Article $article
     *
     * @return $this
     */
    public function addArticle(Article $article)
    {
        $this->articles[] = $article;
        return $this;
    }
    /**
     * @param Article $article
     *
     * @return $this
     */
    public function removeArticle(Article $article)
    {
        $this->articles->removeElement($article);
        return $this;
    }

    /**
     * @var null|string
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $name;
    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @var null|string
     * @ORM\Column(type="string", length=255, unique=true)
     * @Gedmo\Slug(fields={"name"})
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
     * @var null|string
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $imageName;
    /**
     * @return null|string
     */
    public function getImageName()
    {
        return $this->imageName;
    }
    /**
     * @return null|string
     */
    public function getImage()
    {
        return $this->getImageName();
    }
    /**
     * @param string $imageName
     *
     * @return $this
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;
        return $this;
    }
}
