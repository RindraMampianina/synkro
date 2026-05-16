<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Task;
use App\Domain\Entity\Project;
use App\Domain\Entity\User;

interface TaskRepositoryInterface
{
    public function findById(string $id): ?Task;
    public function findByProject(Project $project): array;
    public function findByAssignee(User $user): array;
    public function save(Task $task): void;
    public function remove(Task $task): void;
}