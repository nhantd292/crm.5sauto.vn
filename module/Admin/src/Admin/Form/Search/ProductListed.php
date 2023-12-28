<?php
namespace Admin\Form\Search;
use \Zend\Form\Form as Form;

class ProductListed extends Form{ 
    
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
		
		// Sản phẩm
		$this->add(array(
		    'name'			=> 'filter_product',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Sản phẩm -',
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    )
		));

		// Nhóm màu thảm
		$this->add(array(
		    'name'			=> 'filter_carpet_color',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Nhóm màu thảm -',
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\ColorGroupTable')->listItem(array('type' => CARPET_COLOR), array('task' => 'by-type')), array('key' => 'id', 'value' => 'name')),
		    )
		));
		// Nhóm màu rối
		$this->add(array(
		    'name'			=> 'filter_tangled_color',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Nhóm màu rối -',
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\ColorGroupTable')->listItem(array('type' => TANGLED_COLOR), array('task' => 'by-type')), array('key' => 'id', 'value' => 'name')),
		    )
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