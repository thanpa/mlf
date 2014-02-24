<?php
/**
 * Date class for date functionality.
 *
 * @author Thanasis <hello@thanpa.com>
 */
class Date
{
    /**
     * Holds the unix timestamp of the date instance.
     *
     * @var integer
     */
    private $_timestamp = 0;
    /**
     * Constructs the Date.
     *
     * @param mixed $date The date can be a string or an integer.
     * @return null
     */
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
    /**
     * Returns the Date unix timestamp.
     *
     * @return integer
     */
    public function getTimestamp()
    {
        return $this->_timestamp;
    }
    /**
     * Returns the Date hours in the day.
     *
     * @return integer
     */
    public function getHours()
    {
        return (int) date('H', $this->_timestamp);
    }
    /**
     * Returns the time in H:i format.
     *
     * @return string
     */
    public function getTime()
    {
        return date('H:i', $this->_timestamp);
    }
    /**
     * Returns the Date month in the year.
     *
     * @return integer
     */
    public function getMonth()
    {
        return (int) date('m', $this->_timestamp);
    }
    /**
     * Returns the Date day in the month.
     *
     * @return integer
     */
    public function getDay()
    {
        return (int) date('d', $this->_timestamp);
    }
    /**
     * Returns the Date.
     *
     * @return string
     */
    public function getDate()
    {
        return date('d-m-Y', $this->_timestamp);
    }
    /**
     * Returns the Date in a specified format.
     *
     * @param string $format
     * @return string
     */
    public function show($format = 'd-m-Y H:i')
    {
        return date($format, $this->_timestamp);
    }
    /**
     * Returns the Date day in the year.
     *
     * @return integer
     */
    public function getDayOfTheYear()
    {
        return (int) date('z', $this->_timestamp);
    }
    /**
     * Returns the Date in a DB friendly way.
     *
     * @return string
     */
    public function __toString()
    {
        return date('Y-m-d', $this->_timestamp);
    }
    /**
     * Calculates a new Date with the use of strtotime.
     *
     * @param Date $date The initial Date.
     * @param string $toCalc The diff
     * @return \Date
     */
    public static function calc(Date $date, $toCalc)
    {
        return new Date(strtotime(sprintf('%s %s', $date, $toCalc)));
    }
}