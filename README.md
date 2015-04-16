# blesta/Pricing

A library for handling pricing. Supports:

- Unit Prices
- Item Prices
    - Unit Price that may include discounts and taxes
- Discounts
    - Percentages
    - Fixed amounts
- Taxes (inclusive, exclusive)
    - Inclusive and Exclusive
    - Applied in sequence of compounded
- Item Collection
    - Iterate over Item Prices
    - Aggregate totals over Item Prices

## Installation

Install via composer:

```js
    "repositories": [
        {
            "type": "package",
            "package": {
                "name": "blesta/pricing",
                "version": "dev-master",
                "dist": {
                    "url": "http://git.blestalabs.com/billing/proration/pricing/archive.zip",
                    "type": "zip"
                },
                "source": {
                    "url": "http://git.blestalabs.com/billing/pricing",
                    "type": "git",
                    "reference": "tree/master"
                }
            }
        }
    ]
```
```sh
composer require blesta/pricing:dev-master
```

## Basic Usage
```php
TODO

```