<?php
namespace Blesta\Pricing\Tests\Unit\Description;

use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Blesta\Pricing\Description\AbstractPriceDescription
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
        $stub = $this->getMockForAbstractClass('Blesta\Pricing\Description\AbstractPriceDescription');
        $this->assertSame($description, $stub->getDescription());

        // Set my own description
        $description = '100x Product 1 - Limited Time Offer';
        $stub = $this->getMockForAbstractClass('Blesta\Pricing\Description\AbstractPriceDescription');
        $stub->setDescription($description);
        $this->assertSame($description, $stub->getDescription());
    }
}
