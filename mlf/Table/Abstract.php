<?php
/**
 * Abstraction of the tables.
 *
 * @author Thanasis <hello@thanpa.com>
 */
class Table_Abstract
{
    /**
     * The name of the current table.
     *
     * @var string
     */
    protected $_name;
    /**
     * Singleton instances.
     *
     * @var array
     */
    private static $_instances = array();
    /**
     * The instance of mysqli.
     *
     * @var mysqli
     */
    private $_mysqli;
    /**
     * Constant for table true value.
     */
    const TABLE_TRUE = '1';
    /**
     * Constant for table false value.
     */
    const TABLE_FALSE = '0';
    /**
     * Returns the instance of the current table.
     *
     * @return mixed
     */
    public static function getInstance()
    {
        $class = get_called_class();
        if (!isset(self::$_instances[$class]) || !(self::$_instances[$class] instanceof $class)) {
            self::$_instances[$class] = new $class();
        }
        return self::$_instances[$class];
    }
    /**
     * Constructs the current table.
     *
     * @return null
     * @throws Exception In case of a DB connection error.
     */
    public function __construct()
    {
        $config = Config::getInstance('database', 'development');
        $this->_mysqli = new mysqli(
            $config->get('host'),
            $config->get('user'),
            $config->get('password'),
            $config->get('name')
        );
        if ($this->_mysqli->connect_error) {
            throw new Exception('No db connection');
        }
        $this->_mysqli->query('SET NAMES UTF8');
    }
    /**
     * Destructs the current table.
     *
     * @return null
     */
    public function __destruct()
    {
        $this->_mysqli->close();
    }
    /**
     * Queries the DB with an SQL command.
     *
     * @param string $sql
     * @return mixed Depending the query: array for multi row select, stdClass for single row select, int for update.
     */
    public function query($sql)
    {
        $sql = trim($sql);
        $command = strtoupper(substr($sql, 0, strpos($sql, ' ')));
        if ($command == 'SELECT' || $command == 'DESCRIBE') {
            $resource = $this->_mysqli->query($sql);
            if ($resource === false) {
                throw new Exception("There is an error with the query:\n{$sql}");
            }
            $result = array();
            while ($object = $resource->fetch_object()) {
                $result[] = $object;
            }
            if (count($result) === 1) {
                $result = current($result);
            }
            if (count($result) === 0) {
                $result = null;
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
    /**
     * Creates a select for the DB.
     *
     * @param array|null $where
     * @param string $fields
     * @param string $order
     * @param integer $limit
     * @return The result from the database.
     * @throws Exception In case there is no table name set.
     */
    public function select($where = null, $fields = '*', $order = '', $limit = 0, $group = '')
    {
        if (empty($this->_name)) {
            throw new Exception('No table name set');
        }
        if ($where === null) {
            $where = '1 = 1';
        }
        $sql = sprintf(
            'SELECT %s FROM %s %s %s %s %s',
            $fields,
            $this->_name,
            sprintf('WHERE %s ', is_array($where) ? implode(' AND ', $this->_getWhereFields($where)) : $where),
            (empty($group)) ? '' : sprintf('GROUP BY %s ', $group),
            (empty($order)) ? '' : sprintf('ORDER BY %s ', $order),
            ($limit === 0) ? '' : sprintf('LIMIT %d ', $limit)
        );
        return $this->query($sql);
    }
    /**
     * Inserts data in the datavase.
     *
     * @param array $data The data to insert.
     * @return The result from the database.
     * @throws Exception In case there is no table name set.
     */
    public function insert(array $data)
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
    /**
     * Updates data in the datavase.
     *
     * @param array $data The data to update.
     * @param array $where Where to update.
     * @return The result from the database.
     * @throws Exception In case there is no data.
     * @throws Exception In case there is no table name set.
     */
    public function update(array $data, array $where)
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
    /**
     * Deletes data from the datavase.
     *
     * @param array $where Where to delete.
     * @return The result from the database.
     * @throws Exception In case there is no table name set.
     */
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
    /**
     * Describes the current database table.
     *
     * @return The result from the database.
     * @throws Exception In case there is no table name set.
     */
    public function describe()
    {
        if (empty($this->_name)) {
            throw new Exception('No table name set');
        }
        $sql = sprintf('DESCRIBE %s', $this->_name);
        return $this->query($sql);
    }
    /**
     * Returns the fields of the current database table.
     *
     * @return array The fields.
     */
    public function getFields()
    {
        $description = $this->describe();
        $fields = array();
        foreach ($description as $item) {
            $fields[] = $item->Field;
        }
        return $fields;
    }
    /**
     * Escapes the data before puting them in the query.
     *
     * @param mixed $unescaped
     * @return string The escaped data.
     */
    public function escape($unescaped)
    {
        return $this->_mysqli->real_escape_string($unescaped);
    }
    /**
     * Returns the SQL like where statement.
     *
     * @param array $data The where data.
     * @return string
     */
    private function _getWhereFields(array $data)
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
    /**
     * Returns the SQL like update statement.
     *
     * @param array $data The update data.
     * @return string
     */
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
    /**
     * Returns the SQL like data field names.
     *
     * @param array $data The data.
     * @return string
     */
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
    /**
     * Returns the SQL like data values.
     *
     * @param array $data The data.
     * @return string
     */
    private function _getValues($data)
    {
        $values = array_values($data);
        array_walk(
            $values,
            function (&$item, $field, $obj)
            {
                unset($field);
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