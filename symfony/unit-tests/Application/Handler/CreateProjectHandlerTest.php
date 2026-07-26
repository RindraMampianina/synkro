<?php

declare(strict_types=1);

namespace App\Tests\Application\Handler;

use App\Application\Command\CreateProjectCommand;
use App\Application\Handler\CreateProjectHandler;
use App\Domain\Entity\Project;
use App\Domain\Entity\User;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Infrastructure\Mercure\MercurePublisher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mercure\HubInterface;

final class CreateProjectHandlerTest extends TestCase
{
    public function testCreatesProjectAndPublishesEvent(): void
    {
        $owner = new User('owner@synkro.com', 'Owner');
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $projectRepository = $this->createMock(ProjectRepositoryInterface::class);
        $hub = $this->createMock(HubInterface::class);

        $userRepository->expects(self::once())
            ->method('findById')
            ->with('user-1')
            ->willReturn($owner);

        $projectRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (Project $project) use ($owner): bool {
                return $project->getName() === 'Nouveau projet'
                    && $project->getOwner() === $owner
                    && $project->getDescription() === 'Desc';
            }));

        $hub->expects(self::once())
            ->method('publish')
            ->willReturn('event-id');

        $handler = new CreateProjectHandler(
            $projectRepository,
            $userRepository,
            new MercurePublisher($hub),
        );
        $project = $handler(new CreateProjectCommand('Nouveau projet', 'user-1', 'Desc'));

        self::assertSame('Nouveau projet', $project->getName());
        self::assertSame($owner, $project->getOwner());
    }

    public function testThrowsWhenOwnerIsMissing(): void
    {
        $userRepository = $this->createStub(UserRepositoryInterface::class);
        $projectRepository = $this->createMock(ProjectRepositoryInterface::class);
        $hub = $this->createMock(HubInterface::class);

        $userRepository->method('findById')->willReturn(null);
        $projectRepository->expects(self::never())->method('save');
        $hub->expects(self::never())->method('publish');

        $handler = new CreateProjectHandler(
            $projectRepository,
            $userRepository,
            new MercurePublisher($hub),
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('User not found.');

        $handler(new CreateProjectCommand('Fail', 'missing'));
    }
}
