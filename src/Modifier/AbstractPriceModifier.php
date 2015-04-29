<?php

/**
 * Abstract Price Modifier for setting a price amount and type
 */
abstract class AbstractPriceModifier extends AbstractPriceDescription implements PriceModifierInterface
{
    /**
     * @var float The price amount
     */
    protected $amount;

    /**
     * @var string The price type
     */
    protected $type;

    /**
     * Initializes a price
     *
     * @param float $amount The price amount
     * @param string $type The price type
     */
    public function __construct($amount, $type)
    {
        $this->amount = $amount;
        $this->type = $type;
    }

    /**
     * Retrieves the price amount
     *
     * @return float The price amount
     */
    public function amount()
    {
        return $this->amount;
    }

    /**
     * Retrieves the price type
     *
     * @return string The price type
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        // Nothing to do
    }
}
