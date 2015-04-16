<?php

/**
 *
 */
class TaxPrice extends AbstractPriceModifier
{

    public function off($price)
    {
        if ('inclusive' == $this->type) {
            return $price - $this->on($price);
        }
        return $price;
    }

    public function on($price)
    {
        return max(0, $this->amount / 100) * $price;
    }

    public function including($price)
    {
        if ('exclusive' == $this->type) {
            return $price + $this->on($price);
        }
        return $price;
    }
}
