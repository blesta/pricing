<?php

/**
 *
 */
class PricingFactory
{
    public function unitPrice($amount, $qty)
    {
        return new UnitPrice($amount, $qty);
    }

    public function itemPrice($amount, $qty)
    {
        return new ItemPrice($amount, $qty);
    }

    public function discountPrice($discount, $type)
    {
        return new DiscountPrice($discount, $type);
    }

    public function taxPrice($rate, $type)
    {
        return new TaxPrice($rate, $type);
    }

    public function itemPriceCollection()
    {
        return new ItemPriceCollection();
    }
}
