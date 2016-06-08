<?php
namespace Blesta\Pricing\Tests\Unit;

use Blesta\Pricing\PricingFactory;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Blesta\Pricing\PricingFactory
 */
class PricingFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::unitPrice
     * @uses Blesta\Pricing\Type\UnitPrice::__construct
     * @uses Blesta\Pricing\Type\UnitPrice::setPrice
     * @uses Blesta\Pricing\Type\UnitPrice::setQty
     * @uses Blesta\Pricing\Type\UnitPrice::setKey
     */
    public function testUnitPrice()
    {
        $pricing_factory = new PricingFactory();
        $this->assertInstanceOf('Blesta\Pricing\Type\UnitPrice', $pricing_factory->unitPrice(5.00, 2));
    }

    /**
     * @covers ::itemPrice
     * @uses Blesta\Pricing\Type\ItemPrice::__construct
     * @uses Blesta\Pricing\Type\ItemPrice::resetDiscountSubtotal
     * @uses Blesta\Pricing\Type\ItemPrice::subtotal
     * @uses Blesta\Pricing\Type\UnitPrice::__construct
     * @uses Blesta\Pricing\Type\UnitPrice::setPrice
     * @uses Blesta\Pricing\Type\UnitPrice::setQty
     * @uses Blesta\Pricing\Type\UnitPrice::setKey
     * @uses Blesta\Pricing\Type\UnitPrice::total
     */
    public function testItemPrice()
    {
        $pricing_factory = new PricingFactory();
        $this->assertInstanceOf('Blesta\Pricing\Type\ItemPrice', $pricing_factory->itemPrice(5.00, 2));
    }

    /**
     * @covers ::discountPrice
     * @uses Blesta\Pricing\Modifier\DiscountPrice::__construct
     * @uses Blesta\Pricing\Modifier\AbstractPriceModifier::__construct
     */
    public function testDiscountPrice()
    {
        $pricing_factory = new PricingFactory();
        $this->assertInstanceOf(
            'Blesta\Pricing\Modifier\DiscountPrice',
            $pricing_factory->discountPrice(20.00, 'percent')
        );
    }

    /**
     * @covers ::taxPrice
     * @uses Blesta\Pricing\Modifier\TaxPrice::__construct
     * @uses Blesta\Pricing\Modifier\AbstractPriceModifier::__construct
     */
    public function testTaxPrice()
    {
        $pricing_factory = new PricingFactory();
        $this->assertInstanceOf(
            'Blesta\Pricing\Modifier\TaxPrice',
            $pricing_factory->taxPrice(7.75, 'exclusive')
        );
    }

    /**
     * @covers ::itemPriceCollection
     */
    public function testItemPriceCollection()
    {
        $pricing_factory = new PricingFactory();
        $this->assertInstanceOf(
            'Blesta\Pricing\Collection\ItemPriceCollection',
            $pricing_factory->itemPriceCollection()
        );
    }
}
