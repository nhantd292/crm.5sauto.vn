<?php
namespace Admin\Form\Contract;
use \Zend\Form\Form as Form;

class Input extends Form {
	
	public function __construct($sm, $params){
		parent::__construct();
		
		$userInfo = new \ZendX\System\UserInfo();
		$userInfo = $userInfo->getUserInfo();
		
		// FORM Attribute
		$this->setAttributes(array(
			'action'	    => '',
			'method'	    => 'POST',
			'class'		    => 'horizontal-form',
			'role'		    => 'form',
			'name'		    => 'adminForm',
			'id'		    => 'adminForm',
			'enctype'		=> 'multipart/form-data'
		));
		
		// Id
		$this->add(array(
		    'name'			=> 'id',
		    'type'			=> 'Hidden',
		));
		
		// File import
		$this->add(array(
			'name'			=> 'file_import',
			'type'			=> 'File',
			'attributes'	=> array(
				'class'		=> 'form-control',
			),
		));
		
		// Submit
		$this->add(array(
		    'name'			=> 'submit',
		    'type'			=> 'Submit',
		    'attributes'	=> array(
		        'value'     => 'Bắt đầu import',
		        'class'		=> 'btn btn-sm green',
		        'style'     => 'border: 1px solid #35aa47;'
		    ),
		));
	}
}