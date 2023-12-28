<?php
namespace Admin\Form\Search;
use \Zend\Form\Form as Form;

class ColorGroup extends Form{ 
    
	public function __construct($sm, $params = null){
		parent::__construct();
		
		$userInfo = new \ZendX\System\UserInfo();
		$userInfo = $userInfo->getUserInfo();
		
		// FORM Attribute
		$this->setAttributes(array(
			'action'	=> '',
			'method'	=> 'POST',
			'class'		=> 'horizontal-form',
			'role'		=> 'form',
			'name'		=> 'adminForm',
			'id'		=> 'adminForm',
		));

		// Keyword
		$this->add(array(
			'name' => 'filter_keyword',
			'type' => 'Text',
			'attributes' => array(
				'placeholder' => 'Từ khóa',
				'class' => 'form-control input-sm',
				'id' => 'filter_keyword',
			),
		));
		
		// Submit
		$this->add(array(
		    'name'			=> 'filter_submit',
		    'type'			=> 'Submit',
		    'attributes'	=> array(
		        'value'     => 'Tìm',
		        'class'		=> 'btn btn-sm green',
		    ),
		));
		
		// Xóa
		$this->add(array(
		    'name'			=> 'filter_reset',
		    'type'			=> 'Submit',
		    'attributes'	=> array(
		        'value'     => 'Xóa',
		        'class'		=> 'btn btn-sm red',
		    ),
		));
		
		// Order
		$this->add(array(
		    'name'			=> 'order',
		    'type'			=> 'Hidden',
		));
		
		// Order By
		$this->add(array(
		    'name'			=> 'order_by',
		    'type'			=> 'Hidden',
		));
	}
}