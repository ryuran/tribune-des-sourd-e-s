<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MessageRepository")
 * @ORM\Table(name="message")
 */
class Message
{
    /**
     * @var null|integer
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="messagesFrom")
     * @ORM\JoinColumn(name="user_from_id", referencedColumnName="id")
     */
    protected $userFrom;
    /**
     * @return User|null
     */
    public function getUserFrom()
    {
        return $this->userFrom;
    }
    public function setUserFrom(User $userFrom)
    {
        $this->userFrom = $userFrom;
        if ($userFrom !== null) {
            $this->userFromId = $userFrom->getId();
        }
        return $this;
    }

    /**
     * @var null|integer
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $userFromId;
    /**
     * @return null|integer
     */
    public function getUserFromId()
    {
        return $this->userFromId;
    }
    /**
     * @param integer $userFromId
     *
     * @return $this
     */
    public function setUserFromId($userFromId)
    {
        $this->userFromId = $userFromId;
        return $this;
    }

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="User", inversedBy="messagesTo")
     * @ORM\JoinColumn(name="user_to_id", referencedColumnName="id")
     */
    protected $userTo;
    /**
     * @return User|null
     */
    public function getUserTo()
    {
        return $this->userTo;
    }
    public function setUserTo(User $userTo)
    {
        $this->userTo = $userTo;
        if ($userTo !== null) {
            $this->userTo = $userTo->getId();
        }
        return $this;
    }

    /**
     * @var null|integer
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    protected $userToId;
    /**
     * @return null|integer
     */
    public function getUserToId()
    {
        return $this->userToId;
    }
    /**
     * @param integer $userToId
     *
     * @return $this
     */
    public function setUserToId($userToId)
    {
        $this->userToId = $userToId;
        return $this;
    }

    /**
     * @var Room|null
     * @ORM\ManyToOne(targetEntity="Room", inversedBy="messages")
     * @ORM\JoinColumn(name="room_id", referencedColumnName="id")
     */
    protected $room;
    /**
     * @return Room|null
     */
    public function getRoom()
    {
        return $this->room;
    }
    public function setRoom(Room $room)
    {
        $this->room = $room;
        if ($room !== null) {
            $this->roomId = $room->getId();
        }
        return $this;
    }

    /**
     * @var null|integer
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    protected $roomId;
    /**
     * @return null|integer
     */
    public function getRoomId()
    {
        return $this->roomId;
    }
    /**
     * @param integer $roomId
     *
     * @return $this
     */
    public function setRoomId($roomId)
    {
        $this->roomId = $roomId;
        return $this;
    }

    /**
     * @var null|string
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $fileName;
    /**
     * @return null|string
     */
    public function getFileName()
    {
        return $this->fileName;
    }
    /**
     * @param string $fileName
     *
     * @return $this
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * @var null|integer
     * @ORM\Column(type="bigint")
     */
    protected $fileSize;
    /**
     * @return null|integer
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }
    /**
     * @param integer $fileSize
     *
     * @return $this
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;
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
