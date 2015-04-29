<?php

/**
 * Interface for price modifications
 */
interface PriceModifierInterface extends PriceDescriptionInterface
{
    /**
     * Initializes a price modifier
     *
     * @param float $amount The price modifier amount
     * @param string $type The price modifier type
     */
    public function __construct($amount, $type);

    /**
     * Retrieves the price modifier amount
     *
     * @return float The amount
     */
    public function amount();

    /**
     * Retrieves the price modifier type
     *
     * @return string The type
     */
    public function type();

    /**
     * Retrieves the price after modification
     *
     * @param float $price The price to modify
     * @return float The price after modification
     */
    public function off($price);

    /**
     * Retrieves the modification price
     *
     * @param float $price The price to modify
     * @return float The modification price
     */
    public function on($price);

    /**
     * Reset the state of the price modifier
     */
    public function reset();
}
