<?php
namespace Admin\Form;
use \Zend\Form\Form as Form;

class Login extends Form {
	
	public function __construct(){
		parent::__construct();
		
		// FORM Attribute
		$this->setAttributes(array(
			'action'	=> '',
			'method'	=> 'POST',
			'class'		=> 'horizontal-form',
			'role'		=> 'form',
			'name'		=> 'adminForm',
			'id'		=> 'adminForm',
		));
		
		// Username
		$this->add(array(
		    'name'			=> 'username',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control placeholder-no-fix',
		        'id'			=> 'username',
		        'placeholder'	=> 'Tên đăng nhập',
		        'autocomplete'  => 'off'
		    ),
		));
		
		// Password
		$this->add(array(
		    'name'			=> 'password',
		    'type'			=> 'Password',
		    'attributes'	=> array(
		        'class'			=> 'form-control placeholder-no-fix',
		        'id'			=> 'password',
		        'placeholder'	=> 'Mật khẩu',
		        'autocomplete'  => 'off'
		    ),
		));
	}
}