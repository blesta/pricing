<?php

/**
 *
 */
abstract class AbstractPriceModifier extends AbstractPriceDescription implements PriceModifierInterface
{
    protected $amount;
    protected $type;

    public function __construct($amount, $type)
    {
        $this->amount = $amount;
        $this->type = $type;
    }

    public function amount()
    {
        return $this->amount;
    }

    public function type()
    {
        return $this->type;
    }
}
