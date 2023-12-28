<?php
namespace Admin\Form\Contract;
use \Zend\Form\Form as Form;

class ShippingFee extends Form {
	
	public function __construct($sm, $params){
		parent::__construct();
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
		
		// Phí ships khách hàng hỗ trợ khi không nhận hàng
		$this->add(array(
		    'name'			=> 'shipping_fee',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control mask_currency',
		    )
		));
	}
}