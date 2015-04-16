<?php

class DiscountPriceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers DiscountPrice::__construct
     * @uses AbstractPriceModifier::__construct
     */
    public function testConstruct()
    {
        $this->assertInstanceOf("DiscountPrice", new DiscountPrice(5.00, 'percent'));
    }

    /**
     * @covers DiscountPrice::off
     * @uses DiscountPrice::on
     * @dataProvider offProvider
     */
    public function testOff($discount, $price, $price_after)
    {
        $this->assertEquals($price_after, $discount->off($price));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function offProvider()
    {
        return array(
            array(new DiscountPrice(0, 'percent'), 10.00, 10.00),
            array(new DiscountPrice(50, 'percent'), 10.00, 5.00),
            array(new DiscountPrice(100, 'percent'), 10.00, 0.00),
            array(new DiscountPrice(-100, 'percent'), 10.00, 10.00),

            array(new DiscountPrice(0, 'amount'), 10.00, 10.00),
            array(new DiscountPrice(50, 'amount'), 10.00, 0.00),
            array(new DiscountPrice(100, 'amount'), 10.00, 0.00),
            array(new DiscountPrice(-100, 'amount'), 10.00, 10.00)
        );
    }

    /**
     * @covers DiscountPrice::on
     * @dataProvider onProvider
     */
    public function testOn($discount, $price, $discount_price)
    {
        $this->assertEquals($discount_price, $discount->on($price));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function onProvider()
    {
        return array(
            array(new DiscountPrice(0, 'percent'), 10.00, 0.00),
            array(new DiscountPrice(50, 'percent'), 10.00, 5.00),
            array(new DiscountPrice(100, 'percent'), 10.00, 10.00),
            array(new DiscountPrice(-100, 'percent'), 10.00, 0.00),

            array(new DiscountPrice(0, 'amount'), 10.00, 0.00),
            array(new DiscountPrice(50, 'amount'), 10.00, 10.00),
            array(new DiscountPrice(100, 'amount'), 10.00, 10.00),
            array(new DiscountPrice(-100, 'amount'), 10.00, 0.00)
        );
    }
}
