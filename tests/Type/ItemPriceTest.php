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
     * @uses UnitPrice::__construct
     * @uses TaxPrice::__construct
     * @uses AbstractPriceModifier::__construct
     * @expectedException InvalidArgumentException
     */
    public function testSetTaxInvalidException()
    {
        // Invalid argument: not TaxPrice
        $item = new ItemPrice(10);
        $item->setTax(new stdClass());
    }

    /**
     * @covers ::setTax
     * @uses UnitPrice::__construct
     * @uses TaxPrice::__construct
     * @uses AbstractPriceModifier::__construct
     * @expectedException InvalidArgumentException
     */
    public function testSetTaxMultipleException()
    {
        // Invalid argument: Multiple TaxPrice's of the same instance
        $item = new ItemPrice(10);
        $tax_price = new TaxPrice(10, 'exclusive');
        $item->setTax($tax_price, $tax_price);
    }

    /**
     * Tax data provider
     *
     * @return array
     */
    public function taxProvider()
    {
        return array(
            array(new ItemPrice(10, 0), array(new TaxPrice(10, 'exclusive'))),
            array(new ItemPrice(10), array(new TaxPrice(10, 'exclusive'))),
            array(new ItemPrice(10, 1), array(new TaxPrice(10, 'exclusive'), new TaxPrice(10, 'exclusive'))),
        );
    }

    /**
     * @covers ::totalAfterTax
     * @uses ItemPrice::__construct
     * @uses ItemPrice::subtotal
     * @uses ItemPrice::setTax
     * @uses ItemPrice::taxAmount
     * @uses ItemPrice::amountTax
     * @uses ItemPrice::amountTaxAll
     * @uses ItemPrice::compoundTaxAmount
     * @uses ItemPrice::totalAfterDiscount
     * @uses ItemPrice::discountAmount
     * @uses ItemPrice::amountDiscount
     * @uses ItemPrice::amountDiscountAll
     * @uses UnitPrice::__construct
     * @uses UnitPrice::total
     * @uses TaxPrice::__construct
     * @uses TaxPrice::on
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
        } elseif ($item->subtotal() < 0) {
            $this->assertLessThan($item->subtotal(), $item->totalAfterTax());
        } else {
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
     * @uses ItemPrice::__construct
     * @uses ItemPrice::subtotal
     * @uses ItemPrice::setDiscount
     * @uses ItemPrice::discountAmount
     * @uses ItemPrice::amountDiscount
     * @uses ItemPrice::amountDiscountAll
     * @uses UnitPrice::__construct
     * @uses UnitPrice::total
     * @uses DiscountPrice::__construct
     * @uses DiscountPrice::on
     * @uses DiscountPrice::off
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
        } else {
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
     * @uses UnitPrice::__construct
     * @uses UnitPrice::total
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
    public function subtotalProvider()
    {
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
     * @uses ItemPrice::setTax
     * @uses ItemPrice::setDiscount
     * @uses ItemPrice::totalAfterTax
     * @uses ItemPrice::totalAfterDiscount
     * @uses ItemPrice::taxAmount
     * @uses ItemPrice::amountTax
     * @uses ItemPrice::amountTaxAll
     * @uses ItemPrice::compoundTaxAmount
     * @uses ItemPrice::discountAmount
     * @uses ItemPrice::amountDiscount
     * @uses ItemPrice::amountDiscountAll
     * @uses ItemPrice::subtotal
     * @uses AbstractPriceModifier::__construct
     * @uses TaxPrice::__construct
     * @uses TaxPrice::on
     * @uses DiscountPrice::__construct
     * @uses DiscountPrice::on
     * @uses DiscountPrice::off
     * @uses UnitPrice::__construct
     * @uses UnitPrice::total
     */
    public function testTotal()
    {
        $item = new ItemPrice(10, 2);

        // Total is the subtotal when no taxes or discounts exist
        $this->assertEquals($item->subtotal(), $item->total());

        // Total is the total after tax when no discount exists
        $item->setTax(new TaxPrice(5.25, 'exclusive'));
        $this->assertEquals($item->totalAfterTax(), $item->total());

        // Total is the total after discount and tax
        $item->setDiscount(new DiscountPrice(50, 'percent'));
        $this->assertEquals($item->totalAfterDiscount() + $item->taxAmount(), $item->total());
    }

    /**
     * @covers ::discounts
     * @uses ItemPrice::setDiscount
     * @uses DiscountPrice::__construct
     * @uses UnitPrice::__construct
     * @uses AbstractPriceModifier::__construct
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
     * @covers ::amountTax
     * @covers ::amountTaxALl
     * @covers ::compoundTaxAmount
     * @uses ItemPrice::setTax
     * @uses ItemPrice::setDiscount
     * @uses ItemPrice::totalAfterTax
     * @uses ItemPrice::totalAfterDiscount
     * @uses ItemPrice::discountAmount
     * @uses ItemPrice::amountDiscount
     * @uses ItemPrice::amountDiscountAll
     * @uses ItemPrice::subtotal
     * @uses TaxPrice::__construct
     * @uses TaxPrice::on
     * @uses UnitPrice::__construct
     * @uses UnitPrice::total
     * @dataProvider taxAmountProvider
     */
    public function testTaxAmount($item, array $taxes, $expected_amount)
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
        } else {
            $this->assertLessThanOrEqual(0, $tax_amount);
        }

        // The given expected amount should be the end result with all taxes applied
        $this->assertEquals($expected_amount, $item->taxAmount());
    }

    /**
     * Tax Amount provider
     *
     * @return array
     */
    public function taxAmountProvider()
    {
        return array(
            array(new ItemPrice(100, 2), array(new TaxPrice(10, 'exclusive')), 20),
            array(new ItemPrice(0, 2), array(new TaxPrice(10, 'exclusive')), 0),
            array(new ItemPrice(-100, 2), array(new TaxPrice(10, 'exclusive')), -20),
        );
    }

    /**
     * @covers ::taxAmount
     * @covers ::amountTax
     * @covers ::amountTaxALl
     * @covers ::compoundTaxAmount
     * @uses ItemPrice::setTax
     * @uses ItemPrice::setDiscount
     * @uses ItemPrice::totalAfterTax
     * @uses ItemPrice::totalAfterDiscount
     * @uses ItemPrice::discountAmount
     * @uses ItemPrice::amountDiscount
     * @uses ItemPrice::amountDiscountAll
     * @uses ItemPrice::subtotal
     * @uses TaxPrice::__construct
     * @uses TaxPrice::on
     * @uses UnitPrice::__construct
     * @uses UnitPrice::total
     * @dataProvider taxAmountCompoundProvider
     */
    public function testTaxAmountCompound($item, array $taxes, $expected_amount)
    {
        // Set all taxes
        call_user_func_array(array($item, "setTax"), $taxes);

        // Compound tax is greater than the sum of each tax individually
        $tax_sum = 0;
        foreach ($taxes as $tax) {
            $tax_amount = $item->taxAmount($tax);
            $tax_sum += $tax_amount;
            $this->assertGreaterThanOrEqual($tax_amount, $item->taxAmount());
        }

        // Sum of individual tax calculations are greater than the total
        // i.e. Tax1 + (Tax1 + Tax2) > (Tax1 + Tax2)
        $this->assertLessThan($tax_sum, $item->taxAmount());

        // Total tax amount is the expected amount
        $this->assertEquals($expected_amount, $item->taxAmount());
    }

    /**
     * Compound Tax Amount provider
     *
     * @return array
     */
    public function taxAmountCompoundProvider()
    {
        return array(
            array(new ItemPrice(100, 2), array(new TaxPrice(10, 'exclusive'), new TaxPrice(7.75, 'exclusive')), 37.05),
        );
    }

    /**
     * @covers ::discountAmount
     * @covers ::amountDiscount
     * @covers ::amountDiscountAll
     * @uses ItemPrice::setDiscount
     * @uses ItemPrice::subtotal
     * @uses UnitPrice::__construct
     * @uses UnitPrice::total
     * @dataProvider discountAmountProvider
     */
    public function testDiscountAmount($item, array $discounts, $expected_amount)
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
        } else {
            $this->assertGreaterThanOrEqual($subtotal, $item->discountAmount());
        }

        // The given expected amount should be the end result with all discounts applied
        $this->assertEquals($expected_amount, $item->discountAmount());
    }

    /**
     * Creates a stub of DiscountPrice
     *
     * @param mixed $value The value to mock from DiscountPrice::on
     * @return stub
     */
    protected function discountPriceMock($value)
    {
        $dp = $this->getMockBuilder("DiscountPrice")
            ->disableOriginalConstructor()
            ->getMock();
        $dp->method("on")
            ->willReturn($value);

        return $dp;
    }

    /**
     * Discount amount provider
     *
     * @return array
     */
    public function discountAmountProvider()
    {
        return array(
            array(new ItemPrice(100, 2), array(), 0),
            array(new ItemPrice(100, 2), array($this->discountPriceMock(20)), 20),
            array(
                new ItemPrice(100, 2),
                array(
                    $this->discountPriceMock(20),
                    $this->discountPriceMock(40)
                ),
                60
            ),
            array(new ItemPrice(100, 2), array($this->discountPriceMock(200)), 200),
            array(
                new ItemPrice(100, 2),
                array(
                    $this->discountPriceMock(2),
                    $this->discountPriceMock(3.75)
                ),
                5.75
            ),
            array(
                new ItemPrice(100, 2),
                array(
                    $this->discountPriceMock(40),
                    $this->discountPriceMock(2)
                ),
                42
            ),

            array(new ItemPrice(-100, 2), array($this->discountPriceMock(-20)), -20),
            array(
                new ItemPrice(-100, 2),
                array(
                    $this->discountPriceMock(-20),
                    $this->discountPriceMock(-40)
                ),
                -60
            ),
            array(new ItemPrice(-100, 2), array($this->discountPriceMock(-200)), -200),
            array(
                new ItemPrice(-100, 2),
                array(
                    $this->discountPriceMock(-2),
                    $this->discountPriceMock(-3.75)
                ),
                -5.75
            ),
            array(
                new ItemPrice(-100, 2),
                array(
                    $this->discountPriceMock(-40),
                    $this->discountPriceMock(-2)
                ),
                -42
            ),
        );
    }

    /**
     * @covers ::resetDiscounts
     * @uses ItemPrice::__construct
     * @uses ItemPrice::setDiscount
     */
    public function testResetDiscounts()
    {
        $discountMock = $this->getMockBuilder('DiscountPrice')
            ->disableOriginalConstructor()
            ->getMock();
        $discountMock->expects($this->once())
            ->method('reset');

        $item = new ItemPrice(10);
        $item->setDiscount($discountMock);
        $item->resetDiscounts();
    }

    /**
     * @covers ::taxes
     * @uses ItemPrice::setTax
     * @uses ItemPrice::__construct
     * @uses TaxPrice::__construct
     * @uses AbstractPriceModifier::__construct
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
