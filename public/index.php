<?php
session_start();
date_default_timezone_set('Europe/Athens');
setlocale(LC_ALL, 'el_GR');
if (!defined('APP_PATH')) {
    define('APP_PATH', sprintf('%s/application', dirname(getcwd())));
}
if (!defined('MLF_PATH')) {
    define('MLF_PATH', sprintf('%s/mlf', dirname(getcwd())));
}
if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', sprintf('%s/public', dirname(getcwd())));
}
if (!defined('JS_PATH')) {
    define('JS_PATH', sprintf('%s/public/js', dirname(getcwd())));
}
if (!defined('CSS_PATH')) {
    define('CSS_PATH', sprintf('%s/public/css', dirname(getcwd())));
}
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(getcwd()));
}
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', true);
}
function debug($var)
{
    if (DEBUG_MODE) {
        $type = gettype($var);
        if ($type == 'boolean') {
            $value = var_export($var, true);
        } else {
            $value = print_r($var, true);
        }
        echo sprintf("<pre>%s: %s</pre>", $type, $value);
    }
}
spl_autoload_register(
    function ($className) {
        $found = false;
        $paths = array(
            ROOT_PATH,
            MLF_PATH,
            APP_PATH,
        );
        $parts = explode('_', $className);
        foreach ($paths as $path) {
            $path = sprintf('%s/%s.php', $path, implode('/', $parts));
            if (is_readable($path)) {
                require_once $path;
                $found = true;
            }
        }
        if (!$found) {
            throw new Exception(sprintf("%s was not found in paths '%s'", $className, implode(';', $paths)));
        }
    }
);
$request = Request::getInstance();
$response = Response::getInstance();
try {
    if (!isset($argv)) {
        $argv = array();
    }
    $request->load($_POST, $_GET, $_FILES, $argv);
    $controllerName = $request->controller;
    $actionName = $request->action;
    $controller = new $controllerName();
    if (!method_exists($controller, $actionName)) {
        throw new Exception(sprintf('Can not find action %s in controller %s', $actionName, $controllerName));
    }
    $response->setBody($controller->{$request->action}());
    $response->send();
} catch (Exception $e) {
    $response->setBody(sprintf("<pre>%s\n%s</pre>", $e->getMessage(), $e->getTraceAsString()));
    $response->send();
}
?>
