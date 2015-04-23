<?php

/**
 * @coversDefaultClass AbstractPriceModifier
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
        $stub = $this->getMockForAbstractClass("AbstractPriceModifier", array($price, 'inclusive'));
        $stub->expects($this->any())
            ->method('amount')
            ->willReturn($price);
        $this->assertSame($price, $stub->amount());
    }

    /**
     * @covers ::__construct
     * @covers ::type
     */
    public function testType()
    {
        $type = 'inclusive';
        $stub = $this->getMockForAbstractClass("AbstractPriceModifier", array(10.00, $type));
        $stub->expects($this->any())
            ->method('type')
            ->willReturn($type);
        $this->assertSame($type, $stub->type());
    }
}
