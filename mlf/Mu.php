<?php
class Mu
{
    private $_amount = 0;
    public function __construct($amount, $passingMu = false)
    {
        $this->_amount = $passingMu ? $amount : intval($amount * 100);
    }
    public function __toString()
    {
        return (string)$this->_amount;
    }
    public function amount()
    {
        return round($this->_amount / 100, 2);
    }
    public function muAmount()
    {
        return $this->_amount;
    }
}