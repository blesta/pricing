<?php

/**
 * @coversDefaultClass ItemPriceCollection
 */
class ItemPriceCollectionTest extends PHPUnit_Framework_TestCase
{

    /**
     * @covers ::append
     */
    public function testAppend()
    {
        $collection = new ItemPriceCollection();
        $items = array(new ItemPrice(10, 2), new ItemPrice(5, 1));

        #
        # TODO: replace manual count with $collection->count()
        #
        // Add 1 item
        $collection->append($items[0]);
        $num_items = 0;
        foreach ($collection as $item_price) {
            $this->assertSame($items[$num_items], $item_price);
            $num_items++;
        }
        $this->assertEquals(1, $num_items);

        // Add a second item
        $collection->append($items[1]);
        $num_items = 0;
        foreach ($collection as $item_price) {
            $this->assertSame($items[$num_items], $item_price);
            $num_items++;
        }
        $this->assertEquals(2, $num_items);
    }

    /**
     * @covers ::append
     * @covers ::remove
     */
    public function testRemove()
    {
        $collection = new ItemPriceCollection();
        $item1 = new ItemPrice(2, 1);
        $items = array(new ItemPrice(1, 2), $item1, new ItemPrice(3, 1), $item1);

        foreach ($items as $item) {
            $collection->append($item);
        }

        #
        # TODO: replace manual count with $collection->count()
        #
        // Remove an item, leaving 3 remaining
        $collection->remove($items[0]);
        $num_items = 0;
        foreach ($collection as $item_price) {
            $num_items++;
        }
        $this->assertEquals(3, $num_items);

        // Remove an item that exists multiple times
        $collection->remove($item1);
        $num_items = 0;
        foreach ($collection as $item_price) {
            $num_items++;
        }
        $this->assertEquals(1, $num_items);
    }
}
