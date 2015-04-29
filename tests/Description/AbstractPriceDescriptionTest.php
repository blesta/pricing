<?php

/**
 * @coversDefaultClass AbstractPriceDescription
 */
class AbstractPriceDescriptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::setDescription
     * @covers ::getDescription
     */
    public function testGetDescription()
    {
        // Initially null
        $description = null;
        $stub = $this->getMockForAbstractClass("AbstractPriceDescription");
        $this->assertSame($description, $stub->getDescription());

        // Set my own description
        $description = '100x Product 1 - Limited Time Offer';
        $stub = $this->getMockForAbstractClass("AbstractPriceDescription");
        $stub->setDescription($description);
        $this->assertSame($description, $stub->getDescription());
    }
}
