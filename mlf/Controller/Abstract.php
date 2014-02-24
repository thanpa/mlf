<?php
/**
 * Abstraction of the controllers.
 *
 * @author Thanasis <hello@thanpa.com>
 */
abstract class Controller_Abstract
{
    /**
     * Holds the view.
     *
     * @var View
     */
    private $_view;
    /**
     * Halts any redirects requested by the controllers.
     *
     * <p>Used in CLI.
     *
     * @var boolean
     */
    private static $_haltRedirects = false;
    /**
     * Constructs the abstract class.
     *
     * @return null
     */
    public function __construct()
    {
        if ($this->_getRequest()->isConsole()) {
            self::$_haltRedirects = true;
        }
    }
    /**
     * Returns the view.
     *
     * @return View
     */
    public function getView()
    {
        if (!($this->_view instanceof View)) {
            $this->_view = new View();
        }
        return $this->_view;
    }
    /**
     * Sets the view.
     *
     * @param View $view
     * @return boolean
     */
    public function setView(View $view)
    {
        $this->_view = $view;
        return true;
    }
    /**
     * Resets the view to null.
     *
     * @return boolean
     */
    public function resetView()
    {
        $this->_view = null;
        return true;
    }
    /**
     * Returns the current request.
     *
     * @return Request
     */
    protected function _getRequest()
    {
        return Request::getInstance();
    }
    /**
     * Redirects the request to a provided URL.
     *
     * @param string $url
     * @return null
     */
    protected function _redirect($url)
    {
        if (!self::$_haltRedirects) {
            $response = Response::getInstance();
            $response->setHeader('location', $url);
            $response->send();
        }
    }
}