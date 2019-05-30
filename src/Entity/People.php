<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @ORM\Column(type="string")
     */
    private $instaId;

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

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\account", inversedBy="people")
     */
    private $account;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\History", mappedBy="interactWith")
     */
    private $histories;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $contacted;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $likedMedias;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $origin;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $profilePic;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $ratio;
  
    
    public function __construct($username,$userId,$account,$infos)
    {
        $this->histories = new ArrayCollection();
        $this->username=$username;
        $this->instaId= $userId;
        $this->to_follow= true;
        $this->follow_date= new \DateTime('@'.strtotime('now'));
        $this->is_following_back= false;
        $this->nb_followers= 0;
        $this->account =$account;
        $this->contacted =false ;
        $this->updated =new \DateTime('@'.strtotime('now'));
        $this->ratio = $infos->ratio;
        $this->profilePic = $infos->profilePic;
        $this->origin = $infos->origin;
    }
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

    public function getInstaId(): ?string
    {
        return $this->instaId;
    }

    public function setInstaId(string $instaId): self
    {
        $this->instaId = $instaId;

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

    public function getAccount(): ?account
    {
        return $this->account;
    }

    public function setAccount(?account $account): self
    {
        $this->account = $account;

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
            $history->setInteractWith($this);
        }

        return $this;
    }

    public function removeHistory(History $history): self
    {
        if ($this->histories->contains($history)) {
            $this->histories->removeElement($history);
            // set the owning side to null (unless already changed)
            if ($history->getInteractWith() === $this) {
                $history->setInteractWith(null);
            }
        }

        return $this;
    }

    public function getContacted(): ?bool
    {
        return $this->contacted;
    }

    public function setContacted(?bool $contacted): self
    {
        $this->contacted = $contacted;

        return $this;
    }

    public function getLikedMedias()
    {
        return $this->likedMedias;
    }

    public function setLikedMedias($likedMedias): self
    {
        $this->likedMedias = $likedMedias;

        return $this;
    }

    public function getOrigin(): ?string
    {
        return $this->origin;
    }

    public function setOrigin(?string $origin): self
    {
        $this->origin = $origin;

        return $this;
    }

    public function getProfilePic(): ?string
    {
        return $this->profilePic;
    }

    public function setProfilePic(?string $profilePic): self
    {
        $this->profilePic = $profilePic;

        return $this;
    }

    public function getRatio(): ?float
    {
        return $this->ratio;
    }

    public function setRatio(?float $ratio): self
    {
        $this->ratio = $ratio;

        return $this;
    }

}
