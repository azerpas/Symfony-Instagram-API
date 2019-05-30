<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccountRepository")
 */
class Account
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
     * @ORM\Column(type="string", length=255, nullable=true)
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

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $slots;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $status;
      /**
     * Many Accounts have Many Users.
     * @ORM\ManyToMany(targetEntity="User", mappedBy="accounts")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\People", mappedBy="account")
     */
    private $people;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\History", mappedBy="fromAccount")
     */
    private $histories;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $proxy;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $turnedOn = [];

    public function __construct() {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->people = new ArrayCollection();
        $this->histories = new ArrayCollection();
    }

   
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

    public function getSlots()
    {
        return $this->slots;
    }

    public function setSlots($slots): self
    {
        $this->slots = $slots;
        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getUsers()
    {
        return $this->users;
    }

    public function setUsers($users):self
    {
        $this->users=$users;
    }

    public function getUser(?int $key)
    {
        return $this->users->get($key); 
    }
    
    public function setUser(?int $key,?User $userAccount)
    {
        return $this->users->set($key,$userAccount);
    }

    /**
     * @return Collection|People[]
     */
    public function getPeople(): Collection
    {
        return $this->people;
    }

    public function addPerson(People $person): self
    {
        if (!$this->people->contains($person)) {
            $this->people[] = $person;
            $person->setAccount($this);
        }

        return $this;
    }

    public function removePerson(People $person): self
    {
        if ($this->people->contains($person)) {
            $this->people->removeElement($person);
            // set the owning side to null (unless already changed)
            if ($person->getAccount() === $this) {
                $person->setAccount(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|History[]
     */
    public function getHistories(): Collection
    {
        return $this->histories;
    }

    public function addHistory(History $history): self
    {
        if (!$this->histories->contains($history)) {
            $this->histories[] = $history;
            $history->setFromAccount($this);
        }

        return $this;
    }

    public function removeHistory(History $history): self
    {
        if ($this->histories->contains($history)) {
            $this->histories->removeElement($history);
            // set the owning side to null (unless already changed)
            if ($history->getFromAccount() === $this) {
                $history->setFromAccount(null);
            }
        }

        return $this;
    }

    public function getProxy(): ?string
    {
        return $this->proxy;
    }

    public function setProxy(?string $proxy)
    {
        $this->proxy = $proxy;

        return $this;
    }

    public function getTurnedOn()
    {
        return $this->turnedOn;
    }

    public function setTurnedOn($turnedOn)
    {
        $this->turnedOn = $turnedOn;

        return $this;
    }
    
}
