<?php
namespace Blesta\Pricing\Tests\Unit\Modifier;

use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Blesta\Pricing\Modifier\AbstractPriceModifier
 */
class AbstractPriceModifierTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::amount
     */
    public function testAmount()
    {
        $price = 5.00;
        $stub = $this->getMockForAbstractClass('Blesta\Pricing\Modifier\AbstractPriceModifier', [$price, 'inclusive']);
        $this->assertSame($price, $stub->amount());
    }

    /**
     * @covers ::__construct
     * @covers ::type
     */
    public function testType()
    {
        $type = 'inclusive';
        $stub = $this->getMockForAbstractClass('Blesta\Pricing\Modifier\AbstractPriceModifier', [10.00, $type]);
        $this->assertSame($type, $stub->type());
    }

    /**
     * @covers ::__construct
     * @covers ::reset
     */
    public function testReset()
    {
        $stub = $this->getMockForAbstractClass('Blesta\Pricing\Modifier\AbstractPriceModifier', [10.00, 'inclusive']);
        $this->assertNull($stub->reset());
    }
}
