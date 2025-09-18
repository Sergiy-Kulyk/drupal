<?php
declare(strict_types=1);

namespace App\Catalog\Value;

final class Discount
{
    private const TYPE_AMOUNT = 'amount';
    private const TYPE_PERCENT = 'percent';

    private string $type;
    private float $value;

    private function __construct(string $type, float $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    public static function fromAmount(int $amount): self
    {
        return new self(self::TYPE_AMOUNT, $amount);
    }

    public static function fromPercent(float $percent): self
    {
        return new self(self::TYPE_PERCENT, $percent * 100);
    }

    public function getDiscountAmountForPrice(Amount $priceAmount): Amount
    {
        $discountAmount = 0;
        switch ($this->type) {
            case self::TYPE_PERCENT:
                $discountAmount = (int)($priceAmount->getCents() / 100 * $this->value);
                break;
            case self::TYPE_AMOUNT:
                $discountAmount = (int)$this->value;
                break;
        }

        return new Amount($discountAmount);
    }
}
