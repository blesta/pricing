<?php

/**
 * Determine pricing for a single item considering discounts and taxes
 */
class ItemPrice extends UnitPrice implements PriceTotalInterface
{
    /**
     * @var array A numerically-indexed array of DiscountPrice objects
     */
    protected $discounts = array();
    /**
     * @var array A numerically-indexed array containing an array of TaxPrice objects
     */
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

        // Check for duplicate TaxPrice instances from the given arguments
        foreach ($taxes as $i => $tax_price_i) {
            foreach ($taxes as $j => $tax_price_j) {
                // Throw exception if the same instance of a TaxPrice was given multiple times
                if ($j > $i && $tax_price_i === $tax_price_j) {
                    throw new InvalidArgumentException(sprintf(
                        '%s requires unique instances of %s, but identical instances were given.',
                        'setTax',
                        'TaxPrice'
                    ));
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

    /**
     * Retrieves the total item price amount considering all taxes without discounts
     */
    public function totalAfterTax()
    {
        return $this->subtotal() + $this->taxAmount();
    }

    /**
     * Retrieves the total item price amount considering all discounts without taxes
     */
    public function totalAfterDiscount()
    {
        return $this->subtotal() - $this->discountAmount();
    }

    /**
     * Retrieves the total item price amount not considering discounts or taxes
     *
     * @return float The item subtotal
     */
    public function subtotal()
    {
        return parent::total();
    }

    /**
     * Retrieves the total item price amount considering all discounts and taxes
     *
     * @return float The total item price
     */
    public function total()
    {
        return $this->totalAfterDiscount() + $this->taxAmount();
    }

    /**
     * Retrieves the total tax amount considering all item taxes, or just the given tax
     *
     * @param TaxPrice $tax A specific tax price whose tax to calculate for this item (optional, default null)
     * @return float The total tax amount for all taxes set for this item, or the total tax amount
     *  for the given tax price if given
     */
    public function taxAmount(TaxPrice $tax = null)
    {
        $tax_amount = 0;
        $taxable_price = $this->totalAfterDiscount();

        // Determine the tax set on this item's price
        if ($tax) {
            $tax_amount = $tax->on($taxable_price);
        } else {
            // Determine all taxes set on this item's price, compounded accordingly
            foreach ($this->taxes as $tax_group) {
                $compound_tax = 0;
                foreach ($tax_group as $tax) {
                    $compound_tax += $tax->on($taxable_price + $compound_tax);
                }

                // Sum all taxes
                $tax_amount += $compound_tax;
            }
        }

        return $tax_amount;
    }

    /**
     * Retrieves the total discount amount considering all item discounts, or just the given discount
     *
     * @param DiscountPrice $discount A specific discount price whose discount to calculate
     *  for this item (optional, default null)
     * @return float The total discount amount for all discounts set for this item, or the
     *  total discount amount for the given discount price if given
     */
    public function discountAmount(DiscountPrice $discount = null)
    {
        $total_discount = 0;
        $subtotal = $this->subtotal();

        // Determine the discount set on this item's price
        if ($discount) {
            $total_discount = $discount->on($subtotal);
        } else {
            // Determine all the discounts set on this item's price
            $temp_subtotal = $subtotal;
            foreach ($this->discounts as $discount) {
                $total_discount += $discount->on($temp_subtotal);

                // Update the subtotal for this item to remove the amount discounted
                $temp_subtotal = $discount->off($temp_subtotal);
            }
        }

        // Total discount not to exceed the subtotal amount, neither positive nor negative
        return (
            $subtotal >= 0
            ? min($subtotal, $total_discount)
            : max($subtotal, $total_discount)
        );
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

    /**
     * Resets the applied discount amounts for all assigned DiscountPrice's
     */
    public function resetDiscounts()
    {
        foreach ($this->discounts as $discount) {
            $discount->reset();
        }
    }
}
