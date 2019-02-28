<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PeopleRepository")
 */
class People
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $username;

    /**
     * @ORM\Column(type="integer")
     */
    private $insta_id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $to_follow;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $follow_date;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $is_following_back;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nb_followers;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getInstaId(): ?int
    {
        return $this->insta_id;
    }

    public function setInstaId(int $insta_id): self
    {
        $this->insta_id = $insta_id;

        return $this;
    }

    public function getToFollow(): ?bool
    {
        return $this->to_follow;
    }

    public function setToFollow(bool $to_follow): self
    {
        $this->to_follow = $to_follow;

        return $this;
    }

    public function getFollowDate(): ?\DateTimeInterface
    {
        return $this->follow_date;
    }

    public function setFollowDate(?\DateTimeInterface $follow_date): self
    {
        $this->follow_date = $follow_date;

        return $this;
    }

    public function getIsFollowingBack(): ?bool
    {
        return $this->is_following_back;
    }

    public function setIsFollowingBack(?bool $is_following_back): self
    {
        $this->is_following_back = $is_following_back;

        return $this;
    }

    public function getNbFollowers(): ?int
    {
        return $this->nb_followers;
    }

    public function setNbFollowers(?int $nb_followers): self
    {
        $this->nb_followers = $nb_followers;

        return $this;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(?\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }
}
