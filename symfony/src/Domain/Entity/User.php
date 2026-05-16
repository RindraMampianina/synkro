<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(type: 'string', length: 100)]
    private string $fullName;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(targetEntity: Project::class, mappedBy: 'owner')]
    private Collection $ownedProjects;

    #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'members')]
    private Collection $projects;

    public function __construct(string $email, string $fullName)
    {
        $this->email = $email;
        $this->fullName = $fullName;
        $this->createdAt = new \DateTimeImmutable();
        $this->ownedProjects = new ArrayCollection();
        $this->projects = new ArrayCollection();
    }

    public function getId(): ?string { return $this->id; }
    public function getEmail(): string { return $this->email; }
    public function getFullName(): string { return $this->fullName; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getOwnedProjects(): Collection { return $this->ownedProjects; }
    public function getProjects(): Collection { return $this->projects; }

    public function getUserIdentifier(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function setPassword(string $password): void { $this->password = $password; }
    public function getRoles(): array { return array_unique([...$this->roles, 'ROLE_USER']); }
    public function setRoles(array $roles): void { $this->roles = $roles; }
    public function eraseCredentials(): void {}
}