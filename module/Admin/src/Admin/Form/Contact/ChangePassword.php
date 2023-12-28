<?php
namespace Admin\Form\Contact;
use \Zend\Form\Form as Form;

class ChangePassword extends Form {
	
	public function __construct($sm){
		parent::__construct();
		
		// FORM Attribute
		$this->setAttributes(array(
			'action'	    => '',
			'method'	    => 'POST',
			'class'		    => 'horizontal-form',
			'role'		    => 'form',
			'name'		    => 'adminForm',
			'id'		    => 'adminForm',
		));
		
		// Modal
		$this->add(array(
		    'name'			=> 'modal',
		    'type'			=> 'Hidden',
		    'attributes'	=> array(
		        'value'     => 'success',
		    )
		));
		
		// Id
		$this->add(array(
		    'name'			=> 'id',
		    'type'			=> 'Hidden',
		));
		
		// Mật khẩu
		$this->add(array(
		    'name'			=> 'password',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		    => 'form-control',
		    )
		));
		
		// Hành động
		$this->add(array(
		    'name'			=> 'password_status',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'value_options'	=> array(1 => 'Có', 0 => 'Không'),
		    )
		));
	}
}