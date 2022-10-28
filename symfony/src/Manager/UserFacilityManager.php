<?php

namespace App\Manager;

use App\Entity\UserFacility;
use Doctrine\ORM\EntityManagerInterface;

class UserFacilityManager
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function save(UserFacility $userFacility): void
    {
        $this->entityManager->persist($userFacility);
        $this->entityManager->flush();
    }
}