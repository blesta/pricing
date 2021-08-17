<?php
namespace Blesta\Pricing\Modifier;

use InvalidArgumentException;

/**
 * Determines tax based on prices
 */
class TaxPrice extends AbstractPriceModifier
{
    // Tax types
    const INCLUSIVE_CALCULATED = 'inclusive_calculated';
    const INCLUSIVE = 'inclusive';
    const EXCLUSIVE = 'exclusive';

    /**
     * @var bool Whether this tax should be subtracted instead of added
     */
    public $subtract = false;

    /**
     * Sets tax information
     *
     * @throws InvalidArgumentException If $amount is negative
     *
     * @param float $amount The positive tax amount as a percentage
     * @param string $type The type of tax the $amount represents. One of:
     *  - inclusive_calculated Taxes are subtracted from the item price
     *  - inclusive Prices include tax
     *  - exclusive Prices do not include tax
     * @param bool $subtract Whether this tax should be subtracted instead of added
     */
    public function __construct($amount, $type, $subtract = false)
    {
        // Amount must be non-negative
        if (!is_numeric($amount) || $amount < 0) {
            throw new InvalidArgumentException(sprintf(
                'TaxPrice must be instantiated with a positive amount.'
            ));
        }
        $this->subtract = $subtract;

        parent::__construct($amount, $type);
    }

    /**
     * Determines the price after removing tax (from inclusive tax)
     *
     * @param float $price The price
     * @return float The $price without tax
     */
    public function off($price)
    {
        if (TaxPrice::INCLUSIVE == $this->type || TaxPrice::INCLUSIVE_CALCULATED == $this->type) {
            return $price - $this->on($price);
        }
        return $price;
    }

    /**
     * Determines the amount of tax for the given price
     *
     * @param float $price The price
     * @return float The tax amount
     */
    public function on($price)
    {
        if (TaxPrice::INCLUSIVE_CALCULATED == $this->type) {
            return ( $price / ( 100 + $this->amount ) ) * $this->amount;
        } else {
            return max(0, $this->amount / 100) * $price;
        }
    }

    /**
     * Determines the price including tax
     *
     * @param float $price The price before tax
     * @return float The price including tax
     */
    public function including($price)
    {
        if (TaxPrice::EXCLUSIVE == $this->type) {
            return $price + $this->on($price);
        }
        return $price;
    }
}
