<?php

use Blesta\Items\Item\ItemInterface;

/**
 * Interface for including Item data
 */
interface ItemMetaInterface
{
    /**
     * Attaches the given item
     *
     * @param ItemInterface $item The Item to add
     */
    public function attach(ItemInterface $item);

    /**
     * Detaches the given item
     *
     * @param ItemInterface $item The Item to remove
     */
    public function detach(ItemInterface $item);

    /**
     * Retrieves all attached meta items
     *
     * @return ItemCollection A collection containing the items
     */
    public function meta();
}
