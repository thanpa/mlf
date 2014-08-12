<?php
/**
 * Security class.
 *
 * @author Thanasis <hello@thanpa.com>
 */
class Security
{
    /**
     * Returns an MD5 hash.
     *
     * @param string $string
     * @return string
     */
    public static function hash($string)
    {
        return md5($string);
    }
}