<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\RegistrationToken;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class RegistrationTokenRepository
{
    private EntityRepository $doctrineRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        /** @var EntityRepository $doctrineRepository */
        $doctrineRepository = $entityManager->getRepository(RegistrationToken::class);
        $this->doctrineRepository = $doctrineRepository;
    }

    /**
     * @param string $token
     * @return RegistrationToken|null
     */
    public function findOneByToken(string $token): ?RegistrationToken
    {
        /** @var ?RegistrationToken $registrationToken */
        $registrationToken = $this->doctrineRepository->findOneBy([
            'token' => hash(RegistrationToken::TOKEN_HASHING_ALGORITHM, $token)
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
            'token' => hash(RegistrationToken::TOKEN_HASHING_ALGORITHM, $token)
        ]);
    }
}
