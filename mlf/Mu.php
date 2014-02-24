<?php
/**
 * Monetary Unit class for currency functionality.
 *
 * @author Thanasis <hello@thanpa.com>
 */
class Mu
{
    /**
     * The amount of the instance.
     *
     * @var integer
     */
    private $_amount = 0;
    /**
     * Constructs the instance.
     *
     * <p>If $passingMu is true, then the $amount will be an integer, otherwise it will be a float.
     *
     * @param mixed $amount
     * @param boolean $passingMu
     */
    public function __construct($amount, $passingMu = false)
    {
        $this->_amount = $passingMu ? $amount : intval($amount * 100);
    }
    /**
     * Returns a string representation of the amount.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->_amount;
    }
    /**
     * Returns a normal representation of the amount.
     *
     * @return float
     */
    public function amount()
    {
        return round($this->_amount / 100, 2);
    }
    /**
     * Returns a monetary unit representation of the amount.
     *
     * @return integer
     */
    public function muAmount()
    {
        return $this->_amount;
    }
}