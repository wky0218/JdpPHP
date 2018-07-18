<?php


class Db
{


    public static $instance = array();

    /**
     * 数据库连接
     * @access static
     * @param  array  $config 参数
     * @return Connection
     */
    public static function connect($config = array())
    {

        $name = md5(serialize($config));       
       
        if(!isset(self::$instance[$name])){   

            if(empty($config)){
                $config = C('DB_CONFIG');
            }            

            if (empty($config['type'])) {
                halt('Undefined db type');
            }

            $db_driver = ucfirst($config['type']);   
            $dbClass = LIB_PATH .'Db/'. $db_driver.'.class.php';

            if(is_file($dbClass)){
                require_once $dbClass; 
            }else{
             
                halt('数据库驱动' . $dbClass . '不存在');           
            }

            $object_db_driver = $db_driver::getInstance();
            self::$instance[$name] = $object_db_driver->connect($config);

        }
        

        return self::$instance[$name];

    }



    /**
     * 魔术方法
     * @access public
     * @param  string $func 方法名
     * @param  array  $args 参数
     * @return mixed
     */
    public static function __callStatic($func, $args)
    {
            
        return call_user_func_array(array(self::connect(), $func), $args);
 

    }
    
    
}
