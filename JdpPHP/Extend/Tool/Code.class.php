<?php
/**
 * ��֤����
 */
class Code{
	// ��֤�볤��
	private $codeLen;
	// ��֤����
	private $codeWidth;
	// ��֤��߶�
	private $codeHeight;
	// ����ɫ
	private $bgColor;
	// �����С
	private $fontSize;
	// ������ɫ
	private $FontColor;
	// ����
	private $codeStr;

	private $img;

	public function __construct($config=array())
	{

		

		// ��֤�볤��
		$codeLen	= null;
		// ��֤����
		$codeWidth	= null;
		// ��֤��߶�
		$codeHeight = null;
		// ����ɫ
		$bgColor 	= null;
		// �����С
		$fontSize	= null;
		// ������ɫ
		$FontColor  = null;
		// ����
		$codeStr	= null;


		// �������� ӳ��ɱ���
		if(!empty($config))
			extract($config);

		// ��֤�볤��
		$this->codeLen 		= is_null($codeLen) 	? C('CODE_LEN') 		: $codeLen;
		// ��֤����
		$this->codeWidth 	= is_null($codeWidth) 	? C('CODE_WIDTH') 		: $codeWidth;

		// ��֤��߶�
		$this->codeHeight 	= is_null($codeHeight) 	? C('CODE_HEIGHT') 		: $codeHeight;
		// ����ɫ
		$this->bgColor 		= is_null($bgColor) 	? C('BG_COLOR') : $bgColor;
		// �����С
		$this->fontSize 	= is_null($fontSize) 	? C('FONT_SIZE') 		: $fontSize;
		// ������ɫ
		$this->FontColor	= is_null($FontColor) 	? C('FONT_COLOR') 	: $FontColor;
		// ����
		$this->codeStr		= is_null($codeStr) 	? C('CODE_STR') 	: $codeStr;


	}
	/**
	 * [show ��ʾ��֤��]
	 * @return [type] [description]
	 */
	public function show()
	{
		header('Content-type:image/png');
		// 1 ��������
		$this->_create_img();
		// 2 ����
		$this->_create_point();
		// 3 ����
		$this->_create_line();
		// 4 д����
		$this->_creart_font();
		// 5 ��ʾͼƬ
		imagepng($this->img);
		// 6 �ͷ���Դ
		imagedestroy($this->img);

	}
	/**
	 * [_create_img ��������]
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
	 * [_create_point �����ŵ�]
	 * @return [type] [description]
	 */
	private function _create_point()
	{
		for($i = 0 ; $i < 200 ; $i++)
		{
			// ������ɫ
			$color = imagecolorallocate($this->img, mt_rand(0,255), mt_rand(0,255), mt_rand(0,255));
			// ����
			imagesetpixel($this->img, mt_rand(0,$this->codeWidth), mt_rand(0,$this->codeHeight), $color);
		}
	}
	/**
	 * [_create_line ��������]
	 * @return [type] [description]
	 */
	private function _create_line()
	{
		for($i = 0 ; $i < 20 ; $i++)
		{
			// ������ɫ
			$color = imagecolorallocate($this->img, mt_rand(0,255), mt_rand(0,255), mt_rand(0,255));
			// ����
			imageline($this->img, mt_rand(0,$this->codeWidth), mt_rand(0,$this->codeHeight), mt_rand(0,$this->codeWidth), mt_rand(0,$this->codeHeight), $color);
		}
	}
	/**
	 * [_creart_font д����]
	 * @return [type] [description]
	 */
	private function _creart_font()
	{
		$str = '';
		for($i = 0 ; $i < $this->codeLen ; $i++)
		{
			$text 	= $this->codeStr[mt_rand(0,strlen($this->codeStr)-1)];
			// ������ɫ
			$color 	= hexdec($this->FontColor);
			// ����x
			$x 		= $this->codeWidth/$this->codeLen;
			$x 		= $x*$i + 10;
			// ����y
			$y 		= ($this->codeHeight + $this->fontSize)/2;
			// д����
			imagettftext($this->img, $this->fontSize , mt_rand(-45,45), $x, $y, $color, C('CODE_FONT'), $text);
			// ���浽�ַ���
			$str .= $text;
		}
		// ������֤��
		$_SESSION['code'] = strtoupper($str);
	}
	
}
?>