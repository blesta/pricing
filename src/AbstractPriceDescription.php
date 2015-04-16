<?php

/**
 *
 */
abstract class AbstractPriceDescription implements PriceDescriptionInterface
{
    protected $description;

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }
}
