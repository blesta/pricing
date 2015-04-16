<?php

/**
 *
 */
class DiscountPrice extends AbstractPriceModifier
{
    public function __construct($amount, $type)
    {
        parent::__construct($amount, $type);

        #
        # TODO: Do more if type == 'amount', keep running total of discount applied via off()?
        #
    }

    public function off($price)
    {
        return $price - $this->on($price);
    }

    public function on($price)
    {
        if ('percent' === $this->type) {
            return ($price * max(0, $this->amount) / 100);
        } else {
            return min($price, max(0, $this->amount));
        }
    }
}
