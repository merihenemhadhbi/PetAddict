<?php

namespace App\Entity;

use App\Repository\LostRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LostRepository::class) @ORM\HasLifecycleCallbacks
 */
class Lost
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="losts")
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;
    /**
     * @ORM\ManyToOne(targetEntity=Address::class, inversedBy="addressLost")
     * @ORM\JoinColumn(nullable=true)
     */
    private $addressLost;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $createdBy;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $updatedBy;

    public function getId(): ?int
    {
        return $this->id;
    }
        /**
     * @ORM\Column(type="string", length=255)
     */
    private $animal;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(string $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }
    
    /** @ORM\PrePersist */
    public function prePersist()
    {
        $this->createdAt = new DateTime();
        $this->createdBy = $this->user->getUserName();
    }

    /** @ORM\PreUpdate */
    public function preUpdate()
    {
        $this->updatedAt = new DateTime();
        $this->updatedBy = $this->user->getUserName();
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
    public function getAddress(): ?Address
{
    return $this->addressLost;
}

public function setAddress(?Address $addressLost): self
{
    $this->addressLost = $addressLost;

    return $this;
}


    public function getAnimal(): ?string
    {
        return $this->animal;
    }

    public function setAnimal(string $animal): self
    {
        $this->animal = $animal;

        return $this;
    }
}
