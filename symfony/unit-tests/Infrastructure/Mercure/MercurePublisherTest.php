<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Mercure;

use App\Domain\Entity\Project;
use App\Domain\Entity\Task;
use App\Domain\Entity\User;
use App\Domain\ValueObject\TaskPriority;
use App\Infrastructure\Mercure\MercurePublisher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

final class MercurePublisherTest extends TestCase
{
    public function testPublishTaskCreatedUsesProjectTasksTopic(): void
    {
        $owner = new User('owner@synkro.com', 'Owner');
        $project = $this->createProjectWithId($owner, 'project-123');
        $task = new Task('Realtime', $project, TaskPriority::HIGH);

        $hub = $this->createMock(HubInterface::class);
        $hub->expects(self::once())
            ->method('publish')
            ->with(self::callback(static function (Update $update): bool {
                $topics = $update->getTopics();
                $data = json_decode($update->getData(), true, 512, JSON_THROW_ON_ERROR);

                return $topics === ['https://synkro.app/projects/project-123/tasks']
                    && $data['type'] === 'task.created'
                    && $data['payload']['title'] === 'Realtime'
                    && $data['payload']['priority'] === 'high'
                    && $data['payload']['projectId'] === 'project-123';
            }))
            ->willReturn('id');

        $publisher = new MercurePublisher($hub);
        $publisher->publishTaskCreated($task);
    }

    public function testPublishProjectCreatedUsesOwnerTopic(): void
    {
        $owner = new User('owner@synkro.com', 'Owner');
        $project = $this->createProjectWithId($owner, 'project-456');

        $hub = $this->createMock(HubInterface::class);
        $hub->expects(self::once())
            ->method('publish')
            ->with(self::callback(static function (Update $update): bool {
                $topics = $update->getTopics();
                $data = json_decode($update->getData(), true, 512, JSON_THROW_ON_ERROR);

                return $topics === ['https://synkro.app/users/owner@synkro.com/projects']
                    && $data['type'] === 'project.created'
                    && $data['payload']['id'] === 'project-456'
                    && $data['payload']['name'] === 'Synkro';
            }))
            ->willReturn('id');

        $publisher = new MercurePublisher($hub);
        $publisher->publishProjectCreated($project);
    }

    private function createProjectWithId(User $owner, string $id): Project
    {
        $project = new Project('Synkro', $owner);

        $reflection = new \ReflectionProperty(Project::class, 'id');
        $reflection->setValue($project, $id);

        return $project;
    }
}
