<?php
/**
 * JDP 视图类
 */
class View
{
    // 模板输出变量
    protected $tpl_var = array();
    // 模板文件后缀
    protected $tpl_ext = ".html";
    // 模板文件的内容
    protected $tpl_con;
    // 模板编译文件
    protected $parse_file;
    // 模板缓存文件
    protected $cache_file;
    // 模板缓存时间
    protected $life_time;
    // 是否开启缓存
    protected $cache_on = false;
    // 模板目录
    protected $tpl_dir;
    // 编译目录
    protected $parse_dir;
    // 缓存目录
    protected $cache_dir;
    // 模板左定界符
    protected $left = '<{';
    // 模板右定界符
    protected $right = '}>';

    /**
     * 初始化设置
     */
    public function __construct($tpl_dir = '', $parse_dir = '')
    {
        $this->tpl_dir = empty($tpl_dir) ? APP_TPL_PATH : $tpl_dir;
        $this->parse_dir = empty($parse_dir) ? APP_TPL_PARSE_PATH : $parse_dir;
        $this->cache_dir = empty($cache_dir) ? APP_HTML_CACHE_PATH : '';
        $left_delim = C('TPL_L_DELIM');
        $right_delim = C('TPL_R_DELIM');
        $this->left = $left_delim ? $left_delim : $this->left;
        $this->right = $right_delim ? $right_delim : $this->right;

    }

    /**
     * 模板变量赋值
     */
    public function assign($name, $value = '')
    {
        if (is_array($name)) {
            $this->tpl_var = array_merge($this->tpl_var, $name);
        } else {
            $this->tpl_var[$name] = $value;
        }

    }

    /**
     * display()方法
     * 1获取指定模板
     * 2设置编译文件
     */
    public function display($file = '')
    {

        // 默认的模板名称格式
        $default_tpl_name = APP_CONTROLLER_NAME . '_' . APP_ACTION_NAME;

        if ('' == $file) {
            $this->tpl_file = $this->tpl_dir . '/' . $default_tpl_name . $this->tpl_ext;
        } else {
            $this->tpl_file = $file;
        }

        if (!file_exists($this->tpl_file)) {
            exit($this->tpl_file . '模板文件不存在!');
        }

        $this->parse_file = $this->parse_dir . '/' . md5($this->tpl_file) . '.php'; //设置编译文件路径
        $this->_showContent();
    }

    /**
     * 解析模板标签并生成编译文件
     */
    public function compile()
    {
        $debug = defined('APP_DEBUG') ? APP_DEBUG : false;

        // 如果编译文件不存在或开启调试模式则重新生成编译文件
        if (!file_exists($this->parse_file) || $debug == 1) {
            $this->tpl_con = file_get_contents($this->tpl_file); //读取模板文件内容

            $this->parseTemplate(); //解析模板标签

            if (!file_put_contents($this->parse_file, $this->tpl_con)) {
                exit('编译文件生成失败!');
            }

        } else if (2 == $debug) {

            $isChanged = $this->isChanged($this->tpl_file);

            if ($isChanged) {
                $this->parseTemplate(); //解析模板标签

                if (!file_put_contents($this->parse_file, $this->tpl_con)) {
                    exit('编译文件生成失败!');
                }

            }

        }
    }
    /**
     * 判断当前文件和包含文件是否有改动
     * @return  bool
     */
    public function isChanged($tpl)
    {
        $changed = false;
        $con = file_get_contents($tpl);
        if($tpl == $this->tpl_file){
           $this->tpl_con= $con;
        }        

        //编译成PHP时也需要点时间,当修改得很快时，第二次修改保存时，可能第一次PHP都还没生成，
        //所以修改时加上一点时间来保证修改时模板的时间大于编译文件的时间
        if (filemtime($tpl) + 5 >= filemtime($this->parse_file)) {
            $changed = true;
            return $changed;
        }
        

        $mode = '/' . ($this->left) . "\s*include\s+file\s*=\s*([\"|']?)([^ \"'}>]+)([\"|']?)\s*\/" . ($this->right) . '/iU';
        preg_match_all($mode, $con, $arr);

        foreach ($arr[2] as $k => $v) {
            $file = $this->tpl_dir . "/" . $v . $this->tpl_ext;

            if (filemtime($file) + 5 >= filemtime($this->parse_file)) {
                $changed = true;
                break;
            }
            $this->isChanged($file);

        }
        return $changed;
    }

    /**
     * 模板标签替换
     */
    public function parseTemplate()
    {

        // include包含文件
        $mode = '/' . $this->left . '\s*include\s+file\s*=\s*\'(.+)\'' . '\s*\/\s*' . $this->right . '/iU';
        $this->tpl_con = preg_replace_callback($mode, array($this, 'paseIncludeFile'), $this->tpl_con);

        $patterns = array();
        //if
        $patterns[] = '/' . $this->left . '\s*if([^{]+?)\s*' . $this->right . '/';
        $patterns[] = '/' . $this->left . '\s*else\s*' . $this->right . '/';
        $patterns[] = '/' . $this->left . '\s*elseif([^{]+?)\s*' . $this->right . '/';
        $patterns[] = '/' . $this->left . '\s*\/if\s*' . $this->right . '/';
        //foreach
        $patterns[] = '/' . $this->left . '\s*foreach([^{]+?)\s*' . $this->right . '/';
        $patterns[] = '/' . $this->left . '\s*\/foreach\s*' . $this->right . '/';
        //for
        $patterns[] = '/' . $this->left . '\s*for([^{]+?)\s*' . $this->right . '/';
        $patterns[] = '/' . $this->left . '\s*\/for\s*' . $this->right . '/';

        //变量
        $patterns[] = '/' . $this->left . '\s*(\$[\s*a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff\[\s*\]\']*)\s*' . $this->right . '/';

        //常量
        $patterns[] = '/' . $this->left . '\s*([A-Z_\x7f-\xff][A-Z0-9_\x7f-\xff]*)\s*' . $this->right . '/s';
        //U方法
        $patterns[] = '/' . $this->left . '\s*:U([^{]+?)\s*' . $this->right . '/';
        //两个变量或常量的运算
        $patterns[] = '/' . $this->left . '\s*(.*)([\+\-\*\/\%])(.*)\s*' . $this->right . '/';

        $replacements = array();

        $replacements[] = '<?php if $1 { ?>';
        $replacements[] = '<?php } else { ?>';
        $replacements[] = '<?php } elseif $1 { ?>';
        $replacements[] = '<?php } ?>';

        $replacements[] = '<?php foreach $1 { ?>';
        $replacements[] = '<?php } ?>';
        $replacements[] = '<?php for $1 { ?>';
        $replacements[] = '<?php } ?>';
        $replacements[] = '<?php echo $1;?>';

        $replacements[] = '<?php echo $1;?>';
        $replacements[] = '<?php echo U$1 ;?>';
        $replacements[] = '<?php echo $1$2$3;?>';

        $this->tpl_con = preg_replace($patterns, $replacements, $this->tpl_con);

        // 模板常量
        $TPS = C('TMPL_PARSE_STRING');

        if ('' !== $TPS && count((array) $TPS) > 0) {
            foreach ($TPS as $key => $v) {
                $t1[] = "/$key/";
                $t2[] = $v;
            }

            $this->tpl_con = preg_replace($t1, $t2, $this->tpl_con);
        }

    }

    // 显示数据
    private function _showContent()
    {
        $this->cache_on = C('HTML_CACHE_ON') ? C('HTML_CACHE_ON') : $this->cache_on;
        // 缓存规则数组
        $cache_rules = C('HTML_CACHE_RULES');
        // 缓存规则数组中的键
        $rules_key = APP_CONTROLLER_NAME . ":" . APP_ACTION_NAME;
        if ($this->cache_on && isset($cache_rules[$rules_key])) {

            $rule_str = $cache_rules[$rules_key][0];
            $rule_str = str_replace('{:controler}', APP_CONTROLLER_NAME, $rule_str);
            $rule_str = str_replace('{:method}', APP_ACTION_NAME, $rule_str);
            $rule_str = preg_replace_callback("/\{([^_]*)\}/i", function ($matchs) {
                $param = $matchs[1];
                return $_GET[$param];
            }, $rule_str);

            // dump($rule_str);
            // 目录不存在则创建目录
            is_dir($this->cache_dir) || mkdir($this->cache_dir, 0777, true);
            $this->cache_file = $this->cache_dir . $rule_str . $this->tpl_ext; //设置缓存文件路径

            ob_start();
            extract($this->tpl_var);
            $this->compile(); //生成编译文件
            include $this->parse_file; //载入编译文件
            file_put_contents($this->cache_file, ob_get_contents()); //生成静态html缓存文件
            ob_end_clean();
            include $this->cache_file; //载入静态html缓存文件

        } else {
            extract($this->tpl_var);
            $this->compile(); //生成编译文件
            include $this->parse_file; //载入编译文件
        }
    }

    /**
     * 读取包含文件的内容
     */
    private function paseIncludeFile($match)
    {
        $file = $this->tpl_dir . "/" . $match[1] . $this->tpl_ext;
        //dump($file);
        if (file_exists($file)) {
            $include_con = file_get_contents($file); //读取模板文件内容

            $mode = '/' . $this->left . '\s*include\s+file\s*=\s*\'(.+)\'' . '\s*\/\s*' . $this->right . '/iU';
            $include_con = preg_replace_callback($mode, array($this, 'paseIncludeFile'), $include_con);

            return $include_con;
        } else {
            die("模板:" . $filename . " 不存在");
        }
    }

}
