<?php
declare(strict_types=1);

namespace App\Catalog\Value;

final class Product
{
    private string $name;
    private Amount $price;

    public function __construct(string $name, Amount $price)
    {
        $this->name = $name;
        $this->price = $price;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'price' => number_format($this->getPrice()->getCents() / 100, 2)
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): Amount
    {
        return $this->price;
    }
}
