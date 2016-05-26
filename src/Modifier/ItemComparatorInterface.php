<?php

/**
 * Item Comparator Interface for ItemPrices
 */
interface ItemComparatorInterface
{
    /**
     * Sets the pricing callback that expects four arguments:
     * - the old price as a float
     * - the new price as a float
     * - the old ItemPrice meta data for an item
     * - the new ItemPrice meta data for an item
     * and returns a float value representing a price.
     *
     * @param callable $callback The callback
     */
    public function setPriceCallback(callable $callback);

    /**
     * Sets the pricing callback that expects two arguments:
     * - the old ItemPrice meta data for an item
     * - the new ItemPrice meta data for an item
     * and returns a string representing the ItemPrice description
     *
     * @param callable $callback The callback
     */
    public function setDescriptionCallback(callable $callback);

    /**
     * Combines two ItemPrices into a single ItemPrice
     *
     * @param ItemPrice $item1 An ItemPrice to merge
     * @param ItemPrice $item2 An ItemPrice to merge with
     * @return ItemPrice|null The merged ItemPrice, or null
     */
    public function merge(ItemPrice $item1, ItemPrice $item2);
}
