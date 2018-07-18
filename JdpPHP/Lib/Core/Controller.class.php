<?php
defined('JDP_PATH') or exit('Access Denied!');
/**
 * JDP Control控制器基类 抽象类
 */
 class Controller {
    // 视图对像
    protected $view = null; 
    // 初始化方法
    public function __construct() {
        $this->view = new View(); //实例化视图类 
        // 控制器初始化
        if (method_exists($this, '_initialize'))
            $this->_initialize();
    } 
    // 赋值到模板变量
    protected function assign($name, $value = '') {
        $this->view->assign($name, $value);

        return $this;
    } 
    // 视图显示方去
    protected function display($file = '') {
        $this->view->display($file);
    } 
    // 成功执行方法
    protected function success($message = '', $jumpUrl = '',$wait=3) {
        $this->__jump($message, $jumpUrl, $wait, 1);
    } 
    // 失败执行方法
    protected function error($message = '', $jumpUrl = '',$wait=3) {
        $this->__jump($message, $jumpUrl, $wait, 0);
    } 

    /**
     * 默认跳转操作 支持错误导向和正确跳转
	 * 调用模板显示 默认为public目录下面的success页面
     * 
     * @param string $msg 提示信息
     * @param Boolean $type 1成功,0失败
     * @param string $jumpUrl 页面跳转地址
     * @access private 
     * @return void 
     */
    private function __jump($msg = "", $jumpurl = "", $wait = 3, $type = 0) {
        $jumpdata = array('msg' => $msg,
            'jumpurl' => $jumpurl,
            'wait' => $wait,
            'type' => $type
            );
        $jumpdata['title'] = ($type == 1) ? "提示信息" : "错误信息";

        if ($type == 1) {
            if (!isset($jumpdata['jumpurl']) || "" == $jumpdata['jumpurl'])
                $jumpdata['jumpurl'] = $_SERVER["HTTP_REFERER"];
            $this->assign('jumpdata', $jumpdata);
            $this->display(C('TMPL_ACTION_SUCCESS'));
        } else {
            if (!isset($jumpdata['jumpurl']) || "" == $jumpdata['jumpurl'])
                $jumpdata['jumpurl'] = "javascript:history.back(-1);";

            $this->assign('jumpdata', $jumpdata);
            $this->display(C('TMPL_ACTION_ERROR'));
            exit ;
        } 
    } 

    /**
     * Ajax方式返回数据到客户端
     * 
     * @access public 
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @return void 
     */
    protected function ajaxReturn($data, $type = 'JSON') {
        switch (strtoupper($type)) {
            case 'JSON' : 
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data));
            case 'XML' : 
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit($this->xml_encode($data));
            case 'EVAL' : 
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);
            default : 
                // 其他返回格式抛出异常
                exit('该数据格式尚未支持，请修改本函数源码添加对应的头');
        } 
    } 
} 
