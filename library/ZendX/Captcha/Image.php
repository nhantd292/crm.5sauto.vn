<?php
namespace ZendX\Captcha;

use Zend\Captcha\Image AS ZendImage;

class Image extends ZendImage {
    
    protected $imgAlt           = "";
    protected $suffix           = ".png";
    protected $width            = 200;
    protected $height           = 50;
    protected $fsize            = 24;
    protected $font;
    protected $expiration       = 20;
    protected $dotNoiseLevel    = 100;
    protected $lineNoiseLevel   = 5;
    protected $wordlen          = 5;
    
    public function __construct($options = null) {

        $this->imgDir           = PATH_CAPTCHA . '/images';
        $this->imgUrl           = URL_CAPTCHA . '/images';
        $this->fsize            = (!empty($options['fsize'])) ? $options['fsize'] : $this->fsize;
        $this->font             = (!empty($options['font'])) ? $options['font'] : PATH_CAPTCHA . '/fonts/font-one.OTF';
        $this->wordlen          = (!empty($options['wordlen'])) ? $options['wordlen'] : $this->wordlen;
        $this->width            = (!empty($options['width'])) ? $options['width'] : $this->width;
        $this->height           = (!empty($options['height'])) ? $options['height'] : $this->height;
        $this->dotNoiseLevel    = (!empty($options['dotNoiseLevel'])) ? $options['dotNoiseLevel'] : $this->dotNoiseLevel;
        $this->lineNoiseLevel   = (!empty($options['lineNoiseLevel'])) ? $options['lineNoiseLevel'] : $this->lineNoiseLevel;
        $this->expiration       = (!empty($options['expiration'])) ? $options['expiration'] : $this->expiration;
        
        // Thiết lập đường dẫn thư mục hình ảnh chưa Captcha
        $this->setImgDir($this->imgDir);
         
        // Thiết lập đường dẫn url đến thư mục hình ảnh chưa Captcha
        $this->setImgUrl($this->imgUrl);
         
        // Thiết lập font chữ
        $this->setFont($this->font);
         
        // Thiết lập kích thước font chứ
        $this->setFontSize($this->fsize);
         
        // Thiết lập chiều dài ký tự
        $this->setWordlen($this->wordlen);
         
        // Thiết lập kích thước hình ảnh
        $this->setWidth($this->width);
        $this->setHeight($this->height);
         
        // Thiết lập số dấu chấm trong background
        $this->setDotNoiseLevel($this->dotNoiseLevel);
         
        // Thiết lập số đường gạch trong background
        $this->setLineNoiseLevel($this->lineNoiseLevel);
        
        // Thiết lập thời gian tồn tại hình ảnh
        $this->setExpiration($this->expiration);
         
        // Thiết lập phần mở rộng của hình ảnh
        //$this->setSuffix('.jpg');
         
        // Thiết lập thay đổi ký tự xuất hiện
        /* AbstractWord::$CN = array('a');
         AbstractWord::$VN = array('a', '1'); */
         
        // Phát sinh Captcha
        $this->generate();
    }
    
    public function removeImage($captchaId, $options = null) {
        if($options == null) {
            $imgLink = $this->getImgDir() . $captchaId . $this->getSuffix();
            @unlink($imgLink);
        }
    }
}