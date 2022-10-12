<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity]
#[ORM\Table(name: 'address')]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(type: 'string', length: 1024, nullable: true)]
    #[Assert\NotBlank]
    private ?string $streetName = null;

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    #[Assert\NotBlank]
    private ?string $streetNumber = null;

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $apartmentNumber = null;

    #[ORM\Column(type: 'string', length: 1024, nullable: true)]
    #[Assert\NotBlank]
    private ?string $city = null;

    #[ORM\Column(type: 'string', length: 1024, nullable: true)]
    #[Assert\NotBlank]
    private ?string $voivodeship = null;

    #[ORM\Column(type: 'string', length: 1024, nullable: true)]
    #[Assert\NotBlank]
    private ?string $country = null;

    #[ORM\Column(type: 'string', length: 8, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^[0-9]{2}-[0-9]{3}$/')]
    private ?string $zipCode = null;

    public function __toString()
    {
        $aptNumText = $this->apartmentNumber?("m. " . $this->apartmentNumber . "\n"):"\n";
        return "{$this->streetName} {$this->streetNumber} {$aptNumText}{$this->zipCode} {$this->city},\n{$this->voivodeship}\n{$this->country}";
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }


}