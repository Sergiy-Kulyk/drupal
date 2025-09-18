<?php
declare(strict_types=1);

namespace App\Tests\Catalog\Value;

use App\Catalog\Value\Amount;
use App\Catalog\Value\Product;
use PHPUnit\Framework\TestCase;

final class ProductTest extends TestCase
{
    const NAME = 'Product name';
    const PRICE = 1000;

    private Product $product;

    /** @test */
    public function getName_ReturnsOriginalName(): void
    {
        self::assertEquals(
            self::NAME,
            $this->product->getName()
        );
    }

    /** @test */
    public function getPrice_ReturnsOriginalPrice(): void
    {
        self::assertEquals(
            new Amount(self::PRICE),
            $this->product->getPrice()
        );
    }

    /** @test */
    public function toArray_ReturnsFormattedValues(): void
    {
        self::assertEquals(
            [
                'name' => self::NAME,
                'price' => '10.00'
            ],
            $this->product->toArray()
        );
    }

    protected function setUp(): void
    {
        $this->product = new Product(
            self::NAME,
            new Amount(self::PRICE)
        );
    }
}
