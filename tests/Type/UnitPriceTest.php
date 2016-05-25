<?php

class UnitPriceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers UnitPrice::__construct
     * @covers UnitPrice::setPrice
     * @covers UnitPrice::setQty
     * @covers UnitPrice::setKey
     */
    public function testConstruct()
    {
        $this->assertInstanceOf("UnitPrice", new UnitPrice(5.00, 1, 'id'));
    }

    /**
     * @covers UnitPrice::price
     * @uses UnitPrice::__construct
     * @uses UnitPrice::setPrice
     * @uses UnitPrice::setQty
     * @uses UnitPrice::setKey
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
     * @uses UnitPrice::setPrice
     * @uses UnitPrice::setQty
     * @uses UnitPrice::setKey
     */
    public function testQty()
    {
        // Test default quantity
        $price = 5.00;
        $unit_price = new UnitPrice($price);
        $this->assertEquals(1, $unit_price->qty());

        $price = 5.00;
        $qty = 2;
        $unit_price = new UnitPrice($price, $qty);
        $this->assertEquals($qty, $unit_price->qty());
    }

    /**
     * @covers UnitPrice::key
     * @covers UnitPrice::setKey
     * @uses UnitPrice::__construct
     * @uses UnitPrice::setPrice
     * @uses UnitPrice::setQty
     */
    public function testKey()
    {
        // No key is null
        $price = 5.00;
        $unit_price = new UnitPrice($price);
        $this->assertNull($unit_price->key());

        // Set a key
        $key = 'id';
        $unit_price->setKey($key);
        $this->assertEquals($key, $unit_price->key());
    }

    /**
     * @covers UnitPrice::total
     * @uses UnitPrice::__construct
     * @uses UnitPrice::setPrice
     * @uses UnitPrice::setQty
     * @uses UnitPrice::setKey
     */
    public function testTotal()
    {
        $price = 5.00;
        $qty = 2;
        $unit_price = new UnitPrice($price, $qty);
        $this->assertEquals($qty * $price, $unit_price->total());
    }
}
