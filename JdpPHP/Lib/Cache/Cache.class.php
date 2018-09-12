<?php

class Cache
{

    protected static $path = APP_DATA_PATH . '/';

    /**
     * 写入缓存
     * @access static
     * @param string            $name 缓存变量名
     * @param mixed             $value  存储数据
     * @param integer|\DateTime $expire  有效时间（秒）
     * @return boolean
     */
    public static function set($name, $value, $expire = 0)
    {

        $filename = self::$path . $name . ".php";

        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $data = serialize($value);
        $data = "<?php\n//" . sprintf('%012d', $expire) . "\n exit();?>\n" . $data;
        $res = file_put_contents($filename, $data);

        if ($res) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * 读取缓存
     * @access static
     * @param string            $name 缓存变量名
     * @param mixed             $value  存储数据
     * @param integer|\DateTime $expire  有效时间（秒）
     * @return boolean
     */
    public static function get($name)
    {

        $filename = self::$path . $name . ".php";
        $content = file_get_contents($filename);

        if (false !== $content) {
            $expire = (int) substr($content, 8, 12);
            if (0 != $expire && time() > filemtime($filename) + $expire) {
                return false;
            }

            $content = substr($content, 32);
            $content = unserialize($content);
            return $content;

        } else {
            return false;
        }

    }

    /**
     * 清除缓存
     * @access static
     * @param string            $name 缓存变量名
     * @param mixed             $value  存储数据
     * @param integer|\DateTime $expire  有效时间（秒）
     * @return boolean
     */
    public static function clear($name)
    {

        $filename = self::$path . $name . ".php";
        if(is_file($filename)){
            unlink($filename);
        }
        return true;
        

    }


}
