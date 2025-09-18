<?php
declare(strict_types=1);

namespace App\AmountCalculator\Operation;

use App\Catalog\Value\Amount;
use App\Catalog\Value\Discount;
use Assert\Assert;

final class DiscountOperation implements Operation
{
    /** @var Discount[] */
    private array $discounts;

    /**
     * @var Discount[]
     */
    public function __construct(array $discounts)
    {
        Assert::thatAll($discounts)
            ->isInstanceOf(Discount::class);

        $this->discounts = $discounts;
    }

    public function applyTo(Amount $amount): Amount
    {
        $discountTotal = 0;
        foreach ($this->discounts as $discount) {
            $discountTotal += $discount->getDiscountAmountForPrice($amount)->getCents();
        }
        $discountedAmount = $amount->getCents() - $discountTotal;

        return new Amount(max(0, $discountedAmount));
    }
}
