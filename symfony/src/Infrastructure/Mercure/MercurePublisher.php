<?php

declare(strict_types=1);

namespace App\Infrastructure\Mercure;

use App\Domain\Entity\Project;
use App\Domain\Entity\Task;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

final class MercurePublisher
{
    public function __construct(
        private readonly HubInterface $hub,
    ) {}

    public function publishTaskCreated(Task $task): void
    {
        $this->hub->publish(new Update(
            topics: [
                sprintf('https://synkro.app/projects/%s/tasks', $task->getProject()->getId()),
            ],
            data: json_encode([
                'type' => 'task.created',
                'payload' => [
                    'id' => $task->getId(),
                    'title' => $task->getTitle(),
                    'status' => $task->getStatus()->value,
                    'priority' => $task->getPriority()->value,
                    'projectId' => $task->getProject()->getId(),
                    'assigneeId' => $task->getAssignee()?->getId(),
                    'createdAt' => $task->getCreatedAt()->format(\DateTimeInterface::ATOM),
                ],
            ], JSON_THROW_ON_ERROR),
        ));
    }

    public function publishTaskUpdated(Task $task): void
    {
        $this->hub->publish(new Update(
            topics: [
                sprintf('https://synkro.app/projects/%s/tasks', $task->getProject()->getId()),
            ],
            data: json_encode([
                'type' => 'task.updated',
                'payload' => [
                    'id' => $task->getId(),
                    'title' => $task->getTitle(),
                    'status' => $task->getStatus()->value,
                    'priority' => $task->getPriority()->value,
                    'assigneeId' => $task->getAssignee()?->getId(),
                    'updatedAt' => $task->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
                ],
            ], JSON_THROW_ON_ERROR),
        ));
    }

    public function publishProjectCreated(Project $project): void
    {
        $this->hub->publish(new Update(
            topics: [
                $this->userProjectsTopic($project),
            ],
            data: json_encode([
                'type' => 'project.created',
                'payload' => $this->projectPayload($project),
            ], JSON_THROW_ON_ERROR),
        ));
    }

    public function publishProjectUpdated(Project $project): void
    {
        $this->hub->publish(new Update(
            topics: [
                $this->userProjectsTopic($project),
                sprintf('https://synkro.app/projects/%s', $project->getId()),
            ],
            data: json_encode([
                'type' => 'project.updated',
                'payload' => array_merge(
                    $this->projectPayload($project),
                    [
                        'updatedAt' => $project->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
                    ]
                ),
            ], JSON_THROW_ON_ERROR),
        ));
    }

    private function userProjectsTopic(Project $project): string
    {
        return sprintf(
            'https://synkro.app/users/%s/projects',
            $project->getOwner()->getEmail()
        );
    }

    /** @return array<string, mixed> */
    private function projectPayload(Project $project): array
    {
        return [
            'id' => $project->getId(),
            'name' => $project->getName(),
            'description' => $project->getDescription(),
            'ownerId' => $project->getOwner()->getId(),
            'members' => $project->getMembers()->map(
                static fn ($member) => $member->getId()
            )->getValues(),
            'createdAt' => $project->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }
}
