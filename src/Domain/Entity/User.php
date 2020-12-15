<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Serializable;
use Symfony\Component\Uid\Ulid;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 */
class User implements UserInterface, Serializable
{

    /**
     * @ORM\Id
     * @ORM\Column(type="ulid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UlidGenerator::class)
     * @var Ulid|null
     */
    private ?Ulid $ulid = null;

    /**
     * @ORM\Column(type="string", length=254, unique=true)
     * @var string|null
     */
    private ?string $email = null;

    /**
     * @ORM\Column(type="string", length=64)
     * @var string|null
     */
    private ?string $password = null;

    /**
     * @var string|null
     */
    private ?string $plainPassword = null;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     * @var bool
     */
    private bool $isActive = true;

    /**
     * @codeCoverageIgnore
     * @return Ulid|null
     */
    public function getUlid(): ?Ulid
    {
        return $this->ulid;
    }

    /**
     * @codeCoverageIgnore
     * @param Ulid|null $ulid
     */
    public function setUlid(?Ulid $ulid): void
    {
        $this->ulid = $ulid;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->email ?? '';
    }

    /**
     * @codeCoverageIgnore
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @codeCoverageIgnore
     * @param string $email
     * @return void
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @codeCoverageIgnore
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @codeCoverageIgnore
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @codeCoverageIgnore
     * @return string|null
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @codeCoverageIgnore
     * @param string $plainPassword
     */
    public function setPlainPassword(string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @codeCoverageIgnore
     * @return string|null
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @codeCoverageIgnore
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    /**
     * @return void
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        return serialize([$this->ulid, $this->email, $this->password]);
    }

    /**
     * @param string $serialized
     * @return void
     */
    public function unserialize($serialized): void
    {
        [$this->ulid, $this->email, $this->password] = unserialize($serialized);
    }
}
