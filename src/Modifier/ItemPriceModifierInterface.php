<?php

/**
 * Interface for modifying ItemPrices
 */
interface ItemPriceModifierInterface
{
    /**
     * Combines two ItemPrices into a single ItemPrice
     *
     * @param ItemPrice $item1 An ItemPrice to merge
     * @param ItemPrice $item2 An ItemPrice to merge with
     * @return ItemPrice|null The merged ItemPrice, or null
     */
    public function merge(ItemPrice $item1, ItemPrice $item2);
}
