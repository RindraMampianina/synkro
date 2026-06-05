<?php

declare(strict_types=1);

namespace App\Infrastructure\Mercure;

use App\Domain\Entity\Task;
use App\Domain\Entity\Project;
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
            // Topic — les clients abonnés à ce topic recevront l'update
            // Chaque projet a son propre topic
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
            ]),
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
            ]),
        ));
    }

    public function publishProjectUpdated(Project $project): void
    {
        $this->hub->publish(new Update(
            topics: [
                sprintf('https://synkro.app/projects/%s', $project->getId()),
            ],
            data: json_encode([
                'type' => 'project.updated',
                'payload' => [
                    'id' => $project->getId(),
                    'name' => $project->getName(),
                    'updatedAt' => $project->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
                ],
            ]),
        ));
    }
}