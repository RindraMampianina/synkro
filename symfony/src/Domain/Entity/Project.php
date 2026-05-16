<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'projects')]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'ownedProjects')]
    #[ORM\JoinColumn(nullable: false)]
    private User $owner;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'projects')]
    #[ORM\JoinTable(name: 'project_members')]
    private Collection $members;

    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'project', cascade: ['persist', 'remove'])]
    private Collection $tasks;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(string $name, User $owner, ?string $description = null)
    {
        $this->name = $name;
        $this->owner = $owner;
        $this->description = $description;
        $this->createdAt = new \DateTimeImmutable();
        $this->members = new ArrayCollection();
        $this->tasks = new ArrayCollection();
    }

    public function getId(): ?string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getDescription(): ?string { return $this->description; }
    public function getOwner(): User { return $this->owner; }
    public function getTasks(): Collection { return $this->tasks; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function getMembers(): Collection { return $this->members; }

    public function rename(string $name): void
    {
        $this->name = $name;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function addMember(User $user): void
    {
        if (!$this->members->contains($user)) {
            $this->members->add($user);
        }
    }

    public function removeMember(User $user): void
    {
        $this->members->removeElement($user);
    }
}