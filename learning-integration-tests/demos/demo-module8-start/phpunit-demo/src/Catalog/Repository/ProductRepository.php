<?php
declare(strict_types=1);

namespace App\Catalog\Repository;

use App\Catalog\Value\Product;

interface ProductRepository
{
    /**
     * @return Product[]
     */
    public function findProducts(): array;
}
