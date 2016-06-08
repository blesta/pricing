<?php
namespace Blesta\Pricing;

use Blesta\Pricing\Collection\ItemPriceCollection;
use Blesta\Pricing\Modifier\DiscountPrice;
use Blesta\Pricing\Modifier\TaxPrice;
use Blesta\Pricing\Type\ItemPrice;
use Blesta\Pricing\Type\UnitPrice;

/**
 * Pricing Factory for fetching newly-instantiated pricing objects
 */
class PricingFactory
{
    /**
     * Retrieves a new instance of UnitPrice
     *
     * @param float $amount The unit price
     * @param int $qty The quantity of unit price
     * @param string $key A unique identifier (optional, default null)
     * @return \UnitPrice An instance of UnitPrice
     */
    public function unitPrice($amount, $qty, $key = null)
    {
        return new UnitPrice($amount, $qty, $key);
    }

    /**
     * Retrieves a new instance of ItemPrice
     *
     * @param float $amount The unit price
     * @param int $qty The quantity of unit price
     * @param string $key A unique identifier (optional, default null)
     * @return \ItemPrice An instance of ItemPrice
     */
    public function itemPrice($amount, $qty, $key = null)
    {
        return new ItemPrice($amount, $qty, $key);
    }

    /**
     * Retrieves a new instance of DiscountPrice
     *
     * @param float $discount The positive amount to discount
     * @param string $type The type of discount the $discount represents. One of:
     *  - percent The $discount represents a percentage discount
     *  - amount The $discount represents an amount discount
     * @return \DiscountPrice An instance of DiscountPrice
     */
    public function discountPrice($discount, $type)
    {
        return new DiscountPrice($discount, $type);
    }

    /**
     * Retrieves a new instance of TaxPrice
     *
     * @param float $rate The positive tax amount as a percentage
     * @param string $type The type of tax the $rate represents. One of:
     *  - inclusive Prices include tax
     *  - exclusive Prices do not include tax
     * @return \TaxPrice An instance of TaxPrice
     */
    public function taxPrice($rate, $type)
    {
        return new TaxPrice($rate, $type);
    }

    /**
     * Retrieves a new instance of ItemPriceCollection
     *
     * @return \ItemPriceCollection An instance of ItemPriceCollection
     */
    public function itemPriceCollection()
    {
        return new ItemPriceCollection();
    }
}
