<?php 
// +----------------------------------------------------------------------
// | JdpPHP
// +----------------------------------------------------------------------
// | Verson 1.0
// +----------------------------------------------------------------------
// | Author: alice <wky0218@hotmail.com>
// +----------------------------------------------------------------------
/**
 * JdpPHP 系统函数
 */

/**
 * 浏览器变量打印
 * 
 * @param mixed $var 变量
 * @return void 
 */
function dump($var) {
    ob_start();
    var_dump($var);
    $output = ob_get_clean();
    if (!extension_loaded('xdebug')) {
        $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
        $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
    } 
    echo($output);
    return null;
} 

/**
 * 错误提示方法
 * 
 * @param mixed $msg 变量
 * @return void 
 */
function halt($msg) {
    header("Content-type:text/html;charset=utf-8");
    die("<h1>{$msg} ):</h1>");
} 

/**
 * 获取和设置配置参数 支持批量定义
 * 
 * @param string $ |array $name 配置变量
 * @param mixed $value 配置值
 * @return mixed 
 */
function C($name = null, $val = null) {
    // 设置静态变量
    static $config = array(); 
    // 如果传进来的是一个数组则合并
    if (is_array($name)) {
        // 转换键名为大写
        $name = array_change_key_case($name, CASE_UPPER); 
        // 合拼数组，后传入的数组要放到后面，后面的优先级高,合并时候
        $config = array_merge($config, $name);
        return;
    } 

    if (is_null($name)) {
        // 所有配置
        return $config;
    } 

    if (is_string($name)) {
        if (is_null($val)) {
            // 输出配置项 不存在输出空
            return isset($config[$name])?$config[$name]:'';
        } else {
            // 转大写 设置值
            $name = strtoupper($name);
            $config[$name] = $val;
        } 
    } 
} 


/**
 * D函数用于实例化一个自定义的模型文件，没有则用默认Model
 * 
 * @param string $tablePrefix 表前缀
 * @param mixed $link 数据库连接信息
 * @return Model 
 */
function D($name = '', $tablePrefix = '', $db_config = array('host'=>'','user'=>'','password'=>'','dbname'=>'')) {
    static $_model = array();

    $dbname = $db_config['dbname']?$db_config['dbname']:C('DB_NAME');
    $tablePrefix = $tablePrefix?$tablePrefix:C('DB_PREFIX');
    $class = 'Model';

    $guid = $dbname.':'.$tablePrefix . $name . '_' . $class; 
    // dump($guid);die;
    // 查长应用模型目录是否存在自定义模型,有则实例化它，没有则实例化默认模型
    $file = APP_MODEL_PATH . "/" . ucfirst($name) . 'Model' . '.class.php';

    if (is_file($file)) {
        require $file;
        $class = $name . 'Model';
        $guid = APP_NAME . $tablePrefix . $name . '_' . $class;
    } 

    if (!isset($_model[$guid])){
        $_model[$guid] = new $class($name, $tablePrefix, $db_config);
    }

    return $_model[$guid];
} 

/**
 * 文件缓存方法
 * 
 * @name 缓存文件名称
 * @value 缓存数据
 * @path 缓存路径
 */
function F($name, $value = '', $path = APP_DATA_PATH) {
    static $_fcache = array();
    $filename = $path . $name . ".php";

    if ($value !== '') {
        if (is_null($value)) {
            // 如果value为null删除缓存
            unlink($filename);
            return;
        } else {
            // 缓存数据
            $dir = dirname($filename);
            if (!is_dir($dir))
                mkdir($dir, 0755, true);
            $_fcache[$name] = $value;
            file_put_contents($filename, "<?php return " . var_export($value, true) . ";?>");
            return true;
        } 
    } 
    if (is_file($filename))
        $value = include $filename;

    return $value;
} 

/**
 * 获取当前时间戳(精确到毫秒)
 * @return  float  
 */
function microtime_float(){
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}

/**
 * 格式化时间戳，精确到毫秒，x代表毫秒
 * @return  $str
 */
function microtime_format($tag, $time){
    list($sec, $usec) = explode(".", $time);
    $date = date($tag,$sec);
    return str_replace('x', $usec, $date);
}

/**
 * U函数用于生成一个url地址
 * 
 * @param  $cm string 控制器/方法
 * @param  $arr array  url参数
 */
function U($mca, $arr = '') {

    $http_host = $_SERVER['HTTP_HOST'];
    $http_x = $_SERVER['REQUEST_SCHEME'];
    $mca_arr = explode('/', trim($mca, '/'));
    $count = count($mca_arr);
    if(3==$count){
        $m = ucfirst($mca_arr[0]);
        $c = ucfirst($mca_arr[1]);
        $a = $mca_arr[2];
    }else if(2==$count){
        $m = APP_MODULE_NAME;
        $c = ucfirst($mca_arr[0]);
        $a = $mca_arr[1];   
    }else if(1==$count){
        $m = APP_MODULE_NAME;
        $c = APP_CONTROLLER_NAME;
        $a = $mca_arr[0];
    }else{
        return "U($mca, $arr)";
    }

    $default_module = ucfirst(C('DEFAULT_MODULE'));
   
    $url_model = C('URL_MODEL');
    $str = '';
    if (is_array($arr)) {
        foreach($arr as $k => $v) {
            
            if ($url_model == 1){
                $str .= '/' . $k . '/' . $v;
            }
            else if ($url_model == 2){
                $str .= '/' . $k . '/' . $v;
            }
            else{
                $str .= '&' . $k . '=' . $v;
            }
        } 
    } 
    //1.pathinfo模式:localhost/myweb/index.php/home/index/index
    //2.伪静态:localhost/myweb/home/index/index
    $_script_name = $_SERVER['ORIG_SCRIPT_NAME']?$_SERVER['ORIG_SCRIPT_NAME']:$_SERVER['SCRIPT_NAME'];
    if ($url_model == 1){
        $MOP = ($m!=$default_module)?$m.'/':''; 
        $url = $http_x.'://' . $http_host . $_script_name . '/' .$MOP. $c . '/' . $a . $str;
    }else if($url_model == 2){//localhost/myweb/index.php/home/index/index
        $MOP = ($m!=$default_module)?$m.'/':''; 

        $web_dir = preg_replace('/\/index.php/i', '',$_script_name);

        $url = $http_x.'://' .  $http_host  .$web_dir. '/' .$MOP. $c . '/' . $a . $str;
    }else{
        $MOP = ($m!=$default_module)?'m='.$m.'&':'';
        $url = $http_x.'://' .  $http_host . $_script_name . "?" .$MOP. 'c=' . $c . '&' . 'a=' . $a . $str;
    }

    return $url;
} 

?>
