<?php

/**
 * @coversDefaultClass ItemPriceCollection
 */
class ItemPriceCollectionTest extends PHPUnit_Framework_TestCase
{

    /**
     * @covers ::append
     * @uses ::count
     */
    public function testAppend()
    {
        $collection = new ItemPriceCollection();
        $items = array(new ItemPrice(10, 2), new ItemPrice(5, 1));

        // Add 1 item
        $collection->append($items[0]);
        foreach ($collection as $item_price) {
            $this->assertSame($items[0], $item_price);
        }
        $this->assertEquals(1, $collection->count());

        // Add a second item
        $collection->append($items[1]);
        $i = 0;
        foreach ($collection as $item_price) {
            $this->assertSame($items[$i++], $item_price);
        }
        $this->assertEquals(2, $collection->count());
    }

    /**
     * @covers ::append
     * @covers ::remove
     * @uses ::count
     */
    public function testRemove()
    {
        $collection = new ItemPriceCollection();

        // Add 4 items to the collection
        $item1 = new ItemPrice(2, 1);
        $items = array(new ItemPrice(1, 2), $item1, new ItemPrice(3, 1), $item1);
        foreach ($items as $item) {
            $collection->append($item);
        }

        // Remove an item, leaving 3 remaining
        $collection->remove($items[0]);
        foreach ($collection as $item_price) {
            $this->assertNotSame($items[0], $item_price);
        }
        $this->assertEquals(3, $collection->count());

        // Remove an item that exists multiple times
        $collection->remove($item1);
        foreach ($collection as $item_price) {
            $this->assertNotSame($item1, $item_price);
        }
        $this->assertEquals(1, $collection->count());
    }

    /**
     * @covers ::count
     * @uses ::append
     * @uses ::remove
     */
    public function testCount()
    {
        // No items, count is zero
        $collection = new ItemPriceCollection();
        $this->assertEquals(0, $collection->count());

        // One item, count is one
        $item = new ItemPrice(10, 1);
        $collection->append($item);
        $count = 1;
        $this->assertEquals($count, $collection->count());

        // Multiple items, count increasing by one each time
        for ($i=0; $i<10; $i++) {
            $collection->append(new ItemPrice($i, 1));
            $this->assertEquals(++$count, $collection->count());
        }

        // Removing an item, the count decreases by one
        $collection->remove($item);
        $this->assertEquals(--$count, $collection->count());
    }

    /**
     * @covers ::totalAfterTax
     * @dataProvider totalProvider
     */
    public function testTotalAfterTax(ItemPriceCollection $collection, array $expected_totals)
    {
        $this->assertEquals($expected_totals['total_with_tax'], $collection->totalAfterTax());
    }

    /**
     * @covers ::totalAfterDiscount
     * @dataProvider totalProvider
     */
    public function testTotalAfterDiscount(ItemPriceCollection $collection, array $expected_totals)
    {
        #
        # TODO: total is incorrect for discount amounts
        #
        $this->assertEquals($expected_totals['total_with_discount'], $collection->totalAfterDiscount());

        $this->markTestIncomplete('Discount amounts are not considered.');
    }

    /**
     * @covers ::subtotal
     * @dataProvider totalProvider
     */
    public function testSubtotal(ItemPriceCollection $collection, array $expected_totals)
    {
        $this->assertEquals($expected_totals['subtotal'], $collection->subtotal());
    }

    /**
     * @covers ::total
     * @dataProvider totalProvider
     */
    public function testTotal(ItemPriceCollection $collection, array $expected_totals)
    {
        #
        # TODO: total is incorrect for discount amounts. discount amounts are not decreased per item
        #
        echo "\nTOTAL:" . $collection->total() . "\n";
        $this->assertEquals($expected_totals['total'], $collection->total());

        $this->markTestIncomplete('Discount amounts are not considered.');
    }

    /**
     *
     * @covers ::taxAmount
     * @dataProvider totalProvider
     */
    public function testTaxAmount(ItemPriceCollection $collection, array $expected_totals)
    {
        $this->assertEquals($expected_totals['tax'], $collection->taxAmount());
    }

    /**
     * @covers ::discountAmount
     * @dataProvider totalProvider
     */
    public function testDiscountAmount()
    {
        #
        # TODO: consider discounts over multiple items
        #
        $this->markTestIncomplete('Discount amounts are not considered.');
    }

    /**
     * @covers ::taxes
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
     * Data provider for subtotal/total
     *
     * @return array
     */
    public function totalProvider()
    {
        // Item with discounts and tax
        $tax_price = new TaxPrice(10, 'exclusive');
        $item1 = new ItemPrice(10, 2);
        $item1->setDiscount(new DiscountPrice(20, 'percent'));
        $item1->setDiscount(new DiscountPrice(1, 'amount'));
        $item1->setTax($tax_price);

        // Item with tax
        $item2 = new ItemPrice(5.25, 3);
        $item2->setTax($tax_price);

        // Item with compound tax and discount
        $item3 = new ItemPrice(100.00, 1);
        $item3->setDiscount(new DiscountPrice(1.50, 'amount'));
        $item3->setTax(new TaxPrice(8, 'exclusive'), new TaxPrice(5, 'exclusive'));

        // Amount discount applied to multiple items
        $discount_price = new DiscountPrice(18.00, 'amount');
        $item4 = new ItemPrice(15, 1);
        $item4->setDiscount($discount_price);
        $item5 = new ItemPrice(5, 1);
        $item5->setDiscount($discount_price);

        // Set collections of the items
        $collection1 = new ItemPriceCollection();
        $collection1->append($item1)->append($item1);

        $collection2 = new ItemPriceCollection();
        $collection2->append($item1)->append($item2);

        $collection3 = new ItemPriceCollection();
        $collection3->append($item1)->append($item2)->append($item3);

        $collection4 = new ItemPriceCollection();
        $collection4->append($item4)->append($item5);

        return array(
            array($collection1, $this->getItemTotals($item1, $item1)),
            array($collection2, $this->getItemTotals($item1, $item2)),
            array($collection3, $this->getItemTotals($item1, $item2, $item3)),
            array($collection4, $this->getItemTotals($item4, $item5)),
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
            $totals['total_with_tax'] += $item->totalAfterTax();
            $totals['total_with_discount'] += $item->totalAfterDiscount();
            $totals['tax'] += $item->taxAmount();
            $totals['discount'] += $item->discountAmount();
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
     * @covers ::current
     * @uses ::append
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
     * @uses ::append
     * @uses ::remove
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
     * @uses ::append
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
     * @uses ::append
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
