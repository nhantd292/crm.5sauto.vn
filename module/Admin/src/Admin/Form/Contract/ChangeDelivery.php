<?php
namespace Admin\Form\Contract;
use \Zend\Form\Form as Form;

class ChangeDelivery extends Form {
	
	public function __construct($sm, $params = NULL){
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

        // nhân viên
        $this->add(array(
            'name'			=> 'user_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Nhân viên giục đơn -',
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\UserTable')->listItem(['company_department_id' => 'giuc-don'], array('task' => 'list-user-department')), array('key' => 'id', 'value' => 'name')),
            )
        ));

		
		// Hidden để lưu danh sách id đơn hàng cần chuyển
		$this->add(array(
		    'name'			=> 'contract_ids',
		    'type'			=> 'Hidden',
		));
	}
}