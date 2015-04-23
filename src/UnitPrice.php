<?php

/**
 * Builds a unit price
 */
class UnitPrice extends AbstractPriceDescription
{
    /**
     * @var float The unit price
     */
    protected $price;

    /**
     * @var int The quantity of unit prices
     */
    protected $qty;

    /**
     * Initialize the unit price
     *
     * @param float $price The unit price
     * @param int $qty The quantity of unit prices (optional, default 1)
     */
    public function __construct($price, $qty = 1)
    {
        $this->price = $price;
        $this->qty = $qty;
    }

    /**
     * Retrieves the set unit price
     *
     * @return float The unit price
     */
    public function price()
    {
        return $this->price;
    }

    /**
     * Retrieves the set quantity
     *
     * @return int The quantity
     */
    public function qty()
    {
        return $this->qty;
    }

    /**
     * Retrieves the total price
     *
     * @return float The total price considering quantity
     */
    public function total()
    {
        return $this->qty * $this->price;
    }
}
