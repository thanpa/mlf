<?php
class Controller_Abstract
{
    private $_view;
    private static $_haltRedirects = false;
    public function __construct()
    {
        if ($this->_getRequest()->isConsole()) {
            self::$_haltRedirects = true;
        }
    }
    public function getView()
    {
        if (!($this->_view instanceof View)) {
            $this->_view = new View();
        }
        return $this->_view;
    }
    public function setView(View $view)
    {
        $this->_view = $view;
        return true;
    }
    public function resetView()
    {
        $this->_view = null;
        return true;
    }
    protected function _getRequest()
    {
        return Request::getInstance();
    }
    protected function _redirect($url)
    {
        if (!self::$_haltRedirects) {
            $response = Response::getInstance();
            $response->setHeader('location', $url);
            $response->send();
        }
    }
}