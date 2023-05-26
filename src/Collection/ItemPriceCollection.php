<?php
namespace Blesta\Pricing\Collection;

use Blesta\Pricing\Modifier\DiscountPrice;
use Blesta\Pricing\Modifier\ItemComparatorInterface;
use Blesta\Pricing\Modifier\TaxPrice;
use Blesta\Pricing\Total\PriceTotalInterface;
use Blesta\Pricing\Type\ItemPrice;
use Iterator;

/**
 * Maintains a collection of ItemPrice objects
 */
class ItemPriceCollection implements PriceTotalInterface, Iterator
{
    /**
     * @var array A collection of ItemPrice objects
     */
    private $collection = [];

    /**
     * @var int The current index position within the collection
     */
    private $position = 0;

    /**
     * Adds an ItemPrice to the collection
     *
     * @param ItemPrice $price An item to add to the collection
     * @return ItemPriceCollection reference to this
     */
    #[\ReturnTypeWillChange]
    public function append(ItemPrice $price)
    {
        $this->collection[] = $price;
        return $this;
    }

    /**
     * Removes an ItemPrice from the collection
     *
     * @param ItemPrice $price An item to remove from the collection
     * @return ItemPriceCollection reference to this
     */
    #[\ReturnTypeWillChange]
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
    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->collection);
    }

    /**
     * Retrieves the total price of all items within the collection including taxes without discounts
     *
     * @return float The total price including taxes without including discounts
     */
    #[\ReturnTypeWillChange]
    public function totalAfterTax()
    {
        $total = 0;
        foreach ($this->collection as $item) {
            $total += $item->totalAfterTax();
        }

        // Reset any discount amounts or excluded tax types back
        $this->resetDiscounts();
        $this->resetTaxes();

        return $total;
    }

    /**
     * Retrieves the total price of all items within the collection including discounts without taxes
     *
     * @return float The total price including discounts without including taxes
     */
    #[\ReturnTypeWillChange]
    public function totalAfterDiscount()
    {
        $total = 0;
        foreach ($this->collection as $item) {
            $total += $item->totalAfterDiscount();
        }

        // Reset any discount amounts or excluded tax types back
        $this->resetDiscounts();
        $this->resetTaxes();

        return $total;
    }

    /**
     * Retrieves the subtotal of all items within the collection
     *
     * @return float The subtotal of all items in the collection
     */
    #[\ReturnTypeWillChange]
    public function subtotal()
    {
        // Sum the subtotals of each ItemPrice
        $total = 0;
        foreach ($this->collection as $item_price) {
            $total += $item_price->subtotal();
        }
        $this->resetDiscounts();

        return $total;
    }

    /**
     * Retrieves the total of all items within the collection
     *
     * @return float The total of all items in the collection
     */
    #[\ReturnTypeWillChange]
    public function total()
    {
        // Sum the totals of each ItemPrice
        $total = 0;
        foreach ($this->collection as $item_price) {
            $total += $item_price->total();
        }

        // Reset any discount amounts or excluded tax types back
        $this->resetDiscounts();
        $this->resetTaxes();

        return $total;
    }

    /**
     * Retrieves the total tax amount for all ItemPrice's within the collection
     *
     * @param TaxPrice $tax A TaxPrice to apply to all ItemPrice's in the collection, ignoring
     *  any TaxPrice's that may already be set on the items within the collection (optional)
     * @param string $type The type of tax for which to retrieve amounts (optional)
     */
    #[\ReturnTypeWillChange]
    public function taxAmount(TaxPrice $tax = null, $type = null)
    {
        $total = 0;
        foreach ($this->collection as $item_price) {
            $total += $item_price->taxAmount($tax, $type);
        }

        // Reset any discount amounts or excluded tax types back
        $this->resetDiscounts();
        $this->resetTaxes();

        return $total;
    }

    /**
     * Retrieves the total discount amount for all items within the collection
     *
     * @param DiscountPrice $discount A DiscountPrice to apply to all ItemPrice's in the
     *  collection, ignoring any DiscountPrice's that may already be set on the items within
     *  the collection (optional)
     */
    #[\ReturnTypeWillChange]
    public function discountAmount(DiscountPrice $discount = null)
    {
        // Apply the given discount to all items
        $total = 0;
        // Calculate the discount amount from each item's own discounts
        foreach ($this->collection as $item_price) {
            $total += $item_price->discountAmount($discount);
        }

        // Reset any discount amounts or excluded tax types back
        $this->resetDiscounts();
        $this->resetTaxes();

        return $total;
    }

    /**
     * Retrieves a list of all unique TaxPrice objects apart of this collection
     *
     * @return array An array of TaxPrice objects
     */
    #[\ReturnTypeWillChange]
    public function taxes()
    {
        // Include unique instances of TaxPrice
        $taxes = [];
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
    #[\ReturnTypeWillChange]
    public function discounts()
    {
        // Include unique instances of DiscountPrice
        $discounts = [];
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
    #[\ReturnTypeWillChange]
    public function resetDiscounts()
    {
        foreach ($this->collection as $item_price) {
            $item_price->resetDiscounts();
        }
    }

    /**
     * Marks the given tax type as not shown in totals returned by all ItemPrices in the collection
     *
     * @param string $tax_type The type of tax to exclude
     * @return ItemPriceCollection A reference to this object
     */
    #[\ReturnTypeWillChange]
    public function excludeTax($tax_type)
    {
        foreach ($this->collection as $item_price) {
            $item_price->excludeTax($tax_type);
        }

        return $this;
    }

    /**
     * Resets the list of tax types for all ItemPrices in the collection
     */
    #[\ReturnTypeWillChange]
    public function resetTaxes()
    {
        foreach ($this->collection as $item_price) {
            $item_price->resetTaxes();
        }
    }

    /**
     * Merges this ItemPriceCollection with the given ItemPriceCollection to produce
     * a new ItemPriceCollection for ItemPrices that share a key.
     *
     * The resulting ItemPriceCollection is composed of ItemPrices as constructed by
     * the given comparator.
     *
     * Multiple items sharing the same key from the same collection are subject to
     * being merged multiple times in the order in which they appear in the collection.
     *
     * @param ItemPriceCollection $collection The collection to be merged
     * @param ItemComparatorInterface $comparator The comparator used to merge item prices
     */
    #[\ReturnTypeWillChange]
    public function merge(ItemPriceCollection $collection, ItemComparatorInterface $comparator)
    {
        // Set a new collection for the merged results
        $price_collection = new self();

        foreach ($collection as $new_item) {
            foreach ($this as $current_item) {
                // Only items with matching non-null keys may be merged
                if ($current_item->key() !== null
                    && $current_item->key() === $new_item->key()
                    && ($item = $comparator->merge($current_item, $new_item))
                ) {
                    $price_collection->append($item);
                }
            }
        }

        return $price_collection;
    }

    /**
     * Retrieves the item in the collection at the current pointer
     *
     * @return mixed The ItemPrice in the collection at the current position, otherwise null
     */
    #[\ReturnTypeWillChange]
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
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->position;
    }

    /**
     * Moves the pointer to the next item in the collection
     */
    #[\ReturnTypeWillChange]
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
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        // Reset the array pointer to the first entry in the collection
        reset($this->collection);

        // Set the position to the first entry in the collection if there is one
        $first_index = key($this->collection);
        $this->position = $first_index === null
            ? 0
            : $first_index;
    }

    /**
     * Determines whether the current pointer references a valid item in the collection
     *
     * @return bool True if the pointer references a valid item in the collection, false otherwise
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return array_key_exists($this->position, $this->collection);
    }
}
