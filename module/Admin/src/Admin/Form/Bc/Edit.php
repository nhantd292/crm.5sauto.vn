<?php
namespace Admin\Form\Bc;
use \Zend\Form\Form as Form;

class Edit extends Form {
	
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
		
		// Ngày đơn hàng
		$this->add(array(
		    'name'			=> 'date',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		    => 'form-control date-picker',
		        'placeholder'	=> 'dd/mm/yyyy',
		    )
		));
		
		// Ngày đăng ký thi
		$this->add(array(
		    'name'			=> 'date_register',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		    => 'form-control date-picker',
		        'placeholder'	=> 'dd/mm/yyyy',
		    )
		));
		
		// Ngày thi speaking
		$this->add(array(
		    'name'			=> 'date_speaking',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		    => 'form-control date-picker',
		        'placeholder'	=> 'dd/mm/yyyy',
		    )
		));
		
		// Đơn giá
		$this->add(array(
		    'name'			=> 'price',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		  => 'form-control mask_currency',
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