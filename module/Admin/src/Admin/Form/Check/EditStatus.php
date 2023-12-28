<?php
namespace Admin\Form\Check;
use \Zend\Form\Form as Form;

class EditStatus extends Form {
	
	public function __construct($sm, $params){
		parent::__construct();

		$userInfo = new \ZendX\System\UserInfo();
		$permission = $userInfo->getPermissionOfUser();
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
		
		// Modal
		$this->add(array(
		    'name'			=> 'modal',
		    'type'			=> 'Hidden',
		    'attributes'	=> array(
		        'value'		=> 'success',
		    ),
		));
		$this->add(array(
			'name' => 'price_transport',
		    'attributes'	=> array(
				'class'		=> 'mask_currency form-control',
		    ),
		));
		$disabled = '';
		$status = $sm->get('Admin\Model\ContractTable')->getItem(array('id' => $params['data']['id']), null);
		$permissions = explode(',', $permission['permission_ids']);

		if ($status['status_check_id'] == 'hoan' AND !in_array(ADMIN, $permissions) AND !in_array(SYSTEM, $permissions) ) {
			$disabled = "disabled";
		}

		// Trạng thái
		$this->add(array(
		    'name'			=> 'status_check_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
				'disabled' => $disabled
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "status-check" )), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name')),
		    ),
		));
	}
}