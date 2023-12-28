<?php
namespace Admin\Form\Bc;
use \Zend\Form\Form as Form;

class Bill extends Form {
	
	public function __construct($sm){
		parent::__construct();
		
		// FORM Attribute
		$this->setAttributes(array(
			'action'	    => '',
			'method'	    => 'POST',
			'class'		    => 'horizontal-form',
			'role'		    => 'form',
			'name'		    => 'adminForm',
			'id'		    => 'adminForm',
		));
		
		// Modal
		$this->add(array(
		    'name'			=> 'modal',
		    'type'			=> 'Hidden',
		    'attributes'	=> array(
		        'value'     => 'success',
		    )
		));
		
		// Id
		$this->add(array(
		    'name'			=> 'id',
		    'type'			=> 'Hidden',
		));
		
		// Ngày hóa đơn
		$this->add(array(
		    'name'			=> 'date',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		  => 'form-control date-picker',
		        'placeholder' => 'dd/mm/yyyy',
		    )
		));
		
		// Mã hóa đơn
		$this->add(array(
		    'name'			=> 'code',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		  => 'form-control mask_integer',
		    )
		));
		
		// Phân loại
		$this->add(array(
		    'name'			=> 'type',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> array('Thu' => 'Thu', 'Chi' => 'Chi'),
		    ),
		));
		
		// Hình thức hóa đơn
		$this->add(array(
		    'name'			=> 'bill_type_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( 'where' => array( 'code' => 'sale-bill-type' )), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name')),
		    )
		));
		
		// Số tiền thu
		$this->add(array(
		    'name'			=> 'paid_price',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control mask_currency',
		    )
		));
		
		// Số tiền chi
		$this->add(array(
		    'name'			=> 'accrued_price',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control mask_currency',
		    )
		));
		
		// Ghi chú
		$this->add(array(
		    'name'			=> 'note',
		    'type'			=> 'Textarea',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
		    )
		));
	}
}