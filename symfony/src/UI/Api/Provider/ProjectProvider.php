<?php

declare(strict_types=1);

namespace App\UI\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Infrastructure\Security\ProjectAccessChecker;
use App\UI\Api\Resource\ProjectResource;

final class ProjectProvider implements ProviderInterface
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly ProjectAccessChecker $projectAccessChecker,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?ProjectResource
    {
        $project = $this->projectRepository->findById($uriVariables['id']);

        if (!$project) {
            return null;
        }

        $this->projectAccessChecker->assertCanAccess($project);

        $resource = new ProjectResource();
        $resource->id = $project->getId();
        $resource->name = $project->getName();
        $resource->description = $project->getDescription();
        $resource->ownerId = $project->getOwner()->getId();
        $resource->createdAt = $project->getCreatedAt();
        $resource->members = array_map(
            static fn ($member) => $member->getId(),
            $project->getMembers()->toArray()
        );

        return $resource;
    }
}
