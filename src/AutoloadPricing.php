<?php

/**
 * Pricing autoloader for PHP < 5.3
 */
class AutoloadPricing
{
    /**
     * Attempt to load the given class
     *
     * @param string $class The class to load
     */
    public static function load($class)
    {
        $baseDir = dirname(__FILE__) . DIRECTORY_SEPARATOR;

        $classes = array(
            'AbstractPriceDescription' => $baseDir . 'AbstractPriceDescription.php',
            'AbstractPriceModifier' => $baseDir . 'AbstractPriceModifier.php',
            'DiscountPrice' => $baseDir . 'DiscountPrice.php',
            'ItemPrice' => $baseDir . 'ItemPrice.php',
            'ItemPriceCollection' => $baseDir . 'ItemPriceCollection.php',
            'PriceDescriptionInterface' => $baseDir . 'PriceDescriptionInterface.php',
            'PriceModifierInterface' => $baseDir . 'PriceModifierInterface.php',
            'PriceTotalInterface' => $baseDir . 'PriceTotalInterface.php',
            'PricingFactory' => $baseDir . 'PricingFactory.php',
            'TaxPrice' => $baseDir . 'TaxPrice.php',
            'UnitPrice' => $baseDir . 'UnitPrice.php'
        );

        if (isset($classes[$class])) {
            include $classes[$class];
        }
    }
}
