<?php

/**
 *
 */
class ItemPriceCollection implements PriceTotalInterface, Iterator
{
    public function append(ItemPrice $price)
    {

    }
    public function remove(ItemPrice $price)
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
        // all item total() values without any tax or discounts
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

    // Iterator
    public function current()
    {

    }
    public function key()
    {

    }
    public function next()
    {
        
    }
    public function rewind()
    {

    }
    public function valid()
    {

    }
}
