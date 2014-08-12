<?php
/**
 * Config class.
 *
 * @author Thanasis <hello@thanpa.com>
 */
class Config
{
    /**
     * List of locations that the application will look for the config file.
     *
     * @var array
     */
    private $_locations = array(
        'application',
        'mlf',
    );
    /**
     * Holds the information from the ini file.
     *
     * @var array
     */
    private $_ini = array();
    /**
     * The instances of Config.
     *
     * @var Config
     */
    private static $_instances = array();
    /**
     * Constructs the config.
     *
     * @param string $filename
     */
    public function __construct($filename, $environment = 'development')
    {
        $path = '';
        foreach ($this->_locations as $location) {
            $test = sprintf('../%s/Config/%s.ini', $location, $filename);
            if (file_exists($test) && is_readable($test)) {
                $path = $test;
                break;
            }
        }
        if (!empty($path)) {
            $allEnvironments = $this->_parseIniFileExtended($path, true);
            $this->_ini = $allEnvironments[$environment];
        }
    }
    /**
     * Returns a config value by key.
     *
     * @param string $key
     */
    public function get($key)
    {
        if (!array_key_exists($key, $this->_ini)) {
            throw new Exception('There is no such key in the config file');
        }
        return $this->_ini[$key];
    }
    /**
     * Parses INI file adding extends functionality via ":base" postfix on namespace.
     *
     * @param string $filename
     * @return array
     */
    private function _parseIniFileExtended($filename) {
        $ini = parse_ini_file($filename, true);
        $config = array();
        foreach($ini as $namespace => $properties){
            list($name, $extends) = explode(':', $namespace);
            if (!isset($config[$name])) {
                $config[$name] = array();
            }
            if (isset($ini[$extends])){
                foreach($ini[$extends] as $prop => $val) {
                    $config[$name][$prop] = $val;
                }
            }
            foreach($properties as $prop => $val) {
                $config[$name][$prop] = $val;
            }
        }
        return $config;
    }
    /**
     * Returns the instances of the Config.
     *
     * @return Config
     */
    public static function getInstance($name)
    {
        if (empty(self::$_instances[$name]) || !(self::$_instances[$name] instanceof Config)) {
            self::$_instances[$name] = new Config($name);
        }
        return self::$_instances[$name];
    }
}