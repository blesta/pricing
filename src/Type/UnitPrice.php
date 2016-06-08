<?php
namespace Blesta\Pricing\Type;

use Blesta\Pricing\Description\AbstractPriceDescription;

/**
 * Builds a unit price
 */
class UnitPrice extends AbstractPriceDescription implements PriceInterface
{
    /**
     * @var float The unit price
     */
    protected $price;

    /**
     * @var int The quantity of unit prices
     */
    protected $qty;

    /**
     * @var string The unique identifier
     */
    protected $key;

    /**
     * Initialize the unit price
     *
     * @param float $price The unit price
     * @param int $qty The quantity of unit prices (optional, default 1)
     * @param string $key A unique identifier (optional, default null)
     */
    public function __construct($price, $qty = 1, $key = null)
    {
        $this->setPrice($price);
        $this->setQty($qty);
        $this->setKey($key);
    }

    /**
     * {@inheritdoc}
     */
    public function price()
    {
        return $this->price;
    }

    /**
     * {@inheritdoc}
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * {@inheritdoc}
     */
    public function qty()
    {
        return $this->qty;
    }

    /**
     * {@inheritdoc}
     */
    public function setQty($qty)
    {
        $this->qty = $qty;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Retrieves the total price
     *
     * @return float The total price considering quantity
     */
    public function total()
    {
        return $this->qty * $this->price;
    }
}
