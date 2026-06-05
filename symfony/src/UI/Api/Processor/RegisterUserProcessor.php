<?php

declare(strict_types=1);

namespace App\UI\Api\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Application\Command\RegisterUserCommand;
use App\UI\Api\Resource\UserResource;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class RegisterUserProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): UserResource
    {
        /** @var UserResource $data */
        $envelope = $this->bus->dispatch(new RegisterUserCommand(
            email: $data->email,
            fullName: $data->fullName,
            plainPassword: $data->plainPassword,
        ));

        $user = $envelope->last(HandledStamp::class)->getResult();

        $resource = new UserResource();
        $resource->id = $user->getId();
        $resource->email = $user->getEmail();
        $resource->fullName = $user->getFullName();

        return $resource;
    }
}