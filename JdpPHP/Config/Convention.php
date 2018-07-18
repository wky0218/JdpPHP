<?php 
// +----------------------------------------------------------------------
// | JdpPHP
// +----------------------------------------------------------------------
// | Verson 1.0
// +----------------------------------------------------------------------
// | Author: alice <wky0218@hotmail.com>
// 
/**
 * JdpPHP系统默认配置文件
 * 该文件请不要修改，如果要覆盖默认配置的值，可在应用配置文件中设定配置项
 * 所有配置参数都可以在生效前动态改变
 */
defined('JDP_PATH') or exit();

return array(

    /**
     * 验证码
     */
    'CODE_LEN' => 4, // 验证码长度
    'CODE_WIDTH' => 200, // 验证码宽度
    'CODE_HEIGHT' => 30, // 验证码高度
    'CODE_FONT' => DATA_PATH . '/Font/font.ttf', // 验证码字体文件
    'FONT_SIZE' => 16, // 验证码字体大小
    'FONT_COLOR' => '#000000', // 验证码字体颜色
    'BG_COLOR' => '#FFFFFF', // 验证码背景颜色
    'CODE_STR' => 'qwertyuioplkjhgfdsazxcvbnnm0987654321', // 验证码种子
    /**
     * 布局
     */
    'TPL_L_DELIM' => '{', // 左定界符
    'TPL_R_DELIM' => '}', // 右定界符
    'TPL_TEMPLATE_SUFFIX' => '.html', // 默认模板文件后缀
    /**
     * 数据库
     */
	'DB_CONFIG'  => array(			
		'type'					=> 'mysql', //数据库类型
		'host'					=> 'localhost', //数据库连接地址
		'user'					=> 'root', //数据库用户名
		'password'				=> '', //数据库密码
		'dbname'				=> '', //数据库名称
		'prefix'				=> '', //数据库表前缀
		'charset'    			=> 'utf8',
	),
    /**
     * 默认设定
     */
    'DEFAULT_M_LAYER' => 'Model', // 默认的模型层名称
    'DEFAULT_C_LAYER' => 'Controller', // 默认的控制器层名称
    'DEFAULT_V_LAYER' => 'View', // 默认的视图层名称
    'DEFAULT_MODULE' => 'Home', // 默认模块
    'DEFAULT_CONTROLLER' => 'Index', // 默认控制器名称
    'DEFAULT_ACTION' => 'index', // 默认操作名称
    'DEFAULT_CHARSET' => 'utf-8', // 默认输出编码
    'DEFAULT_TIMEZONE' => 'PRC', // 默认时区
    /**
     * 系统变量名称设置
     */
    'VAR_MODULE' => 'm', // 默认模块获取变量
    'VAR_CONTROLLER' => 'c', // 默认控制器获取变量
    'VAR_ACTION' => 'a', // 默认操作获取变量
    'VAR_PATHINFO' => 's', // 兼容模式PATHINFO获取变量例如 ?s=/module/controller/action/..
    /**
     * URL设置
     */
    'URL_MODEL' => 0, // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
    // 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为普通 模式
    'URL_PATHINFO_DEPR' => '/', // PATHINFO模式下，各参数之间的分割符号
    'URL_HTML_SUFFIX' => 'html', // URL伪静态后缀设置
    'URL_ROUTER_ON' => false, // 是否开启URL路由
    'URL_ROUTE_RULES' => array(), // 默认路由规则 针对模块
    'URL_MAP_RULES' => array(), // URL映射定义规则
    'SELECT_CACHE' => false, // 开启查询缓存
    'HTML_CACHE_ON' => false, // 是否开启静态缓存
    'TMPL_ACTION_ERROR' => JDP_PATH . '/Tpl/error.tpl', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS' => JDP_PATH . '/Tpl/success.tpl', // 默认错误跳转对应的模板文件
    );
