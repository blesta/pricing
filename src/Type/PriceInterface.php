<?php
namespace Blesta\Pricing\Type;

/**
 * Interface for pricing
 */
interface PriceInterface
{
    /**
     * Retrieve the price
     *
     * @return float The price
     */
    public function price();

    /**
     * Sets the price
     *
     * @param float $price The price to set
     */
    public function setPrice($price);

    /**
     * Retrieve the quantity
     *
     * @return int The quantity
     */
    public function qty();

    /**
     * Sets the quantity
     *
     * @param int $qty The quantity
     */
    public function setQty($qty);

    /**
     * Retrieves the key
     *
     * @return string The key
     */
    public function key();

    /**
     * Sets a key
     *
     * @param string $key The key
     */
    public function setKey($key);
}
