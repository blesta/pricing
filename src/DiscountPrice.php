<?php

/**
 * Determine discounts from prices
 */
class DiscountPrice extends AbstractPriceModifier
{
    /**
     * @var float The total discount amount remaining for the amount type
     */
    protected $discount_remaining;

    /**
     * Sets discount information
     *
     * @throws InvalidArgumentException If $amount is negative
     *
     * @param float $amount The positive amount to discount
     * @param string $type The type of discount the $amount represents. One of:
     *  - percent The $amount represents a percentage discount (NOT already divided by 100)
     *  - amount The $amount represents an amount discount
     */
    public function __construct($amount, $type)
    {
        // Amount must be non-negative
        if (!is_numeric($amount) || $amount < 0) {
            throw new InvalidArgumentException(sprintf(
                'DiscountPrice must be instantiated with a positive amount.'
            ));
        }

        parent::__construct($amount, $type);

        // Keep a running total of the remaining discount for amounts
        $this->discount_remaining = 0;
        if ('percent' !== $this->type) {
            $this->discount_remaining = $amount;
        }
    }

    /**
     * Determines the price remaining after discount.
     * If the discount is an amount type, the discount off will be determined from
     * the discount amount remaining rather than the full discount amount.
     *
     * @param float $price The base price before discount
     * @return float The $price after discount
     */
    public function off($price)
    {
        $discount = $this->on($price);

        // Update the running total of the discount amount remaining
        if ('percent' !== $this->type) {
            // The usable discount amount must consider the total discount remaining
            $applied_discount = min($this->discount_remaining, abs($discount));

            // Update the total discount remaining and set the discount off
            $this->discount_remaining -= $applied_discount;
            $discount = $applied_discount;
        }

        return $price - abs($discount);
    }

    /**
     * Determines the discount amount from the given price
     *
     * @param float $price The base price before discount
     * @return float The discount amount
     */
    public function on($price)
    {
        // Percent discount may cover at most the entire price
        if ('percent' === $this->type) {
            return ($this->amount > 100 ? $price : $price * $this->amount / 100);
        } else {
            $discount = min(abs($price), $this->discount_remaining);
            return ($price >= 0 ? $discount : -$discount);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->discount_remaining = $this->amount;
    }
}
