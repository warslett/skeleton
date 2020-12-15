<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\PasswordResetToken;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class PasswordResetTokenRepository
{
    private EntityRepository $doctrineRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        /** @var EntityRepository $doctrineRepository */
        $doctrineRepository = $entityManager->getRepository(PasswordResetToken::class);
        $this->doctrineRepository = $doctrineRepository;
    }

    /**
     * @param string $token
     * @return PasswordResetToken|null
     */
    public function findOneByToken(string $token): ?PasswordResetToken
    {
        /** @var ?PasswordResetToken $registrationToken */
        $registrationToken = $this->doctrineRepository->findOneBy([
            'token' => hash(PasswordResetToken::TOKEN_HASHING_ALGORITHM, $token)
        ]);
        return $registrationToken;
    }

    /**
     * @param string $token
     * @return int
     */
    public function countByToken(string $token): int
    {
        return $this->doctrineRepository->count([
            'token' => hash(PasswordResetToken::TOKEN_HASHING_ALGORITHM, $token)
        ]);
    }
}
