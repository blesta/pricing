# blesta/pricing

A library for handling pricing. Supports:

- Unit Prices
- Item Prices
    - Unit Price that may include discounts and taxes
- Discounts
    - Percentages
    - Fixed amounts
- Taxes (inclusive, exclusive)
    - Inclusive and Exclusive
    - Applied in sequence or compounded
- Item Collection
    - Iterate over Item Prices
    - Aggregate totals over Item Prices

## Installation

Install via composer:

```sh
composer require blesta/pricing:~1.0
```

## Basic Usage

### UnitPrice

```php
$price = new UnitPrice(5.00, 2);
$price->setDescription("2 X 5.00");
$unit_price = $price->price(); // 5.00
$qty = $price->qty(); // 2
$total = $price->total(); // 10.00
```

### DiscountPrice

```php
$discount = new DiscountPrice(25.00, "percent");
$discount->setDescription("25% off");
$price_after_discount = $discount->off(100.00); // 75.00
$discount_price = $discount->on(100.00); // 25.00
```

### TaxPrice

Exclusive tax (price does not include tax):

```php
$tax = new TaxPrice(10.00, "exclusive");
$tax->setDescription("10 % tax");
$tax->on(100.00); // 10.00
$tax->off(100.00); // 100.00 (price on exclusive tax doesn't include tax, so nothing to take off)
$tax->including(100.00); // 110.00
```

Inclusive tax (price already includes tax):

```php
$tax = new TaxPrice(25.00, "inclusive");
$tax->setDescription("25 % tax");
$tax->on(100.00); // 25.00
$tax->off(100.00); // 75.00
$tax->including(100.00); // 100.00
```

Cascading tax (tax on a tax):

```php
$price = new UnitPrice(10.00);
$tax1 = new TaxPrice(10.00, "exclusive");
$tax2 = new TaxPrice(5.00, "exclusive");
$tax2->on(
    $tax1->on(
        $price->total()
    )
    + $price->total()
); // ((10.00 * 0.10) + 10.00) * 0.05 -> 0.55
```

### ItemPrice

```php
$item_price = new ItemPrice(10.00, 3);
$item_price->total(); // 30.00
```

With discount applied:

```php
$discount = new DiscountPrice(5.00, "percent");

// call setDiscount() as many times as needed to apply discounts
$item_price->setDiscount($discount);
$item_price->totalAfterDiscount(); // 28.50
```

Amount applied for a specific discount:

```php
$discount1 = new DiscountPrice(5.00, "percent");
$discount2 = new DiscountPrice(25.00, "percent");

// NOTE: Order matters here
$item_price->setDiscount($discount1);
$item_price->setDiscount($discount2);

$item_price->discountAmount($discount1); // 1.50
$item_price->discountAmount($discount2); // 7.125 ((30.00 - 1.50) * 0.25)
```

With tax applied:

```php
$tax = new TaxPrice(10.00, "exclusive");

// call setTax() as many times as needed to apply multiple levels of taxes
$item_price->setTax($tax);
// pass as many TaxPrice obects to setTax as you want to compound tax
// ex. $item_price->setTax($tax1, $tax2, ...);
$item_price->totalAfterTax(); // 30.30
```

With tax and discount:

```php
$item_price->total(); // 31.35
```

Amount applied for a specific tax:

```php
$tax1 = new TaxPrice(10.00, "exclusive");
$tax2 = new TaxPrice(5.00, "exclusive");

// NOTE: order *DOES NOT* matter
$item_price->setTax($tax1);
$item_price->setTax($tax2);

$item_price->taxAmount($tax1); // 3.00
$item_price->taxAmount($tax2); // 1.50
```

Cascading tax:

```php
$tax1 = new TaxPrice(10.00, "exclusive");
$tax2 = new TaxPrice(5.00, "exclusive");
$tax3 = new TaxPrice(2.50, "exclusive");

$item_price->setTax($tax1, $tax2, $tax3);
$item_price->taxAmount($tax1); // 3.00
$item_price->taxAmount($tax2); //  ((30.00 * 0.10) + 30.00) * 0.05 -> 1.65
$item_price->taxAmount($tax3); //  (((30.00 * 0.10) + 30.00) * 0.05) + 30.00 * 0.025 -> 0.86625
```

### ItemPriceCollection

```php
$item_collection = new ItemPriceCollection();

$item1 = new ItemPrice(10.00, 3);
$item2 = new ItemPrice(25.00, 2);
$item_collection->append($item1)->append($item2);

$item_collection->total(); // 80.00

foreach ($item_collection as $item) {
    $item->total(); // 30.00, 50.00
}
```

### PricingFactory

Using the PricingFactory can streamline usage. Assume you have the following:

```php

$products = array(
    array('desc' => 'Apples', 'amount' => 0.5, 'qty' => 3),
    array('desc' => 'Oranges', 'amount' => 0.75, 'qty' => 10)
);
```

So we initialize our PricingFactory, and let it create our DiscountPrice and TaxPrice objects for use.

```php
$pricing_factory = new PricingFactory();

// Some coupon
$discount = $pricing_factory->discountPrice(50.00, "percent")
    ->setDescription('Super-Saver Coupon');

// Typical local sales tax
$tax = $pricing_factory->taxPrice(10.00, "exclusive")
    ->setDescription("Sales tax");
```

Then we let the PricingFactory initialize our ItemPriceCollection, and each ItemPrice over our data set.

```php
$item_collection = $pricing_factory->itemPriceCollection();

foreach ($products as $product) {
    $item = $item_collection->itemPrice($product['amount'], $product['qty'])
        ->setDescription($product['desc'])
        ->setTax($tax);

    if ('Apples' === $product['desc']) {
        $item->setDiscount($discount);
    }
    $item_collection->append($item);
}

$item_collection->discountAmount($discount); // 0.75
$item_collection->taxAmount($tax); // 0.90
$item_collection->subtotal(); // 9.00
$item_collection->totalAfterTax(); // 9.90
$item_collection->totalAfterDiscount(); // 8.25
$item_collection->total(); // 9.075
```
