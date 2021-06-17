<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass=AdoptionRepository::class) @ORM\HasLifecycleCallbacks
 */
class Adoption
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $createdBy;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $updatedBy;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="adoptions")
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=AdoptionRequest::class, mappedBy="adoption", orphanRemoval=true)
     */
    private $adoptionRequests;

    /**
     * @ORM\OneToOne(targetEntity=Animal::class, inversedBy="adoption", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $animal;

    public function __construct()
    {
        $this->adoptionRequests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?string $updatedBy): self
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

    /**
     * @return Collection|AdoptionRequest[]
     */
    public function getAdoptionRequests(): Collection
    {
        return $this->adoptionRequests;
    }

    public function addAdoptionRequest(AdoptionRequest $adoptionRequest): self
    {
        if (!$this->adoptionRequests->contains($adoptionRequest)) {
            $this->adoptionRequests[] = $adoptionRequest;
            $adoptionRequest->setAdoption($this);
        }

        return $this;
    }

    public function removeAdoptionRequest(AdoptionRequest $adoptionRequest): self
    {
        if ($this->adoptionRequests->removeElement($adoptionRequest)) {
            // set the owning side to null (unless already changed)
            if ($adoptionRequest->getAdoption() === $this) {
                $adoptionRequest->setAdoption(null);
            }
        }

        return $this;
    }

    public function getAnimal(): ?Animal
    {
        return $this->animal;
    }

    public function setAnimal(Animal $animal): self
    {
        $this->animal = $animal;

        return $this;
    }
}
