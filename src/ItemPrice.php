<?php

/**
 *
 */
class ItemPrice extends UnitPrice implements PriceTotalInterface
{
    protected $discounts = array();
    protected $taxes = array();

    /**
     * Assigns a discount to the item
     *
     * @param DiscountPrice $discount A discount object to set for the item
     */
    public function setDiscount(DiscountPrice $discount)
    {
        // Disallow duplicates from being set
        if (!in_array($discount, $this->discounts, true)) {
            $this->discounts[] = $discount;
        }
    }

    /**
     * Assigns a TaxPrice to the item
     * Passing multiple TaxPrice arguments will set them to be compounded
     *
     * @throws InvalidArgumentException If something other than a TaxPrice was given
     */
    public function setTax()
    {
        $taxes = func_get_args();
        foreach ($taxes as $tax) {
            // Only a TaxPrice instance is accepted
            if (!($tax instanceof TaxPrice)) {
                throw new InvalidArgumentException(sprintf(
                    '%s requires an instance of %s, %s given.',
                    'setTax',
                    'TaxPrice',
                    gettype($tax)
                ));
            }
        }

        // Remove duplicate TaxPrice's from the given arguments
        foreach ($taxes as $i => $tax_price_i) {
            foreach ($taxes as $j => $tax_price_j) {
                // Remove all later instances of the same TaxPrice
                if ($j > $i && $tax_price_i === $tax_price_j) {
                    unset($taxes[$j]);
                }
            }
        }

        // Remove duplicate TaxPrice's that already exist
        foreach ($taxes as $index => $tax_price) {
            foreach ($this->taxes as $tax_row) {
                if (in_array($tax_price, $tax_row, true)) {
                    unset($taxes[$index]);
                }
            }
        }

        $this->taxes[] = array_values($taxes);
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

    /**
     * Fetch all unique taxes set
     *
     * @return array An array of TaxPrice objects
     */
    public function taxes()
    {
        $all_taxes = array();
        foreach ($this->taxes as $taxes) {
            $all_taxes = array_merge($all_taxes, array_values($taxes));
        }
        return $all_taxes;
    }
    /**
     * Fetch all unique discounts set
     *
     * @return array An array of DiscountPrice objects
     */
    public function discounts()
    {
        return $this->discounts;
    }
}
