<?php
namespace Admin\Form\Contract;
use \Zend\Form\Form as Form;

class EditPromotion extends Form {
	
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
		
		// Contact Id
		$this->add(array(
		    'name'			=> 'contact_id',
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
		
		// Đơn giá
		$this->add(array(
		    'name'			=> 'price',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		  => 'form-control mask_currency',
		        'readonly'    => 'readonly'
		    )
		));
		
		// Giảm theo %
		$this->add(array(
		    'name'			=> 'price_promotion_percent',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		  => 'form-control mask_double',
		    )
		));
		
		// Giảm theo giá
		$this->add(array(
		    'name'			=> 'price_promotion_price',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		  => 'form-control mask_currency',
		    )
		));
		
		// Lý do giảm giá
		$this->add(array(
		    'name'			=> 'promotion_content',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		  => 'form-control',
		    )
		));
		
		// Lý do sửa
		$this->add(array(
		    'name'			=> 'note_log',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		  => 'form-control',
		    )
		));
	}
}