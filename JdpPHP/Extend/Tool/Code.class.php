<?php
/**
 * 验证码类
 */
class Code{
	// 验证码长度
	private $codeLen;
	// 验证码宽度
	private $codeWidth;
	// 验证码高度
	private $codeHeight;
	// 背景色
	private $bgColor;
	// 字体大小
	private $fontSize;
	// 字体颜色
	private $FontColor;
	// 种子
	private $codeStr;

	private $img;

	public function __construct($config=array())
	{

		

		// 验证码长度
		$codeLen	= null;
		// 验证码宽度
		$codeWidth	= null;
		// 验证码高度
		$codeHeight = null;
		// 背景色
		$bgColor 	= null;
		// 字体大小
		$fontSize	= null;
		// 字体颜色
		$FontColor  = null;
		// 种子
		$codeStr	= null;


		// 处理数组 映射成变量
		if(!empty($config))
			extract($config);

		// 验证码长度
		$this->codeLen 		= is_null($codeLen) 	? C('CODE_LEN') 		: $codeLen;
		// 验证码宽度
		$this->codeWidth 	= is_null($codeWidth) 	? C('CODE_WIDTH') 		: $codeWidth;

		// 验证码高度
		$this->codeHeight 	= is_null($codeHeight) 	? C('CODE_HEIGHT') 		: $codeHeight;
		// 背景色
		$this->bgColor 		= is_null($bgColor) 	? C('BG_COLOR') : $bgColor;
		// 字体大小
		$this->fontSize 	= is_null($fontSize) 	? C('FONT_SIZE') 		: $fontSize;
		// 字体颜色
		$this->FontColor	= is_null($FontColor) 	? C('FONT_COLOR') 	: $FontColor;
		// 种子
		$this->codeStr		= is_null($codeStr) 	? C('CODE_STR') 	: $codeStr;


	}
	/**
	 * [show 显示验证码]
	 * @return [type] [description]
	 */
	public function show()
	{
		header('Content-type:image/png');
		// 1 创建画布
		$this->_create_img();
		// 2 画点
		$this->_create_point();
		// 3 画线
		$this->_create_line();
		// 4 写文字
		$this->_creart_font();
		// 5 显示图片
		imagepng($this->img);
		// 6 释放资源
		imagedestroy($this->img);

	}
	/**
	 * [_create_img 创建画布]
	 * @return [type] [description]
	 */
	private function _create_img()
	{
		
		$img = imagecreatetruecolor($this->codeWidth, $this->codeHeight);
		$bgColor = hexdec($this->bgColor);
		imagefill($img, 0,0, $bgColor);
		$this->img = $img;
	}
	/**
	 * [_create_point 画干扰点]
	 * @return [type] [description]
	 */
	private function _create_point()
	{
		for($i = 0 ; $i < 200 ; $i++)
		{
			// 设置颜色
			$color = imagecolorallocate($this->img, mt_rand(0,255), mt_rand(0,255), mt_rand(0,255));
			// 画点
			imagesetpixel($this->img, mt_rand(0,$this->codeWidth), mt_rand(0,$this->codeHeight), $color);
		}
	}
	/**
	 * [_create_line 画干扰线]
	 * @return [type] [description]
	 */
	private function _create_line()
	{
		for($i = 0 ; $i < 20 ; $i++)
		{
			// 设置颜色
			$color = imagecolorallocate($this->img, mt_rand(0,255), mt_rand(0,255), mt_rand(0,255));
			// 画线
			imageline($this->img, mt_rand(0,$this->codeWidth), mt_rand(0,$this->codeHeight), mt_rand(0,$this->codeWidth), mt_rand(0,$this->codeHeight), $color);
		}
	}
	/**
	 * [_creart_font 写文字]
	 * @return [type] [description]
	 */
	private function _creart_font()
	{
		$str = '';
		for($i = 0 ; $i < $this->codeLen ; $i++)
		{
			$text 	= $this->codeStr[mt_rand(0,strlen($this->codeStr)-1)];
			// 设置颜色
			$color 	= hexdec($this->FontColor);
			// 计算x
			$x 		= $this->codeWidth/$this->codeLen;
			$x 		= $x*$i + 10;
			// 计算y
			$y 		= ($this->codeHeight + $this->fontSize)/2;
			// 写文字
			imagettftext($this->img, $this->fontSize , mt_rand(-45,45), $x, $y, $color, C('CODE_FONT'), $text);
			// 保存到字符串
			$str .= $text;
		}
		// 保存验证码
		$_SESSION['code'] = strtoupper($str);
	}
	
}
?>