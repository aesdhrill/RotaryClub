<?php

namespace App\Entity;

use App\Enum\UserStatus;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks]

#[UniqueEntity(
    fields: ['email'],
    message: 'form.new_user.already_exists'
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(type: 'text', length: 180, unique: true)]
    #[Assert\Email(groups: ['signup'])]
    private string $email;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(groups: ['signup'])]
    #[Assert\Length(min: 8, groups: ['signup'])]
    private string $password;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotBlank]
    private ?string $surname = null;

    #[ORM\OneToOne(targetEntity: 'Address')]
    #[ORM\JoinColumn(name: 'address_id', referencedColumnName: 'id', nullable:  true)]
    private ?Address $address = null;

    #[ORM\Column(type: 'integer', options: ['default' => UserStatus::INACTIVE])]
    private int $status = UserStatus::INACTIVE;

    #[ORM\Column(type: 'datetime', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTime $validTo;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: 'Token', cascade: ['persist'])]
    private Collection $tokens;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->getUserIdentifier();
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getSurname(): ?string
    {
        return $this->surname;
    }

    /**
     * @param string|null $surname
     */
    public function setSurname(?string $surname): void
    {
        $this->surname = $surname;
    }

    public function getFullname(): string
    {
        return "{$this->name} {$this->surname}";
    }

    /**
     * @return Address|null
     */
    public function getAddress(): ?Address
    {
        return $this->address;
    }

    /**
     * @param Address|null $address
     */
    public function setAddress(?Address $address): void
    {
        $this->address = $address;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * @return \DateTime
     */
    public function getValidTo(): \DateTime
    {
        return $this->validTo;
    }

    /**
     * @param \DateTime $validTo
     */
    public function setValidTo(\DateTime $validTo): void
    {
        $this->validTo = $validTo;
    }

    /**
     * @return Collection
     */
    public function getTokens(): Collection
    {
        return $this->tokens;
    }

    /**
     * @param Collection $tokens
     */
    public function setTokens(Collection $tokens): void
    {
        $this->tokens = $tokens;
    }

//    /**
//     * @return \DateTimeImmutable
//     */
//    public function getCreatedAt(): \DateTimeImmutable
//    {
//        return $this->createdAt;
//    }

//    /**
//     * @param \DateTimeImmutable $createdAt
//     */
//    #[ORM\PrePersist]
//    public function setCreatedAt(\DateTimeImmutable $createdAt): void
//    {
//        $this->createdAt = $createdAt;
//    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }
}