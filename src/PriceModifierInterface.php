<?php

/**
 * Interface for modifying prices
 */
interface PriceModifierInterface extends PriceDescriptionInterface
{
    /**
     * Initialize the price amount and type
     *
     * @param float $amount The price amount
     * @param string $type The price type
     */
    public function __construct($amount, $type);

    /**
     * Retrieves the price amount
     *
     * @return float The price amount
     */
    public function amount();

    /**
     * Retrieves the price type
     *
     * @return string The price type
     */
    public function type();

    /**
     * Determines the price after a modification
     *
     * @param float $price The price to modify
     * @return float The price after modification
     */
    public function off($price);

    /**
     * Determines the price modification
     *
     * @param float $price The price to modify
     * @param float The modification price
     */
    public function on($price);
}
