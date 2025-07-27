<?php

declare(strict_types=1);

namespace App\User\Domain;

use App\Shared\Domain\Data\ValueObject\UserId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity()]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'user_id')]
    private UserId $id;

    #[ORM\Column(length: 30)]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column(type: Types::JSON, options: [
        'jsonb' => true,
    ])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    public function __construct()
    {
        $this->id = UserId::generate();
    }

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    #[\Override]
    public function getUserIdentifier(): string
    {
        if ($this->username === null || $this->username === '') {
            throw new \LogicException('The user identifier cannot be empty.');
        }

        return $this->username;
    }

    /**
     * @return list<string>
     */
    #[\Override]
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    /**
     * @param array<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = array_values($roles);

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    #[\Override]
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    #[\Override]
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
