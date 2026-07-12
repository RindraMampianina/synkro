<?php

declare(strict_types=1);

namespace App\UI\Api\Resource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\UI\Api\Processor\CreateProjectProcessor;
use App\UI\Api\Provider\ProjectProvider;
use App\UI\Api\Provider\ProjectCollectionProvider;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Project',
    operations: [
        new GetCollection(provider: ProjectCollectionProvider::class),
        new Get(provider: ProjectProvider::class),
        new Post(processor: CreateProjectProcessor::class),
    ]
)]
final class ProjectResource
{
    public ?string $id = null;

    #[Assert\NotBlank]
    public string $name = '';

    public ?string $description = null;

    #[ApiProperty(writable: false)]
    public ?string $ownerId = null;

    #[ApiProperty(writable: false)]
    public ?array $members = [];

    #[ApiProperty(writable: false)]
    public ?\DateTimeImmutable $createdAt = null;
}