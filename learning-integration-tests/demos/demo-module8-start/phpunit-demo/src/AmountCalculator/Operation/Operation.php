<?php
declare(strict_types=1);

namespace App\AmountCalculator\Operation;

use App\Catalog\Value\Amount;

interface Operation
{
    public function applyTo(Amount $amount): Amount;
}
