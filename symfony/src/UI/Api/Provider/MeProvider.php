<?php

declare(strict_types=1);

namespace App\UI\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Infrastructure\Security\CurrentUserResolver;
use App\UI\Api\Resource\UserResource;

final class MeProvider implements ProviderInterface
{
    public function __construct(
        private readonly CurrentUserResolver $currentUserResolver,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): UserResource
    {
        $user = $this->currentUserResolver->requireUser();

        $resource = new UserResource();
        $resource->id = $user->getId();
        $resource->email = $user->getEmail();
        $resource->fullName = $user->getFullName();

        return $resource;
    }
}
