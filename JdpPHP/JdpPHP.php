<?php 
// +----------------------------------------------------------------------
// | JdpPHP
// +----------------------------------------------------------------------
// | Verson 1.0
// +----------------------------------------------------------------------
// | Author: alice <wky0218@hotmail.com>
// +----------------------------------------------------------------------
/**
 * JdpPHP公共入口文件
 */

class JDP {
    /**
     * run 核心方法
     */
    public static function run () {
        // 定义常量
        self::_set_contst(); 
        // 载入框架需要的文件
        self::_import(); 
        // 运行应用类方法
        Application::run();
    } 

    /**
     * _set_contst 定义常量
     */
    private static function _set_contst() {
        // 规范路径
        $path = str_replace('\\', '/', __FILE__); 
        // 定义框架常量，框架目录
        define('JDP_PATH', dirname($path)); 
        // 定义框架Config常量，配置文件
        define('CONFIG_PATH', JDP_PATH . '/Config'); 
        // 定义框架Data常量
        define('DATA_PATH', JDP_PATH . '/Data'); 
        // 定义框架Extend常量
        define('EXTEND_PATH', JDP_PATH . '/Extend'); 
        // 定义框架Tool常量
        define('TOOL_PATH', EXTEND_PATH . '/Tool'); 
        // 定义框架Lib常量
        define('LIB_PATH', JDP_PATH . '/Lib'); 
        // 定义框架Compile常量
        define('COMPILE_PATH', LIB_PATH . '/Compile'); 
        // 定义框架Core常量
        define('CORE_PATH', LIB_PATH . '/Core'); 
        // 定义框架Function常量
        define('FUNCTION_PATH', LIB_PATH . '/Function'); 
        // 定义应用常量
        defined('APP_PATH') || define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME'])); 
        // 系统运行时目录
        defined('APP_RUNTIME_PATH') || define('APP_RUNTIME_PATH', APP_PATH . '/Runtime'); 
        // 定义应用公共目录
        defined('APP_COMMON_PATH') || define('APP_COMMON_PATH', APP_PATH . '/Common'); 
        // 定义应用公共目录下的公共库
        defined('APP_COMMON_PATH_COMMON') || define('APP_COMMON_PATH_COMMON', APP_COMMON_PATH . '/Common'); 
        // 定义应用公共目录下的配置目录
        defined('APP_COMMON_PATH_CONF') || define('APP_COMMON_PATH_CONF', APP_COMMON_PATH . '/Conf'); 
        // 应用日志目录
        defined('APP_LOG_PATH') || define('APP_LOG_PATH', APP_RUNTIME_PATH . '/Logs'); 
        // 应用缓存目录
        defined('APP_TEMP_PATH') || define('APP_TEMP_PATH', APP_RUNTIME_PATH . '/Temp'); 
        // 应用数据目录
        defined('APP_DATA_PATH') || define('APP_DATA_PATH', APP_RUNTIME_PATH . '/Data'); 
        // 应用模板编译目录
        defined('APP_CACHE_PATH') || define('APP_CACHE_PATH', APP_RUNTIME_PATH . '/Cache'); 
        // 系统运行模式
        define ('IS_CGI', (0 === strpos (PHP_SAPI, 'cgi') || false !== strpos (PHP_SAPI, 'fcgi')) ? 1 : 0); // apache2handler
        define ('IS_WIN', strstr (PHP_OS, 'WIN') ? 1 : 0); // 系统环境 WINNT
        define ('IS_CLI', PHP_SAPI == 'cli' ? 1 : 0); // 是否是命令行界面
         
        // 在非命令行模式下运行php脚本
        if (! IS_CLI) {
            // 当前文件名
            if (! defined ('_PHP_FILE_')) {
                if (IS_CGI) {
                    // CGI/FASTCGI模式下
                    $_temp = explode ('.php', $_SERVER ['PHP_SELF']);

                    define ('_PHP_FILE_', rtrim (str_replace ($_SERVER ['HTTP_HOST'], '', $_temp [0] . '.php'), '/'));
                } else {
                    define ('_PHP_FILE_', rtrim ($_SERVER ['SCRIPT_NAME'], '/')); // 也就是入口文件名 /index.php
                } 
            } 

            if (! defined ('__ROOT__')) {
                $_root = rtrim (dirname (_PHP_FILE_), '/');
                define ('__ROOT__', (($_root == '/' || $_root == '\\') ? '' : $_root));
            } 
        } 
        // 定义是否是POST提交常量
        $type = $_SERVER['REQUEST_METHOD'] == 'GET'? false : true;
        define('IS_POST', $type); 
        // 定义IS_AJAX
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            define('IS_AJAX', true);
        } else {
            define('IS_AJAX', false);
        } 
    } 

    /**
     * [_import 载入框架需要的文件]
     * 
     * @return [type] [description]
     */
    private static function _import() {
        $files = require COMPILE_PATH . "/Compile.php";

        foreach($files as $f) {
           
            require $f;
        } 
    } 
} 
// 执行run方法
JDP::run();

?>
