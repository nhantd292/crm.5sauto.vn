<?php
namespace Admin\Form\Search;
use \Zend\Form\Form as Form;

class EventContact extends Form{
    
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
		
		// Keyword
		$this->add(array(
		    'name'			=> 'filter_keyword',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'placeholder'   => 'Từ khóa',
		        'class'			=> 'form-control input-sm',
		        'id'			=> 'filter_keyword',
		    ),
		));
		
		// Status
		$this->add(array(
		    'name'			=> 'filter_status',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Trạng thái -',
		        'value_options'	=> array( 
		            'call'            => 'Gọi điện', 
		            'listen'          => 'Nghe máy',
		            'no_listen'       => 'Không nghe máy',
		            'busy'            => 'Bận',
		            'wrong_number'    => 'Sai số',
		            'sms'             => 'SMS',
		            'mail'            => 'Mail',
		            'agree'           => 'Đồng ý',
		            'confirm'         => 'Xác thực',
		            'ticket'          => 'Nhận vé',
		            'join'            => 'Tham gia',
		            'contract'        => 'đơn hàng',
		        ),
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
		
		// Id
		$this->add(array(
		    'name'			=> 'id',
		    'type'			=> 'Hidden',
		));
	}
}