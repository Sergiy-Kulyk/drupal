<?php
declare(strict_types=1);

namespace App\Tests\AmountCalculator\Operation;

use App\AmountCalculator\Operation\DiscountOperation;
use App\Catalog\Value\Amount;
use App\Catalog\Value\Discount;
use PHPUnit\Framework\TestCase;

/** @covers \App\AmountCalculator\Operation\DiscountOperation */
final class DiscountOperationTest extends TestCase
{
    /** @test */
    public function applyTo_WithMultipleDiscounts_ReturnsDiscountedAmount(): void
    {
        $operation = new DiscountOperation([
            Discount::fromAmount(10),
            Discount::fromAmount(10)
        ]);

        self::assertEquals(
            new Amount(80),
            $operation->applyTo(new Amount(100))
        );
    }

    /** @test */
    public function applyTo_WithoutDiscounts_ReturnsOriginalAmount(): void
    {
        $operation = new DiscountOperation([]);

        self::assertEquals(
            new Amount(100),
            $operation->applyTo(new Amount(100))
        );
    }

    /** @test */
    public function applyTo_WithDiscountGreaterThanAmount_ReturnsZero(): void
    {
        $operation = new DiscountOperation([
            Discount::fromAmount(200)
        ]);

        self::assertEquals(
            new Amount(0),
            $operation->applyTo(new Amount(100))
        );
    }
}
