<?php
namespace Admin\Form\FormData;
use \Zend\Form\Form as Form;

class ChangeUser extends Form {
	
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
		
		// Người quản lý mới
		$this->add(array(
		    'name'			=> 'user_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'     => 'form-control select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::createSelect($sm->get('Admin\Model\UserTable')->listItem(array('data' => array('company_branch_id' => $params->company_branch_id, 'status' => 1)), array('task' => 'list-full-info')), array('key' => 'id', 'value' => 'fullname, company_group_name, company_group_parent_name, company_branch_name')),
		    ),
		));
	}
}