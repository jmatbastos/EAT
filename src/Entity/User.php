<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Users
 *
 * @ORM\Table(name="users", uniqueConstraints={@ORM\UniqueConstraint(name="index_users_on_email", columns={"email"})})
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="users_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY") 
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true, options={"default"="NULL"})
	 * @Assert\NotBlank(message="Please enter your name")  
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true) 
	 * @Assert\NotBlank(message="Please enter an email")
     * @Assert\Email()
     */
    private $email;
	
	/**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var int
     *
     * @ORM\Column(name="created_by", type="integer", nullable=true)
     */
    private $createdBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     */
    private $updatedAt;

    /**
     * @var string|null
     *
     * @ORM\Column(name="password_digest", type="string", length=255, nullable=false)
     */
    private $password;

    /**
     * @var string|null
     *
     * @ORM\Column(name="remember_digest", type="string", length=255, nullable=true)
     */
    private $rememberDigest;

    /**
     * @var string|null
     *
     * @ORM\Column(name="activation_digest", type="string", length=255, nullable=true)
     */
    private $activationDigest;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="activated", type="boolean", nullable=true)
     */
    private $activated;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="activated_at", type="datetime", nullable=true)
     */
    private $activatedAt;

    /**
     * @var string|null
     *
     * @ORM\Column(name="reset_digest", type="string", length=255, nullable=true)
     */
    private $resetDigest;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="reset_sent_at", type="datetime", nullable=true)
     */
    private $resetSentAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }
	
	 /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }
	
    /**
     * @see UserInterface
     */	
	public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?int $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

     /**
     * @see UserInterface
     */   	
	public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRememberDigest(): ?string
    {
        return $this->rememberDigest;
    }

    public function setRememberDigest(?string $rememberDigest): self
    {
        $this->rememberDigest = $rememberDigest;

        return $this;
    }

    public function getActivationDigest(): ?string
    {
        return $this->activationDigest;
    }

    public function setActivationDigest(?string $activationDigest): self
    {
        $this->activationDigest = $activationDigest;

        return $this;
    }

    public function getActivated(): ?bool
    {
        return $this->activated;
    }

    public function setActivated(?bool $activated): self
    {
        $this->activated = $activated;

        return $this;
    }

    public function getActivatedAt(): ?\DateTimeInterface
    {
        return $this->activatedAt;
    }

    public function setActivatedAt(?\DateTimeInterface $activatedAt): self
    {
        $this->activatedAt = $activatedAt;

        return $this;
    }

    public function getResetDigest(): ?string
    {
        return $this->resetDigest;
    }

    public function setResetDigest(?string $resetDigest): self
    {
        $this->resetDigest = $resetDigest;

        return $this;
    }

    public function getResetSentAt(): ?\DateTimeInterface
    {
        return $this->resetSentAt;
    }

    public function setResetSentAt(?\DateTimeInterface $resetSentAt): self
    {
        $this->resetSentAt = $resetSentAt;

        return $this;
    }
	
    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

	


}
