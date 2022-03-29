<?php
namespace Blesta\Pricing\Type;

use Blesta\Pricing\Modifier\DiscountPrice;
use Blesta\Pricing\Modifier\TaxPrice;
use Blesta\Pricing\Total\PriceTotalInterface;
use InvalidArgumentException;

/**
 * Determine pricing for a single item considering discounts and taxes
 */
class ItemPrice extends UnitPrice implements PriceTotalInterface
{
    /**
     * @var array A cached value of discount subtotals
     */
    private $discount_amounts = [];
    /**
     * @var bool Whether or not to cache discount subtotals
     */
    private $cache_discount_amounts = false;
    /**
     * @var float The item price subtotal after individual discounts were applied
     */
    private $discounted_subtotal = 0;

    /**
     * @var array A numerically-indexed array of DiscountPrice objects
     */
    protected $discounts = [];
    /**
     * @var array A numerically-indexed array containing an array of TaxPrice objects
     */
    protected $taxes = [];
    /**
     * @var array A list of tax types and whether or not they are shown in totals returned by this object
     */
    protected $tax_types = [
        TaxPrice::INCLUSIVE => true,
        TaxPrice::EXCLUSIVE => true,
        TaxPrice::INCLUSIVE_CALCULATED => true,
    ];
    /**
     * @var bool Whether to apply discounts before calculating tax
     */
    protected $discount_taxes = true;

    /**
     * Initialize the item price
     *
     * @param float $price The unit price
     * @param int $qty The quantity of unit prices (optional, default 1)
     * @param string $key A unique identifier (optional, default null)
     */
    public function __construct($price, $qty = 1, $key = null)
    {
        parent::__construct($price, $qty, $key);

        // Reset the internal discount subtotal
        $this->resetDiscountSubtotal();
    }

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
     * Sets whether to calculate tax before or after discounts are applied
     *
     * @param bool $discount_taxes True to calculate taxes after discounts are applied, false otherwise
     */
    public function setDiscountTaxes($discount_taxes)
    {
        $this->discount_taxes = $discount_taxes;
    }

    /**
     * Retrieves the total item price amount considering all taxes without discounts
     */
    public function totalAfterTax()
    {
        return parent::total() + $this->taxAmount();
    }

    /**
     * Retrieves the total item price amount considering all discounts without taxes
     */
    public function totalAfterDiscount()
    {
        return parent::total() - $this->discountAmount();
    }

    /**
     * Retrieves the total item price amount not considering discounts or taxes
     *
     * @return float The item subtotal
     */
    public function subtotal()
    {
        // inclusive_calculated taxes should be subtracted from the subtotal
        if (parent::total() < 0) {
            return parent::total() + abs($this->taxAmount(null, TaxPrice::INCLUSIVE_CALCULATED));
        } else {
            return parent::total() - abs($this->taxAmount(null, TaxPrice::INCLUSIVE_CALCULATED));
        }
    }

    /**
     * Retrieves the total item price amount considering all discounts and taxes
     *
     * @return float The total item price
     */
    public function total()
    {
        // discountAmount() is called twice: once by totalAfterDiscount, and once by taxAmount
        // The discount must be removed only once, so flag it to be ignored the second time
        $this->discount_amounts = [];
        $this->cache_discount_amounts = true;
        $total = $this->totalAfterDiscount();

        // Include tax without taking the discount off again, and reset the flag
        $this->cache_discount_amounts = false;
        $total += $this->taxAmount();
        $this->discount_amounts = [];

        return $total;
    }

    /**
     * Retrieves the total tax amount considering all item taxes, or just the given tax
     *
     * @param TaxPrice $tax A specific tax price whose tax to calculate for this item (optional, default null)
     * @param string $type The type of tax for which to retrieve amounts (optional) (see $tax_types for options)
     * @return float The total tax amount for all taxes set for this item, or the total tax amount
     *  for the given tax price if given
     */
    public function taxAmount(TaxPrice $tax = null, $type = null)
    {
        // Determine the tax set on this item's price
        if ($tax) {
            $tax_amount = $this->amountTax($tax);
        } else {
            $tax_amount = $this->amountTaxAll($type);
        }

        return $tax_amount;
    }

    /**
     * Retrieves the tax amount for the given TaxPrice
     *
     * @param TaxPrice $tax A specific tax price whose tax to calculate for this item
     * @return float The total tax amount for the given TaxPrice
     */
    private function amountTax(TaxPrice $tax)
    {
        // Apply tax either before or after the discount
        $taxable_price = $this->discount_taxes ? $this->totalAfterDiscount() : parent::total();
        $tax_amount = 0;

        foreach ($this->taxes as $tax_group) {
            // Only calculate the tax amount if the tax exists in a tax group
            if (in_array($tax, $tax_group, true)) {
                $tax_amount = $this->compoundTaxAmount($tax_group, $taxable_price, $tax);
            }
        }

        return $tax_amount;
    }

    /**
     * Retrieves the total tax amount considering all item taxes
     *
     * @param string $type The type of tax for which to retrieve amounts (optional) (see $tax_types for options)
     * @return float The total tax amount for all taxes set for this item
     */
    private function amountTaxAll($type = null)
    {
        // Apply tax either before or after the discount
        $taxable_price = $this->discount_taxes ? $this->totalAfterDiscount() : parent::total();
        $tax_amount = 0;

        // Determine all taxes set on this item's price, compounded accordingly
        foreach ($this->taxes as $tax_group) {
            // Sum all taxes
            $tax_amount += $this->compoundTaxAmount($tax_group, $taxable_price, null, $type);
        }

        return $tax_amount;
    }

    /**
     * Retrieves the tax amount for a specific tax group
     *
     * @param array $tax_group A subset of the taxes array
     * @param float $taxable_price The total amount from which to calculate tax
     * @param TaxPrice $tax A specific tax from the group whose tax amount to retrieve (optional)
     * @param string $type The type of tax for which to retrieve amounts (optional) (see $tax_types for options)
     * @return float The total tax amount for all taxes set for this item in this group, or
     *  the tax amount for the given TaxPrice
     */
    private function compoundTaxAmount(array $tax_group, $taxable_price, TaxPrice $tax = null, $type = null)
    {
        $compound_tax = 0;
        $tax_total = 0;

        foreach ($tax_group as $tax_price) {
            // If a tax type is specified, skip taxes that don't match it
            $tax_type = $tax_price->type();

            // Calculate the compound tax
            $tax_amount = $tax_price->on($taxable_price + $compound_tax);

            // Subtracted or inclusive_calculated taxes should be deducted from the compound tax
            if ($tax_price->subtract || $tax_type === TaxPrice::INCLUSIVE_CALCULATED) {
                $compound_tax -= $tax_amount;
            } else {
                $compound_tax += $tax_amount;
            }

            if ($type && $tax_type !== $type) {
                continue;
            }

            if (isset($this->tax_types[$tax_type]) && $this->tax_types[$tax_type]) {
                if ($tax_price->subtract) {
                    // Subtract the tax amount instead of adding
                    $tax_total -= $tax_amount;
                } elseif ($tax_type === TaxPrice::INCLUSIVE_CALCULATED && $type !== TaxPrice::INCLUSIVE_CALCULATED) {
                    // Don't add inclusive_calculated taxes to the total unless specifically
                    // fetching the total for that tax type
                    $tax_total += 0;
                } else {
                    // Add tax normally
                    $tax_total += $tax_amount;
                }
            } elseif ($tax && $tax === $tax_price) {
                // Return a total of zero if we were given a tax, but it is of an excluded tax type
                return 0;
            }

            // Ignore any other group taxes, and only return the tax amount for the given TaxPrice
            if ($tax && $tax === $tax_price) {
                return $tax_amount;
            }
        }

        return $tax_total;
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
        $subtotal = parent::total();

        // Determine the discount set on this item's price
        if ($discount) {
            $total_discount = $this->amountDiscount($discount);
        } else {
            $total_discount = $this->amountDiscountAll();
        }

        // Total discount not to exceed the subtotal amount, neither positive nor negative
        return (
            $subtotal >= 0
                ? min($subtotal, $total_discount)
                : max($subtotal, $total_discount)
        );
    }

    /**
     * Retrieves the total discount amount considering the given discount
     *
     * @param DiscountPrice $discount A specific discount price whose discount to calculate
     *  for this item
     * @return float The total discount amount for the given discount price
     */
    private function amountDiscount(DiscountPrice $discount)
    {
        $total_discount = 0;

        // Only calculate the discount amount if the discount is set for this item
        if (in_array($discount, $this->discounts, true)) {
            // Get the discount on the discounted subtotal remaining
            $total_discount = $discount->on($this->discounted_subtotal);

            // Update the discounted subtotal for this item by removing the amount discounted
            $this->discounted_subtotal = $discount->off($this->discounted_subtotal);
        }

        return $total_discount;
    }

    /**
     * Retrieves the total discount amount considering all item discounts
     *
     * @return float The total discount amount for all discounts set for this item
     */
    private function amountDiscountAll()
    {
        $subtotal = parent::total();
        $total_discount = 0;

        // Determine all the discounts set on this item's price
        foreach ($this->discounts as $key => $discount) {
            // Fetch the discount amount and remove it from the DiscountPrice,
            // or use the values previously cached
            if ($this->cache_discount_amounts || empty($this->discount_amounts)) {
                // Get the discount on the subtotal
                $discount_amount = $discount->on($subtotal);
                $total_discount += $discount_amount;

                // Cache the discount set for this DiscountPrice
                if ($this->cache_discount_amounts) {
                    $this->discount_amounts[$key] = $discount_amount;
                }

                // Update the subtotal for this item to remove the amount discounted
                $subtotal = $discount->off($subtotal);
            } else {
                // Use the cached discount amount for this DiscountPrice
                $total_discount += $this->discount_amounts[$key];
            }
        }

        return $total_discount;
    }

    /**
     * Fetch all unique taxes set
     *
     * @param bool $unique True to fetch all unique taxes for the item,
     *  or false to fetch all groups of taxes (default true)
     * @return array An array of TaxPrice objects when $unique is true,
     *  or an array containing arrays of grouped TaxPrice objects
     */
    public function taxes($unique = true)
    {
        // Retrieve all taxes within their respective groups
        if (!$unique) {
            return $this->taxes;
        }

        // Retrieve all unique taxes
        $all_taxes = [];
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
        // Reset the internal discounted subtotal
        $this->resetDiscountSubtotal();

        // Reset each discount
        foreach ($this->discounts as $discount) {
            $discount->reset();
        }
    }

    /**
     * Resets the discounted subtotal used internally
     */
    private function resetDiscountSubtotal()
    {
        $this->discounted_subtotal = parent::total();
    }

    /**
     * Marks the given tax type as not shown in totals returned by this object
     *
     * @param string $tax_type The type of tax to exclude
     * @return A reference to this object
     */
    public function excludeTax($tax_type)
    {
        if (array_key_exists($tax_type, $this->tax_types)) {
            $this->tax_types[$tax_type] = false;
        }

        return $this;
    }

    /**
     * Resets the list of tax types to show in totals returned by this object
     */
    public function resetTaxes()
    {
        foreach ($this->tax_types as $type => $value) {
            $this->tax_types[$type] = true;
        }
    }
}
