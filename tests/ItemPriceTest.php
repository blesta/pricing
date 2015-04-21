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

    /**
     * @covers ::totalAfterTax
     * @uses ItemPrice::subtotal
     * @uses ItemPrice::setTax
     * @dataProvider totalAfterTaxProvider
     */
    public function testTotalAfterTax($item, $taxes)
    {
        // No taxes set. Subtotal is the total after tax
        $this->assertEquals($item->subtotal(), $item->totalAfterTax());

        // Set taxes
        call_user_func_array(array($item, "setTax"), $taxes);

        // Total will be larger or smaller than the subtotal if it's positive or negative
        if ($item->subtotal() > 0) {
            $this->assertGreaterThan($item->subtotal(), $item->totalAfterTax());
        }
        elseif ($item->subtotal() < 0) {
            $this->assertLessThan($item->subtotal(), $item->totalAfterTax());
        }
        else {
            $this->assertEquals(0, $item->totalAfterTax());
        }
    }

    /**
     * Total After Tax data provider
     *
     * @return array
     */
    public function totalAfterTaxProvider()
    {
        return array(
            array(new ItemPrice(100.00, 2), array(new TaxPrice(10, 'exclusive'))),
            array(new ItemPrice(0.00, 2), array(new TaxPrice(10, 'exclusive'))),
            array(new ItemPrice(-100.00, 2), array(new TaxPrice(10, 'exclusive'))),

            array(new ItemPrice(100.00, 2), array(new TaxPrice(10, 'exclusive'), new TaxPrice(10, 'exclusive'))),
            array(new ItemPrice(-100.00, 2), array(new TaxPrice(10, 'exclusive'), new TaxPrice(20, 'exclusive'))),
        );
    }

    /**
     * @covers ::totalAfterDiscount
     * @uses ItemPrice::subtotal
     * @dataProvider totalAfterDiscountProvider
     */
    public function testTotalAfterDiscount($item, $discounts)
    {
        // No discounts set. Subtotal is the total after discount
        $this->assertEquals($item->subtotal(), $item->totalAfterDiscount());

        foreach ($discounts as $discount) {
            $item->setDiscount($discount);
        }

        // Total will be larger or smaller than the subtotal if it's positive or negative
        if ($item->subtotal() > 0) {
            $this->assertLessThanOrEqual($item->subtotal(), $item->totalAfterDiscount());
        }
        else {
            $this->assertGreaterThanOrEqual($item->subtotal(), $item->totalAfterDiscount());
        }
    }

    /**
     * Total After Discount data provider
     *
     * @return array
     */
    public function totalAfterDiscountProvider()
    {
        return array(
            array(new ItemPrice(10, 1), array(new DiscountPrice(10, 'percent'))),
            array(new ItemPrice(0, 1), array(new DiscountPrice(10, 'percent'))),
            array(new ItemPrice(10, 2), array(new DiscountPrice(10, 'percent'), new DiscountPrice(10, 'percent'))),
            array(new ItemPrice(-10, 2), array(new DiscountPrice(10, 'percent'))),
            array(new itemPrice(10, 2), array(new DiscountPrice(3, 'amount'))),
            array(new itemPrice(-10, 2), array(new DiscountPrice(5, 'amount'))),
        );
    }

    /**
     * @covers ::subtotal
     * @dataProvider subtotalProvider
     */
    public function testSubtotal($price, $qty)
    {
        $item = new ItemPrice($price, $qty);
        $this->assertEquals($price*$qty, $item->subtotal());
    }

    /**
     * Subtotal provider
     *
     * @return array
     */
    public function subtotalProvider() {
        return array(
            array(10.00, 2),
            array(10.00, 1),
            array(10.00, 0),
            array(0, 5),
            array(-10.00, 1),
            array(-10.00, 2),
        );
    }

    /**
     * @covers ::total
     * @uses ItemPrice::subTotal
     * @uses ItemPrice::setDiscount
     * @uses ItemPrice::setTax
     * @uses ItemPrice::totalAfterTax
     * @uses ItemPrice::totalAfterDiscount
     * @uses ItemPrice::taxAmount
     */
    public function testTotal()
    {
        $item = new ItemPrice(10, 2);
        $tax = new TaxPrice(5.25, 'exclusive');
        $discount = new DiscountPrice(50, 'percent');

        // Total is the subtotal when no taxes or discounts exist
        $this->assertEquals($item->subtotal(), $item->total());

        // Total is the total after tax when no discount exists
        $item->setTax($tax);
        $this->assertEquals($item->totalAfterTax(), $item->total());

        // Total is the total after discount and tax
        $item->setDiscount($discount);
        $this->assertEquals($item->totalAfterDiscount() + $item->taxAmount(), $item->total());
    }

    /**
     * @covers ::discounts
     * @uses DiscountPrice::__construct
     * @uses ItemPrice::setDiscount
     */
    public function testDiscounts()
    {
        // No discounts set
        $item = new ItemPrice(10, 1);
        $this->assertEmpty($item->discounts());

        $discounts = array(
            new DiscountPrice(10, 'exclusive'),
            new DiscountPrice(5.00, 'exclusive')
        );

        foreach ($discounts as $discount) {
            // Check the discount is set
            $item->setDiscount($discount);
            $this->assertContains($discount, $item->discounts());
        }

        // Check all discounts are set
        $this->assertCount(count($discounts), $item->discounts());
    }

    /**
     * @covers ::taxAmount
     * @uses ItemPrice::setTax
     * @uses TaxPrice::on
     * @dataProvider taxAmountProvider
     */
    public function testTaxAmount($item, array $taxes)
    {
        // No taxes set. No tax amount
        $subtotal = $item->subtotal();
        $this->assertEquals(0, $item->taxAmount());

        // Set all taxes
        call_user_func_array(array($item, "setTax"), $taxes);

        // Test a specific tax amount just for the first tax
        $this->assertEquals($taxes[0]->on($subtotal), $item->taxAmount($taxes[0]));

        // Test with all taxes applied
        $tax_amount = $item->taxAmount();
        if ($subtotal >= 0) {
            $this->assertGreaterThanOrEqual(0, $tax_amount);
        }
        else {
            $this->assertLessThanOrEqual(0, $tax_amount);
        }

        // Test compound tax specifically
        if (count($taxes) > 1) {
            // Compound tax is greater than the sum of each tax individually
            $tax_sum = 0;
            foreach ($taxes as $tax) {
                $tax_sum += $item->taxAmount($tax);
            }

            $this->assertGreaterThan($tax_sum, $item->taxAmount());
        }
    }

    /**
     * Tax Amount provider
     *
     * @return array
     */
    public function taxAmountProvider()
    {
        return array(
            array(new ItemPrice(100, 2), array(new TaxPrice(10, 'exclusive'))),
            array(new ItemPrice(100, 2), array(new TaxPrice(10, 'exclusive'), new TaxPrice(7.75, 'exclusive'))),
            array(new ItemPrice(0, 2), array(new TaxPrice(10, 'exclusive'))),
            array(new ItemPrice(-100, 2), array(new TaxPrice(10, 'exclusive'))),
        );
    }

    /**
     * @covers ::discountAmount
     * @uses ItemPrice::setDiscount
     * @uses ItemPrice::subtotal
     * @uses DiscountPrice::on
     * @dataProvider discountAmountProvider
     */
    public function testDiscountAmount($item, array $discounts)
    {
        // No discount set
        $subtotal = $item->subtotal();
        $this->assertEquals(0, $item->discountAmount());

        foreach ($discounts as $discount) {
            $item->setDiscount($discount);

            // Test discount amount just for this discount
            $this->assertEquals($discount->on($subtotal), $item->discountAmount($discount));
        }

        // Test with all discounts applied
        if ($subtotal >= 0) {
            $this->assertLessThanOrEqual($subtotal, $item->discountAmount());
        }
        else {
            $this->assertGreaterThanOrEqual($subtotal, $item->discountAmount());
        }
    }

    /**
     * Discount amount provider
     *
     * @return array
     */
    public function discountAmountProvider()
    {
        return array(
            array(new ItemPrice(100, 2), array()),
            array(new ItemPrice(100, 2), array(new DiscountPrice(10, 'percent'))),
            array(new ItemPrice(100, 2), array(new DiscountPrice(10, 'percent'), new DiscountPrice(20, 'percent'))),
            array(new ItemPrice(100, 2), array(new DiscountPrice(100, 'percent'))),
            array(new ItemPrice(100, 2), array(new DiscountPrice(2, 'amount'), new DiscountPrice(3.75, 'amount'))),
            array(new ItemPrice(100, 2), array(new DiscountPrice(20, 'percent'), new DiscountPrice(2, 'amount'))),

            array(new ItemPrice(-100, 2), array(new DiscountPrice(10, 'percent'))),
            array(new ItemPrice(-100, 2), array(new DiscountPrice(10, 'percent'), new DiscountPrice(20, 'percent'))),
            array(new ItemPrice(-100, 2), array(new DiscountPrice(100, 'percent'))),
            array(new ItemPrice(-100, 2), array(new DiscountPrice(2, 'amount'), new DiscountPrice(3.75, 'amount'))),
            array(new ItemPrice(-100, 2), array(new DiscountPrice(20, 'percent'), new DiscountPrice(2, 'amount'))),
        );
    }

    /**
     * @covers ::taxes
     */
    public function testTaxes()
    {
        $item = new ItemPrice(10);

        // No taxes set
        $this->assertCount(0, $item->taxes());

        // 1 tax set
        $item->setTax(new TaxPrice(10, 'exclusive'));
        $this->assertCount(1, $item->taxes());

        // 3 taxes set
        $item->setTax(new TaxPrice(100, 'exclusive'), new TaxPrice(20, 'exclusive'));
        $this->assertCount(3, $item->taxes());
    }
}
