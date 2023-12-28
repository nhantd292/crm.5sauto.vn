<?php
namespace Admin\Form\Contact;
use \Zend\Form\Form as Form;

class ChangeClass extends Form {
	
	public function __construct($sm, $params = NULL){
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
		
		// Id
		$this->add(array(
		    'name'			=> 'id',
		    'type'			=> 'Hidden',
		));
		
		// Khóa học
		$this->add(array(
		    'name'			=> 'product_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		// Lớp học
		$this->add(array(
		    'name'			=> 'training_class_id',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		          => 'form-control select2 select2_advance',
		        'value'               => '',
		        'data-table'          => TABLE_TRAINING_CLASS,
		        'data-id'             => 'id',
		        'data-text'           => 'name',
		        'data-parent'         => '',
		        'data-parent-field'   => 'product_id',
		        'data-parent-name'    => 'product_id',
		        'data-where_status'   => 1,
		    ),
		));
		
		// Hidden để lưu danh sách id của liên hệ cần chuyển
		$this->add(array(
		    'name'			=> 'contact_ids',
		    'type'			=> 'Hidden',
		));
	}
}