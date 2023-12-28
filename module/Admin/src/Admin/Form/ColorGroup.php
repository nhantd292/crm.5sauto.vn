<?php
namespace Admin\Form;
use \Zend\Form\Form as Form;

class ColorGroup extends Form {
	
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
		
		// tên
		$this->add(array(
		    'name'			=> 'name',
		    'type'			=> 'Text',
		    'attributes'	=> array(
				'class'		=> 'form-control',
				'onchange'  => 'createAlias(this, "input[name=\"code\"]")'
		    ),
		));
		
		// mã
		$this->add(array(
		    'name'			=> 'code',
		    'type'			=> 'Text',
		    'attributes'	=> array(
				'class'		=> 'form-control',
		    ),
        ));

		// Giá
		$this->add(array(
		    'name'			=> 'price',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control mask_currency',
		    ),
        ));

		// Đơn vị tính
		$this->add(array(
		    'name'			=> 'unit_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "unit" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));

		$this->add(array(
		    'name'			=> 'type',
		    'type'			=> 'Select',
		    'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
                'value_options'	=> array(CARPET_COLOR => 'Màu thảm', TANGLED_COLOR => 'Màu rối'),
		    ),
		));
	}
}