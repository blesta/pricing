<?php

/**
 * @coversDefaultClass ItemPrice
 */
class ItemPriceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $this->assertInstanceOf("ItemPrice", new ItemPrice(5.00, 1));
    }

    /**
     * @covers ::setDiscount
     * @covers ::discounts
     * @dataProvider discountProvider
     */
    public function testSetDiscount($item, $discount)
    {
        $item->setDiscount($discount);
        $this->assertContains($discount, $item->discounts());

        $item->setDiscount($discount);
        $this->assertCount(
            1,
            $item->discounts(),
            'Only one instance of each discount may exist.'
        );
    }

    /**
     * Discount data provider
     *
     * @return array
     */
    public function discountProvider()
    {
        return array(
            array(new ItemPrice(10, 0), new DiscountPrice(10, 'percent')),
            array(new ItemPrice(10), new DiscountPrice(10, 'percent'))
        );
    }


    /**
     * @covers ::setTax
     * @covers ::taxes
     * @dataProvider taxProvider
     */
    public function testSetTax($item, $tax)
    {
        $item->setTax($tax);
        $this->assertContains($tax, $item->taxes());

        $item->setTax($tax);
        $this->assertCount(
            1,
            $item->taxes(),
            'Only one instance of each tax may exist.'
        );
    }

    /**
     * Tax data provider
     *
     * @return array
     */
    public function taxProvider()
    {
        return array(
            array(new ItemPrice(10, 0), new TaxPrice(10, 'exclusive')),
            array(new ItemPrice(10), new TaxPrice(25, 'exclusive'))
        );
    }
}
