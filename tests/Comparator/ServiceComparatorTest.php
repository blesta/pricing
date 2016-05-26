<?php

/**
 * @coversDefaultClass ServiceComparator
 */
class ServiceComparatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::merge
     * @covers ::price
     * @uses AbstractItemComparator::__construct
     * @uses AbstractItemComparator::setPriceCallback
     * @uses AbstractItemComparator::setDescriptionCallback
     * @uses AbstractPriceDescription::getDescription
     * @uses AbstractPriceDescription::setDescription
     * @uses ItemPrice::__construct
     * @uses ItemPrice::resetDiscountSubtotal
     * @uses ItemPrice::subtotal
     * @uses ItemPrice::total
     * @uses ItemPrice::discounts
     * @uses ItemPrice::taxes
     * @uses ItemPrice::setTax
     * @uses ItemPrice::setDiscount
     * @uses ItemPrice::totalAfterTax
     * @uses ItemPrice::totalAfterDiscount
     * @uses ItemPrice::taxAmount
     * @uses ItemPrice::amountTaxAll
     * @uses ItemPrice::compoundTaxAmount
     * @uses ItemPrice::discountAmount
     * @uses ItemPrice::amountDiscountAll
     * @uses ItemPrice::meta
     * @uses UnitPrice::__construct
     * @uses UnitPrice::setPrice
     * @uses UnitPrice::setQty
     * @uses UnitPrice::setKey
     * @uses UnitPrice::price
     * @uses UnitPrice::qty
     * @uses UnitPrice::key
     * @uses UnitPrice::total
     * @uses TaxPrice::on
     * @uses DiscountPrice::off
     * @uses DiscountPrice::on
     * @uses PricingFactory::itemPrice
     * @dataProvider mergeProvider
     */
    public function testMerge(
        ItemPrice $item1,
        ItemPrice $item2,
        callable $price,
        callable $desc,
        $total,
        $qty,
        $description,
        TaxPrice $tax = null,
        DiscountPrice $discount = null
    ) {
        $comparator = new ServiceComparator($price, $desc);
        $item = $comparator->merge($item1, $item2);
        $this->assertInstanceOf('ItemPrice', $item);

        $this->assertEquals($total, $item->total());
        $this->assertEquals($qty, $item->qty());
        $this->assertEquals($description, $item->getDescription());

        if ($tax) {
            foreach ($item->taxes() as $current_tax) {
                $this->assertEquals($tax, $current_tax);
            }
        }

        if ($discount) {
            foreach ($item->discounts() as $current_discount) {
                $this->assertEquals($discount, $current_discount);
            }
        }
    }

    /**
     * Data provider for merging items
     */
    public function mergeProvider()
    {
        $tax = new TaxPrice(10, 'exclusive');
        $discount = new DiscountPrice(10, 'percent');

        $item = new ItemPrice(10, 2);
        $item->setTax($tax);
        $item->setDiscount($discount);

        return array(
            array(
                new ItemPrice(2, 1),
                new ItemPrice(1, 1),
                function ($old_price, $new_price, $old_meta, $new_meta) {
                    return ($old_price - $new_price);
                },
                function ($old_meta, $new_meta) {
                    return 'New Description';
                },
                1,
                1,
                'New Description',
                null,
                null,
            ),
            array(
                new ItemPrice(2, 10),
                new ItemPrice(2, 5),
                function ($old_price, $new_price, $old_meta, $new_meta) {
                    return ($old_price + $new_price);
                },
                function ($old_meta, $new_meta) {
                    return 'Item Desc';
                },
                30,
                1,
                'Item Desc',
                null,
                null
            ),
            array(
                new ItemPrice(5, 1),
                $item,
                function ($old_price, $new_price, $old_meta, $new_meta) {
                    return ($old_price - $new_price);
                },
                function ($old_meta, $new_meta) {
                    return 'Test negative';
                },
                -14.85, // 5 - 20, minus 10% discount (16.5) and 10% tax (14.85)
                1,
                'Test negative',
                $tax,
                $discount
            ),
            array(
                $item,
                new ItemPrice(5, 1),
                function ($old_price, $new_price, $old_meta, $new_meta) {
                    return ($old_price - $new_price);
                },
                function ($old_meta, $new_meta) {
                    return 'Test positive';
                },
                14.8, // 20 with 10% discount and 10% tax, minus 5
                1,
                'Test positive',
                null,
                null
            )
        );
    }
}
