<?php
class Date
{
    private $_timestamp = 0;
    public function __construct($date = null)
    {
        if (empty($date)) {
            $this->_timestamp = time();
        } else if (is_numeric($date)) {
            $this->_timestamp = $date;
        } else {
            if (strpos($date, '/')) {
                $date = str_replace('/', '-', $date);
            }
            $this->_timestamp = strtotime($date);
        }
    }
    public function getTimestamp()
    {
        return $this->_timestamp;
    }
    public function getHours()
    {
        return date('H', $this->_timestamp);
    }
    public function getTime()
    {
        return date('H:i', $this->_timestamp);
    }
    public function getMonth()
    {
        return date('m', $this->_timestamp);
    }
    public function getDay()
    {
        return date('d', $this->_timestamp);
    }
    public function getDate()
    {
        return date('d-m-Y', $this->_timestamp);
    }
    public function show($format = 'd-m-Y H:i')
    {
        return date($format, $this->_timestamp);
    }
    public function getDayOfTheYear()
    {
        return date('z', $this->_timestamp);
    }
    public function __toString()
    {
        return date('Y-m-d', $this->_timestamp);
    }
    public static function calc(Date $date, $toCalc)
    {
        return new Date(strtotime(sprintf('%s %s', $date, $toCalc)));
    }
}