<?php

declare(strict_types=1);

namespace App\Tests\Domain\ValueObject;

use App\Domain\ValueObject\TaskPriority;
use PHPUnit\Framework\TestCase;

final class TaskPriorityTest extends TestCase
{
    public function testLabels(): void
    {
        self::assertSame('Basse', TaskPriority::LOW->label());
        self::assertSame('Moyenne', TaskPriority::MEDIUM->label());
        self::assertSame('Haute', TaskPriority::HIGH->label());
    }

    public function testValues(): void
    {
        self::assertSame('low', TaskPriority::LOW->value);
        self::assertSame('medium', TaskPriority::MEDIUM->value);
        self::assertSame('high', TaskPriority::HIGH->value);
    }
}
