<?php
namespace Admin\Form\Check;
use \Zend\Form\Form as Form;

class RefundContract extends Form {
	
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

        // Nội dung chăm sóc
        $this->add(array(
            'name'			=> 'history_content',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'			=> 'form-control',
                'placeholder'	=> 'Ghi chú hoàn đơn'
            )
        ));
	}
}