<?php

/**
 *
 */
interface PriceTotalInterface
{
    public function totalAfterTax();
    public function totalAfterDiscount();
    public function subtotal();
    public function total();
    public function taxAmount(TaxPrice $tax =  null);
    public function discountAmount(DiscountPrice $discount =  null);
    public function taxes(); // return array of distinct TaxPrice
    public function discounts(); // return array of distinct DiscountPrice
}
