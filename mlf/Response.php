<?php
/**
 * Request class.
 *
 * @author Thanasis <hello@thanpa.com>
 */
class Response
{
    /**
     * Response headers.
     *
     * @var array
     */
    private $_headers = array();
    /**
     * Response body.
     *
     * @var string
     */
    private $_body = '';
    /**
     * Flag to determine if the response is sent.
     *
     * @var boolean
     */
    private $_isSent;
    /**
     * Singleton instance.
     *
     * @var Response
     */
    private static $_instance;
    /**
     * Returns the instance of the Response.
     *
     * @return Response
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof Response)) {
            self::$_instance = new Response();
        }
        return self::$_instance;
    }
    /**
     * Sets a header.
     *
     * @param string $key
     * @param string $value
     * @return null
     */
    public function setHeader($key, $value)
    {
        $this->_headers[] = "{$key}:{$value}";
    }
    /**
     * Sets the body.
     *
     * @param string $body
     * @return null
     */
    public function setBody($body)
    {
        $this->_body = $body;
    }
    /**
     * Sends the response to the output.
     *
     * @return null
     */
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