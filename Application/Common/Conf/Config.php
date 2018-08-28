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