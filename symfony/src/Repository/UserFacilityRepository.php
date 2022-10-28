<?php

namespace App\Repository;

use App\Entity\UserFacility;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserFacilityRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, UserFacility::class);
    }
}