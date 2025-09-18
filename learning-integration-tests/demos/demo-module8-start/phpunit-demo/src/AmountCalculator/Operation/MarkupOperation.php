<?php
declare(strict_types=1);

namespace App\AmountCalculator\Operation;

use App\Catalog\Value\Amount;
use Assert\Assert;

final class MarkupOperation implements Operation
{
    /** @var float */
    private $markup;

    /**
     * @param float $markup
     */
    public function __construct($markup)
    {
        Assert::that($markup)->float();
        $this->markup = $markup;
    }

    public function applyTo(Amount $amount): Amount
    {
        $cents = (int)($amount->getCents() * (1 + $this->markup));

        return new Amount($cents);
    }
}
