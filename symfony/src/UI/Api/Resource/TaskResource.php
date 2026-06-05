<?php

declare(strict_types=1);

namespace App\UI\Api\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\UI\Api\Processor\CreateTaskProcessor;
use App\UI\Api\Processor\UpdateTaskStatusProcessor;
use App\UI\Api\Provider\TaskProvider;
use App\UI\Api\Provider\TaskCollectionProvider;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Task',
    operations: [
        new GetCollection(provider: TaskCollectionProvider::class),
        new Get(provider: TaskProvider::class),
        new Post(processor: CreateTaskProcessor::class),
        new Patch(
            uriTemplate: '/tasks/{id}/status',
            processor: UpdateTaskStatusProcessor::class,
        ),
    ]
)]
final class TaskResource
{
    public ?string $id = null;

    #[Assert\NotBlank]
    public string $title = '';

    public ?string $description = null;
    public ?string $status = null;
    public ?string $priority = 'medium';
    public ?string $projectId = null;
    public ?string $assigneeId = null;
    public ?\DateTimeImmutable $dueDate = null;
    public ?\DateTimeImmutable $createdAt = null;
}