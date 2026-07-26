<?php

declare(strict_types=1);

namespace App\Tests\Application\Handler;

use App\Application\Command\RegisterUserCommand;
use App\Application\Handler\RegisterUserHandler;
use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegisterUserHandlerTest extends TestCase
{
    public function testRegistersUserWithHashedPassword(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $userRepository->expects(self::once())
            ->method('findByEmail')
            ->with('rindra@synkro.com')
            ->willReturn(null);

        $passwordHasher->expects(self::once())
            ->method('hashPassword')
            ->with(self::isInstanceOf(User::class), 'plain-password')
            ->willReturn('hashed-password');

        $userRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (User $user): bool {
                return $user->getEmail() === 'rindra@synkro.com'
                    && $user->getFullName() === 'Rindra'
                    && $user->getPassword() === 'hashed-password';
            }));

        $handler = new RegisterUserHandler($userRepository, $passwordHasher);
        $user = $handler(new RegisterUserCommand('rindra@synkro.com', 'Rindra', 'plain-password'));

        self::assertSame('rindra@synkro.com', $user->getEmail());
        self::assertSame('hashed-password', $user->getPassword());
    }

    public function testThrowsWhenEmailAlreadyExists(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $userRepository->method('findByEmail')->willReturn(new User('rindra@synkro.com', 'Rindra'));
        $userRepository->expects(self::never())->method('save');
        $passwordHasher->expects(self::never())->method('hashPassword');

        $handler = new RegisterUserHandler($userRepository, $passwordHasher);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Email already exists.');

        $handler(new RegisterUserCommand('rindra@synkro.com', 'Rindra', 'plain-password'));
    }
}
