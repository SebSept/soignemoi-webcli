<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Security;

use Exception;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    /**
     * @var string[] The user roles
     */
    private array $roles = [];

    private string $token;

    private ?int $id = null;

    public function __construct(private string $email)
    {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * role sans ROLE_ en minuscule.
     */
    public function getRole(): string
    {
        if (!isset($this->getRoles()[0])) {
            return '';
        }

        return strtolower(str_replace('ROLE_', '', $this->getRoles()[0]));
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): static
    {
        if (count($roles) > 1) {
            throw new Exception('Un seul role possible dans notre systeme.');
        }

        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }
}
