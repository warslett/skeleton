<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class PasswordResetToken
{
    public const TOKEN_HASHING_ALGORITHM = 'sha256';

    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     * @var string
     */
    private string $token;

    /**
     * @var string|null
     */
    private ?string $tokenPlainText = null;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_ulid", referencedColumnName="ulid")
     */
    private User $user;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $expiry;

    public function __construct(string $token, User $user, DateTimeImmutable $expiry)
    {
        $this->token = hash(self::TOKEN_HASHING_ALGORITHM, $token);
        $this->tokenPlainText = $token;
        $this->user = $user;
        $this->expiry = $expiry;
    }

    /**
     * @return string
     */
    public function getHashedToken(): string
    {
        return $this->token;
    }

    /**
     * @return string|null
     */
    public function getTokenPlainText(): ?string
    {
        return $this->tokenPlainText;
    }

    /**
     * @codeCoverageIgnore
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @codeCoverageIgnore
     * @return DateTimeImmutable
     */
    public function getExpiry(): DateTimeImmutable
    {
        return $this->expiry;
    }

    /**
     * @codeCoverageIgnore
     * @param DateTimeImmutable $expiry
     */
    public function setExpiry(DateTimeImmutable $expiry): void
    {
        $this->expiry = $expiry;
    }
}
