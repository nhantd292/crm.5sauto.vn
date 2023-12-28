<?php
namespace ZendX\Validator;

use Zend\Validator\AbstractValidator;

class Captcha extends AbstractValidator{
	const NOT_EQUAL   = 'captchaNotEqual';
	
	protected $_captchaId;
	protected $messageTemplates = array(
		self::NOT_EQUAL   => "Mã xác nhận không chính xác",
	);
	
	public function __construct($captchaId)
	{
	    $this->_captchaId = $captchaId;
	    parent::__construct($captchaId); // Không có nội dung này sẽ không lấy được thông báo lỗi
	}
	
	public function isValid($value) {
	    $captchaSession = new \Zend\Session\Container('Zend_Form_Captcha_' . $this->_captchaId);

        if(strcmp($captchaSession->word, $value) != 0) {
            $this->error(self::NOT_EQUAL);
            return false;
        }
        
        return true;
	}
}