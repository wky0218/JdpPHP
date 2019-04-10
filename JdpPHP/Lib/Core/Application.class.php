<?php
// +----------------------------------------------------------------------
// | JdpPHP
// +----------------------------------------------------------------------
// | Verson 1.0
// +----------------------------------------------------------------------
// | Author: alice <wky0218@hotmail.com>
// +----------------------------------------------------------------------

/**
 * JdpPHP引导类
 */
class Application {
    // 程序运行入口方法
    public static function run() {
        // 初始化框架
        self::_init();
        // 创建应用目录
        self::_create_dir();
        // 建立DEMO
        self::_create_demo();
        // 自动载入需要的类
        // spl_autoload_register将函数注册到autoload函数队列
        spl_autoload_register(array(__CLASS__, '_auto'));
        // 实例化 运行方法
        self::_app_run();
    }
    // 初始化框架
    private static function _init() {
        // 开启session
        session_start();
        // 设置时区
        date_default_timezone_set("PRC");
        // 系统的配置文件路径
        $sys_config = CONFIG_PATH . '/Convention.php';
        C(include $sys_config);
    }
    /**
     * _create_dir 创建默认访问的应用目录
     */
    private static function _create_dir() {
        $module = C('DEFAULT_MODULE');

        $dir = array(
                APP_PATH, 
                APP_COMMON_PATH,
                APP_COMMON_PATH_COMMON, 
                APP_COMMON_PATH_CONF, 
                APP_PATH . '/'. $module, 
                APP_PATH . '/'. $module . '/Common', 
                APP_PATH . '/'. $module . '/Controller',
                APP_PATH . '/'. $module . '/Model', 
                APP_PATH . '/'. $module . '/Conf', 
                APP_PATH . '/'. $module . '/View', 
                APP_RUNTIME_PATH,
                APP_LOG_PATH, 
                APP_TEMP_PATH, 
                APP_DATA_PATH,
                APP_CACHE_PATH,
                );
        foreach ($dir as $d) {
            
            // 目录不存在则创建目录
            is_dir($d) || mkdir($d, 0777, true);
        }
    }
    /**
     * _create_demo 创建demo
     */
    private static function _create_demo() {
        $module = C('DEFAULT_MODULE');
        // 设置内容
        $php = <<<str
<?php

class IndexController extends Controller{

    public function index(){
        header("Content-type:text/html;charset=utf-8");
        echo "<h1>欢迎使用JDP开源框架(:</h1>";
    }
}
str;
        // 文件存在不创建
        $control = APP_PATH .'/'. $module . '/Controller/' . 'IndexController.class.php';
        if (!is_file($control)) {
            // 写入文件 失败提示
            file_put_contents($control, $php) || halt('创建' . $control . '失败');
        }
    }
    private static function _auto($className) {
        // 如果是控制器 去应用中的Controller文件夹中 不是去 框架中的Extend文件夹下的Tool中找
        if (substr($className, -10) == 'Controller' && strlen($className) > 10) {
            // 文件路径
            $file = APP_PATH .'/'. APP_MODULE_NAME . '/Controller/' . $className . '.class.php';
            // 检查文件是否存在
            if (!is_file($file)) {
                //如果有空的控器就执行空控制器里的空方法
                $emptyController = APP_PATH .'/'. APP_MODULE_NAME . '/Controller/EmptyController.class.php';
                if (is_file($emptyController)) {
                    $obj_empty = new EmptyController();
                    if (!method_exists($obj_empty, '_empty')) {
                        halt("控制器EmptyController中方法:_empty不存在");
                    } else {
                        $obj_empty->_empty();
                        exit;
                    }
                } else {
                    halt('控制器' . $className . '不存在');
                }
            }
        } else {
            $file = TOOL_PATH . "/" . $className . '.class.php';
            // 检查文件是否存在
            is_file($file) || halt('扩展类' . $className . '不存在');
        }
        // 载入文件
        require $file;
    }
    /**
     * _app_run 运行程序
     * 分析URL参数，执行对应的模块，方法，参数等
     */
    public static function _app_run() {
        // 应用配置文件路径
        $app_config = APP_COMMON_PATH_CONF . '/Config.php';
        // 载入配置
        if (is_file($app_config)) {
            C(include $app_config);
        } else {
            $commonConf = <<<conf
<?php
// +----------------------------------------------------------------------
// | JdpPHP 
// +----------------------------------------------------------------------
// | Verson 1.0
// +----------------------------------------------------------------------
// | Author: alice <wky0218@hotmail.com>
// 

/**
 * 公共的配置文件
 */ 
defined('JDP_PATH') or exit();

return array(
    'MODULE_ALLOW_LIST'    =>    array('Home','Admin'),
    'DEFAULT_MODULE'            =>  'Home',  // 默认模块
    /*验证码*/
    'CODE_LEN'          => 4, //验证码长度
    'CODE_WIDTH'        => 200, //验证码宽度
    'CODE_HEIGHT'       => 30, //验证码高度
    'CODE_FONT'         => DATA_PATH.'/Font/font.ttf',  //验证码字体文件
    'FONT_SIZE'         => 16, //验证码字体大小
    'FONT_COLOR'        => '#000000', //验证码字体颜色
    'BG_COLOR'          => '#FFFFFF', //验证码背景颜色
    'CODE_STR'          => 'qwertyuioplkjhgfdsazxcvbnnm0987654321', //验证码种子
    
    /*布局*/
    'TPL_L_DELIM'       => '<{', //左定界符
    'TPL_R_DELIM'       => '}>', //右定界符

    /*数据库*/
    'DB_CONFIG'  => array(          
        'type'                  => 'mysql', //数据库类型
        'host'                  => 'localhost', //数据库连接地址
        'user'                  => 'root', //数据库用户名
        'password'              => '', //数据库密码
        'dbname'                => '', //数据库名称
        'prefix'                => 'jdp_', //数据库表前缀
        'charset'               => 'utf8',
    ),

        
);
conf;
            // 写入文件 失败提示
            file_put_contents($app_config, $commonConf) || halt('创建' . $app_config . '失败');
            C(include $app_config);
        }
        header("Content-type:text/html;charset=utf-8");
        // 系统参数获取变量
        $varPath = C('VAR_PATHINFO');
        $varModule = C('VAR_MODULE');
        $varController = C('VAR_CONTROLLER');
        $varAction = C('VAR_ACTION');
        // 判断URL里面是否有兼容模式参数index.php?s=/module/controller/action/...
        if (isset($_GET[$varPath])) {
            $_SERVER['PATH_INFO'] = $_GET[$varPath];
            unset($_GET[$varPath]);
        }
        $_PATH_ARR = array();
        // 分析PATH_INFO
        if (isset($_SERVER['ORIG_PATH_INFO']) || isset($_SERVER['PATH_INFO'])) {
            $_SERVER['PATH_INFO'] = $_SERVER['ORIG_PATH_INFO'] ? $_SERVER['ORIG_PATH_INFO'] : $_SERVER['PATH_INFO'];
            $depr = C('URL_PATHINFO_DEPR');
            define('__INFO__', trim($_SERVER['PATH_INFO'], '/'));
            $_PATH_ARR = explode($depr, __INFO__, 2);
            $module = ucfirst($_PATH_ARR[0]);
            // 允许的模块列表
            $allowList = C('MODULE_ALLOW_LIST');
            if (empty($allowList) || in_array($module, (array)$allowList)) {
                $_GET[$varModule] = $module;
                $_SERVER['PATH_INFO'] = $_PATH_ARR[1];
            }
        }
        // [获取当前模块名称]
        define('APP_MODULE_NAME', !empty($_GET[$varModule]) ? ucfirst($_GET[$varModule]) : C('DEFAULT_MODULE'));
        // 检查模块是否存在
        if (APP_MODULE_NAME && in_array(APP_MODULE_NAME, C('MODULE_ALLOW_LIST')) && is_dir(APP_PATH .'/'. APP_MODULE_NAME)) {
            // 定义当前模块路径
            define('APP_MODULE_PATH', APP_PATH .'/'. APP_MODULE_NAME);
            // 定义当前模块的模版文件路径
            define('APP_TPL_PATH', APP_MODULE_PATH . '/View');
            // 定义当前模块的模版编译文件路径APP_TPL_PARSE_PATH
            define('APP_TPL_PARSE_PATH', APP_CACHE_PATH .'/'. APP_MODULE_NAME);
            // 创建编译文件目录
            is_dir(APP_TPL_PARSE_PATH) || mkdir(APP_TPL_PARSE_PATH, 0777, true);
            // 定义当前模块静态html内容缓存文件路径
            define('APP_HTML_CACHE_PATH', APP_RUNTIME_PATH . '/Html/' . APP_MODULE_NAME . '/');
            // 定义当前模块的模型文件路径
            define('APP_MODEL_PATH', APP_MODULE_PATH . '/Model');
            // 加载模块配置文件
            if (is_file(APP_MODULE_PATH . '/Conf/Config.php')) {
                C(include APP_MODULE_PATH . '/Conf/Config.php');
            }
            // 加载模块函数文件
            if (is_file(APP_MODULE_PATH . '/Common/Function.php')) {
                include APP_MODULE_PATH . '/Common/Function.php';
            }
        } else {
            halt('无法加载模块:' . APP_MODULE_NAME);
        }
        // 定义当前应用的地址
        if (!defined('__APP__')) {
            define('__APP__', 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME']);
        }
        // 定义当前模块的地址
        if (!defined('__MODULE__')) {
            define('__MODULE__', (APP_MODULE_NAME == C('DEFAULT_MODULE') ? __APP__ : __APP__ . '/' . APP_MODULE_NAME));
        }
        $paths = array();
        if (isset($_SERVER['PATH_INFO']) && '' != $_SERVER['PATH_INFO']) {
            $depr = C('URL_PATHINFO_DEPR');
            $_pathinfo = trim($_SERVER['PATH_INFO'], $depr);
            //去掉URL后缀
            $paths = explode($depr, preg_replace('/\.(' . trim(C('URL_HTML_SUFFIX'), '.') . ')$/i', '', $_pathinfo));
            // 获取控制器
            $_GET[$varController] = array_shift($paths);
            // 获取方法
            $acname = array_shift($paths);
            $_GET[$varAction] = ($acname != 'index.php') ? $acname : '';
        }
        // [获取当前控制器名称]
        $controllerName = !empty($_GET[$varController]) ? $_GET[$varController] : C('DEFAULT_CONTROLLER');
        $controllerName = ucfirst($controllerName);
        // 定义当前的控制器名称常量
        define('APP_CONTROLLER_NAME', $controllerName);
        // [获取当前方法名称]
        $actionName = !empty($_GET[$varAction]) ? $_GET[$varAction] : C('DEFAULT_ACTION');
        // 定义当前的方法名称常量
        define('APP_ACTION_NAME', $actionName);
        // 解析剩余的URL参数
        $j = count($paths);
        for ($i = 0;$i < $j;$i = $i + 2) {
            if (isset($paths[$i + 1])) $_GET[$paths[$i]] = $paths[$i + 1];
        }
        // 控制器全名
        $fullController = $controllerName . "Controller";
        // 实例化控制器对像
        $obj = new $fullController();
        if (!method_exists($obj, $actionName)) {
            //如果有空的控器就执行空控制器里的空方法
            $emptyController = APP_PATH .'/'. APP_MODULE_NAME . '/Controller/EmptyController.class.php';
            if (is_file($emptyController)) {
                $obj_empty = new EmptyController();
                if (!method_exists($obj_empty, '_empty')) {
                    halt("控制器EmptyController中方法:_empty不存在");
                } else {
                    $obj_empty->_empty();
                    exit;
                }
            } else {
                halt("控制器{$fullController}中方法{$actionName}不存在");
            }
        }
        //判断是否开启静态缓存
        $html_cache_on = C('HTML_CACHE_ON');
        if ($html_cache_on) {
            // // 缓存规则数组
            // $html_cache_rules = C('HTML_CACHE_RULES');
            // // 缓存规则数组中的键
            // $html_cache_rkey = APP_CONTROLLER_NAME . ":" . APP_ACTION_NAME;
            // $rule_str = $html_cache_rules[$html_cache_rkey][0];
            // $rule_str = str_replace('{:controler}', APP_CONTROLLER_NAME, $rule_str);
            // $rule_str = str_replace('{:method}', APP_ACTION_NAME, $rule_str);
            // $rule_str = preg_replace_callback("/\{([^_]*)\}/i", function ($matches) {
            //     $param = $matches[1];
            //     $v = $_GET[$param];
            //     return $v;
            // }, $rule_str);
            // // 目录不存在则创建目录
            // is_dir(APP_HTML_CACHE_PATH) || mkdir(APP_HTML_CACHE_PATH, 0777, true);
            // $html_cache_file = APP_HTML_CACHE_PATH .'/'. $rule_str . 'html'; //缓存文件路径
            // $html_cache_lifeTime = $html_cache_rules[$html_cache_rkey][1]; //缓存时间
            // if (file_exists($html_cache_file) && filemtime($html_cache_file) + $html_cache_lifeTime > time()) {
            //     include $html_cache_file; //载入缓存文件
            //     return;
            // }
        }
        // 执行对像方法
        // $obj->$actionName();
        call_user_func_array(array($obj, $actionName), array_splice($_GET, 100));
    }
}
?>
