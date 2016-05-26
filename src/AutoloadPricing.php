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
        $collectionDir = $baseDir . 'Collection' . DIRECTORY_SEPARATOR;
        $comparatorDir = $baseDir . 'Comparator' . DIRECTORY_SEPARATOR;
        $descriptionDir = $baseDir . 'Description' . DIRECTORY_SEPARATOR;
        $metaDir = $baseDir . 'Meta' . DIRECTORY_SEPARATOR;
        $modifierDir = $baseDir . 'Modifier' . DIRECTORY_SEPARATOR;
        $totalDir = $baseDir . 'Total' . DIRECTORY_SEPARATOR;
        $typeDir = $baseDir . 'Type' . DIRECTORY_SEPARATOR;

        $classes = array(
            'AbstractItemComparator' => $comparatorDir . 'AbstractItemComparator.php',
            'AbstractPriceDescription' => $descriptionDir . 'AbstractPriceDescription.php',
            'AbstractPriceModifier' => $modifierDir . 'AbstractPriceModifier.php',
            'DiscountPrice' => $modifierDir . 'DiscountPrice.php',
            'ItemMetaInterface' => $metaDir . 'ItemMetaInterface.php',
            'ItemPrice' => $typeDir . 'ItemPrice.php',
            'ItemPriceCollection' => $collectionDir . 'ItemPriceCollection.php',
            'PriceDescriptionInterface' => $descriptionDir . 'PriceDescriptionInterface.php',
            'PriceModifierInterface' => $modifierDir . 'PriceModifierInterface.php',
            'PriceTotalInterface' => $totalDir . 'PriceTotalInterface.php',
            'PricingFactory' => $baseDir . 'PricingFactory.php',
            'PriceInterface' => $typeDir . 'PriceInterface.php',
            'ServiceComparator' => $comparatorDir . 'ServiceComparator.php',
            'TaxPrice' => $modifierDir . 'TaxPrice.php',
            'UnitPrice' => $typeDir . 'UnitPrice.php'
        );

        if (isset($classes[$class])) {
            include $classes[$class];
        }
    }
}
