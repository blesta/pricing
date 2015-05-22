<?php

/**
 * Maintains a collection of ItemPrice objects
 */
class ItemPriceCollection implements PriceTotalInterface, Iterator
{
    /**
     * @var array A collection of ItemPrice objects
     */
    private $collection = array();

    /**
     * @var int The current index position within the collection
     */
    private $position = 0;

    /**
     * Adds an ItemPrice to the collection
     *
     * @param ItemPrice $price An item to add to the collection
     * @return reference to this
     */
    public function append(ItemPrice $price)
    {
        $this->collection[] = $price;
        return $this;
    }

    /**
     * Removes an ItemPrice from the collection
     *
     * @param ItemPrice $price An item to remove from the collection
     * @return reference to this
     */
    public function remove(ItemPrice $price)
    {
        // Remove all instances of the price from the collection
        foreach ($this->collection as $index => $item) {
            if ($item === $price) {
                unset($this->collection[$index]);
            }
        }

        return $this;
    }

    /**
     * Retrieves the count of all ItemPrice objects in the collection
     *
     * @return int The number of ItemPrice objects in the collection
     */
    public function count()
    {
        return count($this->collection);
    }

    /**
     * Retrieves the total price of all items within the collection including taxes without discounts
     *
     * @return float The total price including taxes without including discounts
     */
    public function totalAfterTax()
    {
        $total = 0;
        foreach ($this->collection as $item) {
            $total += $item->totalAfterTax();
        }

        // Reset any discount amounts back
        $this->resetDiscounts();

        return $total;
    }

    /**
     * Retrieves the total price of all items within the collection including discounts without taxes
     *
     * @return float The total price including discounts without including taxes
     */
    public function totalAfterDiscount()
    {
        $total = 0;
        foreach ($this->collection as $item) {
            $total += $item->totalAfterDiscount();
        }

        // Reset any discount amounts back
        $this->resetDiscounts();

        return $total;
    }

    /**
     * Retrieves the subtotal of all items within the collection
     *
     * @return float The subtotal of all items in the collection
     */
    public function subtotal()
    {
        // Sum the subtotals of each ItemPrice
        $total = 0;
        foreach ($this->collection as $item_price) {
            $total += $item_price->subtotal();
        }

        return $total;
    }

    /**
     * Retrieves the total of all items within the collection
     *
     * @return float The total of all items in the collection
     */
    public function total()
    {
        // Sum the totals of each ItemPrice
        $total = 0;
        foreach ($this->collection as $item_price) {
            $total += $item_price->total();
        }

        // Reset any discount amounts back
        $this->resetDiscounts();

        return $total;
    }

    /**
     * Retrieves the total tax amount for all ItemPrice's within the collection
     *
     * @param TaxPrice $tax A TaxPrice to apply to all ItemPrice's in the collection, ignoring
     *  any TaxPrice's that may already be set on the items within the collection (optional)
     */
    public function taxAmount(TaxPrice $tax = null)
    {
        $total = 0;
        foreach ($this->collection as $item_price) {
            $total += $item_price->taxAmount($tax);
        }

        // Reset any discount amounts back
        $this->resetDiscounts();

        return $total;
    }

    /**
     * Retrieves the total discount amount for all items within the collection
     *
     * @param DiscountPrice $discount A DiscountPrice to apply to all ItemPrice's in the
     *  collection, ignoring any DiscountPrice's that may already be set on the items within
     *  the collection (optional)
     */
    public function discountAmount(DiscountPrice $discount = null)
    {
        // Apply the given discount to all items
        $total = 0;
        // Calculate the discount amount from each item's own discounts
        foreach ($this->collection as $item_price) {
            $total += $item_price->discountAmount($discount);
        }

        // Reset any discount amounts back
        $this->resetDiscounts();

        return $total;
    }

    /**
     * Retrieves a list of all unique TaxPrice objects apart of this collection
     *
     * @return array An array of TaxPrice objects
     */
    public function taxes()
    {
        // Include unique instances of TaxPrice
        $taxes = array();
        foreach ($this->collection as $item_price) {
            foreach ($item_price->taxes() as $tax_price) {
                if (!in_array($tax_price, $taxes, true)) {
                    $taxes[] = $tax_price;
                }
            }
        }

        return $taxes;
    }

    /**
     * Retrieves a list of all unique DiscountPrice objects apart of this collection
     *
     * @return array An array of DiscountPrice objects
     */
    public function discounts()
    {
        // Include unique instances of DiscountPrice
        $discounts = array();
        foreach ($this->collection as $item_price) {
            foreach ($item_price->discounts() as $discount_price) {
                if (!in_array($discount_price, $discounts, true)) {
                    $discounts[] = $discount_price;
                }
            }
        }

        return $discounts;
    }

    /**
     * Resets the applied discount amounts for all ItemPrice's in the collection
     */
    public function resetDiscounts()
    {
        foreach ($this->collection as $item_price) {
            $item_price->resetDiscounts();
        }
    }

    /**
     * Retrieves the item in the collection at the current pointer
     *
     * @return mixed The ItemPrice in the collection at the current position, otherwise null
     */
    public function current()
    {
        return (
            $this->valid()
            ? $this->collection[$this->position]
            : null
        );
    }

    /**
     * Retrieves the index currently being pointed at in the collection
     *
     * @return int The index of the position in the collection
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Moves the pointer to the next item in the collection
     */
    public function next()
    {
        // Set the next position to the position of the next item in the collection
        $position = $this->position;
        foreach ($this->collection as $index => $item) {
            if ($index > $position) {
                $this->position = $index;
                break;
            }
        }

        // If there is no next item in the collection, increment the position instead
        if ($position == $this->position) {
            ++$this->position;
        }
    }

    /**
     * Moves the pointer to the first item in the collection
     */
    public function rewind()
    {
        // Reset the array pointer to the first entry in the collection
        reset($this->collection);

        // Set the position to the first entry in the collection if there is one
        $first_index = key($this->collection);
        $this->position = (
            $first_index === null
            ? 0
            : $first_index
        );
    }

    /**
     * Determines whether the current pointer references a valid item in the collection
     *
     * @return boolean True if the pointer references a valid item in the collection, false otherwise
     */
    public function valid()
    {
        return array_key_exists($this->position, $this->collection);
    }
}
