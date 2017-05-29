<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="room",indexes={@ORM\Index(name="search_idx", columns={"slug", "name"})})
 */
class Room
{
    public function __construct()
    {
        $this->messages = new ArrayCollection();
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
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Message", mappedBy="room")
     */
    protected $messages;
    /**
     * @return ArrayCollection
     */
    public function getMessages()
    {
        return $this->messages;
    }
    /**
     * @param Message $message
     *
     * @return $this
     */
    public function addMessage(Message $message)
    {
        $this->messages[] = $message;
        return $this;
    }
    /**
     * @param Message $message
     *
     * @return $this
     */
    public function removeMessage(Message $message)
    {
        $this->messages->removeElement($message);
        return $this;
    }

    /**
     * @var null|string
     * @ORM\Column(type="string", length=255)
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
