<?php

/**
 * Abstract Price Description for setting a price description
 */
abstract class AbstractPriceDescription implements PriceDescriptionInterface
{
    /**
     * @var string The price description
     */
    protected $description;

    /**
     * Sets a description for a price
     *
     * @param string $description The price description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Retrieves the price description
     *
     * @return string The price description
     */
    public function getDescription()
    {
        return $this->description;
    }
}
