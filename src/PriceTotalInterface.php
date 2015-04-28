<?php

/**
 * Interface for calculating totals
 */
interface PriceTotalInterface
{
    /**
     * Retrieves the price including tax
     *
     * @return float The price including tax
     */
    public function totalAfterTax();

    /**
     * Retrieves the price including discount
     *
     * @return float The price including discount
     */
    public function totalAfterDiscount();

    /**
     * Retrieves the price
     *
     * @return float The subtotal price
     */
    public function subtotal();

    /**
     * Retrieves the total price considering all price modifiers
     *
     * @return float The total price
     */
    public function total();

    /**
     * Retrieves the tax amount
     *
     * @param TaxPrice $tax A TaxPrice to determine the tax amount from (optional)
     * @return float The tax amount
     */
    public function taxAmount(TaxPrice $tax = null);

    /**
     * Retrieves the discount amount
     *
     * @param DiscountPrice $discount A DiscountPrice to determine the discount amount from (optional)
     * @return float The discount amount
     */
    public function discountAmount(DiscountPrice $discount = null);

    /**
     * Retrieves a set of TaxPrice objects
     *
     * @return array An array containing TaxPrice objects
     */
    public function taxes();

    /**
     * Retrieves a set of DiscountPrice objects
     *
     * @return array An array containing DiscountPrice objects
     */
    public function discounts();

    /**
     * Reset discount amounts
     */
    public function resetDiscounts();
}
