<?php
declare(strict_types=1);

namespace App\Catalog\Repository;

use App\Catalog\Value\Amount;
use App\Catalog\Value\Product;
use App\Tests\IntegrationTestCase;

/** @covers \App\Catalog\Repository\DoctrineProductRepository */
final class DoctrineProductRepositoryTest extends IntegrationTestCase
{
  /** @test */
  function findProducts_WithDiscount_ReturnsFullPriceProducts(): void {
    // Initialize container.
    $this->initializeContainer();
    $this->resetDatabase();
    $this->insertRecord('product', [
      'name' => 'Concert',
      'cost' => 100,
      'markup' => 10,
    ]);

    $repository = $this->diContainer->get(DoctrineProductRepository::class);

    self::assertEquals([
      new Product('Concert', new Amount(1100)),
    ], $repository->findProducts());
  }
}
