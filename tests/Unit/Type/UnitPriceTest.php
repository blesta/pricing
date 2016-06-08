<?php
namespace Blesta\Pricing\Tests\Unit\Type;

use Blesta\Pricing\Type\UnitPrice;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Blesta\Pricing\Type\UnitPrice
 */
class UnitPriceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::setPrice
     * @covers ::setQty
     * @covers ::setKey
     */
    public function testConstruct()
    {
        $this->assertInstanceOf('Blesta\Pricing\Type\UnitPrice', new UnitPrice(5.00, 1, 'id'));
    }

    /**
     * @covers ::price
     * @covers ::setPrice
     * @uses Blesta\Pricing\Type\UnitPrice::__construct
     * @uses Blesta\Pricing\Type\UnitPrice::setQty
     * @uses Blesta\Pricing\Type\UnitPrice::setKey
     */
    public function testPrice()
    {
        $price = 5.00;
        $qty = 2;
        $unit_price = new UnitPrice($price, $qty);
        $this->assertEquals($price, $unit_price->price());

        $price = 15.00;
        $unit_price->setPrice($price);
        $this->assertEquals($price, $unit_price->price());
    }

    /**
     * @covers ::qty
     * @covers ::setQty
     * @uses Blesta\Pricing\Type\UnitPrice::__construct
     * @uses Blesta\Pricing\Type\UnitPrice::setPrice
     * @uses Blesta\Pricing\Type\UnitPrice::setKey
     */
    public function testQty()
    {
        // Test default quantity
        $price = 5.00;
        $unit_price = new UnitPrice($price);
        $this->assertEquals(1, $unit_price->qty());

        $qty = 5;
        $unit_price->setQty($qty);
        $this->assertEquals($qty, $unit_price->qty());
    }

    /**
     * @covers ::key
     * @covers ::setKey
     * @uses Blesta\Pricing\Type\UnitPrice::__construct
     * @uses Blesta\Pricing\Type\UnitPrice::setPrice
     * @uses Blesta\Pricing\Type\UnitPrice::setQty
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
     * @covers ::total
     * @uses Blesta\Pricing\Type\UnitPrice::__construct
     * @uses Blesta\Pricing\Type\UnitPrice::setPrice
     * @uses Blesta\Pricing\Type\UnitPrice::setQty
     * @uses Blesta\Pricing\Type\UnitPrice::setKey
     */
    public function testTotal()
    {
        $price = 5.00;
        $qty = 2;
        $unit_price = new UnitPrice($price, $qty);
        $this->assertEquals($qty * $price, $unit_price->total());
    }
}
