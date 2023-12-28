<?php
namespace Admin\Form;
use \Zend\Form\Form as Form;

class Product extends Form {
	
	public function __construct($sm = null){
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
				'class'		=> 'form-control',
				'onchange'  => 'createAlias(this, "input[name=\"code\"]")'
			),
		));

		// Code
		$this->add(array(
			'name'			=> 'code',
			'type'			=> 'Text',
			'attributes'	=> array(
				'class'		=> 'form-control',
			),
		));
		
		// Giá vốn
		$this->add(array(
		    'name'			=> 'price',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control mask_currency',
		    )
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

		// Nhóm sản phâm
		$this->add(array(
		    'name'			=> 'product_group_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "product-group" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));

		// Giá niêm yết
		// $this->add(array(
		//     'name'			=> 'listed_price',
		//     'type'			=> 'Text',
		//     'attributes'	=> array(
		//         'class'		=> 'form-control mask_currency',
		//     )
		// ));
		
		// Ordering
		$this->add(array(
		    'name'			=> 'ordering',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'value'     => 255,
		        'class'		=> 'form-control mask_interger',
		    )
		));
		
		// Status
		$this->add(array(
			'name'			=> 'status',
			'type'			=> 'Select',
			'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
			    'value'     => 1,
			),
		    'options'		=> array(
		        'value_options'	=> array( 1	=> 'Hiển thị', 0 => 'Không hiển thị'),
		    )
		));

		// Đánh giá thợ may
		$this->add(array(
			'name'			=> 'tailors_status',
			'type'			=> 'Select',
			'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
			    'value'     => 0,
			),
		    'options'		=> array(
		        'value_options'	=> array( 0	=> 'Không', 1 => 'có'),
		    )
		));
	}
}