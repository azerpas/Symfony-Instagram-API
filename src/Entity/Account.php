<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\component\Security\Core\user\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccountRepository")
 */
class Account implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $blacklist;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $search_settings;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $settings;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getBlacklist()
    {
        return $this->blacklist;
    }

    public function setBlacklist($blacklist): self
    {
        $this->blacklist = $blacklist;

        return $this;
    }

    public function getSearchSettings()
    {
        return $this->search_settings;
    }

    public function setSearchSettings($search_settings): self
    {
        $this->search_settings = $search_settings;

        return $this;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function setSettings($settings): self
    {
        $this->settings = $settings;

        return $this;
    }
}
