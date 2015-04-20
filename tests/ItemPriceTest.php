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
    public function testSetTax($item, array $taxes)
    {
        // Add all given taxes
        call_user_func_array(array($item, "setTax"), $taxes);
        foreach ($taxes as $tax) {
            $this->assertContains($tax, $item->taxes());
        }

        // At most, each tax should be added, but may be less if duplicates set
        $num_set_taxes = count($item->taxes());
        $this->assertLessThanOrEqual(count($taxes), $num_set_taxes);

        // The same tax should not be added again
        $item->setTax($taxes[0]);
        $this->assertCount(
            $num_set_taxes,
            $item->taxes(),
            'Only one instance of each tax may exist.'
        );
    }

    /**
     * @covers ::setTax
     * @expectedException InvalidArgumentException
     */
    public function testSetTaxException() {
        $item = new ItemPrice(10);
        $item->setTax(new stdClass());
    }

    /**
     * Tax data provider
     *
     * @return array
     */
    public function taxProvider()
    {
        $tax_price1 = new TaxPrice(10, 'exclusive');
        $tax_price2 = new TaxPrice(10, 'exclusive');

        return array(
            array(new ItemPrice(10, 0), array($tax_price1)),
            array(new ItemPrice(10), array($tax_price1)),
            array(new ItemPrice(10, 1), array($tax_price1, $tax_price2)),
            array(new ItemPrice(10, 1), array($tax_price1, $tax_price1)),
        );
    }
}
