<?php

/**
 * Interface for price descriptions
 */
interface PriceDescriptionInterface
{
    /**
     * Retrieves the price description
     *
     * @return string The price description
     */
    public function getDescription();

    /**
     * Sets a price description
     *
     * @param string $description The price description
     */
    public function setDescription($description);
}
