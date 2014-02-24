<?php
class Response
{
    private $_headers = array();
    private $_body = '';
    private $_isSent;
    private static $_instance;
    public static function getInstance()
    {
        if (!(self::$_instance instanceof Response)) {
            self::$_instance = new Response();
        }
        return self::$_instance;
    }
    public function setHeader($key, $value)
    {
        $this->_headers[] = "{$key}:{$value}";
    }
    public function setBody($body)
    {
        $this->_body = $body;
    }
    public function send()
    {
        if (!$this->_isSent) {
            foreach ($this->_headers as $header) {
                header($header);
            }
            echo $this->_body;
            $this->_isSent = true;
        }
    }
}