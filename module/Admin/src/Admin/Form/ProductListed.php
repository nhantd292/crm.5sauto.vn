<?php
namespace Admin\Form;
use \Zend\Form\Form as Form;

class ProductListed extends Form {
	
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
		
		// Giá
		$this->add(array(
		    'name'			=> 'price',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control mask_currency',
		    ),
        ));

		// % khuyến mãi
		$this->add(array(
		    'name'			=> 'percenter',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control mask_currency',
		    ),
        ));

		// Sản phẩm
		$this->add(array(
		    'name'			=> 'product_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));

		// Nhóm màu sắc thảm
		$this->add(array(
		    'name'			=> 'group_carpet_color_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\ColorGroupTable')->listItem(array('type' => CARPET_COLOR), array('task' => 'by-type')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		// Nhóm màu rối
		$this->add(array(
		    'name'			=> 'group_tangled_color_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\ColorGroupTable')->listItem(array('type' => TANGLED_COLOR), array('task' => 'by-type')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		// Loại sản phẩm
		$this->add(array(
		    'name'			=> 'flooring_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'flooring')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
        ));

	}
}