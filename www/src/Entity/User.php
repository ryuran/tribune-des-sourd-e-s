<?php
namespace App\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="user",indexes={@ORM\Index(name="search_idx", columns={"username", "email"})})
 */
class User implements UserInterface
{
    /**
     * Liste des types d'email (sous la forme d'une liste de clé) qui pourronts lui être envoyé
     * Permet de configurer s'il souhaite les recevoir où pas
     */
    const EMAIL_KEYS = [
      'newsletter' => false
    ];
    /**
     * Liste des rôles utilisateurs
     */
    const ROLES = [
        'user' => 'ROLE_USER',
        'contributor' => 'ROLE_CONTRIBUTOR',
        'admin' => 'ROLE_ADMIN'
    ];
    /**
     * Liste des états
     */
    const STATES = [
        'disabled' => 0,
        'wait_validation' => 1,
        'active' => 2
    ];

    public function __construct()
    {
        $this->salt = base_convert(sha1('salt' . uniqid(mt_rand(), true)), 16, 36);
        $this->roles = [self::ROLES['user']];
        $this->state = self::STATES['disabled'];
        $this->enabledEmails = [];
        $this->locale = 'fr';
        $this->initToken();
        $this->articles = new ArrayCollection();
        $this->favorites = new ArrayCollection();
        $this->messagesFrom = new ArrayCollection();
        $this->messagesTo = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getUsername();
    }

    /**
     * @var null|integer
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
     * @ORM\OneToMany(targetEntity="Article", mappedBy="user")
     */
    protected $articles;
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
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Favorite", mappedBy="user")
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
     * @ORM\OneToMany(targetEntity="Message", mappedBy="userFrom")
     */
    protected $messagesFrom;
    /**
     * @return ArrayCollection
     */
    public function getMessagesFrom()
    {
        return $this->messagesFrom;
    }
    /**
     * @param Message $messageFrom
     *
     * @return $this
     */
    public function addMessageFrom(Message $messageFrom)
    {
        $this->messagesFrom[] = $messageFrom;
        return $this;
    }
    /**
     * @param Message $messageFrom
     *
     * @return $this
     */
    public function removeMessageFrom(Message $messageFrom)
    {
        $this->messagesFrom->removeElement($messageFrom);
        return $this;
    }

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Message", mappedBy="userTo")
     */
    protected $messagesTo;
    /**
     * @return ArrayCollection
     */
    public function getMessagesTo()
    {
        return $this->messagesTo;
    }
    /**
     * @param Message $messageTo
     *
     * @return $this
     */
    public function addMessageTo(Message $messageTo)
    {
        $this->messagesTo[] = $messageTo;
        return $this;
    }
    /**
     * @param Message $messageTo
     *
     * @return $this
     */
    public function removeMessageTo(Message $messageTo)
    {
        $this->messagesTo->removeElement($messageTo);
        return $this;
    }

    /**
     * @var integer
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(groups={"Admin"})
     */
    protected $state;
    /**
     * @return integer
     */
    public function getState()
    {
        return $this->state;
    }
    /**
     * @return string
     */
    public function getStateName()
    {
        return array_search($this->state, self::STATES);
    }
    /**
     * @param integer $state
     *
     * @return $this
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @var null|string
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(groups={"Login", "Register", "Forget", "Admin"})
     * @Assert\Regex(
     *     pattern= "/^[a-z0-9-_]{3,16}$/i",
     *     htmlPattern= "^[a-zA-Z0-9-_]+$",
     *     message= "Only 3-16 alphanumeric characters.",
     *     groups={"Register", "Edit", "Admin"}
     * )
     */
    protected $username;
    /**
     * @return null|string
     */
    public function getUsername()
    {
        return $this->username;
    }
    /**
     * @param string $username
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }


    /**
     * @var null|string
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(groups={"Register", "Admin"})
     * @Assert\Email(
     *     message= "The email is not a valid email.",
     *     checkMX= true,
     *     groups={"Register", "Edit", "Admin"}
     * )
     */
    protected $email;
    /**
     * @return null|string
     */
    public function getEmail()
    {
        return $this->email;
    }
    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @var null|string
     * @ORM\Column(type="string", length=255)
     */
    protected $salt;
    /**
     * @return null|string
     */
    public function getSalt()
    {
        return $this->salt;
    }
    /**
     * @param string $salt
     *
     * @return $this
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
        return $this;
    }

    /**
     * @var null|string
     * @ORM\Column(type="string", length=255)
     */
    protected $password;
    /**
     * @var null|string
     * @Assert\NotBlank(groups={"Login", "Register", "Reset"})
     */
    protected $plainPassword;
    /**
     * @return null|string
     */
    public function getPassword()
    {
        return $this->password;
    }
    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }
    /**
     * @param string $plainPassword
     *
     * @return $this
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }
    public function eraseCredentials()
    {
    }

    /**
     * @var array
     * @ORM\Column(type="array")
     * @Assert\NotBlank(groups={"Admin"})
     */
    protected $roles;
    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }
    /**
     * @param array $roles
     *
     * @return $this
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
        return $this;
    }
    /**
     * @return string
     */
    public function getRole()
    {
        return $this->getRoles()[0];
    }
    /**
     * @return null|string
     */
    public function getRoleName()
    {
        return array_search($this->getRole(), self::ROLES);
    }
    /**
     * @param string $role
     *
     * @return $this
     */
    public function setRole($role)
    {
        $this->roles = [$role];
        return $this;
    }

    /**
     * @var array
     * @ORM\Column(type="json_array")
     */
    protected $enabledEmails;
    /**
     * @return array
     */
    public function getEnabledEmails()
    {
        return array_merge(self::EMAIL_KEYS, $this->enabledEmails);
    }
    /**
     * @param array $enabledEmails
     *
     * @return $this
     */
    public function setEnabledEmails($enabledEmails)
    {
        $this->enabledEmails = $enabledEmails;
        return $this;
    }

    /**
     * @var string
     * @ORM\Column(type="string", length=10)
     */
    protected $locale;
    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
    /**
     * @param string $locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $token;
    /**
     * @return string
     */
    public function getToken()
    {
        return $this->locale;
    }
    /**
     * @param string $token
     *
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }
    public function initToken()
    {
        $this->setToken(base_convert(sha1('token' . uniqid(mt_rand(), true)), 16, 36));
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
