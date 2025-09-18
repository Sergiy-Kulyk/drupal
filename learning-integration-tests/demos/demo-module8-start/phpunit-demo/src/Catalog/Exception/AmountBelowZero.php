<?php
declare(strict_types=1);

namespace App\Catalog\Exception;

final class AmountBelowZero extends \InvalidArgumentException
{
    public static function fromInt(int $amountInCents): self
    {
        return new self('Money amount cannot be below zero, ' . $amountInCents . ' given');
    }
}
