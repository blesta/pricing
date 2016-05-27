<?php

/**
 * Abstract Item Comparator for ItemPrices
 */
abstract class AbstractItemComparator implements ItemComparatorInterface
{
    /**
     * @var A callback method for fetching a price
     */
    protected $price_callback;
    /**
     * @var A callback method for fetching a description
     */
    protected $description_callback;

    /**
     * Initializes a set of callbacks
     *
     * @param callable $price_callback The pricing callback that accepts four
     *  arguments for the old and new price, and the old and new ItemPrice
     *  meta data (each a Blesta\Items\Item\ItemCollection), and returns a float
     * @param callable $description_callback The description callback that
     *  accepts two arguments for the old and new ItemPrice meta data (each
     *  a Blesta\Items\Item\ItemCollection), and returns a string
     */
    public function __construct(callable $price_callback, callable $description_callback)
    {
        $this->setPriceCallback($price_callback);
        $this->setDescriptionCallback($description_callback);
    }

    /**
     * {@inheritdoc}
     */
    public function setPriceCallback(callable $callback)
    {
        $this->price_callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescriptionCallback(callable $callback)
    {
        $this->description_callback = $callback;
    }
}
