<?php
/**
 * View class.
 *
 * @author Thanasis <hello@thanpa.com>
 */
class View
{
    /**
     * Holds the setted vars for the current view.
     *
     * @var array
     */
    private $_vars = array();
    /**
     * The name of the view.
     *
     * @var string
     */
    private $_name = '';
    /**
     * The layout to use.
     *
     * @var string
     */
    public $layout = 'default';
    /**
     * The content to print.
     *
     * @var string
     */
    public $content = '';
    /**
     * The title to print.
     *
     * @var string
     */
    public $title = '';
    /**
     * Returns the current HTTP request.
     *
     * @return Request
     */
    public function getRequest()
    {
        return Request::getInstance();
    }
    /**
     * Renders the provided template.
     *
     * @param string $name The name of the view to render.
     * @return string The rendered output.
     * @throws Exception
     */
    public function render($name)
    {
        $this->_name = $name;
        $path = sprintf('%s/View/%s/%s.phtml', APP_PATH, Request::getInstance()->controllerAllias, $this->_name);
        if (!is_readable($path)) {
            throw new Exception(sprintf('Can not read view file %s', $path));
        }
        if (empty($this->layout)) {
            $this->layout = 'default';
        }
        $layoutPath = sprintf('%s/View/Layout/%s.phtml', APP_PATH, $this->layout);
        if (!is_readable($layoutPath)) {
            throw new Exception(sprintf('Can not read view file %s', $layoutPath));
        }
        ob_start();
        foreach ($this->_vars as $varName => $value) {
            $this->$varName = $value;
        }
        include $path;
        $this->content = ob_get_clean();
        ob_start();
        include $layoutPath;
        return ob_get_clean();
    }
    /**
     * Sets variables for the view.
     *
     * @param string $name
     * @param mixed $value
     * @return \View
     */
    public function set($name, $value)
    {
        $this->_vars[$name] = $value;
        return $this;
    }
    /**
     * Renders a block in the view.
     *
     * @param string $name The nane of the block.
     * @return string
     * @throws Exception
     */
    public function block($name)
    {
        $path = sprintf('%s/View/Block/%s.phtml', APP_PATH, $name);
        if (!is_readable($path)) {
            throw new Exception(sprintf('Can not read view file %s', $path));
        }
        ob_start();
        include $path;
        return ob_get_clean();
    }
    /**
     * Returns any magically found scripts for this view.
     *
     * @return string
     * @throws Exception
     */
    public function scripts()
    {
        if (empty($this->_name)) {
            throw new Exception ('View without a name?');
        }
        $scripts = array('js' => array(), 'css' => array());
        if (is_readable(sprintf('%s/View/%s/%s.js', JS_PATH, Request::getInstance()->controllerAllias, $this->_name))) {
            $scripts['js'][] = sprintf('/js/View/%s/%s.js', Request::getInstance()->controllerAllias, $this->_name);
        }
        if (is_readable(sprintf('%s/View/%s/%s.css', CSS_PATH, Request::getInstance()->controllerAllias, $this->_name))) {
            $scripts['css'][] = sprintf('css/View/%s/%s.css', Request::getInstance()->controllerAllias, $this->_name);
        }
        return $scripts;
    }
}
