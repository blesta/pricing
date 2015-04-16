<?php

/**
 *
 */
interface PriceModifierInterface extends PriceDescriptionInterface
{
    public function __construct($amount, $type);
    public function amount();
    public function type();
    public function off($price);
    public function on($price);
}
