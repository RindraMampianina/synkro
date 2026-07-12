<?php

declare(strict_types=1);

namespace App\UI\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Infrastructure\Security\CurrentUserResolver;
use App\UI\Api\Resource\ProjectResource;

final class ProjectCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly CurrentUserResolver $currentUserResolver,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $currentUser = $this->currentUserResolver->requireUser();
        $projects = $this->projectRepository->findByOwner($currentUser);

        return array_map(static function ($project) {
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
        }, $projects);
    }
}
