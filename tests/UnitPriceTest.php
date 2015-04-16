<?php

class UnitPriceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers UnitPrice::__construct
     */
    public function testConstruct()
    {
        $this->assertInstanceOf("UnitPrice", new UnitPrice(5.00, 1));
    }

    /**
     * @covers UnitPrice::price
     * @uses UnitPrice::__construct
     */
    public function testPrice()
    {
        $price = 5.00;
        $qty = 2;
        $unit_price = new UnitPrice($price, $qty);
        $this->assertEquals($price, $unit_price->price());
    }

    /**
     * @covers UnitPrice::qty
     * @uses UnitPrice::__construct
     */
    public function testQty()
    {
        $price = 5.00;
        $qty = 2;
        $unit_price = new UnitPrice($price, $qty);
        $this->assertEquals($qty, $unit_price->qty());
    }

    /**
     * @covers UnitPrice::total
     * @uses UnitPrice::__construct
     */
    public function testTotal()
    {
        $price = 5.00;
        $qty = 2;
        $unit_price = new UnitPrice($price, $qty);
        $this->assertEquals($qty * $price, $unit_price->total());
    }
}
