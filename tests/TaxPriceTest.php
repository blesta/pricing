<?php

class TaxPriceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers TaxPrice::__construct
     * @uses AbstractPriceModifier::__construct
     */
    public function testConstruct()
    {
        $this->assertInstanceOf('TaxPrice', new TaxPrice(10.00, 'exclusive'));
    }

    /**
     * @covers TaxPrice::__construct
     * @expectedException InvalidArgumentException
     */
    public function testConstructException()
    {
        // Amount must be non-negative
        $tax = new TaxPrice(-10, 'exclusive');
    }

    /**
     * @covers TaxPrice::off
     * @uses TaxPrice::on
     * @dataProvider offProvider
     */
    public function testOff($tax, $price, $price_after)
    {
        $this->assertEquals($price_after, $tax->off($price));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function offProvider()
    {
        return array(
            array(new TaxPrice(0, 'exclusive'), 10.00, 10.00),
            array(new TaxPrice(50, 'exclusive'), 10.00, 10.00),
            array(new TaxPrice(100, 'exclusive'), 10.00, 10.00),
            array(new TaxPrice(0, 'exclusive'), -10.00, -10.00),
            array(new TaxPrice(50, 'exclusive'), -10.00, -10.00),
            array(new TaxPrice(100, 'exclusive'), -10.00, -10.00),

            array(new TaxPrice(0, 'inclusive'), 10.00, 10.00),
            array(new TaxPrice(50, 'inclusive'), 10.00, 5.00),
            array(new TaxPrice(100, 'inclusive'), 10.00, 0.00),
            array(new TaxPrice(0, 'inclusive'), -10.00, -10.00),
            array(new TaxPrice(50, 'inclusive'), -10.00, -5.00),
            array(new TaxPrice(100, 'inclusive'), -10.00, 0.00),
        );
    }

    /**
     * @covers TaxPrice::on
     * @dataProvider onProvider
     */
    public function testOn($tax, $price, $tax_amount)
    {
        $this->assertEquals($tax_amount, $tax->on($price));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function onProvider()
    {
        return array(
            array(new TaxPrice(0, 'exclusive'), 10.00, 0.00),
            array(new TaxPrice(50, 'exclusive'), 10.00, 5.00),
            array(new TaxPrice(100, 'exclusive'), 10.00, 10.00),
            array(new TaxPrice(0, 'exclusive'), -10.00, 0.00),
            array(new TaxPrice(50, 'exclusive'), -10.00, -5.00),
            array(new TaxPrice(100, 'exclusive'), -10.00, -10.00),

            array(new TaxPrice(0, 'inclusive'), 10.00, 0.00),
            array(new TaxPrice(50, 'inclusive'), 10.00, 5.00),
            array(new TaxPrice(100, 'inclusive'), 10.00, 10.00),
            array(new TaxPrice(0, 'inclusive'), -10.00, 0.00),
            array(new TaxPrice(50, 'inclusive'), -10.00, -5.00),
            array(new TaxPrice(100, 'inclusive'), -10.00, -10.00),
        );
    }

    /**
     * @covers TaxPrice::including
     * @uses TaxPrice::on
     * @dataProvider includingProvider
     */
    public function testIncluding($tax, $price, $result)
    {
        $this->assertEquals($result, $tax->including($price));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function includingProvider()
    {
        return array(
            array(new TaxPrice(0, 'exclusive'), 10.00, 10.00),
            array(new TaxPrice(50, 'exclusive'), 10.00, 15.00),
            array(new TaxPrice(100, 'exclusive'), 10.00, 20.00),
            array(new TaxPrice(0, 'exclusive'), -10.00, -10.00),
            array(new TaxPrice(50, 'exclusive'), -10.00, -15.00),
            array(new TaxPrice(100, 'exclusive'), -10.00, -20.00),

            array(new TaxPrice(0, 'inclusive'), 10.00, 10.00),
            array(new TaxPrice(50, 'inclusive'), 10.00, 10.00),
            array(new TaxPrice(100, 'inclusive'), 10.00, 10.00),
            array(new TaxPrice(0, 'inclusive'), -10.00, -10.00),
            array(new TaxPrice(50, 'inclusive'), -10.00, -10.00),
            array(new TaxPrice(100, 'inclusive'), -10.00, -10.00),
        );
    }
}
