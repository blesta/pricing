<?php

/**
 *
 */
class UnitPrice extends AbstractPriceDescription
{
    protected $price;
    protected $qty;

    public function __construct($price, $qty = 1)
    {
        $this->price = $price;
        $this->qty = $qty;
    }

    public function price()
    {
        return $this->price;
    }

    public function qty()
    {
        return $this->qty;
    }

    public function total()
    {
        return $this->qty * $this->price;
    }
}
