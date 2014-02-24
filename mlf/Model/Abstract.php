<?php
/**
 * Abstraction of the models.
 *
 * @author Thanasis <hello@thanpa.com>
 */
class Model_Abstract
{
    /**
     * Associative array for assigning the lazy loading data.
     *
     * @var array
     */
    private $_data = array();
    /**
     * Overloads the needed entity.
     *
     * @param string $name
     * @return mixed The entity requested.
     */
    public function __get($name)
    {
        if (!array_key_exists($name, $this->_data)) {
            $function = sprintf('_get%s', ucfirst($name));
            if (method_exists($this, $function)) {
                $this->_data[$name] = $this->$function();
            } else {
                $this->_data[$name] = null;
                trigger_error(
                    sprintf('Requested unset property %s in class %s, return null instead.', $name, get_class($this))
                );
            }
        }
        return $this->_data[$name];
    }
    /**
     * Sets magically the needed properties.
     *
     * @param string $name
     * @param mixed $value
     * @return null
     */
    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }
    /**
     * Checks if the variable is overloaded.
     *
     * @param string $name
     * @return boolean If the variable is set.
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->_data) || method_exists($this, sprintf('_get%s', ucfirst($name)));
    }
    /**
     * Unsets the value of the overloaded property.
     *
     * @param string $name
     * @return null
     */
    public function __unset($name)
    {
        unset($this->_data[$name]);
    }
    /**
     * Generates an entity with data from the database.
     *
     * @param stdClass $data The data from the DB.
     * @param mixed $instance The instance to setup the data into.
     * @return mixed The instance.
     */
    protected static function gen(stdClass $data, $instance)
    {
        foreach ($data as $field => $value){
            if (substr($field, -4) == 'Date' || $field == 'date') {
                $instance->$field = new Date($value);
            } elseif (substr($field, -6) == 'Amount' || $field == 'amount') {
                $instance->$field = new Mu($value, true);
            } else {
                $instance->$field = $value;
            }
        }
        return $instance;
    }
    /**
     * Generates a collection of entities using data from the database.
     *
     * @param stdClass $stdData  The data from the DB.
     * @param type $className The class name of the instance to setup the data into.
     * @return mixed The instance.
     */
    protected static function genCollection(stdClass $stdData, $className)
    {
        $count = count($stdData);
        $collection = array();
        if ($count > 1) {
            $collection = array();
            foreach ($stdData as $data) {
                $collection[] = self::gen($data, new $className());
            }
        } else if ($count == 1) {
            $collection = array(self::gen($stdData, new $className()));
        }
        return $collection;
    }
}