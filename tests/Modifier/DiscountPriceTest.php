<?php

/**
 * @coversDefaultClass DiscountPrice
 */
class DiscountPriceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @uses AbstractPriceModifier::__construct
     */
    public function testConstruct()
    {
        $this->assertInstanceOf("DiscountPrice", new DiscountPrice(5.00, 'percent'));
        $this->assertInstanceOf("DiscountPrice", new DiscountPrice(5.00, 'amount'));
    }

    /**
     * Test InvalidArgumentException is thrown
     *
     * @covers ::__construct
     * @uses AbstractPriceModifier::__construct
     * @expectedException InvalidArgumentException
     */
    public function testConstructException()
    {
        // Amount must be non-negative
        $discount = new DiscountPrice(-1, 'amount');
    }

    /**
     * @covers ::off
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
            array(new DiscountPrice(10, 'percent'), 10.00, 9.00),
            array(new DiscountPrice(50, 'percent'), 10.00, 5.00),
            array(new DiscountPrice(100, 'percent'), 10.00, 0.00),
            array(new DiscountPrice(200, 'percent'), 10.00, 0.00),

            array(new DiscountPrice(0, 'percent'), -10.00, -10.00),
            array(new DiscountPrice(10, 'percent'), -10.00, -11.00),
            array(new DiscountPrice(50, 'percent'), -10.00, -15.00),
            array(new DiscountPrice(100, 'percent'), -10.00, -20.00),
            array(new DiscountPrice(200, 'percent'), -10.00, -20.00),

            array(new DiscountPrice(0, 'amount'), 10.00, 10.00),
            array(new DiscountPrice(3, 'amount'), 10.00, 7.00),
            array(new DiscountPrice(50, 'amount'), 10.00, 0.00),
            array(new DiscountPrice(100, 'amount'), 10.00, 0.00),
            array(new DiscountPrice(3, 'amount'), -10.00, -13.00),
            array(new DiscountPrice(50, 'amount'), -10.00, -20.00),
            array(new DiscountPrice(100, 'amount'), -10.00, -20.00),
        );
    }

    /**
     * Test amount discounts for multiple prices, as the discount remaining should
     * change with each price the discount is applied to
     *
     * @covers ::off
     * @uses DiscountPrice::on
     * @dataProvider offMultipleProvider
     */
    public function testOffMultiple($discount, $prices, $price_after_all)
    {
        $price_remaining = 0;
        foreach ($prices as $price) {
            $price_remaining += $discount->off($price);
        }

        $this->assertEquals($price_after_all, $price_remaining);
    }

    /**
     * Data provider for testOffMultiple
     * @return array
     */
    public function offMultipleProvider()
    {
        return array(
            array(new DiscountPrice(0, 'amount'), array(4, 10), 14),
            array(new DiscountPrice(10, 'amount'), array(4, 10), 4),
            array(new DiscountPrice(20, 'amount'), array(4, 10), 0),
            array(new DiscountPrice(100, 'amount'), array(4, 10), 0),
            array(new DiscountPrice(10, 'amount'), array(-4, -10), -24),
            array(new DiscountPrice(20, 'amount'), array(-4, -10), -28),
            array(new DiscountPrice(100, 'amount'), array(-4, -10), -28),
            array(new DiscountPrice(5, 'amount'), array(-4, 10), 1),

            array(new DiscountPrice(10, 'amount'), array(9, 5, 4), 8),
        );
    }

    /**
     * @covers ::on
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
            array(new DiscountPrice(200, 'percent'), 10.00, 10.00),
            array(new DiscountPrice(0, 'percent'), -10.00, 0.00),
            array(new DiscountPrice(50, 'percent'), -10.00, -5.00),
            array(new DiscountPrice(100, 'percent'), -10.00, -10.00),
            array(new DiscountPrice(200, 'percent'), -10.00, -10.00),

            array(new DiscountPrice(0, 'amount'), 10.00, 0.00),
            array(new DiscountPrice(3, 'amount'), 10.00, 3.00),
            array(new DiscountPrice(10, 'amount'), 10.00, 10.00),
            array(new DiscountPrice(20, 'amount'), 10.00, 10.00),
            array(new DiscountPrice(0, 'amount'), -10.00, 0.00),
            array(new DiscountPrice(3, 'amount'), -10.00, -3.00),
            array(new DiscountPrice(10, 'amount'), -10.00, -10.00),
            array(new DiscountPrice(20, 'amount'), -10.00, -10.00),
        );
    }

    /**
     * @covers ::reset
     * @uses DiscountPrice::__construct
     * @uses AbstractPriceModifier::__construct
     * @uses DiscountPrice::off
     * @uses DiscountPrice::on
     */
    public function testReset()
    {
        $discount = new DiscountPrice(10, 'amount');
        $this->assertEquals(0, $discount->off(10));
        $this->assertEquals(10, $discount->off(10));

        $discount->reset();
        $this->assertEquals(0, $discount->off(10));
    }
}
