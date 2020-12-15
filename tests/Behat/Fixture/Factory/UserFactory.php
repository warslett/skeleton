<?php

declare(strict_types=1);

namespace App\Tests\Behat\Fixture\Factory;

use App\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Uid\Ulid;

class UserFactory
{
    public const DEFAULT_PASSWORD = 'development';

    private EntityManagerInterface $entityManager;
    private UserPasswordEncoderInterface $passwordEncoder;
    private Generator $faker;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder,
        Generator $faker
    ) {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->faker = $faker;
    }

    public function createUser(array $properties = []): User
    {
        $user = new User();
        /** @var Ulid|null $ulid */
        $ulid = isset($properties['ulid']) ? Ulid::fromString($properties['ulid']) : null;
        $user->setUlid($ulid);
        $user->setEmail($properties['email'] ?? $this->faker->email);
        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,
                $properties['password'] ?? self::DEFAULT_PASSWORD
            )
        );
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $user;
    }
}
