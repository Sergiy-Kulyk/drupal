<?php
declare(strict_types=1);

namespace App\AmountCalculator;

use App\AmountCalculator\Operation\Operation;
use App\Catalog\Value\Amount;
use Assert\Assert;

final class AmountCalculator
{
    /**
     * @param Operation[] $operations
     */
    public function getResult(Amount $amount, array $operations): Amount
    {
        Assert::thatAll($operations)
            ->isInstanceOf(Operation::class);

        foreach ($operations as $operation) {
            $amount = $operation->applyTo($amount);
        }

        return $amount;
    }
}
