<?php

namespace App\Manager;

use App\Entity\Token;
use Doctrine\ORM\EntityManagerInterface;

class TokenManager
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function save(Token $token): void
    {
        $this->entityManager->persist($token);
        $this->entityManager->flush();
    }
}
