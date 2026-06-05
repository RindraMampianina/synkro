<?php

declare(strict_types=1);

namespace App\UI\Api\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Application\Command\UpdateTaskStatusCommand;
use App\UI\Api\Resource\TaskResource;
use Symfony\Component\Messenger\MessageBusInterface;

final class UpdateTaskStatusProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TaskResource
    {
        /** @var TaskResource $data */
        $this->bus->dispatch(new UpdateTaskStatusCommand(
            taskId: $uriVariables['id'],
            newStatus: $data->status,
        ));

        return $data;
    }
}