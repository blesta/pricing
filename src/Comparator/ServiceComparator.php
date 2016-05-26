<?php

/**
 * Compares service data for pricing
 */
class ServiceComparator extends AbstractItemComparator
{
    /**
     * {@inheritdoc}
     */
    public function merge(ItemPrice $item1, ItemPrice $item2)
    {
        // Create a new item
        $factory = new PricingFactory();

        // The new item's price has 1 quantity, re-uses the new key, and is set a new price
        $item = $factory->itemPrice($this->price($item1, $item2), 1, $item2->key());

        // The new item's description is determined by the caller's callback on the meta data
        $description = call_user_func(
            $this->description_callback,
            $item1->meta(),
            $item2->meta()
        );
        $item->setDescription($description);

        // Set the same discounts and taxes as the to item
        foreach ($item2->discounts() as $discount) {
            $item->setDiscount($discount);
        }

        // Set each group of taxes exactly as they are set on the to item
        foreach ($item2->taxes(false) as $tax_group) {
            call_user_func_array(array($item, 'setTax'), $tax_group);
        }

        return $item;
    }

    /**
     * Retrieves the combined price of the given items
     *
     * @param ItemPrice $item1 The from item
     * @param ItemPrice $item2 The to item
     * @return float The combined price
     */
    private function price(ItemPrice $item1, ItemPrice $item2)
    {
        // The from price (being removed) should be the total, including discounts and taxes
        $from_price = $item1->total();

        // The to price (being added) should be the combined quantity and price without
        // taxes or discounts, as those are applied to the item
        $to_price = $item2->qty() * $item2->price();

        // Use the callback to determine the price based on the current prices and item meta
        return call_user_func(
            $this->price_callback,
            $from_price,
            $to_price,
            $item1->meta(),
            $item2->meta()
        );
    }
}
