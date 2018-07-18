<?php

class IndexController extends Controller{

    public function index(){
        header("Content-type:text/html;charset=utf-8");
        echo "<h1>欢迎使用JDP开源框架(:</h1>";
    }
}