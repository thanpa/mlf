<?php
class Request
{
    public $controller;
    public $action;
    public $url;
    public $post;
    public $get;
    public $files;
    public $pass;
    private static $_instance;
    public static function getInstance()
    {
        if (!(self::$_instance instanceof Request)) {
            self::$_instance = new Request();
        }
        return self::$_instance;
    }
    public function load($post, $get, $files, $argv)
    {
        if (!empty($get['url'])) {
            $this->url = $get['url'];
            unset($get['url']);
        } else if (!empty($argv[1])) {
            $this->url = $argv[1];
            if (!empty($argv[2])) {
                chdir($argv[2]);
            }
        } else {
            $this->url = 'default/index';
        }
        $this->post = $post;
        $this->get = $get;
        $this->files = $files;
        $parts = explode('/', trim($this->url, ' /'));
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
    public function isPost()
    {
        return !empty($this->post);
    }
    public function isGet()
    {
        return !empty($this->get);
    }
    public function isConsole()
    {
        return php_sapi_name() == 'cli';
    }
    public function hasFiles()
    {
        return !empty($this->files);
    }
}