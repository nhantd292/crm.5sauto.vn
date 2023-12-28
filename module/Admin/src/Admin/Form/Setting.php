<?php
namespace Admin\Form;
use \Zend\Form\Form as Form;

class Setting extends Form {
	
	public function __construct($sm){
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
		
		// Id
		$this->add(array(
		    'name'			=> 'id',
		    'type'			=> 'Hidden',
		));
		
		// Name
		$this->add(array(
			'name'			=> 'name',
			'type'			=> 'Text',
			'attributes'	=> array(
				'class'			=> 'form-control',
				'id'			=> 'name',
				'placeholder'	=> 'Nhập tên',
			)
		));
		
		// Code
		$this->add(array(
		    'name'			=> 'code',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		        'id'			=> 'alias',
		        'placeholder'	=> 'Nhập mã',
		    )
		));
		
		// Value
		$this->add(array(
		    'name'			=> 'value',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		        'id'			=> 'alias',
		        'placeholder'	=> 'Nhỏ hơn 500 ký tự',
		    )
		));
		
		// Image
		$this->add(array(
		    'name'			=> 'image',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		        'id'			=> 'image',
		        'placeholder'	=> 'Chọn hình ảnh',
		    ),
		    'options'		=> array(
		        'type'	    => 'open-file',
		        'group'	    => 'images',
		    ),
		));
		
		// Description
		$this->add(array(
		    'name'			=> 'description',
		    'type'			=> 'Textarea',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		        'id'			=> 'description',
		    )
		));
		
		// Content
		$this->add(array(
		    'name'			=> 'content',
		    'type'			=> 'Textarea',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		        'id'			=> 'content',
		    )
		));
	}
}