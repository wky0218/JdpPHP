<?php
class Mysql {
    private static $object;
    private $PDO;
    private $prepare;
    private $stmt = null;
    private $table_name;
    private $table_prefix;
    private $options = array();
    private $sql;

    private function __construct($config = array()) {
    }
    public static function getInstance() {
        if (!(self::$object instanceof self)) {
            self::$object = new self;
        }
        return self::$object;
    }
    private function __clone() {
        trigger_error('Clone is not allow!', E_USER_ERROR);
    }

    
    /**
     * connect
     * @access public
     * @param  array  $config
     * @return mixed
     */
    public function connect($config) {
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
        }
        catch(PDOException $e) {
            $this->Msg("PDO连接错误信息：" . $e->getMessage());
        }
        return $this;
    }
    /**
     *Msg
     *@param string $error
     *@return output
     */
    private function Msg($error = "") {
        $html = "<html>
                  <head>
                    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'/>
                    <title>mysql error</title>
                  </head>
                  <body>
                    <div style='width: 50%; height: 200px; border: 1px solid red; font-size: 12px;'>
                        <div>SQL>>$this->sql;</div>
                       <div>ERROR>>$error</div>
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
    public function table($table_name = '', $table_prefix = '') {
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
    public function insert($param1 = null, $param2 = array()) {
        if (is_string($param1) && is_array($param2)) {
            $sql = $param1;
            $this->stmt = $this->prepareSql($sql);
            if (!empty($param2)) {
                foreach ($param2 as $k => & $v) {
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
            $sql = 'INSERT INTO `'.$this->table_name.'`('.$name.') VALUES('.$value.')';
            $this->stmt = $this->prepareSql($sql);
            foreach ($param1 as $k => & $v) {
                $this->stmt->bindParam(':'.$k, $v);
            }
        }
        $result = $this->sqlExecute();
        return $this->PDO->lastinsertid();
    }
    /**
     * 批量插入
     * @param array  $data 要插入的数据, 格式:array(array(), array());
     * @param int    $rows 一次插入多少条记录
     * @param string $table 表名
     */
    public function insertAll($data, $rows = 100, $table = null) {
        $table = is_null($table) ? $this->table_name : $table;
        foreach ((array)$data as $k => $v) {
            $fields = array_keys($v);
            break;
        }
        $field = join(',', $fields);
        $sql = 'INSERT INTO `'.$table.'`('.$field.') VALUES';
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
            $sql.= '(' . $oneRec_str . '),';
            $t = ($i + 1) % $rows;
            if ($t == 0 || ($i + 1) == count($data)) {
                //将最后的逗号替换成分号
                $sql = rtrim($sql, ',') . ';';
                $this->stmt = $this->prepareSql($sql);
                foreach ($insertData as $bk => & $value) {
                    $this->stmt->bindParam($bk + 1, $value);
                }
                //插入数据库
                $res = $this->sqlExecute();
                $rowCount = $this->stmt->rowCount();
                $insertNum+= $rowCount;
                //重置 字符串 $sql
                $sql = 'INSERT INTO `'.$table.'`('.$field.') VALUES';
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
    public function update($param1 = null, $param2 = array()) {
        if (is_string($param1) && is_array($param2)) {
            $sql = $param1;
            $this->stmt = $this->prepareSql($sql);
            if (!empty($param2)) {
                foreach ($param2 as $k => & $v) {
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
            $iswhere = ($where['condition'] || $whereIn['condition']) ? ' WHERE ' : '';
            $where_and_in = ($where['condition'] && $whereIn['condition']) ? ' and ' : '';
            $sql = ' UPDATE ' . $this->table_name . ' SET ' . $rowSql . $iswhere . $where['condition'] . $where_and_in . $whereIn['condition'];
            $this->stmt = $this->prepareSql($sql);

            $row_values = array_values($param1);
            $binValues = array_merge($row_values, $where['value'], $whereIn['value']);
            foreach ($binValues as $k => & $v) {              
                $this->stmt->bindParam($k + 1, $v);                            
            }
        }

        $result =  $this->sqlExecute();     
        return $this->stmt->rowCount();

    }
    /**
     *delete
     *@param string $sql
     *@return bool
     */
    public function delete($param1 = null, $param2 = array()) {
        if (is_string($param1) && is_array($param2)) {
            $sql = $param1;
            $this->stmt = $this->prepareSql($sql);
            if (!empty($param2)) {
                foreach ($param2 as $k => & $v) {
                    $this->stmt->bindParam($k + 1, $v);
                }
            }
        } else {
            //where
            $where = $this->parseWhere();
            //whereIn
            $whereIn = $this->parseWhereIn();
            $iswhere = ($where['condition'] || $whereIn['condition']) ? ' WHERE ' : '';
            $where_and_in = ($where['condition'] && $whereIn['condition']) ? ' and ' : '';
            $sql = 'DELETE FROM ' . $this->table_name . $iswhere . $where['condition'] . $where_and_in . $whereIn['condition'];
            $this->stmt = $this->prepareSql($sql);
            $binValues = array_merge($where['value'], $whereIn['value']);
            foreach ($binValues as $k => & $v) {
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
    public function select($param1 = null, $param2 = array(), $all = true) {
        if (is_string($param1) && is_array($param2)) {
            $sql = $param1;
            $this->stmt = $this->prepareSql($sql);
            if (!empty($param2)) {
                foreach ($param2 as $k => & $v) {
                    $this->stmt->bindParam($k + 1, $v);
                }
            }
        } else {
            //select fields
            if (isset($this->options['fields'])) {
                $fields = array();
                foreach ($this->options['fields'] as $k => $v) {
                    $fields[] = $v[0];
                }
                $fields_str = implode(',', $fields);
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
                    foreach ((array)$v[1] as $k2 => $v2) {
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
                    if($v[0]){
                        $orderBy_condition[] = $v[0];
                    }                  

                }
               
                if($orderBy_condition){
                    $orderBy_str = ' order by ' . implode(',', $orderBy_condition);
                }
                
            }
           
            //limit conditions
            $limit = '';
            if (isset($this->options['limit'][0][0])) {
                $limit = ' limit ' . $this->options['limit'][0][0];
            }
            $iswhere = ($where['condition'] || $whereIn['condition']) ? ' WHERE ' : '';
            $where_and_in = ($where['condition'] && $whereIn['condition']) ? ' and ' : '';
            $sql = 'SELECT ' . $fields_str . ' FROM ' . $this->table_name . $iswhere . $where['condition'] . $where_and_in . $whereIn['condition'] . $group_by . $having . $orderBy_str . $limit;
            $this->stmt = $this->prepareSql($sql);
            $binValues = array_merge($where['value'], $whereIn['value'], $having_values);
            foreach ($binValues as $k => & $v) {
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
    public function find($param1 = null, $param2 = array()) {
        return $this->select($param1, $param2, false);
    }
    /**
     *count
     *@param string $param1
     *@param array $param2
     *@return mixed
     */
    public function count($param1 = null, $param2 = array()) {
        if (is_string($param1) && is_array($param2)) {
            $sql = $param1;
            $this->stmt = $this->prepareSql($sql);
            if (!empty($param2)) {
                foreach ($param2 as $k => & $v) {
                    $this->stmt->bindParam($k + 1, $v);
                }
            }
        } else {
            //select field
            $fields_str = isset($this->options['fields'][0][0]) ? $this->options['fields'][0][0] : '*';
            //where
            $where = $this->parseWhere();
            //whereIn
            $whereIn = $this->parseWhereIn();
            $iswhere = ($where['condition'] || $whereIn['condition']) ? ' WHERE ' : '';
            $where_and_in = ($where['condition'] && $whereIn['condition']) ? ' and ' : '';
            $sql = 'SELECT count('.$fields_str.') FROM `'.$this->table_name.'`' .$iswhere .$where['condition'] . $where_and_in . $whereIn['condition'];
            $this->stmt = $this->prepareSql($sql);
            $binValues = array_merge($where['value'], $whereIn['value']);
            foreach ($binValues as $k => & $v) {
                $this->stmt->bindParam($k + 1, $v);
            }
        }
        $this->sqlExecute();
        $rows = $this->stmt->fetch();
        $total = $rows[0];
        return $total;
    }
    /**
     *parseWhere
     *@param string $sql
     *@return array
     */
    private function parseWhere() {
        $where = array('condition' => '', 'value' => array());
        if (isset($this->options['where'])) {
            foreach ($this->options['where'] as $k => $v) {
                if (!empty($v[0])) {
                    $where_condition[] = $v[0];
                    foreach ((array)$v[1] as $k2 => $v2) {
                        $where_values[] = $v2;
                    }
                }
            }
            $whereSql = implode(' and ', $where_condition);
            $where['condition'] = $whereSql;
            $where['value'] = $where_values;
        }
        return $where;
    }
    /**
     *parseWhereIn
     *@param string $sql
     *@return array
     */
    private function parseWhereIn() {
        $whereIn = array('condition' => '', 'value' => array());
        if (isset($this->options['whereIn'])) {
            foreach ($this->options['whereIn'] as $k => $v) {
                if($v[1]){
                    $count_arr = count($v[1]);
                    $make_arr = array_fill(0, $count_arr, '?');
                    $whereInArr[] = $v[0] . ' IN (' . implode(',', $make_arr) . ')';
                    foreach ((array)$v[1] as $k2 => $v2) {
                        $whereIn_values[] = $v2;
                    }                    
                }

            }
            $whereInSql = implode(' and ', $whereInArr);
            $whereIn['condition'] = $whereInSql;
            $whereIn['value'] = $whereIn_values;
        }
        return $whereIn;
    }
    /**
     *prepareSql
     *@param string $sql
     *@return statement
     */
    private function prepareSql($sql) {
        $this->sql = $sql;
        try {
            $this->stmt = $this->PDO->prepare($sql);
            unset($this->options);
        }
        catch(PDOException $e) {
            $this->Msg($e->getMessage());
        }
        return $this->stmt;
    }
    /**
     *sqlExecute
     *@param string $sql
     *@return statement
     */
    private function sqlExecute() {        
        try {
            return $this->stmt->execute();           
        }
        catch(PDOException $e) {
            $this->Msg($e->getMessage());
        }
        
    }    
    /**
     * 魔术方法
     * @access public
     * @param  string $func 方法名
     * @param  array  $args 参数
     * @return mixed
     */
    public function __call($func, $args) {
        if (in_array($func, array('fields', 'as', 'join', 'where', 'whereIn', 'orderBy', 'groupBy', 'limit', 'having'))) {
            $this->options[$func][] = $args;
            return $this;
        }
        exit('Call to undefined method :' . $func . '()' . ' in  "' . __FILE__ . '"');
    }
    /**
     * 当前执行的SQL语句
     * @return string
     */
    public function getQueryLog() {
        return $this->sql;
    }
    /**
     * 执行一条SQL语句
     * 用于查询记录
     */
    public function query($sql) {
        return $this->PDO->query($sql);
    }
    /**
     * 执行一条SQL语句
     * 用于插入记录
     */
    public function exec($sql) {
        return $this->PDO->exec($sql);
    }
    /**
     * 执行一条SQL语句
     * 用于删除记录
     */
    public function execute($sql) {
        return $this->PDO->execute($sql);
    }
    /**
     * 转义字符串
     * @param  string $str
     * @return string
     */
    public function quote($str) {
        return $this->PDO->quote($str);
    }
    /**
     * beginTransaction
     * @param  string $str
     * @return string
     */
    public function beginTransaction() {
        return $this->PDO->beginTransaction();
    }
    /**
     * rollback
     * @param  string $str
     * @return string
     */    
    public function rollback() {
        return $this->PDO->rollback();
    }
    /**
     * commit
     * @param  string $str
     * @return string
     */
    public function commit() {
        return $this->PDO->commit();
    }

}
