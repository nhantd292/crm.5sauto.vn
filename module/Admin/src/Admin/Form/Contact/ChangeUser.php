<?php
namespace Admin\Form\Contact;
use \Zend\Form\Form as Form;

class ChangeUser extends Form {
	
	public function __construct($sm, $params = NULL){
		parent::__construct();
		
		$userInfo = new \ZendX\System\UserInfo();
		$userInfo = $userInfo->getUserInfo();

//		echo '<pre>';
//		print_r($params);
//		echo '</pre>';
		
		// FORM Attribute
		$this->setAttributes(array(
			'action'	    => '',
			'method'	    => 'POST',
			'class'		    => 'horizontal-form',
			'role'		    => 'form',
			'name'		    => 'adminForm',
			'id'		    => 'adminForm',
		));

        $this->add(array(
            'name'			=> 'sale_branch_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Chọn -',
                'disable_inarray_validator' => true,
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
            ),
        ));

        // Company Group
        $this->add(array(
            'name'			=> 'sale_group_id',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'		          => 'form-control select2 select2_advance',
                'value'               => '',
                'data-table'          => TABLE_DOCUMENT,
                'data-id'             => 'id',
                'data-text'           => 'name,content',
                'data-parent'         => '',
                'data-parent-field'   => 'document_id',
                'data-where_type'     => 'sales',
            ),
        ));

        // Company Group
        $this->add(array(
            'name'			=> 'user_id',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'		          => 'form-control select2 select2_advance',
                'value'               => '',
                'data-table'          => TABLE_USER,
                'data-id'             => 'id',
                'data-text'           => 'name,code',
                'data-parent'         => '',
                'data-parent-field'   => 'sale_group_id',
            ),
        ));

//        // nhân viên
//        $user = $sm->get('Admin\Model\UserTable')->listItem($params, array('task' => 'list-user-department'));
//        $user_data = array();
//        if(!empty($params['filter_sale_group'])) {
//            foreach ($user AS $key => $val) {
//                if($val['sale_group_id'] == $params['filter_sale_group']) {
//                    $user_data[] = $val;
//                }
//            }
//        }
//        $this->add(array(
//            'name'			=> 'user_id',
//            'type'			=> 'Select',
//            'attributes'	=> array(
//                'class'		=> 'form-control select2 select2_basic',
//            ),
//            'options'		=> array(
//                'empty_option'	=> '- Nhân viên -',
//                'value_options'	=> \ZendX\Functions\CreateArray::create($user_data, array('key' => 'id', 'value' => 'name')),
//            )
//        ));
		
		// Người quản lý mới
//		$sale_group_id = $userInfo['sale_group_id'];
//		$sale_group_ids = !empty($userInfo['sale_group_ids']) ? explode(',', $userInfo['sale_group_ids']) : null;
//		$user = $sm->get('Admin\Model\UserTable')->listItem($params, array('task' => 'list-item-by-group'));
//		$sale_group = $sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
//		$sale_branch = $sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
//		$company_department = $sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'company-department')), array('task' => 'cache-alias'));
//		$user_data = array();
//	    foreach ($user AS $key => $val) {
//	        $data_val = array(
//	            'id' => $val['id'],
//	            'username' => $val['username'],
//	            'name' => $val['name'],
//	            'company_department_name' => $company_department[$val['company_department_id']]['name'],
//	            'sale_group_name' => $sale_group[$val['sale_group_id']]['name'],
//	            'sale_branch_name' => $sale_branch[$val['sale_branch_id']]['name'],
//	        );
//	        if(!empty($userInfo['sale_branch_id'])) {
//	            if($val['sale_branch_id'] == $userInfo['sale_branch_id']) {
//	                if(!empty($userInfo['sale_group_id'])) {
//	                    if($val['sale_group_id'] == $userInfo['sale_group_id']) {
//	                        $user_data[] = $data_val;
//	                    }
//	                } else {
//	                    $user_data[] = $data_val;
//	                }
//	            }
//	        } else {
//	            $user_data[] = $data_val;
//	        }
//	    }
//
//		$this->add(array(
//		    'name'			=> 'user_id',
//		    'type'			=> 'Select',
//		    'attributes'	=> array(
//		        'class'     => 'form-control select2_basic',
//		    ),
//		    'options'		=> array(
//		        'empty_option'	=> '- Chọn -',
//		        'disable_inarray_validator' => true,
//		        'value_options'	=> \ZendX\Functions\CreateArray::createSelect($user_data, array('key' => 'id', 'value' => 'username, name, company_department_name, sale_group_name, sale_branch_name')),
//		    ),
//		));
		
		// Hidden để lưu danh sách id của liên hệ cần chuyển
		$this->add(array(
		    'name'			=> 'contact_ids',
		    'type'			=> 'Hidden',
		));
	}
}