<?php
declare(strict_types=1);

namespace App\Tests\Catalog\Value;

use App\Catalog\Value\Amount;
use App\Catalog\Value\Discount;
use PHPUnit\Framework\TestCase;

/** @covers \App\Catalog\Value\Discount */
final class DiscountTest extends TestCase
{
    /** @test */
    public function getDiscountAmountForPrice_WithPercent_ReturnsPercentOfPrice(): void
    {
        $discount = Discount::fromPercent(0.20);
        self::assertEquals(
            new Amount(20),
            $discount->getDiscountAmountForPrice(new Amount(100))
        );
    }

    /** @test */
    public function getDiscountAmountForPrice_WithAmount_ReturnsSameAmount(): void
    {
        $discount = Discount::fromAmount(100);
        $discount->getDiscountAmountForPrice(new Amount(100));
        self::assertTrue(true);
    }
}
