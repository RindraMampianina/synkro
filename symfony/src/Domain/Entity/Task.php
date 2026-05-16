<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Exception\InvalidTaskTransitionException;
use App\Domain\ValueObject\TaskPriority;
use App\Domain\ValueObject\TaskStatus;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tasks')]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', enumType: TaskStatus::class)]
    private TaskStatus $status;

    #[ORM\Column(type: 'string', enumType: TaskPriority::class)]
    private TaskPriority $priority;

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    private Project $project;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $assignee = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dueDate = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(
        string $title,
        Project $project,
        TaskPriority $priority = TaskPriority::MEDIUM,
        ?string $description = null,
    ) {
        $this->title = $title;
        $this->project = $project;
        $this->priority = $priority;
        $this->description = $description;
        $this->status = TaskStatus::TODO;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?string { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function getStatus(): TaskStatus { return $this->status; }
    public function getPriority(): TaskPriority { return $this->priority; }
    public function getProject(): Project { return $this->project; }
    public function getAssignee(): ?User { return $this->assignee; }
    public function getDueDate(): ?\DateTimeImmutable { return $this->dueDate; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    public function transitionTo(TaskStatus $newStatus): void
    {
        if (!$this->status->canTransitionTo($newStatus)) {
            throw new InvalidTaskTransitionException(
                "Cannot transition from {$this->status->value} to {$newStatus->value}"
            );
        }
        $this->status = $newStatus;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function assignTo(User $user): void
    {
        $this->assignee = $user;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function setDueDate(\DateTimeImmutable $dueDate): void
    {
        $this->dueDate = $dueDate;
        $this->updatedAt = new \DateTimeImmutable();
    }
}