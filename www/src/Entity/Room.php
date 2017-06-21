<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoomRepository")
 * @ORM\Table(name="room",indexes={@ORM\Index(name="search_idx", columns={"slug", "name"})})
 * @Vich\Uploadable
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
     * @param string $imageName
     *
     * @return $this
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;
        return $this;
    }

    /**
     * @Assert\File(
     *     maxSize="8M",
     *     mimeTypes={"image/png", "image/jpeg", "image/pjpeg"}
     * )
     * @Vich\UploadableField(mapping="room_images", fileNameProperty="imageName")
     * @Assert\NotBlank()
     * @var File
     */
    private $imageFile;
    /**
     * @return File
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }
    /**
     * @param File $image
     *
     * @return $this
     */
    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;
        if ($image) {
            $this->updatedAt = new \DateTime('now');
        }
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
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
