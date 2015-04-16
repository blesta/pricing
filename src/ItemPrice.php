<?php

/**
 *
 */
class ItemPrice extends UnitPrice implements PriceTotalInterface
{
    // discard duplicates
    public function setDiscount(DiscountPrice $discount)
    {

    }
    // discard duplicates
    public function setTax(TaxPrice $tax, TaxPrice $tax, TaxPrice $tax)
    {

    }


    // PriceTotalInterface
    public function totalAfterTax()
    {

    }
    public function totalAfterDiscount()
    {

    }
    public function subtotal()
    {

    }
    public function total()
    {

    }
    public function taxAmount(TaxPrice $tax =  null)
    {

    }
    public function discountAmount(DiscountPrice $discount =  null)
    {

    }
    public function taxes()
    {
        // return array of TaxPrice
    }
    public function discounts()
    {
        // return array of DiscountPrice
    }
}