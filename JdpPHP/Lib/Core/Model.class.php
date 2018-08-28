<?php 
// +----------------------------------------------------------------------
// | JdpPHP
// +----------------------------------------------------------------------
// | Verson 1.0
// +----------------------------------------------------------------------
// | Author: alice <wky0218@hotmail.com>
// +----------------------------------------------------------------------
/**
*JdpPHP Model模型类
*PDO预处理机制
*/
class Model {
    // 数据库连接对像
    private $pdo = null; 
    // 当前操作的表
    public $table = ''; 
    // 当前操作的表前缀
    public $tablePrefix = ''; 
    // 数据库配置参数
    protected $db_config = array(); 
    // 查询参数
    protected $options = array(); 
    // 当前执行的SQL语句
    protected $sql = ''; 
    // 编码类型
    protected $dbCharset = 'utf8'; 
    // 数据返回类型, 1代表数组, 2代表对象
    protected $returnType = 1;

    /**
     * 构造函数
     * @param string $name 模型名称
     * @param string $tablePrefix 表前缀
     * @param mixed $connection 数据库连接信息
     */
    public function __construct($table = '', $tablePrefix = '', $db_config = array('host'=>'','user'=>'','password'=>'','dbname'=>'')) {
        // 获取模型名称
        if (!empty($table)) {
            $this->table = $table;
        } 
        // 设置表前缀
        if ('' != $tablePrefix) {
            $this->tablePrefix = $tablePrefix;
        } else {
            $this->tablePrefix = C('DB_PREFIX');
        } 
        // 真实表名,包括表前缀
        $this->table = $this->tablePrefix . $this->table; 
        // 连接数据库
        $this->connect($db_config);
    } 

    /**
     * 连接数据库
     * @access public 
     * @param array $db 数据库配置
     * @return obj PDO声明对像
     */
    public function connect($db) {
        // 根据配置使用不同函数连接数据库,如果配置项为空则读取配置文件
        $host   = $db['host']? $db['host']: C('DB_HOST');
        $user   = $db['user']? $db['user']: C('DB_USER');
        $password = $db['password']? $db['password']: C('DB_PASSWORD');
        $dbname = $db['dbname']? $db['dbname']: C('DB_NAME'); 
        $this->db_config['host'] = $host;
        $this->db_config['user'] = $user;
        $this->db_config['password'] = $password;
        $this->db_config['dbname'] = $dbname;
        try{
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
            $this->pdo->query("SET NAMES $this->dbCharset");
        }catch(PDOException $e){
            echo "Error".$e->getMessage();exit;
        }
        return $this->pdo;
        
    }

    /**
     * 转义字符串
     */
    public function quote($v) {
        return $this->pdo->quote($v);
    }
    /**
     * 执行一条SQL语句
     * 用于查询记录
     */
    public function query($sql) {
        return $this->pdo->query($sql);
    } 

    /**
     * 执行一条SQL语句
     * 用于插入记录
     */    
    public function exec($sql) {
        return $this->pdo->exec($sql);
    } 

    /**
     * 执行一条SQL语句
     * 用于删除记录
     */    
    public function execute($sql) {
        return $this->pdo->execute($sql);
    } 

    /**
     * 查询符合条件的一条记录
     * @param string $where 查询条件
     * @param string $field 查询字段
     * @param string $table 表
     * @return mixed 符合条件的记录
     */
    public function find($sql=null,$where = null, $field = '*', $table = '') {
        return $this->select($sql,$where = null, $field = '*', $table = '', false);
    } 

    /**
     * 查询符合条件的所有记录
     * @param string $where 查询条件
     * @param string $field 查询字段
     * @param string $table 表
     * @return mixed 符合条件的记录
     */
    public function select($sql=null,$where=null, $field='*', $table='', $all=true) {
        if($sql){
            $this->sql = $sql; 
        }else{


            $this->options['where'] = is_null($where) ? @$this->options['where']: $where;
            $this->options['field'] = isset($this->options['field']) ? $this->options['field']: $field;
            $this->options['table'] = $table == '' ? $this->table: $table;
            $sql = "SELECT {$this->options['field']} FROM `{$this->options['table']}` ";
            $sql .= isset($this->options['as']) ? ' AS ' . $this->options['as']: '';
            $sql .= isset($this->options['join']) ? ' LEFT JOIN ' . $this->options['join']: '';
            $sql .= isset($this->options['where']) ? ' WHERE ' . $this->paseWhereArr($this->options['where']): '';
            $sql .= isset($this->options['group']) ? ' GROUP BY ' . $this->options['group']: '';
            $sql .= isset($this->options['having']) ? ' HAVING ' . $this->options['having']: '';
            $sql .= isset($this->options['order']) ? ' ORDER BY ' . $this->paseOrderArr($this->options['order']): '';
            $sql .= isset($this->options['limit']) ? ' LIMIT ' . $this->options['limit']: '';
            $this->sql = $sql; 
        }
        
        $stmt = $this->pdo->prepare($this->sql); //准备SQL语句放到服务器上
      
        //$where['typeid']=6; 
        // $where['id']=array('>',10);
        //M('article')->where($where)->select();
        // 针对外部传入的变量为这种形式时设置预处理
   
        if (isset($this->options['where'])&&is_array($this->options['where'])) {
            foreach((array)$this->options['where'] as $k => &$v) {
                if (is_array($v)) {
                     //$where['id']=array('>',10)
                    if('LIKE'!=$v[0] && !is_array($v[0]) && !is_array($v[1])){
                        $stmt->bindParam(":{$k}", $v[1]);
                    }

                } else {
                    $stmt->bindParam(":{$k}", $v);
                } 
            } 
        } 

        $stmt->execute(); 
        // 取出数据类型，FETCH_ASSOC关联数组，FETCH_BOTH索引与关联兼有
        if (1 == $this->returnType)
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
        else
            $stmt->setFetchMode(PDO::FETCH_BOTH);
        $row = ($all === true)? $stmt->fetchAll(): $stmt->fetch(); 
        $this->options = array(); //清空本次的options参数,
        return $row;
    } 

    /**
     * 查询符合条件的总条数
     * 
     * @param string $where 查询条件
     * @param string $table 表
     * @return mixed 符合条件的记录
     */
     public function count($sql=null) {
        if($sql){
            $this->sql = $sql; 
        }else{
            $this->options['where'] = @$this->options['where'];
            $this->options['table'] = $this->table;
            $sql = "SELECT count(*) FROM `{$this->options['table']}` ";
            $sql .= isset($this->options['where']) ? ' WHERE ' . $this->paseWhereArr($this->options['where']): '';
            $this->sql = $sql; 
        }
        $total = 0;       
        $stmt = $this->pdo->prepare($this->sql); //准备SQL语句放到服务器上

         //$where['typeid']=6; $where['id']=array('>',10);
         //D('article')->where($where)->select();
         //针对外部传入的变量为这种形式时设置预处理
         
        if (isset($this->options['where'])&&is_array($this->options['where'])) {
            foreach($this->options['where'] as $k => &$v) {
               if (is_array($v)) {
                     //$where['id']=array('>',10)
                    if('LIKE'!=$v[0] && !is_array($v[0]) && !is_array($v[1])){
                        $stmt->bindParam(":{$k}", $v[1]);
                    }

                } else {
                    $stmt->bindParam(":{$k}", $v);
                } 
            } 
        } 

        $stmt->execute();
        $rows = $stmt->fetch();
        $total = $rows[0];       
      
        $this->options = array(); //清空本次的options参数,
        return $total;
    }    

    /**
     * 插入一条记录
     * @param array $data 插入的记录, 格式:array('字段名1'=>'值1', '字段名2'=>'值2');
     * @param string $table 表名
     * @return bool 当前记录id
     */
    public function add($data, $table = null) {
        $table = is_null($table) ? $this->table: $table;
        $sql = "INSERT INTO `{$table}`";
        $fields = $values = array();
        $field = $value = ''; 
        //过虑数组中元素为0,flase,空,null的元素
        $data = array_filter($data);

        // 遍历记录, 格式化字段名称与值
        foreach((array)$data as $key => $val) {
            $fields[] = "`{$table}`.`{$key}`"; 
            // 命名占位符
            $values[] = ":{$key}";
        } 
        $field = join(',', $fields);
        $value = join(',', $values); 
        // unset($fields, $values);
        $sql .= "({$field}) VALUES({$value})";
        $this->sql = $sql; 
        // 创建编译对像,放到服务器端
        $stmt = $this->pdo->prepare($sql); 
        // 绑定参数
        foreach($data as $k => &$v) {
            $stmt->bindParam(":{$k}", $v);
        } 

        $this->options = array();
        $stmt->execute();
        return $this->pdo->lastInsertId();
    } 

    /**
     * 批量插入
     * @param array $data 要插入的数据, 格式:array(array(), array());
     * @param string $rows 一次插入多少条记录
     * @param string $table 表名
     */
    public function add_all($data, $rows=100, $table = null) {
        $table = is_null($table) ? $this->table: $table;
        
        foreach((array)$data as $k => $v) {
            $keys = array_keys($v);
            $fields = $keys; 
            $values = $keys;
            break;
        } 
  
        $field = join(',', $fields);        
        
        $insql = "INSERT INTO `{$table}`({$field}) VALUES";
        $i=0;
        foreach($data as $k=>$v){
            $data_arr = array();
            foreach($v as $k2=>$v2){
                $data_arr[] = $this->pdo->quote($v2);
            }   
            $data_str = join(',', $data_arr); 
            $insql .= '('.$data_str.'),';
        
            $t = ($i+1)%$rows;
                        
            if($t == 0||($i+1)==count($data)){
                //将最后的逗号替换成分号
                $insql = rtrim($insql,',').';'; 
                $this->sql = $insql; 
                //dump($this->sql);
                // 创建编译对像,放到服务器端
                $stmt = $this->pdo->prepare($insql); 
                //插入数据库 并且重置 字符串 $insql  
                $stmt ->execute();
                
                $insql = "INSERT INTO `{$table}`({$field}) VALUES";
            }   
            
            $i++;    
        }
    } 



    /**
     * 删除记录
     * 
     * @access public 
     * @param string $where 条件
     * @param string $table 表名
     * @return bool 影响行数
     */
    public function del($sql = null ,$where = null, $table = null) {
        $table = is_null($table) ? $this->table: $table;
        $where = is_null($where) ? @$this->paseWhereArr($this->options['where']): $where;
        
        if($sql){
            $this->sql = $sql; 
        }else{
            $sql = "DELETE FROM `{$table}` WHERE {$where}";
            $this->sql = $sql;            
        }

        // 创建编译对像,放到服务器端
        $stmt = $this->pdo->prepare($this->sql);
        
        //$where['typeid']=6; $where['id']=array('>',10);
        //D('article')->where($where)->del();
        // 针对外部传入的变量为这种形式时设置预处理
        
        if (isset($this->options['where'])&&is_array($this->options['where'])) {
            foreach($this->options['where'] as $k => &$v) {
               if (is_array($v)) {
                     //$where['id']=array('>',10)
                    if(!is_array($v[0]) && !is_array($v[1])){
                        $stmt->bindParam(":{$k}", $v[1]);
                    }

                } else {
                    $stmt->bindParam(":{$k}", $v);
                } 
            } 
        } 
        $this->options = array();
        $stmt->execute();
        return $stmt->rowCount();
    } 

    /**
     * 更新记录
     * 
     * @access public 
     * @param array $data 更新的数据 格式:array('字段名' => 值);
     * @param string $where 更新条件
     * @param string $table 表名
     * @return bool 更新记录数
     * $where['typeid']=6; $where['id']=array('>',10);
     * M('article')->where($where)->update($data);
     * 针对外部传入的变量为这种形式时设置预处理
     */
    public function update($data, $sql=null, $where = null, $table = null) {
        

        if($sql){
            $this->sql = $sql; 
            $stmt = $this->pdo->prepare($this->sql); //创建编译对像,放到服务器端
            $this->options = array();

            $stmt->execute();
            return $stmt->rowCount();
        }else{

            $table = is_null($table) ? $this->table: $table;
            $where = is_null($where) ? @$this->paseWhereArr($this->options['where']): $where;
            $sql = "UPDATE `{$table}` SET ";
            $values = array();

            foreach((array)$data as $key => $val) {
                $val = is_numeric($val) ? $val : "'{$val}'";
                $values[] = "`{$table}`.`{$key}` = :{$key}";
            } 
            $value = join(',', $values);
            $this->sql = $sql . $value . " WHERE {$where}";



            $stmt = $this->pdo->prepare($this->sql); //创建编译对像,放到服务器端
             
            // 给$data绑定参数
            foreach((array)$data as $k => &$v) {
                $stmt->bindParam(":{$k}", $v);
            } 
                   
             //邦定值
             //$options['where']['typeid']=6; 设置预处理
             //$options['where']['id']=array('>',10);设置预处理
             //$options['where']['id']=array(array('>',10),array('<',50));不设置预处理
             //$options['where']['id']=array('in',array(1,2,3));不设置预处理
             //针对外部传入的变量为这种形式时设置预处理
             
            if (isset($this->options['where'])&&is_array($this->options['where'])) {
                foreach($this->options['where'] as $k => &$v) {
                   if (is_array($v)) {
                         //$where['id']=array('>',10)
                        if(!is_array($v[0]) && !is_array($v[1])){
                            $stmt->bindParam(":{$k}", $v[1]);
                        }

                    } else {
                        $stmt->bindParam(":{$k}", $v);
                    } 
                } 
            } 

            $this->options = array();
            $stmt->execute();
            return $stmt->rowCount();
        }


    } 


    /**
     * 把WHERE 语句中的数组转换成字符串
     * $where['typeid']=6; 设置预处理
     * $where['id']=array('>',10);设置预处理
     * $where['id']=array(array('>',10),array('<',50));不设置预处理
     * $where['id']=array('in',array(1,2,3));不设置预处理
     * M('article')->where($where)->select();
     * 针对外部传入的变量为这种形式时设置预处理
     */
    protected function paseWhereArr($sqlStr) {
        if (is_array($sqlStr)) {
            $str = '';
            $i = 1; //控制外部"and" 连接个数
            $count = count($sqlStr);
            foreach($sqlStr as $key => $v) {
                if (is_array($v)) {

                    //第1种$where['id']=array('>',10),like条件不能设置预处理
                    if('LIKE'!=$v[0] && !is_array($v[0]) && !is_array($v[1])){
                        $str .= " {$key} {$v[0]} :{$key} ";//命名占位符
                    }
                    else if('LIKE'==$v[0]){
                        $str .= " {$key} LIKE {$v[1]} "; //命名占位符 
                    }

                    //第2种$where['id']=array('in',array(1,2,3))
                    else if(!is_array($v[0]) && is_array($v[1])){
                        $s = "(" . join(',', $v[1]) . ")";
                        $str .= " {$key} {$v[0]} {$s} ";                       

                    }
                    //第3种$where['id']=array(array('>',10),array('<',50))
                    else if(is_array($v[0]) && is_array($v[1])){                     
                        $str .= " {$key} {$v[0][0]} {$v[0][1]} and {$key} {$v[1][0]} {$v[1][1]} ";                       
                    }

                } else {
                    $str .= " {$key}=:{$key} "; //命名占位符	
                } 

                if ($i < $count){$str .= "and";}                
                $i++; 
            } 

            return $str;
        } else {
            return $sqlStr;
        } 
    } 

    /**
     * 把SQL语句中的数组转换成字符串
     * 用于order语句
     */
    protected function paseOrderArr($sqlStr) {
        if (is_array($sqlStr)) {
            $str = '';
            $i = 1;
            $count = count($sqlStr);
            foreach($sqlStr as $key => $v) {
                $str .= " {$key} {$v} ";
                if ($i < $count)
                    $str .= ",";

                $i++;
            } 

            return $str;
        } else {
            return $sqlStr;
        } 
    } 

    /**
     * 自动加载函数, 实现特殊操作
     */
    public function __call($func, $args) {
        if (in_array($func, array('field', 'as', 'join', 'where', 'order', 'group', 'limit', 'having'))) {
            $param = array_shift($args);
            $this->options[$func] = $param;
            return $this;
        } elseif ($func === 'table') {
            $this->options['table'] = array_shift($args);
            $this->table = $this->options['table'];
            return $this;
        } 
        // 如果函数不存在, 则抛出异常
        exit('Call to undefined method Model::' . $func . '()');
    } 

    /**
     * 当前执行的SQL语句
     * @return string 
     */
    public function _sql() {
        return $this->sql;
    } 


} 

?>