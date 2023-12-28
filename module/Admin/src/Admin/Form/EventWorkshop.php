<?php
namespace Admin\Form;
use \Zend\Form\Form as Form;

class EventWorkshop extends Form {
	
	public function __construct($sm){
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
		
		// Type
		$this->add(array(
		    'name'			=> 'type',
		    'type'			=> 'Hidden',
		    'attributes'	=> array(
		        'value'		=> 'workshop',
		    ),
		));
		
		// Name
		$this->add(array(
			'name'			=> 'name',
			'type'			=> 'Text',
			'attributes'	=> array(
				'class'			=> 'form-control',
				'id'			=> 'name',
				'placeholder'	=> 'Tên',
			),
		));
		
		// public_date
		$this->add(array(
			'name'			=> 'public_date',
			'type'			=> 'Text',
			'attributes'	=> array(
				'class'			=> 'form-control date-picker',
				'placeholder'	=> 'dd/mm/yyyy'
			)
		));
		
		// Company Branch
		$this->add(array(
			'name'			=> 'location',
			'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		    	'placeholder'	=> 'Địa điểm diễn ra sự kiện'
		    ),
		));
		
		// Thời gian
		$this->add(array(
			'name'			=> 'time',
			'type'			=> 'Select',
			'attributes'	=> array(
				'class'           => 'form-control select2 select2_basic',
				'placeholder'     => 'Thời gian',
				'multiple'        => 'multiple'
			),
			'options'		=> array(
				'value_options'	=> array( 'Sáng' => 'Sáng', 'Chiều' => 'Chiều', 'Tối' => 'Tối', 'Cả ngày' => 'Cả Ngày'),
			)
		));
		
		// Giá tiền dự kiến
		$this->add(array(
			'name'			=> 'price_expected',
			'type'			=> 'Text',
			'attributes'	=> array(
				'class'			=> 'form-control mask_currency',
			)
		));
		
		// Diễn giả
		$this->add(array(
			'name'			=> 'speakers',
			'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		    ),
		));
		
		// Chương trình con
		$this->add(array(
			'name'			=> 'options',
			'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		    ),
		));
		
		// Ordering
		$this->add(array(
		    'name'			=> 'ordering',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'value'         => 255,
		        'class'			=> 'form-control',
		    )
		));
		
		// Status
		$this->add(array(
			'name'			=> 'status',
			'type'			=> 'Select',
			'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
			    'value'     => 1,
			),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'value_options'	=> array( 1	=> 'Hiển thị', 0 => 'Không hiển thị'),
		    )
		));
	}
}