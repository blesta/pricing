# blesta/pricing

[![Build Status](https://travis-ci.org/blesta/pricing.svg?branch=master)](https://travis-ci.org/blesta/pricing) [![Coverage Status](https://coveralls.io/repos/github/blesta/pricing/badge.svg?branch=master)](https://coveralls.io/github/blesta/pricing?branch=master)

A library for handling pricing. Supports:

- Unit Prices
- Item Prices
    - Unit Price that may include discounts and taxes
- Discounts
    - Percentages
    - Fixed amounts
- Taxes (inclusive_calculated, inclusive, exclusive)
    - Inclusive and Exclusive
    - Applied in sequence or compounded
    - Inclusive calculated is meant to be subtracted from the item price
- Item Collection
    - Iterate over Item Prices
    - Aggregate totals over Item Prices

## Installation

Install via composer:

```sh
composer require blesta/pricing
```

## Basic Usage

### UnitPrice

```php
use Blesta\Pricing\Type\UnitPrice;

$price = new UnitPrice(5.00, 2, "id");
$price->setDescription("2 X 5.00");
$unit_price = $price->price(); // 5.00
$qty = $price->qty(); // 2
$total = $price->total(); // 10.00
$key = $price->key(); // id

// Update the unit price, quantity, and key
$price->setPrice(10.00);
$price->setQty(3);
$price->setKey('id2');
```

### DiscountPrice

```php
use Blesta\Pricing\Modifier\DiscountPrice;

$discount = new DiscountPrice(25.00, "percent");
$discount->setDescription("25% off");
$price_after_discount = $discount->off(100.00); // 75.00
$discount_price = $discount->on(100.00); // 25.00
```

### TaxPrice

Exclusive tax (price does not include tax):

```php
use Blesta\Pricing\Modifier\TaxPrice;

$tax = new TaxPrice(10.00, TaxPrice::EXCLUSIVE);
$tax->setDescription("10 % tax");
$tax->on(100.00); // 10.00
$tax->off(100.00); // 100.00 (price on exclusive tax doesn't include tax, so nothing to take off)
$tax->including(100.00); // 110.00
```

Inclusive tax (price already includes tax):

```php
use Blesta\Pricing\Modifier\TaxPrice;

$tax = new TaxPrice(25.00, TaxPrice::INCLUSIVE);
$tax->setDescription("25 % tax");
$tax->on(100.00); // 25.00
$tax->off(100.00); // 75.00
$tax->including(100.00); // 100.00
```

Inclusive tax (price already includes tax) calculated based on the price minus tax:

```php
use Blesta\Pricing\Modifier\TaxPrice;

$tax = new TaxPrice(25.00, TaxPrice::INCLUSIVE_CALCULATED);
$tax->setDescription("25 % tax");
$tax->on(100.00); // 20.00
$tax->off(100.00); // 80.00
$tax->including(100.00); // 100.00
```

Cascading tax (tax on a tax):

```php
use Blesta\Pricing\Modifier\TaxPrice;
use Blesta\Pricing\Type\UnitPrice;

$price = new UnitPrice(10.00);
$tax1 = new TaxPrice(10.00, TaxPrice::EXCLUSIVE);
$tax2 = new TaxPrice(5.00, TaxPrice::EXCLUSIVE);
$tax2->on(
    $tax1->on(
        $price->total()
    )
    + $price->total()
); // 0.55 = [((10.00 * 0.10) + 10.00) * 0.05]
```

### ItemPrice

```php
use Blesta\Pricing\Type\ItemPrice;

$item_price = new ItemPrice(10.00, 3);
$item_price->total(); // 30.00
```

With discount applied:

```php
use Blesta\Pricing\Modifier\DiscountPrice;

$discount = new DiscountPrice(5.00, "percent");

// call setDiscount() as many times as needed to apply discounts
$item_price->setDiscount($discount);
$item_price->totalAfterDiscount(); // 28.50
```

Amount applied for a specific discount:

```php
use Blesta\Pricing\Modifier\DiscountPrice;

$item_price = new ItemPrice(10.00, 3);

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
use Blesta\Pricing\Modifier\TaxPrice;

$tax = new TaxPrice(10.00, TaxPrice::EXCLUSIVE);

// call setTax() as many times as needed to apply multiple levels of taxes
$item_price->setTax($tax);
// pass as many TaxPrice objects to setTax as you want to compound tax
// ex. $item_price->setTax($tax1, $tax2, ...);
$item_price->totalAfterTax(); // 32.1375 = (subtotal + ([subtotal - discounts] * taxes)) = (30 + [30 - (1.50 + 7.125)] * 0.10)
```

With tax and discount:

```php
$item_price->total(); // 23.5125 = (subtotal - discounts + ([subtotal - discounts] * taxes)) = (30 - (1.50 + 7.125) + [30 - (1.50 + 7.125)] * 0.10)
```

With tax and discount where the discount does *not* apply to the taxes:

```php
$item_price->setDiscountTaxes(false);
$item_price->total(); // 24.375 = (subtotal - discounts + ([subtotal] * taxes)) = (30 - (1.50 + 7.125) + ([30] * 0.10))
```

Without taxes of the 'exclusive' type:

```php
$item_price->setDiscountTaxes(true);
$item_price->excludeTax(TaxPrice::EXCLUSIVE)->totalAfterTax(); // 30.00 = (30 + [30 - (1.50 + 7.125)] * 0)
$item_price->total(); // 21.375 = (30 - (1.50 + 7.125) + [30 - (1.50 + 7.125)] * 0)

// Be sure to reset the excluded taxes before attempting to fetch totals that should include them again!
$item_price->resetTaxes();
$item_price->total(); // 23.5125 = (subtotal - discounts + ([subtotal - discounts] * taxes)) = (30 - (1.50 + 7.125) + [30 - (1.50 + 7.125)] * 0.10)
$item_price->excludeTax(TaxPrice::EXCLUSIVE)->total(); // 21.375 = (30 - (1.50 + 7.125) + [30 - (1.50 + 7.125)] * 0)
$item_price->resetTaxes();
```

Amount applied for a specific tax:

```php
use Blesta\Pricing\Modifier\TaxPrice;

$tax1 = new TaxPrice(10.00, TaxPrice::EXCLUSIVE);
$tax2 = new TaxPrice(5.00, TaxPrice::INCLUSIVE);

// NOTE: order *DOES NOT* matter
$item_price->setTax($tax1);
$item_price->setTax($tax2);

$item_price->taxAmount($tax1); // 2.1375 = ([subtotal - discounts] * taxes) = ([30 - (1.50 + 7.125)] * 0.10)
$item_price->taxAmount($tax2); // 1.06875 = ([subtotal - discounts] * taxes) = ([30 - (1.50 + 7.125)] * 0.05)
```

Without taxes of the 'exclusive' type:

```php
$item_price->excludeTax(TaxPrice::EXCLUSIVE)->totalAfterTax(); // 31.06875 = (subtotal + ([subtotal - discounts] * taxes)) = (30 + [30 - (1.50 + 7.125)] * 0.05)
$item_price->resetTaxes();
```

Cascading tax:

```php
use Blesta\Pricing\Modifier\TaxPrice;
use Blesta\Pricing\Type\ItemPrice;

$item_price = new ItemPrice(10.00, 3);

$tax1 = new TaxPrice(10.00, TaxPrice::EXCLUSIVE);
$tax2 = new TaxPrice(5.00, TaxPrice::INCLUSIVE);
$tax3 = new TaxPrice(2.50, TaxPrice::EXCLUSIVE);

$item_price->setTax($tax1, $tax2, $tax3);
$item_price->taxAmount($tax1); // 3.00 = ([subtotal - discounts] * taxes) = ([30 - 0] * 0.10)
$item_price->taxAmount($tax2); // 1.65 = ([subtotal - discounts + previous-taxes] * 0.05) = ([30.00 - 0 + 3.00] * 0.05)
$item_price->taxAmount($tax3); // 0.86625 = ([subtotal - discounts + previous-taxes] * 0.025) = ([30.00 - 0 + 3.00 + 1.65] * 0.025)
$item_price->taxAmount(); // 5.51625

// Exclude taxes of the 'inclusive' type
$item_price->excludeTax(TaxPrice::INCLUSIVE);
$item_price->taxAmount($tax1); // 3.00 = ([subtotal - discounts] * taxes) = ([30 - 0] * 0.10)
$item_price->taxAmount($tax2); // 0 = ([subtotal - discounts + previous-taxes] * 0) = ([30.00 - 0 + 3.00] * 0)
$item_price->taxAmount($tax3); // 0.86625 = ([subtotal - discounts + previous-taxes] * 0.025) = ([30.00 - 0 + 3.00 + 1.65] * 0.025)
$item_price->taxAmount(); // 3.86625
$item_price->resetTaxes();
```

### ItemPriceCollection

```php
use Blesta\Pricing\Collection\ItemPriceCollection;
use Blesta\Pricing\Type\ItemPrice;

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
use Blesta\Pricing\PricingFactory;

$pricing_factory = new PricingFactory();

// Some coupon
$discount = $pricing_factory->discountPrice(50.00, "percent");
$discount->setDescription('Super-Saver Coupon');

// Typical local sales tax
$tax = $pricing_factory->taxPrice(10.00, TaxPrice::EXCLUSIVE);
$tax->setDescription("Sales tax");
```

Then we let the PricingFactory initialize our ItemPriceCollection, and each ItemPrice over our data set.

```php
$item_collection = $pricing_factory->itemPriceCollection();

foreach ($products as $product) {
    $item = $pricing_factory->itemPrice($product['amount'], $product['qty']);
    $item->setDescription($product['desc']);
    $item->setTax($tax);

    if ('Apples' === $product['desc']) {
        $item->setDiscount($discount);
    }
    $item_collection->append($item);
}

$item_collection->discountAmount($discount); // 0.75
$item_collection->taxAmount($tax); // 0.825
$item_collection->subtotal(); // 9.00
$item_collection->totalAfterTax(); // 9.825
$item_collection->totalAfterDiscount(); // 8.25
$item_collection->total(); // 9.075
```

You may also exclude specific taxes by their type when calculating totals:

```php
$item_collection->excludeTax(TaxPrice::EXCLUSIVE)->taxAmount($tax); // 0.00
$item_collection->excludeTax(TaxPrice::EXCLUSIVE)->totalAfterTax(); // 9.00
$item_collection->excludeTax(TaxPrice::EXCLUSIVE)->total(); // 8.25
$item_collection->total(); // 9.075 (item tax exclusions in the collection are reset after each call to a ::total..., the ::taxAmount, or ::discountAmount)
```
