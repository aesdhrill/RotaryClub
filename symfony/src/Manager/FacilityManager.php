<?php

namespace App\Manager;

use App\Entity\Facility;
use Doctrine\ORM\EntityManagerInterface;

class FacilityManager
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function save(Facility $facility): void
    {
        $this->entityManager->persist($facility);
        $this->entityManager->flush();
    }
}