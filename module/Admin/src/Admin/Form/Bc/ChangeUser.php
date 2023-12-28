<?php
namespace Admin\Form\Contract;
use \Zend\Form\Form as Form;

class ChangeUser extends Form {
	
	public function __construct($sm, $params = null){
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
		));
		
		// Người quản lý mới
		$sale_group_id = $userInfo['sale_group_id'];
		$sale_group_ids = !empty($userInfo['sale_group_ids']) ? explode(',', $userInfo['sale_group_ids']) : null;
		$user = $sm->get('Admin\Model\UserTable')->listItem($params, array('task' => 'cache'));
		$sale_group = $sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
		$sale_branch = $sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
		$company_department = $sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'company-department')), array('task' => 'cache'));
		$user_data = array();
	    foreach ($user AS $key => $val) {
	        $data_val = array(
	            'id' => $val['id'],
	            'username' => $val['username'],
	            'name' => $val['name'],
	            'company_department_name' => $company_department[$val['company_department_id']]['name'],
	            'sale_group_name' => $sale_group[$val['sale_group_id']]['name'],
	            'sale_branch_name' => $sale_branch[$val['sale_branch_id']]['name'],
	        );
	        if(!empty($userInfo['sale_branch_id'])) {
	            if($val['sale_branch_id'] == $userInfo['sale_branch_id']) {
	                if(!empty($userInfo['sale_group_id'])) {
	                    if($val['sale_group_id'] == $userInfo['sale_group_id']) {
	                        $user_data[] = $data_val;
	                    }
	                } else {
	                    $user_data[] = $data_val;
	                }
	            }
	        } else {
	            $user_data[] = $data_val;
	        }
	    }
		$this->add(array(
		    'name'			=> 'user_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'     => 'form-control select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::createSelect($user_data, array('key' => 'id', 'value' => 'username, name, company_department_name, sale_group_name, sale_branch_name')),
		    ),
		));
		
		// Chuyển toàn bộ hóa đơn của đơn hàng
		$this->add(array(
		    'name'			=> 'transfer_bill',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'     => 'form-control select2_basic',
		    ),
		    'options'		=> array(
		        'disable_inarray_validator' => true,
		        'value_options'	=> array('yes' => 'Có', 'no' => 'Không'),
		    ),
		));
		
		// Chuyển quyền quản lý của liên hệ
		$this->add(array(
		    'name'			=> 'transfer_contact',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'     => 'form-control select2_basic',
		    ),
		    'options'		=> array(
		        'disable_inarray_validator' => true,
		        'value_options'	=> array('yes' => 'Có', 'no' => 'Không'),
		    ),
		));
	}
}