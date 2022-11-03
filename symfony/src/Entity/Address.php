<?php

namespace App\Entity;

use App\Enum\Voivodeship;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: AddressRepository::class)]
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

    #[ORM\Column(type: 'string', length: 6, nullable: true)]
    #[Assert\Regex('/^[\d]{2}-[\d]{3}$/')]
    private ?string $postalCode = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\Choice(callback: [Voivodeship::class, 'getValues'])]
    private ?int $voivodeship = null;

    #[ORM\Column(type: 'string', length: 1024, nullable: true)]
    #[Assert\NotBlank]
    private ?string $country = null;

    public function __toString()
    {
        $aptNumText = $this->apartmentNumber?("m. " . $this->apartmentNumber . "\n"):"\n";
        return "{$this->streetName} {$this->streetNumber} {$aptNumText}{$this->postalCode} {$this->city},\n{$this->voivodeship}\n{$this->country}";
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getStreetName(): ?string
    {
        return $this->streetName;
    }

    /**
     * @param string|null $streetName
     */
    public function setStreetName(?string $streetName): void
    {
        $this->streetName = $streetName;
    }

    /**
     * @return string|null
     */
    public function getStreetNumber(): ?string
    {
        return $this->streetNumber;
    }

    /**
     * @param string|null $streetNumber
     */
    public function setStreetNumber(?string $streetNumber): void
    {
        $this->streetNumber = $streetNumber;
    }

    /**
     * @return string|null
     */
    public function getApartmentNumber(): ?string
    {
        return $this->apartmentNumber;
    }

    /**
     * @param string|null $apartmentNumber
     */
    public function setApartmentNumber(?string $apartmentNumber): void
    {
        $this->apartmentNumber = $apartmentNumber;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     */
    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return string|null
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * @param string|null $postalCode
     */
    public function setPostalCode(?string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @return int|null
     */
    public function getVoivodeship(): ?int
    {
        return $this->voivodeship;
    }

    /**
     * @param int|null $voivodeship
     */
    public function setVoivodeship(?int $voivodeship): void
    {
        $this->voivodeship = $voivodeship;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string|null $country
     */
    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

}