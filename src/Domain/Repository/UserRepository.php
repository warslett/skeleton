<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class UserRepository
{
    private EntityRepository $doctrineRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        /** @var EntityRepository $doctrineRepository */
        $doctrineRepository = $entityManager->getRepository(User::class);
        $this->doctrineRepository = $doctrineRepository;
    }

    /**
     * @param string $email
     * @return int
     */
    public function countByEmail(string $email): int
    {
        return $this->doctrineRepository->count([
            'email' => $email
        ]);
    }

    /**
     * @param string $email
     * @return User|null
     */
    public function findOneByEmail(string $email): ?User
    {
        /** @var User|null $user */
        $user = $this->doctrineRepository->findOneBy([
            'email' => $email
        ]);
        return $user;
    }
}
