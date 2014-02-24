<?php
class Table_Abstract
{
    private $_dbName = 'worklog_db';
    protected $_name;
    private static $_instances = array();
    private $_mysqli;
    const TABLE_TRUE = '1';
    const TABLE_FALSE = '0';
    public static function getInstance()
    {
        $class = get_called_class();
        if (!isset(self::$_instances[$class]) || !(self::$_instances[$class] instanceof $class)) {
            self::$_instances[$class] = new $class();
        }
        return self::$_instances[$class];
    }
    public function __construct()
    {
        $this->_mysqli = new mysqli('localhost', 'worklog_user', 'W0rkl0g@@', $this->_dbName);
        if ($this->_mysqli->connect_error) {
            throw new Exception('No db connection');
        }
    }
    public function __destruct()
    {
        $this->_mysqli->close();
    }
    public function query($sql)
    {
        $sql = trim($sql);
        $command = strtoupper(substr($sql, 0, strpos($sql, ' ')));
        if ($command == 'SELECT' || $command == 'DESCRIBE') {
            $resource = $this->_mysqli->query($sql);
            $result = array();
            while ($object = $resource->fetch_object()) {
                $result[] = $object;
            }
            if (count($result) == 1) {
                $result = current($result);
            }
        } else if ($command == 'INSERT') {
            if ($this->_mysqli->query($sql)) {
                $result = $this->_mysqli->insert_id;
            } else {
                $result = false;
            }
        } else {
            $result = $this->_mysqli->query($sql);
        }
        return $result;
    }
    public function select($where = array(), $fields = '*', $order = '', $limit = 0)
    {
        if (empty($this->_name)) {
            throw new Exception('No table name set');
        }
        $sql = sprintf(
            'SELECT %s FROM %s %s %s %s',
            $fields,
            $this->_name,
            count($where) ? sprintf('WHERE %s ', implode('AND ', $this->_getWhereFields($where))) : '',
            (empty($order)) ? '' : sprintf('ORDER BY %s ', $order),
            ($limit === 0) ? '' : sprintf('LIMIT %d ', $limit)
        );
        return $this->query($sql);
    }
    public function insert($data)
    {
        if (empty($data)) {
            throw new Exception('What are you trying to insert?');
        }
        if (empty($this->_name)) {
            throw new Exception('No table name set');
        }
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->_name,
            implode(', ', $this->_getFields($data)),
            implode(', ', $this->_getValues($data))
        );
        return $this->query($sql);
    }
    public function update($data, $where)
    {
        if (empty($data)) {
            throw new Exception('What are you trying to insert?');
        }
        if (empty($this->_name)) {
            throw new Exception('No table name set');
        }
        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $this->_name,
            implode(', ', $this->_getUpdateFields($data)),
            implode('AND ', $this->_getWhereFields($where))
        );
        return $this->query($sql);
    }
    public function delete($where)
    {
        if (empty($this->_name)) {
            throw new Exception('No table name set');
        }
        $sql = sprintf(
            'DELETE FROM %s WHERE %s',
            $this->_name,
            implode('AND ', $this->_getWhereFields($where))
        );
        return $this->query($sql);
    }
    public function describe()
    {
        if (empty($this->_name)) {
            throw new Exception('No table name set');
        }
        $sql = sprintf('DESCRIBE %s', $this->_name);
        return $this->query($sql);
    }
    public function getFields()
    {
        $description = $this->describe();
        $fields = array();
        foreach ($description as $item) {
            $fields[] = $item->Field;
        }
        return $fields;
    }
    public function escape($unescaped)
    {
        return $this->_mysqli->real_escape_string($unescaped);
    }
    private function _getWhereFields($data)
    {
        array_walk(
            $data,
            function (&$item, $field, $obj)
            {
                $field = trim($field);
                if (strpos($field, ' ') !== false) {
                    $parts = explode(' ', $field);
                    $field = $parts[0];
                    $cmp = $parts[1];
                } else {
                    $cmp = '=';
                }
                switch (gettype($item)) {
                    case 'integer':
                        $item = sprintf("`%s` %s %d", $field, $cmp, $item);
                        break;
                    case 'double':
                    case 'float':
                        $item = sprintf("`%s` %s %F", $field, $cmp, $item);
                        break;
                    case 'boolean':
                        $item = sprintf("`%s` %s %d", $field, $cmp, $obj->escape((int)$item));
                        break;
                    default:
                        $item = sprintf("`%s` %s '%s'", $field, $cmp, $obj->escape($item));
                }
            },
            $this
        );
        return $data;
    }
    private function _getUpdateFields($data)
    {
        array_walk(
            $data,
            function (&$item, $field, $obj)
            {
                switch (gettype($item)) {
                    case 'integer':
                        $item = sprintf("`%s` = %d", $field, $item);
                        break;
                    case 'double':
                    case 'float':
                        $item = sprintf("`%s` = %F", $field, $item);
                        break;
                    case 'boolean':
                        $item = sprintf("`%s` = %d", $field, $obj->escape((int)$item));
                        break;
                    default:
                        $item = sprintf("`%s` = '%s'", $field, $obj->escape($item));
                }
            },
            $this
        );
        return $data;
    }
    private function _getFields($data)
    {
        $fields = array_keys($data);
        array_walk(
            $fields,
            function (&$item)
            {
                $item = sprintf('`%s`', $item);
            }
        );
        return $fields;
    }
    private function _getValues($data)
    {
        $values = array_values($data);
        array_walk(
            $values,
            function (&$item, $field, $obj)
            {
                switch (gettype($item)) {
                    case 'integer':
                    case 'float':
                        break;
                    case 'boolean':
                        $item = (int)$item;
                    default:
                        $item = sprintf("'%s'", $obj->escape($item));
                }
            },
            $this
        );
        return $values;
    }
}