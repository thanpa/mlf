<?php
/**
 * Request class.
 *
 * @author Thanasis <hello@thanpa.com>
 */
class Request
{
    /**
     * Controller name.
     *
     * @var string
     */
    public $controller;
    /**
     * Controller alias name.
     *
     * @var string
     */
    public $controllerAllias;
    /**
     * Action name.
     *
     * @var string
     */
    public $action;
    /**
     * Current URL.
     *
     * @var string
     */
    public $url;
    /**
     * Post data.
     *
     * @var array
     */
    public $post;
    /**
     * Get data.
     *
     * @var array
     */
    public $get;
    /**
     * Files data.
     *
     * @var array
     */
    public $files;
    /**
     * Cookies data.
     *
     * @var array
     */
    public $cookies;
    /**
     * Pass data.
     *
     * @var array
     */
    public $pass;
    /**
     * Singleton instance.
     *
     * @var Request
     */
    private static $_instance;
    /**
     * Returns the instance of the Request.
     *
     * @return Request
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof Request)) {
            self::$_instance = new Request();
        }
        return self::$_instance;
    }
    /**
     * Loads the Request.
     *
     * @param array $post
     * @param array $get
     * @param array $files
     * @param array $cookies
     * @param array $argv
     * @return null
     */
    public function load(array $post, array $get, array $files, array $cookies, array $argv)
    {
        $this->post = $post;
        $this->get = $get;
        $this->files = $files;
        $this->cookies = $cookies;
        if (Auth::getInstance()->isSingedIn()) {
            if (!empty($get['url'])) {
                $this->url = $get['url'];
                unset($get['url']);
            } else if (!empty($argv[1])) {
                $this->url = $argv[1];
            } else {
                $this->url = Acl::DEFAULT_URL;
            }
            $this->url = trim($this->url, ' /');
            if (!Acl::getInstance()->isAllowed($this->url)) {
                $this->url = Acl::DEFAULT_URL;
            }
        } else {
            $this->url = Auth::SINGIN_URL;
        }
        $parts = explode('/', $this->url);
        $this->controllerAllias = ucfirst($parts[0]);
        $this->controller = sprintf('Controller_%s', $this->controllerAllias);
        unset($parts[0]);
        $this->action = trim(str_replace(' ', '', ucwords(strtolower(str_replace('-', ' ', $parts[1])))));
        $this->action{0} = strtolower($this->action{0});
        unset($parts[1]);
        foreach ($parts as $part) {
            if (strpos($part, ':')) {
                $var = explode(':', $part);
                $this->pass[$var[0]] = $var[1];
            }
        }
    }
    /**
     * Returns if the current Request is post.
     *
     * @return boolean
     */
    public function isPost()
    {
        return !empty($this->post);
    }
    /**
     * Returns if the current Request is get.
     *
     * @return boolean
     */
    public function isGet()
    {
        return !empty($this->get);
    }
    /**
     * Returns if the current Request is run by console.
     *
     * @return boolean
     */
    public function isConsole()
    {
        return php_sapi_name() === 'cli';
    }
    /**
     * Returns if the current Request has uploaded files.
     *
     * @return boolean
     */
    public function hasFiles()
    {
        return !empty($this->files);
    }
}