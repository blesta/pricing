<?php

/**
 *
 */
class ItemPrice extends UnitPrice implements PriceTotalInterface
{
    protected $discounts = array();
    protected $taxes = array();

    // discard duplicates
    public function setDiscount(DiscountPrice $discount)
    {
        if (!in_array($discount, $this->discounts, true)) {
            $this->discounts[] = $discount;
        }
    }

    /**
     *
     * @throws InvalidArgumentException If something other than a TaxPrice was given
     */
    public function setTax()
    {
        $taxes = func_get_args();
        foreach ($taxes as $tax) {
            if (!($tax instanceof TaxPrice)) {
                throw new InvalidArgumentException(sprintf(
                    '%s requires an instance of %s, %s given.',
                    'setTax',
                    'TaxPrice',
                    gettype($tax)
                ));
            }
        }
        $this->taxes[] = $taxes;
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
        return array_unique($all_taxes);
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
