<?php

namespace App\Entity;

use App\Enum\TokenType;
use App\Repository\TokenRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: TokenRepository::class)]
#[ORM\Table(name: 'token')]
#[ORM\HasLifecycleCallbacks]
class Token
{
    private const TOKEN_LENGTH = 128; # max 128 for sha3-512

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(type: 'string', length: 128, nullable: false)]
    private string $value;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $validTo;

    #[ORM\Column(type: 'integer')]
    #[Assert\Choice(callback: [TokenType::class, 'getTokenTypes'])]
    private int $type;

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'], inversedBy: 'tokens')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;


    public function __construct()
    {
        $this->value = substr(str_shuffle(hash('sha3-512', microtime())), 0, self::TOKEN_LENGTH);
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
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
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }
}
