<?php

/**
 * @coversDefaultClass ItemPriceCollection
 */
class ItemPriceCollectionTest extends PHPUnit_Framework_TestCase
{

    /**
     * @covers ::append
     * @covers ::count
     * @uses ItemPriceCollection::count
     */
    public function testAppend()
    {
        $itemMock[] = $this->getMockBuilder('ItemPrice')
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock[] = $this->getMockBuilder('ItemPrice')
            ->disableOriginalConstructor()
            ->getMock();

        $collection = new ItemPriceCollection();

        // Add 1 item
        $collection->append($itemMock[0]);
        $this->assertEquals(1, $collection->count());

        // Add a second item
        $collection->append($itemMock[1]);
        $this->assertEquals(2, $collection->count());
    }

    /**
     * @covers ::remove
     * @covers ::count
     * @uses ItemPriceCollection::count
     * @uses ItemPriceCollection::append
     */
    public function testRemove()
    {
        $itemMock[] = $this->getMockBuilder('ItemPrice')
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock[] = $this->getMockBuilder('ItemPrice')
            ->disableOriginalConstructor()
            ->getMock();

        $collection = new ItemPriceCollection();
        $this->assertEquals(0, $collection->count());

        foreach ($itemMock as $item) {
            $collection->append($item);
        }

        $this->assertEquals(count($itemMock), $collection->count());
        $collection->remove($itemMock[0]);
        $this->assertEquals(count($itemMock)-1, $collection->count());
    }

    /**
     * @covers ::totalAfterTax
     * @uses ItemPriceCollection
     * @uses ItemPrice
     * @uses DiscountPrice
     * @uses TaxPrice
     * @uses UnitPrice
     * @dataProvider totalProvider
     */
    public function testTotalAfterTax(ItemPriceCollection $collection, array $expected_totals)
    {
        $this->assertEquals($expected_totals['total_with_tax'], $collection->totalAfterTax());
    }

    /**
     * @covers ::totalAfterDiscount
     * @uses ItemPriceCollection
     * @uses ItemPrice
     * @uses DiscountPrice
     * @uses TaxPrice
     * @uses UnitPrice
     * @dataProvider totalProvider
     */
    public function testTotalAfterDiscount(ItemPriceCollection $collection, array $expected_totals)
    {
        $this->assertEquals($expected_totals['total_with_discount'], $collection->totalAfterDiscount());
    }

    /**
     * @covers ::subtotal
     * @uses ItemPriceCollection
     * @uses ItemPrice
     * @uses DiscountPrice
     * @uses TaxPrice
     * @uses UnitPrice
     * @dataProvider totalProvider
     */
    public function testSubtotal(ItemPriceCollection $collection, array $expected_totals)
    {
        $this->assertEquals($expected_totals['subtotal'], $collection->subtotal());
    }

    /**
     * @covers ::total
     * @covers ::discountAmount
     * @uses ItemPriceCollection
     * @uses ItemPrice
     * @uses DiscountPrice
     * @uses TaxPrice
     * @uses UnitPrice
     * @dataProvider totalProvider
     */
    public function testTotal(ItemPriceCollection $collection, array $expected_totals)
    {
        $this->assertEquals($expected_totals['total'], $collection->total());
    }

    /**
     *
     * @covers ::taxAmount
     * @uses ItemPriceCollection
     * @uses ItemPrice
     * @uses DiscountPrice
     * @uses TaxPrice
     * @uses UnitPrice
     * @dataProvider totalProvider
     */
    public function testTaxAmount(ItemPriceCollection $collection, array $expected_totals)
    {
        $this->assertEquals($expected_totals['tax'], $collection->taxAmount());
    }

    /**
     * @covers ::discountAmount
     * @uses ItemPriceCollection
     * @uses ItemPrice
     * @uses DiscountPrice
     * @uses TaxPrice
     * @uses UnitPrice
     * @dataProvider totalProvider
     */
    public function testDiscountAmount(ItemPriceCollection $collection, array $expected_totals)
    {
        $this->assertEquals($expected_totals['discount'], $collection->discountAmount());
    }

    /**
     * @covers ::taxes
     * @uses ItemPriceCollection
     * @uses ItemPrice
     * @uses DiscountPrice
     * @uses TaxPrice
     * @uses UnitPrice
     * @dataProvider totalProvider
     */
    public function testTaxes(ItemPriceCollection $collection, array $expected_totals)
    {
        $this->assertContainsOnlyInstancesOf("TaxPrice", $collection->taxes());

        // Exactly each expected tax should exist
        foreach ($expected_totals['taxes'] as $tax_price) {
            $this->assertContains($tax_price, $collection->taxes());
        }
        $this->assertCount(count($expected_totals['taxes']), $collection->taxes());
    }

    /**
     * @covers ::discounts
     * @uses ItemPriceCollection
     * @uses ItemPrice
     * @uses DiscountPrice
     * @uses TaxPrice
     * @uses UnitPrice
     * @dataProvider totalProvider
     */
    public function testDiscounts(ItemPriceCollection $collection, array $expected_totals)
    {
        $this->assertContainsOnlyInstancesOf("DiscountPrice", $collection->discounts());

        // Exactly each expected discount should exist
        foreach ($expected_totals['discounts'] as $discount_price) {
            $this->assertContains($discount_price, $collection->discounts());
        }
        $this->assertCount(count($expected_totals['discounts']), $collection->discounts());
    }

    /**
     * @covers ::resetDiscounts
     * @uses ItemPriceCollection::append
     * @uses ItemPriceCollection::discounts
     * @uses ItemPriceCollection::discountAmount
     * @uses ItemPrice
     * @uses UnitPrice
     * @uses AbstractPriceModifier::__construct
     * @uses DiscountPrice
     */
    public function testResetDiscounts()
    {
        $item = new ItemPrice(10, 1);
        $discount = new DiscountPrice(1, 'amount');
        $item->setDiscount($discount);

        $collection = new ItemPriceCollection();
        $collection->append($item);

        // 1 discount on 10 is 1
        $this->assertEquals(1, $item->discountAmount());
        // Discount already applied. No discount again
        $this->assertEquals(0, $item->discountAmount());

        $collection->resetDiscounts();

        // 1 discount on 10 is 1
        $this->assertEquals(1, $item->discountAmount());
    }

    /**
     * Data provider for subtotal/total
     *
     * DO NOT SET DISCOUNT AMOUNTS THAT APPLY TO MULTIPLE ITEMS
     * DO NET SET AN ITEM TO MULTIPLE COLLECTIONS
     * Results will be incorrect without resetting item values appropriately
     *
     * @return array
     */
    public function totalProvider()
    {
        // Items with discounts and tax
        $tax_price = new TaxPrice(10, 'exclusive');
        $item1 = new ItemPrice(10, 2);
        $item1->setDiscount(new DiscountPrice(20, 'percent'));
        $item1->setDiscount(new DiscountPrice(1, 'amount'));
        $item1->setTax($tax_price);

        $item4 = new ItemPrice(10, 2);
        $item4->setDiscount(new DiscountPrice(10, 'percent'));
        $item4->setTax($tax_price);

        // Item with tax
        $item2 = new ItemPrice(6, 4);
        $item2->setTax(new TaxPrice(5, 'exclusive'));
        $item3 = new ItemPrice(5, 5);

        $item5 = new ItemPrice(5.25, 3);
        $item5->setTax($tax_price);

        // Item with compound tax and discount
        $item6 = new ItemPrice(100.00, 1);
        $item6->setDiscount(new DiscountPrice(1.50, 'amount'));
        $item6->setTax(new TaxPrice(8, 'exclusive'), new TaxPrice(5, 'exclusive'));

        // Set collections of the items
        $collection1 = new ItemPriceCollection();
        $collection1->append($item1);

        $collection2 = new ItemPriceCollection();
        $collection2->append($item2)->append($item3);

        $collection3 = new ItemPriceCollection();
        $collection3->append($item4)->append($item5)->append($item6);

        return array(
            array($collection1, $this->getItemTotals($item1)),
            array($collection2, $this->getItemTotals($item2, $item3)),
            array($collection3, $this->getItemTotals($item4, $item5, $item6)),
        );
    }

    /**
     * Retrieves total information for a set of items
     *
     * @param ItemPrice An ItemPrice object
     * @param ...
     * @return array An array of totals combining each item price
     */
    private function getItemTotals()
    {
        // NOTE: 'total', 'total_with_discount', and 'discount' may be INCORRECT
        // if a DiscountPrice of type 'amount' applies to multiple items!
        $totals = array(
            'subtotal' => 0,
            'total' => 0,
            'total_with_tax' => 0,
            'total_with_discount' => 0,
            'tax' => 0,
            'discount' => 0,
            'taxes' => array(),
            'discounts' => array()
        );

        $args = func_get_args();
        foreach ($args as $item) {
            $totals['subtotal'] += $item->subtotal();
            $totals['total'] += $item->total();
            $item->resetDiscounts();
            $totals['total_with_tax'] += $item->totalAfterTax();
            $item->resetDiscounts();
            $totals['total_with_discount'] += $item->totalAfterDiscount();
            $item->resetDiscounts();
            $totals['tax'] += $item->taxAmount();
            $item->resetDiscounts();
            $totals['discount'] += $item->discountAmount();
            $item->resetDiscounts();
            $totals['taxes'] = $this->getUnique($totals['taxes'], $item->taxes());
            $totals['discounts'] = $this->getUnique($totals['discounts'], $item->discounts());
        }

        return $totals;
    }

    /**
     * Includes unique items from $arr2 into $arr1
     *
     * @param array $arr1 An array of objects
     * @param array $arr2 An array of objects to include
     * @return array An array of unique objects
     */
    private function getUnique($arr1, $arr2)
    {
        foreach ($arr2 as $obj) {
            if (!in_array($obj, $arr1, true)) {
                $arr1 = array_merge($arr1, array($obj));
            }
        }

        return $arr1;
    }

    /**
     * Tests totals of items that share amount discounts
     *
     * @covers ::discountAmount
     * @covers ::taxAmount
     * @covers ::total
     * @covers ::totalAfterTax
     * @covers ::totalAfterDiscount
     * @uses ItemPriceCollection
     * @uses ItemPrice
     * @uses DiscountPrice
     * @uses TaxPrice
     * @uses UnitPrice
     * @uses AbstractPriceModifier::__construct
     */
    public function testMultipleDiscountTotals()
    {
        // Test two items with the same discount amounts
        $collection = new ItemPriceCollection();
        $discount1 = new DiscountPrice(5, 'amount');
        $discount2 = new DiscountPrice(10, 'amount');

        $item1 = new ItemPrice(10, 2);
        $item1->setDiscount($discount1);
        $item1->setDiscount($discount2);

        $item2 = new ItemPrice(100, 1);
        $item1->setDiscount($discount1);
        $item1->setDiscount($discount2);

        $collection->append($item1)->append($item2);

        $this->assertEquals(0, $collection->taxAmount());
        $this->assertEquals(15, $collection->discountAmount());
        $this->assertEquals(105, $collection->totalAfterDiscount());
        $this->assertEquals(120, $collection->totalAfterTax());
        $this->assertEquals(105, $collection->total());


        // Test multiple items with varying taxes/discounts
        $collection->remove($item1)->remove($item2);
        $this->assertEquals(0, $collection->count());

        $discount3 = new DiscountPrice(50, 'amount');
        $tax = new TaxPrice(20, 'exclusive');

        $item3 = new ItemPrice(10, 1);
        $item3->setDiscount(new DiscountPrice(10, 'percent'));
        $item3->setDiscount($discount3);
        $item3->setTax(new TaxPrice(10, 'exclusive'));
        $item3->setTax($tax);

        $item4 = new ItemPrice(1000, 2);
        $item4->setDiscount($discount3);
        $item4->setTax($tax);

        $collection->append($item3)->append($item4);

        $this->assertEquals(391.8, $collection->taxAmount());
        $this->assertEquals(51, $collection->discountAmount());
        $this->assertEquals(1959, $collection->totalAfterDiscount());
        $this->assertEquals(2401.8, $collection->totalAfterTax());
        $this->assertEquals(2350.8, $collection->total());
    }

    /**
     * @covers ::current
     * @covers ::valid
     * @uses ItemPrice::__construct
     * @uses ItemPrice::resetDiscountSubtotal
     * @uses ItemPrice::subtotal
     * @uses UnitPrice::__construct
     * @uses UnitPrice::total
     * @uses ItemPriceCollection::append
     */
    public function testCurrent()
    {
        $collection = new ItemPriceCollection();

        // No items exist, there is no current item
        $this->assertNull($collection->current());

        // One item
        $item = new ItemPrice(10, 1);
        $collection->append($item);
        $this->assertSame($item, $collection->current());

        // First item is still the current item
        $collection->append(new ItemPrice(30, 2));
        $this->assertSame($item, $collection->current());
    }

    /**
     * @covers ::key
     * @covers ::next
     */
    public function testKey()
    {
        $collection = new ItemPriceCollection();

        // No items exist, but the key should be at the first index
        $this->assertEquals(0, $collection->key());

        // Key should point at the next index
        $collection->next();
        $this->assertEquals(1, $collection->key());
    }

    /**
     * @covers ::next
     * @covers ::rewind
     * @covers ::current
     * @covers ::key
     * @covers ::valid
     * @uses ItemPrice::__construct
     * @uses ItemPrice::resetDiscountSubtotal
     * @uses ItemPrice::subtotal
     * @uses UnitPrice::__construct
     * @uses UnitPrice::total
     * @uses ItemPriceCollection::append
     * @uses ItemPriceCollection::remove
     */
    public function testNext()
    {
        $collection = new ItemPriceCollection();

        // Position starts at 0, increments each time
        $collection->next();
        $collection->next();
        $collection->next();
        $this->assertEquals(3, $collection->key());

        $collection->rewind();
        $this->assertEquals(0, $collection->key());

        // Add items, ensure next() iterates to the next item, not just the next index
        $item1 = new ItemPrice(1, 1);
        $item2 = new ItemPrice(2, 1);
        $item3 = new ItemPrice(3, 1);
        $collection->append($item1)->append($item2)->append($item3);
        $collection->remove($item2);
        $this->assertSame($item1, $collection->current());

        $collection->next();
        $this->assertSame($item3, $collection->current());

        // Remove the current item, and then get the next item
        $collection->rewind();
        $this->assertSame($item1, $collection->current());
        $collection->remove($item1);
        $collection->next();
        $this->assertSame($item3, $collection->current());

        // The next item is outside the collection and should be null
        $collection->next();
        $this->assertNull($collection->current());
    }

    /**
     * @covers ::rewind
     * @covers ::key
     * @covers ::next
     * @uses ItemPriceCollection::append
     */
    public function testRewind()
    {
        $collection = new ItemPriceCollection();

        // No items exist
        $this->assertEquals(0, $collection->key());

        $collection->rewind();
        $this->assertEquals(0, $collection->key());

        // Increase the position
        $collection->next();
        $collection->next();
        $this->assertEquals(2, $collection->key());

        // Rewind the position back
        $collection->rewind();
        $this->assertEquals(0, $collection->key());
    }

    /**
     * @covers ::valid
     * @covers ::next
     * @uses ItemPriceCollection::append
     * @uses ItemPrice::__construct
     * @uses ItemPrice::resetDiscountSubtotal
     * @uses ItemPrice::subtotal
     * @uses UnitPrice::__construct
     * @uses UnitPrice::total
     */
    public function testValid()
    {
        $collection = new ItemPriceCollection();

        // No items exist, position is not valid
        $this->assertFalse($collection->valid());

        // Item takes first position
        $collection->append(new ItemPrice(10, 1));
        $this->assertTrue($collection->valid());

        // No item exists in the next position
        $collection->next();
        $this->assertFalse($collection->valid());
    }
}
