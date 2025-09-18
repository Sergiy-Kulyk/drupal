<?php
declare(strict_types=1);

namespace App\Catalog\Handler;

use App\Catalog\Repository\ProductRepository;
use App\Catalog\SearchAnalytics\SearchAnalytics;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ProductListHandler extends ProductHandler implements RequestHandlerInterface
{
    private ProductRepository $productRepository;
    private SearchAnalytics $searchAnalytics;

    public function __construct(ProductRepository $productRepository, SearchAnalytics $searchAnalytics)
    {
        $this->productRepository = $productRepository;
        $this->searchAnalytics = $searchAnalytics;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $products = $this->productRepository->findProducts();
        $this->searchAnalytics->track(['price' => null, 'name' => null]);

        return $this->createProductsResponse($products);
    }
}
