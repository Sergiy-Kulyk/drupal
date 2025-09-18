<?php
declare(strict_types=1);

namespace App\Catalog\Handler;

use App\Catalog\Value\Product;
use Laminas\Diactoros\Response\JsonResponse;

abstract class ProductHandler
{
    /**
     * @param Product[] $products
     */
    protected function createProductsResponse(array $products): JsonResponse
    {
        return new JsonResponse(array_map(static function (Product $product) {
            return $product->toArray();
        }, $products));
    }
}
