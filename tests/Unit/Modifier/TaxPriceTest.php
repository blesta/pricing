<?php
namespace Blesta\Pricing\Tests\Unit\Modifier;

use Blesta\Pricing\Modifier\TaxPrice;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Blesta\Pricing\Modifier\TaxPrice
 */
class TaxPriceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @uses Blesta\Pricing\Modifier\AbstractPriceModifier::__construct
     */
    public function testConstruct()
    {
        $this->assertInstanceOf('Blesta\Pricing\Modifier\TaxPrice', new TaxPrice(10.00, TaxPrice::EXCLUSIVE));
    }

    /**
     * @covers ::__construct
     * @expectedException InvalidArgumentException
     */
    public function testConstructException()
    {
        // Amount must be non-negative
        $tax = new TaxPrice(-10, TaxPrice::EXCLUSIVE);
    }

    /**
     * @covers ::off
     * @uses Blesta\Pricing\Modifier\TaxPrice::on
     * @dataProvider offProvider
     */
    public function testOff($tax, $price, $price_after)
    {
        $this->assertEquals($price_after, round($tax->off($price), 2));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function offProvider()
    {
        return [
            [new TaxPrice(0, TaxPrice::EXCLUSIVE), 10.00, 10.00],
            [new TaxPrice(50, TaxPrice::EXCLUSIVE), 10.00, 10.00],
            [new TaxPrice(100, TaxPrice::EXCLUSIVE), 10.00, 10.00],
            [new TaxPrice(0, TaxPrice::EXCLUSIVE), -10.00, -10.00],
            [new TaxPrice(50, TaxPrice::EXCLUSIVE), -10.00, -10.00],
            [new TaxPrice(100, TaxPrice::EXCLUSIVE), -10.00, -10.00],

            [new TaxPrice(0, TaxPrice::INCLUSIVE), 10.00, 10.00],
            [new TaxPrice(50, TaxPrice::INCLUSIVE), 10.00, 5.00],
            [new TaxPrice(100, TaxPrice::INCLUSIVE), 10.00, 0.00],
            [new TaxPrice(0, TaxPrice::INCLUSIVE), -10.00, -10.00],
            [new TaxPrice(50, TaxPrice::INCLUSIVE), -10.00, -5.00],
            [new TaxPrice(100, TaxPrice::INCLUSIVE), -10.00, 0.00],

            [new TaxPrice(0, TaxPrice::INCLUSIVE_CALCULATED), 10.00, 10.00],
            [new TaxPrice(50, TaxPrice::INCLUSIVE_CALCULATED), 10.00, 6.67],
            [new TaxPrice(100, TaxPrice::INCLUSIVE_CALCULATED), 10.00, 5.00],
            [new TaxPrice(0, TaxPrice::INCLUSIVE_CALCULATED), -10.00, -10.00],
            [new TaxPrice(50, TaxPrice::INCLUSIVE_CALCULATED), -10.00, -6.67],
            [new TaxPrice(100, TaxPrice::INCLUSIVE_CALCULATED), -10.00, -5.00],
        ];
    }

    /**
     * @covers ::on
     * @dataProvider onProvider
     */
    public function testOn($tax, $price, $tax_amount)
    {
        $this->assertEquals($tax_amount, round($tax->on($price), 2));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function onProvider()
    {
        return [
            [new TaxPrice(0, TaxPrice::EXCLUSIVE), 10.00, 0.00],
            [new TaxPrice(50, TaxPrice::EXCLUSIVE), 10.00, 5.00],
            [new TaxPrice(100, TaxPrice::EXCLUSIVE), 10.00, 10.00],
            [new TaxPrice(0, TaxPrice::EXCLUSIVE), -10.00, 0.00],
            [new TaxPrice(50, TaxPrice::EXCLUSIVE), -10.00, -5.00],
            [new TaxPrice(100, TaxPrice::EXCLUSIVE), -10.00, -10.00],

            [new TaxPrice(0, TaxPrice::INCLUSIVE), 10.00, 0.00],
            [new TaxPrice(50, TaxPrice::INCLUSIVE), 10.00, 5.00],
            [new TaxPrice(100, TaxPrice::INCLUSIVE), 10.00, 10.00],
            [new TaxPrice(0, TaxPrice::INCLUSIVE), -10.00, 0.00],
            [new TaxPrice(50, TaxPrice::INCLUSIVE), -10.00, -5.00],
            [new TaxPrice(100, TaxPrice::INCLUSIVE), -10.00, -10.00],

            [new TaxPrice(0, TaxPrice::INCLUSIVE_CALCULATED), 10.00, 0.00],
            [new TaxPrice(50, TaxPrice::INCLUSIVE_CALCULATED), 10.00, 3.33],
            [new TaxPrice(100, TaxPrice::INCLUSIVE_CALCULATED), 10.00, 5.00],
            [new TaxPrice(0, TaxPrice::INCLUSIVE_CALCULATED), -10.00, 0.00],
            [new TaxPrice(50, TaxPrice::INCLUSIVE_CALCULATED), -10.00, -3.33],
            [new TaxPrice(100, TaxPrice::INCLUSIVE_CALCULATED), -10.00, -5.00],
        ];
    }

    /**
     * @covers ::including
     * @uses Blesta\Pricing\Modifier\TaxPrice::on
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
        return [
            [new TaxPrice(0, TaxPrice::EXCLUSIVE), 10.00, 10.00],
            [new TaxPrice(50, TaxPrice::EXCLUSIVE), 10.00, 15.00],
            [new TaxPrice(100, TaxPrice::EXCLUSIVE), 10.00, 20.00],
            [new TaxPrice(0, TaxPrice::EXCLUSIVE), -10.00, -10.00],
            [new TaxPrice(50, TaxPrice::EXCLUSIVE), -10.00, -15.00],
            [new TaxPrice(100, TaxPrice::EXCLUSIVE), -10.00, -20.00],

            [new TaxPrice(0, TaxPrice::INCLUSIVE), 10.00, 10.00],
            [new TaxPrice(50, TaxPrice::INCLUSIVE), 10.00, 10.00],
            [new TaxPrice(100, TaxPrice::INCLUSIVE), 10.00, 10.00],
            [new TaxPrice(0, TaxPrice::INCLUSIVE), -10.00, -10.00],
            [new TaxPrice(50, TaxPrice::INCLUSIVE), -10.00, -10.00],
            [new TaxPrice(100, TaxPrice::INCLUSIVE), -10.00, -10.00],

            [new TaxPrice(0, TaxPrice::INCLUSIVE_CALCULATED), 10.00, 10.00],
            [new TaxPrice(50, TaxPrice::INCLUSIVE_CALCULATED), 10.00, 10.00],
            [new TaxPrice(100, TaxPrice::INCLUSIVE_CALCULATED), 10.00, 10.00],
            [new TaxPrice(0, TaxPrice::INCLUSIVE_CALCULATED), -10.00, -10.00],
            [new TaxPrice(50, TaxPrice::INCLUSIVE_CALCULATED), -10.00, -10.00],
            [new TaxPrice(100, TaxPrice::INCLUSIVE_CALCULATED), -10.00, -10.00],
        ];
    }
}
