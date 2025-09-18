<?php
declare(strict_types=1);

namespace App\Tests\Catalog\Exception;

use App\Catalog\Exception\AmountBelowZero;
use PHPUnit\Framework\TestCase;

final class AmountBelowZeroTest extends TestCase
{
    /** @test */
    public function fromInt_WithCents_SetsMessageContainingCents(): void
    {
        $exception = AmountBelowZero::fromInt(-10);
        self::assertEquals(
            'Money amount cannot be below zero, -10 given',
            $exception->getMessage()
        );
    }
}
