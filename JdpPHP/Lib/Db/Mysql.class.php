<?php
// +----------------------------------------------------------------------
// | Mysql
// +----------------------------------------------------------------------
// | Author: alice <wky0218@hotmail.com>
// +----------------------------------------------------------------------
class Mysql
{
    private static $object;
    private $PDO;
    private $prepare;
    private $stmt = null;
    private $table_name;
    private $table_prefix;
    private $options = array();
    private $sql;

    /**
     * __construct
     * @access private
     * @param  array  $config
     */
    private function __construct()
    {
    }

    /**
     * getInstance
     * @access public
     * @param  array  $config
     * @return mixed
     */
    public static function getInstance()
    {
        if (!(self::$object instanceof self)) {
            self::$object = new self;
        }
        return self::$object;
    }

    /**
     * __clone
     * @access public
     * @param  array  $config
     * @return mixed
     */
    private function __clone()
    {
        trigger_error('Clone is not allow!', E_USER_ERROR);
    }

    /**
     * connect
     * @access public
     * @param  array  $config
     * @return mixed
     */
    public function connect($config)
    {
        $type = $config['type'];
        $host = $config['host'];
        $dbname = $config['dbname'];
        $user = $config['user'];
        $password = $config['password'];
        $charset = $config['charset'] ? $config['charset'] : 'utf8';
        $this->table_prefix = isset($config['prefix']) ? $config['prefix'] : '';
        try {
            $this->PDO = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $password);
            $this->PDO->setAttribute(PDO::ATTR_PERSISTENT, true);
            //设置为警告模式
            $this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            //设置抛出错误
            $this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //设置当字符串为空时转换为sql的null
            $this->PDO->setAttribute(PDO::ATTR_ORACLE_NULLS, true);
            //由MySQL完成变量的转义处理
            $this->PDO->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            $this->Msg("PDO连接错误信息：" . $e->getMessage());
        }
        return $this;
    }

    /**
     *Msg
     *@param string $error
     *@return output
     */
    private function Msg($error = "")
    {
        $html = "<html>
                  <head>
                    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'/>
                    <title>mysql error</title>
                  </head>
                  <body>
                    <div style='width: 50%; height: 200px; margin:0 auto;padding:5px;border: 1px solid red; font-size: 12px;'>
                        <div>sql:<br>$this->sql;</div>
                       <div>errorMsg:<br>$error</div>
                    </div>
                  </body>
               </html>
               ";
        echo $html;
        exit;
    }

    /**
     *table
     *@param string $table_name
     *@param string $table_prefix
     *@return obj
     */
    public function table($table_name = '', $table_prefix = '')
    {
        if ('' != $table_prefix) {
            $this->table_prefix = $table_prefix;
        }
        $this->table_name = $this->table_prefix . $table_name;
        return $this;
    }

    /**
     *insert
     *@param string||array $param1
     *@param array $param2
     *@return bool
     */
    public function insert($param1 = null, $param2 = array())
    {
        if (is_string($param1) && is_array($param2)) {
            $sql = $param1;
            $this->stmt = $this->prepareSql($sql);
            if (!empty($param2)) {
                foreach ($param2 as $k => &$v) {
                    $this->stmt->bindParam($k + 1, $v);
                }
            }
        } elseif (is_array($param1)) {
            $param1 = array_filter($param1);
            foreach ($param1 as $key => $val) {
                $names[] = "`{$key}`";
                $values[] = ":{$key}";
            }
            $name = join(',', $names);
            $value = join(',', $values);
            $sql = 'INSERT INTO `' . $this->table_name . '`(' . $name . ') VALUES(' . $value . ')';
            $this->stmt = $this->prepareSql($sql);
            foreach ($param1 as $k => &$v) {
                $this->stmt->bindParam(':' . $k, $v);
            }
        }
        $result = $this->sqlExecute();
        return $this->PDO->lastinsertid();
    }

    /**
     * insertAll
     * @param array  $data
     * @param int    $rows
     * @param string $table
     */
    public function insertAll($data, $rows = 100, $table = null)
    {
        $table = is_null($table) ? $this->table_name : $table;
        foreach ((array) $data as $k => $v) {
            $fields = array_keys($v);
            break;
        }
        $field = join(',', $fields);
        $sql = 'INSERT INTO `' . $table . '`(' . $field . ') VALUES';
        $insertData = array();
        $insertNum = 0;
        $i = 0;
        foreach ($data as $k => $v) {
            $oneRec_arr = array();
            foreach ($v as $k2 => $v2) {
                $oneRec_arr[] = '?';
                $insertData[] = $v2;
            }
            $oneRec_str = join(',', $oneRec_arr);
            $sql .= '(' . $oneRec_str . '),';
            $t = ($i + 1) % $rows;
            if ($t == 0 || ($i + 1) == count($data)) {
                $sql = rtrim($sql, ',') . ';';
                $this->stmt = $this->prepareSql($sql);
                foreach ($insertData as $bk => &$value) {
                    $this->stmt->bindParam($bk + 1, $value);
                }
                $res = $this->sqlExecute();
                $rowCount = $this->stmt->rowCount();
                $insertNum += $rowCount;
                //reset
                $sql = 'INSERT INTO `' . $table . '`(' . $field . ') VALUES';
                $insertData = array();
            }
            $i++;
        }
        return $insertNum;
    }

    /**
     *update
     *@param string||array $param1
     *@param array $param2
     *@return bool
     */
    public function update($param1 = null, $param2 = array())
    {
        if (is_string($param1) && is_array($param2)) {
            $sql = $param1;
            $this->stmt = $this->prepareSql($sql);
            if (!empty($param2)) {
                foreach ($param2 as $k => &$v) {
                    $this->stmt->bindParam($k + 1, $v);
                }
            }
        } elseif (is_array($param1)) {
            $rowSql = array();
            foreach ($param1 as $key => $val) {
                $rowSql[] = " `{$key}` = ?";
            }
            $rowSql = implode(',', $rowSql);
            //where
            $where = $this->parseWhere();
            //whereIn
            $whereIn = $this->parseWhereIn();
            $iswhere = ($where['condition'] || $whereIn['condition']) ? ' WHERE ' : ' WHERE 0 ';
            $where_and_in = ($where['condition'] && $whereIn['condition']) ? ' and ' : '';
            $sql = ' UPDATE ' . $this->table_name . ' SET ' . $rowSql . $iswhere . $where['condition'] . $where_and_in . $whereIn['condition'];
            $this->stmt = $this->prepareSql($sql);
            $row_values = array_values($param1);
            $binValues = array_merge($row_values, $where['value'], $whereIn['value']);
            foreach ($binValues as $k => &$v) {
                $this->stmt->bindParam($k + 1, $v);
            }
        }
        $result = $this->sqlExecute();
        return $this->stmt->rowCount();
    }

    /**
     *delete
     *@param string $sql
     *@return bool
     */
    public function delete($param1 = null, $param2 = array())
    {
        if (is_string($param1) && is_array($param2)) {
            $sql = $param1;
            $this->stmt = $this->prepareSql($sql);
            if (!empty($param2)) {
                foreach ($param2 as $k => &$v) {
                    $this->stmt->bindParam($k + 1, $v);
                }
            }
        } else {
            //where
            $where = $this->parseWhere();
            //whereIn
            $whereIn = $this->parseWhereIn();
            $iswhere = ($where['condition'] || $whereIn['condition']) ? ' WHERE ' : ' WHERE 0 ';
            $where_and_in = ($where['condition'] && $whereIn['condition']) ? ' and ' : '';

            $sql = 'DELETE FROM ' . $this->table_name . $iswhere . $where['condition'] . $where_and_in . $whereIn['condition'];

            $this->stmt = $this->prepareSql($sql);
            $binValues = array_merge($where['value'], $whereIn['value']);
            foreach ($binValues as $k => &$v) {
                $this->stmt->bindParam($k + 1, $v);

            }
        }
        $result = $this->sqlExecute();
        return $this->stmt->rowCount();
    }

    /**
     *select
     *@param string $param1
     *@param array $param2
     *@param bool $all
     *@return mixed
     */
    public function select($param1 = null, $param2 = array(), $all = true)
    {
        if (is_string($param1) && is_array($param2)) {
            $sql = $param1;
            $this->stmt = $this->prepareSql($sql);
            if (!empty($param2)) {
                foreach ($param2 as $k => &$v) {
                    $this->stmt->bindParam($k + 1, $v);
                }
            }
        } else {
            //columns
            if (isset($this->options['fields'])) {
                $fields = array();
                foreach ($this->options['fields'] as $k => $v) {
                    if ($v[0]) {
                        $fields[] = $v[0];
                    }
                }
                $fields_str = $fields ? implode(',', $fields) : '*';
            } else {
                $fields_str = '*';
            }
            //where
            $where = $this->parseWhere();
            //whereIn
            $whereIn = $this->parseWhereIn();
            //gryop by
            $group_by = '';
            if (isset($this->options['groupBy'])) {
                $groupBy_condition = array();
                foreach ($this->options['groupBy'] as $k => $v) {
                    $groupBy_condition[] = $v[0];
                }
                $group_by = ' GROUP BY ' . implode(',', $groupBy_condition);
            }
            //having
            $having = '';
            $having_condition = array();
            $having_values = array();
            if (isset($this->options['having'])) {
                foreach ($this->options['having'] as $k => $v) {
                    $having_condition[] = $v[0];
                    foreach ((array) $v[1] as $k2 => $v2) {
                        $having_values[] = $v2;
                    }
                }
                $having = ' having ' . implode(' and ', $having_condition);
            }
            //order by conditions
            $orderBy_str = '';
            if (isset($this->options['orderBy'])) {
                $orderBy_condition = array();
                foreach ($this->options['orderBy'] as $k => $v) {
                    if ($v[0]) {
                        $orderBy_condition[] = $v[0];
                    }
                }
                if ($orderBy_condition) {
                    $orderBy_str = ' order by ' . implode(',', $orderBy_condition);
                }
            }
            //limit conditions
            $limit = '';
            if (isset($this->options['limit'][0][0])) {
                $limit = ' limit ' . $this->options['limit'][0][0];
            }
            if($all === false){
                $limit = ' limit 1' ;
            }

            $iswhere = ($where['condition'] || $whereIn['condition']) ? ' WHERE ' : '';
            $where_and_in = ($where['condition'] && $whereIn['condition']) ? ' and ' : '';

            $sql = 'SELECT ' . $fields_str . ' FROM ' . $this->table_name . $iswhere . $where['condition'] . $where_and_in . $whereIn['condition'] . $group_by . $having . $orderBy_str . $limit;
            $this->stmt = $this->prepareSql($sql);
            $binValues = array_merge($where['value'], $whereIn['value'], $having_values);
            foreach ($binValues as $k => &$v) {
                $this->stmt->bindParam($k + 1, $v);
            }
        }
        $result = $this->sqlExecute();
        $this->stmt->setFetchMode(PDO::FETCH_ASSOC);
        $row = $all === true ? $this->stmt->fetchAll() : $this->stmt->fetch();
        return $row;
    }

    /**
     *find
     *@param string $param1
     *@param array $param2
     *@return mixed
     */
    public function find($param1 = null, $param2 = array())
    {
        return $this->select($param1, $param2, false);
    }

    /**
     *count
     *@param string $param1
     *@param array $param2
     *@return mixed
     */
    public function count($param1 = null, $param2 = array())
    {
        if (is_string($param1) && is_array($param2)) {
            $sql = $param1;
            $this->stmt = $this->prepareSql($sql);
            if (!empty($param2)) {
                foreach ($param2 as $k => &$v) {
                    $this->stmt->bindParam($k + 1, $v);
                }
            }
        } else {

            //column
            $column_name = isset($this->options['fields'][0][0]) ? $this->options['fields'][0][0] : '*';
            //where
            $where = $this->parseWhere();
            //whereIn
            $whereIn = $this->parseWhereIn();
            $iswhere = ($where['condition'] || $whereIn['condition']) ? ' WHERE ' : '';
            $where_and_in = ($where['condition'] && $whereIn['condition']) ? ' and ' : '';
            $sql = 'SELECT count(' . $column_name . ') FROM `' . $this->table_name . '`' . $iswhere . $where['condition'] . $where_and_in . $whereIn['condition'];

            $this->stmt = $this->prepareSql($sql);

            $binValues = array_merge($where['value'], $whereIn['value']);

            foreach ($binValues as $k => &$v) {
                $this->stmt->bindParam($k + 1, $v);
            }

        }
        $this->sqlExecute();

        $rows = $this->stmt->fetch();
        $total = $rows[0];
        return $total;
    }

    /**
     *increment
     *@param array $column
     *@param array $count
     *@return mixed
     */
    public function increment($column = array(), $count = array())
    {

        $rowSql = array();
        foreach ($column as $key => $value) {
            $n = isset($count[$key]) ? $count[$key] : 1;
            $rowSql[] = '`' . $value . '` = `' . $value . '`+' . $n;
        }
        $rowSql = implode(',', $rowSql);
        $where = $this->parseWhere();
        $whereIn = $this->parseWhereIn();
        $iswhere = ($where['condition'] || $whereIn['condition']) ? ' WHERE ' : '';
        $where_and_in = ($where['condition'] && $whereIn['condition']) ? ' and ' : '';
        $sql = ' UPDATE ' . $this->table_name . ' SET ' . $rowSql . $iswhere . $where['condition'] . $where_and_in . $whereIn['condition'];
        $this->stmt = $this->prepareSql($sql);
        $binValues = array_merge($where['value'], $whereIn['value']);
        foreach ($binValues as $k => &$v) {
            $this->stmt->bindParam($k + 1, $v);
        }

        $result = $this->sqlExecute();
        return $this->stmt->rowCount();

    }

    /**
     *decrement
     *@param string $sql
     *@return array
     */
    public function decrement($column = array(), $count = array())
    {
        $rowSql = array();
        foreach ($column as $key => $value) {
            $n = isset($count[$key]) ? $count[$key] : 1;
            $rowSql[] = '`' . $value . '` = `' . $value . '`-' . $n;
        }
        $rowSql = implode(',', $rowSql);
        $where = $this->parseWhere();
        $whereIn = $this->parseWhereIn();
        $iswhere = ($where['condition'] || $whereIn['condition']) ? ' WHERE ' : '';
        $where_and_in = ($where['condition'] && $whereIn['condition']) ? ' and ' : '';
        $sql = ' UPDATE ' . $this->table_name . ' SET ' . $rowSql . $iswhere . $where['condition'] . $where_and_in . $whereIn['condition'];

        $this->stmt = $this->prepareSql($sql);
        $binValues = array_merge($where['value'], $whereIn['value']);
        foreach ($binValues as $k => &$v) {
            $this->stmt->bindParam($k + 1, $v);
        }
        $result = $this->sqlExecute();
        return $this->stmt->rowCount();
    }

    /**
     *parseWhere
     *@param string $sql
     *@return array
     */
    private function parseWhere()
    {
        $where = array('condition' => '', 'value' => array());
        if (isset($this->options['where'])) {
            $condition = array();
            $values = array();
            foreach ($this->options['where'] as $v) {
                if (!empty($v[0])) {
                    $condition[] = $v[0];
                    foreach ((array) $v[1] as $v2) {
                        $values[] = $v2;
                    }
                }
            }

            if ($condition) {
                $where['condition'] = implode(' and ', $condition);
            }

            $where['value'] = $values;
        }
        return $where;
    }

    /**
     *parseWhereIn
     *@param string $sql
     *@return array
     */
    private function parseWhereIn()
    {
        $whereIn = array('condition' => '', 'value' => array());

        if (isset($this->options['whereIn'])) {
            $condition = array();
            $values = array();
            foreach ($this->options['whereIn'] as $v) {
                if ($v[1] && is_array($v[1])) {
                    $c = count($v[1]);
                    $make_arr = array_fill(0, $c, '?');
                    $condition[] = $v[0] . ' IN (' . implode(',', $make_arr) . ')';
                    foreach ($v[1] as $v2) {
                        $values[] = $v2;
                    }
                }
            }

            if ($condition) {
                $whereIn['condition'] = implode(' and ', $condition);
            }

            $whereIn['value'] = $values;
        }

        return $whereIn;
    }

    /**
     *prepareSql
     *@param string $sql
     *@return statement
     */
    private function prepareSql($sql)
    {
        $this->sql = $sql;
        try {
            $this->stmt = $this->PDO->prepare($sql);
            unset($this->options);
        } catch (PDOException $e) {
            $this->Msg($e->getMessage());
        }
        return $this->stmt;
    }

    /**
     *sqlExecute
     *@param string $sql
     *@return statement
     */
    private function sqlExecute()
    {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            $this->Msg($e->getMessage());
        }
    }

    /**
     * __call
     * @access public
     * @param  string $func
     * @param  array  $args
     * @return mixed
     */
    public function __call($func, $args)
    {
        if (in_array($func, array('fields', 'as', 'join', 'where', 'whereIn', 'orderBy', 'groupBy', 'limit', 'having'))) {
            $this->options[$func][] = $args;
            return $this;
        }
        exit('Call to undefined method :' . $func . '()' . ' in  "' . __FILE__ . '"');
    }

    /**
     * getQueryLog
     * @access public
     * @return mixed
     */
    public function getQueryLog()
    {
        return $this->sql;
    }

    /**
     * query
     * @access public
     * @param  string $sql
     * @return mixed
     */
    public function query($sql)
    {
        return $this->PDO->query($sql);
    }

    /**
     * query
     * @access public
     * @param  string $sql
     * @return mixed
     */
    public function exec($sql)
    {
        return $this->PDO->exec($sql);
    }

    /**
     * quote
     * @param  string $str
     * @return string
     */
    public function quote($str)
    {
        return $this->PDO->quote($str);
    }

    /**
     * beginTransaction
     * @return mixed
     */
    public function beginTransaction()
    {
        return $this->PDO->beginTransaction();
    }

    /**
     * rollback
     * @return mixed
     */
    public function rollback()
    {
        return $this->PDO->rollback();
    }

    /**
     * commit
     * @return mixed
     */
    public function commit()
    {
        return $this->PDO->commit();
    }

}
