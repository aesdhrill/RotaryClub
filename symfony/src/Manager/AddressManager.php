<?php

namespace App\Manager;

use App\Entity\Address;
use Doctrine\ORM\EntityManagerInterface;

class AddressManager
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function save(Address $address): void
    {
        $this->entityManager->persist($address);
        $this->entityManager->flush();
    }
}