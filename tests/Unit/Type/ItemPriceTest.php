<?php
namespace Blesta\Pricing\Tests\Unit\Type;

use Blesta\Pricing\Type\ItemPrice;
use Blesta\Pricing\Modifier\DiscountPrice;
use Blesta\Pricing\Modifier\TaxPrice;
use PHPUnit_Framework_TestCase;
use stdClass;

/**
 * @coversDefaultClass Blesta\Pricing\Type\ItemPrice
 */
class ItemPriceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::resetDiscountSubtotal
     * @uses Blesta\Pricing\Type\ItemPrice::subtotal
     * @uses Blesta\Pricing\Type\UnitPrice::__construct
     * @uses Blesta\Pricing\Type\UnitPrice::setPrice
     * @uses Blesta\Pricing\Type\UnitPrice::setQty
     * @uses Blesta\Pricing\Type\UnitPrice::setKey
     * @uses Blesta\Pricing\Type\UnitPrice::total
     */
    public function testConstruct()
    {
        $this->assertInstanceOf('Blesta\Pricing\Type\ItemPrice', new ItemPrice(5.00, 1, 'id'));
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
        return [
            [new ItemPrice(10, 0), new DiscountPrice(10, 'percent')],
            [new ItemPrice(10), new DiscountPrice(10, 'percent')]
        ];
    }


    /**
     * @covers ::setTax
     * @covers ::taxes
     * @dataProvider taxProvider
     */
    public function testSetTax($item, array $taxes)
    {
        // Add all given taxes
        call_user_func_array([$item, 'setTax'], $taxes);
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
     * @uses Blesta\Pricing\Type\ItemPrice::__construct
     * @uses Blesta\Pricing\Type\ItemPrice::resetDiscountSubtotal
     * @uses Blesta\Pricing\Type\ItemPrice::subtotal
     * @uses Blesta\Pricing\Type\UnitPrice::__construct
     * @uses Blesta\Pricing\Type\UnitPrice::setPrice
     * @uses Blesta\Pricing\Type\UnitPrice::setQty
     * @uses Blesta\Pricing\Type\UnitPrice::setKey
     * @uses Blesta\Pricing\Type\UnitPrice::total
     * @uses Blesta\Pricing\Modifier\TaxPrice::__construct
     * @uses Blesta\Pricing\Modifier\AbstractPriceModifier::__construct
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
     * @uses Blesta\Pricing\Type\ItemPrice::__construct
     * @uses Blesta\Pricing\Type\ItemPrice::resetDiscountSubtotal
     * @uses Blesta\Pricing\Type\ItemPrice::subtotal
     * @uses Blesta\Pricing\Type\UnitPrice::__construct
     * @uses Blesta\Pricing\Type\UnitPrice::setPrice
     * @uses Blesta\Pricing\Type\UnitPrice::setQty
     * @uses Blesta\Pricing\Type\UnitPrice::setKey
     * @uses Blesta\Pricing\Type\UnitPrice::total
     * @uses Blesta\Pricing\Modifier\TaxPrice::__construct
     * @uses Blesta\Pricing\Modifier\AbstractPriceModifier::__construct
     * @expectedException InvalidArgumentException
     */
    public function testSetTaxMultipleException()
    {
        // Invalid argument: Multiple TaxPrice's of the same instance
        $item = new ItemPrice(10);
        $tax_price = new TaxPrice(10, TaxPrice::EXCLUSIVE);
        $item->setTax($tax_price, $tax_price);
    }

    /**
     * Tax data provider
     *
     * @return array
     */
    public function taxProvider()
    {
        return [
            [new ItemPrice(10, 0), [new TaxPrice(10, TaxPrice::EXCLUSIVE)]],
            [new ItemPrice(10), [new TaxPrice(10, TaxPrice::EXCLUSIVE)]],
            [new ItemPrice(10, 1), [new TaxPrice(10, TaxPrice::EXCLUSIVE), new TaxPrice(10, TaxPrice::EXCLUSIVE)]],
        ];
    }

    /**
     * @covers ::setDiscountTaxes
     * @covers ::discountAmount
     * @covers ::taxAmount
     * @covers ::amountTax
     * @covers ::amountTaxAll
     * @covers ::totalAfterDiscount
     * @covers ::totalAfterTax
     * @covers ::total
     * @uses Blesta\Pricing\Type\ItemPrice
     * @uses Blesta\Pricing\Type\UnitPrice
     * @uses Blesta\Pricing\Modifier\AbstractPriceModifier
     * @uses Blesta\Pricing\Modifier\DiscountPrice
     * @uses Blesta\Pricing\Modifier\TaxPrice
     * @dataProvider taxingDiscountsProvider
     */
    public function testTaxingDiscounts(
        $discount_taxes,
        $item,
        array $discounts,
        array $taxes,
        $discount_amount,
        $tax_amount,
        $total_after_discount,
        $total_after_tax,
        $total
    ) {
        // Set whether taxes may be discounted
        $item->setDiscountTaxes($discount_taxes);

        // Apply the taxes and discounts to the item
        foreach ($discounts as $discount) {
            $item->setDiscount($discount);
        }

        foreach ($taxes as $tax) {
            $item->setTax($tax);
        }

        $this->assertEquals($discount_amount, round($item->discountAmount(), 2));

        // Reset discount amounts that were applied so that we can use them again to calculate the next total
        $item->resetDiscounts();
        $this->assertEquals($tax_amount, round($item->taxAmount(), 2));

        // Reset discount amounts that were applied so that we can use them again to calculate the next total
        $item->resetDiscounts();
        $this->assertEquals($total_after_discount, round($item->totalAfterDiscount(), 2));

        // Reset discount amounts that were applied so that we can use them again to calculate the next total
        $item->resetDiscounts();
        $this->assertEquals($total_after_tax, round($item->totalAfterTax(), 2));

        // Reset discount amounts that were applied so that we can use them again to calculate the next total
        $item->resetDiscounts();
        $this->assertEquals($total, round($item->total(), 2));
    }

    /**
     * Data provider for testing whether discounts apply before or after tax
     *
     * @return array
     */
    public function taxingDiscountsProvider()
    {
        return [
            [
                true, // discount taxes
                new ItemPrice(10, 1),
                [new DiscountPrice(10, 'percent')],
                [new TaxPrice(50, TaxPrice::EXCLUSIVE)],
                1.00, // discount amount (10 * 0.1)
                4.50, // tax amount [10 * (1 - 0.1)] * (0.5)
                9.00, // total after discount [10 - (10 * 0.1)]
                14.50, // total after tax ([)10 + 4.50)
                13.50 // grand total (9 + 4.50)
            ],
            [
                false, // do not discount taxes
                new ItemPrice(10, 1),
                [new DiscountPrice(10, 'percent')],
                [new TaxPrice(50, TaxPrice::EXCLUSIVE)],
                1.00, // discount amount (10 * 0.1)
                5.00, // tax amount (10 * 0.5)
                9.00, // total after discount [10 - (10 * 0.1)]
                15.00, // total after tax (10 + 5)
                14.00 // grand total (9 + 5)
            ],
            [
                true, // discount taxes
                new ItemPrice(10, 1),
                [new DiscountPrice(100, 'percent')],
                [new TaxPrice(50, TaxPrice::EXCLUSIVE)],
                10.00, // discount amount (10 * 1)
                0.00, // tax amount [10 * (1 - 1)] * (0.5)
                0.00, // total after discount [10 - (10 * 1)]
                10.00, // total after tax (10 + 0)
                0.00 // grand total (0 + 0)
            ],
            [
                false, // do not discount taxes
                new ItemPrice(10, 1),
                [new DiscountPrice(100, 'percent')],
                [new TaxPrice(50, TaxPrice::EXCLUSIVE)],
                10.00, // discount amount (10 * 1)
                5.00, // tax amount (10 * 0.5)
                0.00, // total after discount [10 - (10 * 1)]
                15.00, // total after tax (10 + 5)
                5.00 // grand total (0 + 5)
            ],
            [
                true, // discount taxes
                new ItemPrice(10, 1),
                [new DiscountPrice(10, 'percent'), new DiscountPrice(100, 'amount')],
                [new TaxPrice(50, TaxPrice::EXCLUSIVE), new TaxPrice(10, TaxPrice::INCLUSIVE)],
                10.00, // discount amount (10)
                0.00, // tax amount (0 * 0.5) + (0 * 0.1)
                0.00, // total after discount (10 - 10)
                10.00, // total after tax (10 + 0)
                0.00 // grand total (0 + 0)
            ],
            [
                false, // discount taxes
                new ItemPrice(10, 1),
                [new DiscountPrice(10, 'percent'), new DiscountPrice(100, 'amount')],
                [new TaxPrice(50, TaxPrice::EXCLUSIVE), new TaxPrice(10, TaxPrice::INCLUSIVE)],
                10.00, // discount amount (10)
                6.00, // tax amount (10 * 0.5) + (10 * 0.1)
                0.00, // total after discount (10 - 10)
                16.00, // total after tax (10 + 6)
                6.00 // grand total (0 + 6)
            ],
            [
                true, // discount taxes
                new ItemPrice(10, 1),
                [new DiscountPrice(10, 'percent'), new DiscountPrice(5, 'amount')],
                [new TaxPrice(50, TaxPrice::EXCLUSIVE), new TaxPrice(10, TaxPrice::INCLUSIVE)],
                6.00, // discount amount (10 * 0.1) + 5
                2.40, // tax amount [(10 - 6) * 0.5)] + [(10 - 6) * 0.1)]
                4.00, // total after discount (10 - 6)
                12.40, // total after tax (10 + 2.40)
                6.40 // grand total (4 + 2.40)
            ],
            [
                false, // discount taxes
                new ItemPrice(10, 1),
                [new DiscountPrice(10, 'percent'), new DiscountPrice(5, 'amount')],
                [new TaxPrice(50, TaxPrice::EXCLUSIVE), new TaxPrice(10, TaxPrice::INCLUSIVE)],
                6.00, // discount amount (10 * 0.1) + 5
                6.00, // tax amount (10 * 0.5) + (10 * 0.1)
                4.00, // total after discount (10 - 6)
                16.00, // total after tax (10 + 6)
                10.00 // grand total (4 + 6)
            ],
            [
                true, // discount taxes
                new ItemPrice(10, 1),
                [new DiscountPrice(10, 'percent'), new DiscountPrice(5, 'amount')],
                [new TaxPrice(50, TaxPrice::EXCLUSIVE), new TaxPrice(10, TaxPrice::INCLUSIVE_CALCULATED)],
                6.00, // discount amount (10 * 0.1) + 5
                2.00, // tax amount [(10 - 6) * 0.5)]
                4.00, // total after discount (10 - 6)
                12.00, // total after tax (10 + 2.00)
                6.00 // grand total (4 + 2.00)
            ],
            [
                false, // discount taxes
                new ItemPrice(10, 1),
                [new DiscountPrice(10, 'percent'), new DiscountPrice(5, 'amount')],
                [new TaxPrice(50, TaxPrice::EXCLUSIVE), new TaxPrice(10, TaxPrice::INCLUSIVE_CALCULATED)],
                6.00, // discount amount (10 * 0.1) + 5
                5.00, // tax amount (10 * 0.5)
                4.00, // total after discount (10 - 6)
                15.00, // total after tax (10 + 5.00)
                9.00 // grand total (4 + 5.00)
            ]
        ];
    }

    /**
     * @covers ::totalAfterTax
     * @uses Blesta\Pricing\Type\ItemPrice::__construct
     * @uses Blesta\Pricing\Type\ItemPrice::resetDiscountSubtotal
     * @uses Blesta\Pricing\Type\ItemPrice::subtotal
     * @uses Blesta\Pricing\Type\ItemPrice::setTax
     * @uses Blesta\Pricing\Type\ItemPrice::taxAmount
     * @uses Blesta\Pricing\Type\ItemPrice::amountTax
     * @uses Blesta\Pricing\Type\ItemPrice::amountTaxAll
     * @uses Blesta\Pricing\Type\ItemPrice::compoundTaxAmount
     * @uses Blesta\Pricing\Type\ItemPrice::totalAfterDiscount
     * @uses Blesta\Pricing\Type\ItemPrice::discountAmount
     * @uses Blesta\Pricing\Type\ItemPrice::amountDiscount
     * @uses Blesta\Pricing\Type\ItemPrice::amountDiscountAll
     * @uses Blesta\Pricing\Type\UnitPrice::__construct
     * @uses Blesta\Pricing\Type\UnitPrice::setPrice
     * @uses Blesta\Pricing\Type\UnitPrice::setQty
     * @uses Blesta\Pricing\Type\UnitPrice::setKey
     * @uses Blesta\Pricing\Type\UnitPrice::total
     * @uses Blesta\Pricing\Modifier\AbstractPriceModifier::type
     * @uses Blesta\Pricing\Modifier\TaxPrice::__construct
     * @uses Blesta\Pricing\Modifier\TaxPrice::on
     * @dataProvider totalAfterTaxProvider
     */
    public function testTotalAfterTax($item, $taxes)
    {
        // No taxes set. Subtotal is the total after tax
        $this->assertEquals($item->subtotal(), $item->totalAfterTax());

        // Set taxes
        call_user_func_array([$item, 'setTax'], $taxes);

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
        return [
            [new ItemPrice(100.00, 2), [new TaxPrice(10, TaxPrice::EXCLUSIVE)]],
            [new ItemPrice(0.00, 2), [new TaxPrice(10, TaxPrice::EXCLUSIVE)]],
            [new ItemPrice(-100.00, 2), [new TaxPrice(10, TaxPrice::EXCLUSIVE)]],

            [new ItemPrice(100.00, 2), [new TaxPrice(10, TaxPrice::EXCLUSIVE), new TaxPrice(10, TaxPrice::EXCLUSIVE)]],
            [new ItemPrice(-100.00, 2), [new TaxPrice(10, TaxPrice::EXCLUSIVE), new TaxPrice(20, TaxPrice::EXCLUSIVE)]],

            [new ItemPrice(100.00, 2), [new TaxPrice(20, TaxPrice::INCLUSIVE_CALCULATED)]],
            [new ItemPrice(-100.00, 2), [new TaxPrice(20, TaxPrice::INCLUSIVE_CALCULATED)]],

            [new ItemPrice(100.00, 2), [new TaxPrice(10, TaxPrice::EXCLUSIVE), new TaxPrice(20, TaxPrice::INCLUSIVE_CALCULATED)]],
            [new ItemPrice(-100.00, 2), [new TaxPrice(10, TaxPrice::EXCLUSIVE), new TaxPrice(20, TaxPrice::INCLUSIVE_CALCULATED)]],
        ];
    }

    /**
     * @covers ::totalAfterDiscount
     * @uses Blesta\Pricing\Type\ItemPrice::__construct
     * @uses Blesta\Pricing\Type\ItemPrice::resetDiscountSubtotal
     * @uses Blesta\Pricing\Type\ItemPrice::subtotal
     * @uses Blesta\Pricing\Type\ItemPrice::setDiscount
     * @uses Blesta\Pricing\Type\ItemPrice::discountAmount
     * @uses Blesta\Pricing\Type\ItemPrice::amountDiscount
     * @uses Blesta\Pricing\Type\ItemPrice::amountDiscountAll
     * @uses Blesta\Pricing\Type\UnitPrice::__construct
     * @uses Blesta\Pricing\Type\UnitPrice::setPrice
     * @uses Blesta\Pricing\Type\UnitPrice::setQty
     * @uses Blesta\Pricing\Type\UnitPrice::setKey
     * @uses Blesta\Pricing\Type\UnitPrice::total
     * @uses Blesta\Pricing\Modifier\DiscountPrice::__construct
     * @uses Blesta\Pricing\Modifier\DiscountPrice::on
     * @uses Blesta\Pricing\Modifier\DiscountPrice::off
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
        return [
            [new ItemPrice(10, 1), [new DiscountPrice(10, 'percent')]],
            [new ItemPrice(0, 1), [new DiscountPrice(10, 'percent')]],
            [new ItemPrice(10, 2), [new DiscountPrice(10, 'percent'), new DiscountPrice(10, 'percent')]],
            [new ItemPrice(-10, 2), [new DiscountPrice(10, 'percent')]],
            [new ItemPrice(10, 2), [new DiscountPrice(3, 'amount')]],
            [new ItemPrice(-10, 2), [new DiscountPrice(5, 'amount')]],
        ];
    }

    /**
     * @covers ::subtotal
     * @uses Blesta\Pricing\Type\ItemPrice::__construct
     * @uses Blesta\Pricing\Type\ItemPrice::resetDiscountSubtotal
     * @uses Blesta\Pricing\Type\ItemPrice::subtotal
     * @uses Blesta\Pricing\Type\UnitPrice::__construct
     * @uses Blesta\Pricing\Type\UnitPrice::setPrice
     * @uses Blesta\Pricing\Type\UnitPrice::setQty
     * @uses Blesta\Pricing\Type\UnitPrice::setKey
     * @uses Blesta\Pricing\Type\UnitPrice::total
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
        return [
            [10.00, 2],
            [10.00, 1],
            [10.00, 0],
            [0, 5],
            [-10.00, 1],
            [-10.00, 2],
        ];
    }

    /**
     * @covers ::total
     * @covers ::discountAmount
     * @covers ::amountDiscount
     * @covers ::amountDiscountAll
     * @uses Blesta\Pricing\Type\ItemPrice::__construct
     * @uses Blesta\Pricing\Type\ItemPrice::resetDiscountSubtotal
     * @uses Blesta\Pricing\Type\ItemPrice::setTax
     * @uses Blesta\Pricing\Type\ItemPrice::setDiscount
     * @uses Blesta\Pricing\Type\ItemPrice::setDiscountTaxes
     * @uses Blesta\Pricing\Type\ItemPrice::totalAfterTax
     * @uses Blesta\Pricing\Type\ItemPrice::totalAfterDiscount
     * @uses Blesta\Pricing\Type\ItemPrice::taxAmount
     * @uses Blesta\Pricing\Type\ItemPrice::amountTax
     * @uses Blesta\Pricing\Type\ItemPrice::amountTaxAll
     * @uses Blesta\Pricing\Type\ItemPrice::compoundTaxAmount
     * @uses Blesta\Pricing\Type\ItemPrice::subtotal
     * @uses Blesta\Pricing\Modifier\AbstractPriceModifier::__construct
     * @uses Blesta\Pricing\Modifier\AbstractPriceModifier::type
     * @uses Blesta\Pricing\Modifier\TaxPrice::__construct
     * @uses Blesta\Pricing\Modifier\TaxPrice::on
     * @uses Blesta\Pricing\Modifier\DiscountPrice::__construct
     * @uses Blesta\Pricing\Modifier\DiscountPrice::on
     * @uses Blesta\Pricing\Modifier\DiscountPrice::off
     * @uses Blesta\Pricing\Type\UnitPrice::__construct
     * @uses Blesta\Pricing\Type\UnitPrice::setPrice
     * @uses Blesta\Pricing\Type\UnitPrice::setQty
     * @uses Blesta\Pricing\Type\UnitPrice::setKey
     * @uses Blesta\Pricing\Type\UnitPrice::total
     */
    public function testTotal()
    {
        $item = new ItemPrice(10, 2);

        // Total is the subtotal when no taxes or discounts exist
        $this->assertEquals($item->subtotal(), $item->total());

        // Total is the total after tax when no discount exists
        $item->setTax(new TaxPrice(5.25, TaxPrice::EXCLUSIVE));
        $this->assertEquals($item->totalAfterTax(), $item->total());

        // Total is the total after discount and tax
        $item->setDiscount(new DiscountPrice(50, 'percent'));
        $this->assertEquals($item->totalAfterDiscount() + $item->taxAmount(), $item->total());

        // Total is the total after discount and tax even when not discounting the tax
        $item->setDiscountTaxes(false);
        $this->assertEquals($item->totalAfterDiscount() + $item->taxAmount(), $item->total());
    }

    /**
     * @covers ::discounts
     * @uses Blesta\Pricing\Type\ItemPrice::__construct
     * @uses Blesta\Pricing\Type\ItemPrice::resetDiscountSubtotal
     * @uses Blesta\Pricing\Type\ItemPrice::subtotal
     * @uses Blesta\Pricing\Type\ItemPrice::setDiscount
     * @uses Blesta\Pricing\Modifier\DiscountPrice::__construct
     * @uses Blesta\Pricing\Type\UnitPrice::__construct
     * @uses Blesta\Pricing\Type\UnitPrice::setPrice
     * @uses Blesta\Pricing\Type\UnitPrice::setQty
     * @uses Blesta\Pricing\Type\UnitPrice::setKey
     * @uses Blesta\Pricing\Type\UnitPrice::total
     * @uses Blesta\Pricing\Modifier\AbstractPriceModifier::__construct
     */
    public function testDiscounts()
    {
        // No discounts set
        $item = new ItemPrice(10, 1);
        $this->assertEmpty($item->discounts());

        $discounts = [
            new DiscountPrice(10, TaxPrice::EXCLUSIVE),
            new DiscountPrice(5.00, TaxPrice::EXCLUSIVE)
        ];

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
     * @uses Blesta\Pricing\Type\ItemPrice::setTax
     * @uses Blesta\Pricing\Type\ItemPrice::setDiscount
     * @uses Blesta\Pricing\Type\ItemPrice::setDiscountTaxes
     * @uses Blesta\Pricing\Type\ItemPrice::totalAfterTax
     * @uses Blesta\Pricing\Type\ItemPrice::totalAfterDiscount
     * @uses Blesta\Pricing\Type\ItemPrice::discountAmount
     * @uses Blesta\Pricing\Type\ItemPrice::amountDiscount
     * @uses Blesta\Pricing\Type\ItemPrice::amountDiscountAll
     * @uses Blesta\Pricing\Type\ItemPrice::subtotal
     * @uses Blesta\Pricing\Type\ItemPrice::excludeTax
     * @uses Blesta\Pricing\Modifier\DiscountPrice
     * @uses Blesta\Pricing\Modifier\AbstractPriceModifier::type
     * @uses Blesta\Pricing\Modifier\TaxPrice::__construct
     * @uses Blesta\Pricing\Modifier\TaxPrice::on
     * @uses Blesta\Pricing\Modifier\TaxPrice::type
     * @uses Blesta\Pricing\Type\UnitPrice::__construct
     * @uses Blesta\Pricing\Type\UnitPrice::setPrice
     * @uses Blesta\Pricing\Type\UnitPrice::setQty
     * @uses Blesta\Pricing\Type\UnitPrice::setKey
     * @uses Blesta\Pricing\Type\UnitPrice::total
     * @dataProvider taxAmountProvider
     */
    public function testTaxAmount(
        $item,
        $tax,
        $expected_amount,
        array $excluded_tax_types,
        $discount = null,
        $discount_amount = 0
    ) {
        // No taxes set. No tax amount
        $subtotal = $item->subtotal();
        $this->assertEquals(0, $item->taxAmount());

        // Set tax price
        $item->setTax($tax);

        // Exclude the given tax types from calculation
        foreach ($excluded_tax_types as $excluded_tax_type) {
            $item->excludeTax($excluded_tax_type);
        }

        $tax_price = in_array($tax->type(), $excluded_tax_types) ? 0 : $tax->on($subtotal);
        // Test the tax amount
        $this->assertEquals($tax_price, $item->taxAmount($tax));

        // Test with all taxes applied
        $tax_amount = $item->taxAmount();
        if ($subtotal >= 0) {
            $this->assertGreaterThanOrEqual(0, $tax_amount);
        } else {
            $this->assertLessThanOrEqual(0, $tax_amount);
        }

        // The given expected amount should be the end result with all taxes applied
        $this->assertEquals($expected_amount, $item->taxAmount());
        $this->assertEquals($tax_price, $item->taxAmount($tax));

        // Test that discounts are properly applied to taxes
        if ($discount) {
            $item->setDiscount($discount);

            // The item tax should be equal to the tax applied to the discounted amount
            $this->assertEquals($discount_amount, $item->taxAmount());

            // When discounts do not apply to taxes, the tax amount should be the tax applied to the
            // subtotal before discount
            $item->setDiscountTaxes(false);
            $this->assertEquals($tax_price, $item->taxAmount($tax));
        }
    }

    /**
     * Tax Amount provider
     *
     * @return array
     */
    public function taxAmountProvider()
    {
        return [
            [new ItemPrice(100, 2), new TaxPrice(10, TaxPrice::EXCLUSIVE), 20, []],
            [new ItemPrice(0, 2), new TaxPrice(10, TaxPrice::EXCLUSIVE), 0, []],
            [new ItemPrice(-100, 2), new TaxPrice(10, TaxPrice::EXCLUSIVE), -20, []],
            [new ItemPrice(100, 2), new TaxPrice(10, TaxPrice::INCLUSIVE), 20, []],
            [new ItemPrice(110, 2), new TaxPrice(10, TaxPrice::INCLUSIVE_CALCULATED), 0, []],
            [new ItemPrice(100, 2), new TaxPrice(10, TaxPrice::EXCLUSIVE), 0, [TaxPrice::EXCLUSIVE]],
            [new ItemPrice(100, 2), new TaxPrice(10, TaxPrice::EXCLUSIVE), 20, [TaxPrice::INCLUSIVE]],
            [new ItemPrice(100, 2), new TaxPrice(10, TaxPrice::INCLUSIVE), 0, [TaxPrice::INCLUSIVE]],
            [new ItemPrice(110, 2), new TaxPrice(10, TaxPrice::INCLUSIVE_CALCULATED), 0, [TaxPrice::INCLUSIVE_CALCULATED]],
            [
                new ItemPrice(100, 2),
                new TaxPrice(10, TaxPrice::EXCLUSIVE),
                20,
                [TaxPrice::INCLUSIVE],
                new DiscountPrice(10, 'percent'),
                18 // [(100 * 2) * 0.1] * (1 - 0.1)
            ],
            [
                new ItemPrice(100, 2),
                new TaxPrice(10, TaxPrice::EXCLUSIVE),
                20,
                [TaxPrice::INCLUSIVE],
                new DiscountPrice(100, 'percent'),
                0 // [(100 * 2) * 1] * (1 - 1)
            ],
            [
                new ItemPrice(110, 2),
                new TaxPrice(10, TaxPrice::INCLUSIVE_CALCULATED),
                0,
                [TaxPrice::INCLUSIVE],
                new DiscountPrice(100, 'percent'),
                0 // [(100 * 2) * 1] * (1 - 1)
            ]
        ];
    }

    /**
     * @covers ::taxAmount
     * @covers ::amountTax
     * @covers ::amountTaxALl
     * @covers ::compoundTaxAmount
     * @uses Blesta\Pricing\Type\ItemPrice::setTax
     * @uses Blesta\Pricing\Type\ItemPrice::setDiscount
     * @uses Blesta\Pricing\Type\ItemPrice::setDiscountTaxes
     * @uses Blesta\Pricing\Type\ItemPrice::totalAfterTax
     * @uses Blesta\Pricing\Type\ItemPrice::totalAfterDiscount
     * @uses Blesta\Pricing\Type\ItemPrice::discountAmount
     * @uses Blesta\Pricing\Type\ItemPrice::amountDiscount
     * @uses Blesta\Pricing\Type\ItemPrice::amountDiscountAll
     * @uses Blesta\Pricing\Type\ItemPrice::excludeTax
     * @uses Blesta\Pricing\Type\ItemPrice::subtotal
     * @uses Blesta\Pricing\Modifier\DiscountPrice
     * @uses Blesta\Pricing\Modifier\AbstractPriceModifier
     * @uses Blesta\Pricing\Modifier\TaxPrice::__construct
     * @uses Blesta\Pricing\Modifier\TaxPrice::on
     * @uses Blesta\Pricing\Type\UnitPrice::__construct
     * @uses Blesta\Pricing\Type\UnitPrice::setPrice
     * @uses Blesta\Pricing\Type\UnitPrice::setQty
     * @uses Blesta\Pricing\Type\UnitPrice::setKey
     * @uses Blesta\Pricing\Type\UnitPrice::total
     * @dataProvider taxAmountCompoundProvider
     */
    public function testTaxAmountCompound(
        $item,
        array $taxes,
        array $expected_tax_amounts,
        array $excluded_tax_types,
        $discount = null
    ) {
        // Set all taxes
        call_user_func_array([$item, 'setTax'], $taxes);

        // Exclude the given tax types from calculation
        foreach ($excluded_tax_types as $excluded_tax_type) {
            $item->excludeTax($excluded_tax_type);
        }

        // The tax amounts should be compounded, and only return the componud amount for that tax
        foreach ($taxes as $index => $tax) {
            $tax_amount = $item->taxAmount($tax);
            $this->assertEquals($expected_tax_amounts[$index], $tax_amount);
        }

        // Total tax amount is the sum of all expected amounts
        $expected_amount = 0;
        foreach ($expected_tax_amounts as $index => $amount) {
            $expected_amount += ($taxes[$index]->type() == TaxPrice::INCLUSIVE_CALCULATED ? 0 : $amount);
        }
        $this->assertEquals($expected_amount, $item->taxAmount());

        // Test that discounts are properly applied to taxes
        if ($discount) {
            $item->setDiscount($discount);

            $discount_ratio = 1 - ($discount->amount() / 100);
            foreach ($taxes as $index => $tax) {
                // Discount the tax
                $this->assertEquals($expected_tax_amounts[$index] * $discount_ratio, $item->taxAmount($tax));
            }
            $this->assertEquals($expected_amount * $discount_ratio, $item->taxAmount());

            // Now set it so that discounts are not applied to taxes, now the tax amount should be the tax
            // applied to the subtotal before discount
            $item->setDiscountTaxes(false);
            foreach ($taxes as $index => $tax) {
                $this->assertEquals($expected_tax_amounts[$index], $item->taxAmount($tax));
            }
            $this->assertEquals($expected_amount, $item->taxAmount());
        }
    }

    /**
     * Compound Tax Amount provider
     *
     * @return array
     */
    public function taxAmountCompoundProvider()
    {
        return [
            [
                new ItemPrice(100, 2),
                [
                    new TaxPrice(10, TaxPrice::EXCLUSIVE),
                    new TaxPrice(7.75, TaxPrice::EXCLUSIVE)
                ],
                [
                    20,
                    17.05
                ],
                []
            ],
            [
                new ItemPrice(100, 2),
                [
                    new TaxPrice(10, TaxPrice::EXCLUSIVE),
                    new TaxPrice(7.75, TaxPrice::EXCLUSIVE)
                ],
                [
                    20,
                    17.05
                ],
                [],
                new DiscountPrice(10, 'percent')
            ],
            [
                new ItemPrice(10, 3),
                [
                    new TaxPrice(10, TaxPrice::EXCLUSIVE),
                    new TaxPrice(5, TaxPrice::EXCLUSIVE),
                    new TaxPrice(2.5, TaxPrice::EXCLUSIVE)
                ],
                [
                    3,
                    1.65,
                    0.86625
                ],
                []
            ],
            [
                new ItemPrice(10, 3),
                [
                    new TaxPrice(10, TaxPrice::EXCLUSIVE),
                    new TaxPrice(5, TaxPrice::EXCLUSIVE),
                    new TaxPrice(2.5, TaxPrice::EXCLUSIVE)
                ],
                [
                    0,
                    0,
                    0
                ],
                [TaxPrice::EXCLUSIVE]
            ],
            [
                new ItemPrice(10, 3),
                [
                    new TaxPrice(10, TaxPrice::INCLUSIVE),
                    new TaxPrice(5, TaxPrice::INCLUSIVE),
                    new TaxPrice(2.5, TaxPrice::EXCLUSIVE)
                ],
                [
                    3,
                    1.65,
                    0
                ],
                [TaxPrice::EXCLUSIVE]
            ],
            [
                new ItemPrice(10, 3),
                [
                    new TaxPrice(10, TaxPrice::INCLUSIVE),
                    new TaxPrice(5, TaxPrice::INCLUSIVE),
                    new TaxPrice(2.5, TaxPrice::EXCLUSIVE)
                ],
                [
                    0,
                    0,
                    0
                ],
                [TaxPrice::INCLUSIVE, TaxPrice::EXCLUSIVE]
            ],
            [
                new ItemPrice(10, 3),
                [
                    new TaxPrice(10, TaxPrice::INCLUSIVE),
                    new TaxPrice(5, TaxPrice::INCLUSIVE),
                    new TaxPrice(2.5, TaxPrice::EXCLUSIVE)
                ],
                [
                    0,
                    0,
                    0.86625
                ],
                [TaxPrice::INCLUSIVE]
            ],
            [
                new ItemPrice(10, 3),
                [
                    new TaxPrice(2.5, TaxPrice::EXCLUSIVE)
                ],
                [
                    0.75
                ],
                [TaxPrice::INCLUSIVE]
            ],
            [
                new ItemPrice(100, 2),
                [
                    new TaxPrice(10, TaxPrice::EXCLUSIVE),
                    new TaxPrice(10, TaxPrice::INCLUSIVE_CALCULATED)
                ],
                [
                    20,
                    20
                ],
                [],
                new DiscountPrice(10, 'percent')
            ],
        ];
    }

    /**
     * @covers ::discountAmount
     * @covers ::amountDiscount
     * @covers ::amountDiscountAll
     * @uses Blesta\Pricing\Type\ItemPrice::setDiscount
     * @uses Blesta\Pricing\Type\ItemPrice::subtotal
     * @uses Blesta\Pricing\Type\UnitPrice::__construct
     * @uses Blesta\Pricing\Type\UnitPrice::setPrice
     * @uses Blesta\Pricing\Type\UnitPrice::setQty
     * @uses Blesta\Pricing\Type\UnitPrice::setKey
     * @uses Blesta\Pricing\Type\UnitPrice::total
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
        $dp = $this->getMockBuilder('Blesta\Pricing\Modifier\DiscountPrice')
            ->disableOriginalConstructor()
            ->getMock();
        $dp->method('on')
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
        return [
            [new ItemPrice(100, 2), [], 0],
            [new ItemPrice(100, 2), [$this->discountPriceMock(20)], 20],
            [
                new ItemPrice(100, 2),
                [
                    $this->discountPriceMock(20),
                    $this->discountPriceMock(40)
                ],
                60
            ],
            [new ItemPrice(100, 2), [$this->discountPriceMock(200)], 200],
            [
                new ItemPrice(100, 2),
                [
                    $this->discountPriceMock(2),
                    $this->discountPriceMock(3.75)
                ],
                5.75
            ],
            [
                new ItemPrice(100, 2),
                [
                    $this->discountPriceMock(40),
                    $this->discountPriceMock(2)
                ],
                42
            ],

            [new ItemPrice(-100, 2), [$this->discountPriceMock(-20)], -20],
            [
                new ItemPrice(-100, 2),
                [
                    $this->discountPriceMock(-20),
                    $this->discountPriceMock(-40)
                ],
                -60
            ],
            [new ItemPrice(-100, 2), [$this->discountPriceMock(-200)], -200],
            [
                new ItemPrice(-100, 2),
                [
                    $this->discountPriceMock(-2),
                    $this->discountPriceMock(-3.75)
                ],
                -5.75
            ],
            [
                new ItemPrice(-100, 2),
                [
                    $this->discountPriceMock(-40),
                    $this->discountPriceMock(-2)
                ],
                -42
            ],
        ];
    }

    /**
     * @covers ::discountAmount
     * @covers ::amountDiscount
     * @covers ::amountDiscountAll
     * @covers ::resetDiscounts
     * @uses Blesta\Pricing\Type\ItemPrice::__construct
     * @uses Blesta\Pricing\Type\ItemPrice::resetDiscountSubtotal
     * @uses Blesta\Pricing\Type\ItemPrice::subtotal
     * @uses Blesta\Pricing\Type\ItemPrice::setDiscount
     * @uses Blesta\Pricing\Type\UnitPrice::__construct
     * @uses Blesta\Pricing\Type\UnitPrice::setPrice
     * @uses Blesta\Pricing\Type\UnitPrice::setQty
     * @uses Blesta\Pricing\Type\UnitPrice::setKey
     * @uses Blesta\Pricing\Type\UnitPrice::total
     * @uses Blesta\Pricing\Modifier\DiscountPrice::__construct
     * @uses Blesta\Pricing\Modifier\DiscountPrice::on
     * @uses Blesta\Pricing\Modifier\DiscountPrice::off
     * @uses Blesta\Pricing\Modifier\DiscountPrice::reset
     * @dataProvider discountAmountsProvider
     */
    public function testDiscountAmounts($item, array $discounts, array $expected_amounts)
    {
        // No discounts set
        foreach ($discounts as $discount) {
            $this->assertEquals(0, $item->discountAmount($discount));

            // Set the discount
            $item->setDiscount($discount);
        }

        for ($i=0; $i<2; $i++) {
            // The index of the expected amounts coincide with the index of the discounts
            foreach ($discounts as $index => $discount) {
                $this->assertEquals($expected_amounts[$index], $item->discountAmount($discount));
            }

            // The discounts must be reset before they can be tested again
            foreach ($discounts as $index => $discount) {
                // Discounts of zero will be equal, otherwise they should be different
                if ($expected_amounts[$index] == 0) {
                    $this->assertEquals($expected_amounts[$index], $item->discountAmount($discount));
                } else {
                    $this->assertNotEquals($expected_amounts[$index], $item->discountAmount($discount));
                }
            }
            $item->resetDiscounts();
        }

        $expected_amount = 0;
        foreach ($expected_amounts as $amount) {
            $expected_amount += $amount;
        }
        $this->assertEquals($expected_amount, $item->discountAmount());
    }

    /**
     * Provider for testDiscountAmounts
     *
     * @return array
     */
    public function discountAmountsProvider()
    {
        return [
            [
                new ItemPrice(10, 3),
                [
                    new DiscountPrice(5.00, 'percent'),
                    new DiscountPrice(25.00, 'percent')
                ],
                [
                    1.50,
                    7.125
                ]
            ],
            [
                new ItemPrice(50, 1),
                [
                    new DiscountPrice(10.00, 'percent'),
                    new DiscountPrice(10.00, 'amount'),
                    new DiscountPrice(50.00, 'percent'),
                    new DiscountPrice(3.00, 'amount'),
                    new DiscountPrice(2.5, 'amount'),
                    new DiscountPrice(50.5, 'percent'),
                    new DiscountPrice(6.25, 'amount'),
                    new DiscountPrice(10, 'percent'),
                    new DiscountPrice(1, 'amount'),
                ],
                [
                    5,
                    10,
                    17.5,
                    3,
                    2.5,
                    6.06,
                    5.94,
                    0,
                    0
                ]
            ]
        ];
    }

    /**
     * @covers ::resetDiscounts
     * @covers ::resetDiscountSubtotal
     * @uses Blesta\Pricing\Type\ItemPrice::__construct
     * @uses Blesta\Pricing\Type\ItemPrice::resetDiscountSubtotal
     * @uses Blesta\Pricing\Type\ItemPrice::subtotal
     * @uses Blesta\Pricing\Type\ItemPrice::setDiscount
     * @uses Blesta\Pricing\Type\UnitPrice::__construct
     * @uses Blesta\Pricing\Type\UnitPrice::setPrice
     * @uses Blesta\Pricing\Type\UnitPrice::setQty
     * @uses Blesta\Pricing\Type\UnitPrice::setKey
     * @uses Blesta\Pricing\Type\UnitPrice::total
     */
    public function testResetDiscounts()
    {
        $discountMock = $this->getMockBuilder('Blesta\Pricing\Modifier\DiscountPrice')
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
     * @uses Blesta\Pricing\Type\ItemPrice::setTax
     * @uses Blesta\Pricing\Type\ItemPrice::__construct
     * @uses Blesta\Pricing\Type\ItemPrice::resetDiscountSubtotal
     * @uses Blesta\Pricing\Type\ItemPrice::subtotal
     * @uses Blesta\Pricing\Type\UnitPrice::__construct
     * @uses Blesta\Pricing\Type\UnitPrice::setPrice
     * @uses Blesta\Pricing\Type\UnitPrice::setQty
     * @uses Blesta\Pricing\Type\UnitPrice::setKey
     * @uses Blesta\Pricing\Type\UnitPrice::total
     * @uses Blesta\Pricing\Modifier\TaxPrice::__construct
     * @uses Blesta\Pricing\Modifier\AbstractPriceModifier::__construct
     * @dataProvider taxesProvider
     */
    public function testTaxes($unique, $taxes, $expected_count, $expected_total)
    {
        $item = new ItemPrice(10);

        foreach ($taxes as $tax_group) {
            call_user_func_array([$item, 'setTax'], $tax_group);
        }

        // Determine the total tax count based on $unique
        $this->assertCount($expected_count, $item->taxes($unique));

        // Determine the total count of all taxes
        $total = 0;
        foreach ($item->taxes() as $group) {
            $total++;
        }
        $this->assertEquals($expected_total, $total);
    }

    /**
     * Data provider for taxes
     */
    public function taxesProvider()
    {
        $tax = new TaxPrice(50, TaxPrice::EXCLUSIVE);

        return [
            [
                true,
                [[]],
                0,
                0
            ],
            [
                true,
                [
                    [new TaxPrice(10, TaxPrice::EXCLUSIVE)]
                ],
                1,
                1
            ],
            [
                true,
                [
                    [new TaxPrice(100, TaxPrice::EXCLUSIVE), new TaxPrice(20, TaxPrice::EXCLUSIVE)],
                    [new TaxPrice(10, TaxPrice::EXCLUSIVE)]
                ],
                3,
                3
            ],
            [
                true,
                [
                    [$tax, new TaxPrice(15, TaxPrice::EXCLUSIVE)],
                    [$tax]
                ],
                2,
                2
            ],
            [
                false,
                [
                    [$tax]
                ],
                1,
                1
            ],
            [
                false,
                [
                    [new TaxPrice(10, TaxPrice::EXCLUSIVE), new TaxPrice(15, TaxPrice::EXCLUSIVE)],
                    [new TaxPrice(25, TaxPrice::EXCLUSIVE)]
                ],
                2,
                3
            ]
        ];
    }

    /**
     * @covers ::resetTaxes
     * @uses Blesta\Pricing\Type\ItemPrice::__construct
     * @uses Blesta\Pricing\Type\ItemPrice::resetDiscountSubtotal
     * @uses Blesta\Pricing\Type\ItemPrice::excludeTax
     * @uses Blesta\Pricing\Type\ItemPrice::subtotal
     * @uses Blesta\Pricing\Type\UnitPrice::__construct
     * @uses Blesta\Pricing\Type\UnitPrice::setPrice
     * @uses Blesta\Pricing\Type\UnitPrice::setQty
     * @uses Blesta\Pricing\Type\UnitPrice::setKey
     * @uses Blesta\Pricing\Type\UnitPrice::total
     * @uses Blesta\Pricing\Modifier\AbstractPriceModifier::type
     */
    public function testResetTaxes()
    {
        $item = new ItemPrice(10);

        $item->excludeTax(TaxPrice::EXCLUSIVE);
        $this->assertAttributeEquals(
            [TaxPrice::INCLUSIVE => true, TaxPrice::EXCLUSIVE => false, TaxPrice::INCLUSIVE_CALCULATED => true],
            'tax_types',
            $item
        );

        $item->resetTaxes();
        $this->assertAttributeEquals(
            [TaxPrice::INCLUSIVE => true, TaxPrice::EXCLUSIVE => true, TaxPrice::INCLUSIVE_CALCULATED => true],
            'tax_types',
            $item
        );
    }

    /**
     * @covers ::excludeTax
     * @uses Blesta\Pricing\Type\ItemPrice::__construct
     * @uses Blesta\Pricing\Type\ItemPrice::resetDiscountSubtotal
     * @uses Blesta\Pricing\Type\ItemPrice::subtotal
     * @uses Blesta\Pricing\Type\UnitPrice::__construct
     * @uses Blesta\Pricing\Type\UnitPrice::setPrice
     * @uses Blesta\Pricing\Type\UnitPrice::setQty
     * @uses Blesta\Pricing\Type\UnitPrice::setKey
     * @uses Blesta\Pricing\Type\UnitPrice::total
     * @uses Blesta\Pricing\Modifier\AbstractPriceModifier::type
     */
    public function testExcludeTax()
    {
        $item = new ItemPrice(10);

        $item->excludeTax('invalid_tax_type');
        $this->assertAttributeEquals(
            [TaxPrice::INCLUSIVE => true, TaxPrice::EXCLUSIVE => true, TaxPrice::INCLUSIVE_CALCULATED => true],
            'tax_types',
            $item
        );

        $item->excludeTax(TaxPrice::EXCLUSIVE);
        $this->assertAttributeEquals(
            [TaxPrice::INCLUSIVE => true, TaxPrice::EXCLUSIVE => false, TaxPrice::INCLUSIVE_CALCULATED => true],
            'tax_types',
            $item
        );

        $item->excludeTax(TaxPrice::INCLUSIVE);
        $this->assertAttributeEquals(
            [TaxPrice::INCLUSIVE => false, TaxPrice::EXCLUSIVE => false, TaxPrice::INCLUSIVE_CALCULATED => true],
            'tax_types',
            $item
        );

        $this->assertInstanceOf('Blesta\Pricing\Type\ItemPrice', $item->excludeTax(TaxPrice::INCLUSIVE));
    }
}
