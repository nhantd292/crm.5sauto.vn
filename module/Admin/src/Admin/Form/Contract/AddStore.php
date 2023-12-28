<?php
namespace Admin\Form\Contract;
use \Zend\Form\Form as Form;

class AddStore extends Form {
	
	public function __construct($sm, $params){
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
			'enctype'		=> 'multipart/form-data'
		));
		
		// Id
		$this->add(array(
		    'name'			=> 'id',
		    'type'			=> 'Hidden',
		));

        // Phone
        $this->add(array(
            'name'			=> 'phone',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'		=> 'form-control mask_phone',
            ),
        ));

        // Submit
        $this->add(array(
            'name'			=> 'submit',
            'type'			=> 'Submit',
            'attributes'	=> array(
                'value'     => 'Tìm kiếm',
                'class'		=> 'btn btn-sm green',
                'style'     => 'border: 1px solid #35aa47;'
            ),
        ));
	}
}