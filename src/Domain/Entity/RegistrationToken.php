<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class RegistrationToken
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
     * @ORM\Column(type="string")
     * @var string
     */
    private string $email;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $expiry;

    /**
     * @param string $token
     * @param string $email
     * @param DateTimeImmutable $expiry
     */
    public function __construct(string $token, string $email, DateTimeImmutable $expiry)
    {
        $this->token = hash(self::TOKEN_HASHING_ALGORITHM, $token);
        $this->tokenPlainText = $token;
        $this->email = $email;
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
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
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
