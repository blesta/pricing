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
     */
    public function append(ItemPrice $price)
    {
        $this->collection[] = $price;
    }

    /**
     * Removes an ItemPrice from the collection
     *
     * @param ItemPrice $price An item to remove from the collection
     */
    public function remove(ItemPrice $price)
    {
        // Remove all instances of the price from the collection
        foreach ($this->collection as $index => $item) {
            if ($item === $price) {
                unset($this->collection[$index]);
            }
        }

        #$this->collection = array_values($this->collection);
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
        return (
            $this->valid()
            ? $this->collection[$this->position]
            : null
        );
    }
    public function key()
    {
        return $this->position;
    }
    public function next()
    {
        // Set the next position to the next entry in the collection
        $position = ++$this->position;
        if (false !== next($this->collection)) {
            $this->position = key($this->collection);
        }
    }
    public function rewind()
    {
        // Set the current position to the first entry in the collection
        reset($this->collection);
        $this->position = key($this->collection);
    }
    public function valid()
    {
        return array_key_exists($this->position, $this->collection);
    }
}
