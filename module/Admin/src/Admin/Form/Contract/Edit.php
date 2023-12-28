<?php
namespace Admin\Form\Contract;
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

		// Tiền đặt cọc
		$this->add(array(
		    'name'			=> 'price_deposits',
		    'type'			=> 'Text',
		    'attributes'	=> array(
				'class'		  => 'form-control mask_currency',
				'disabled'      => 'disabled'
		    )
		));
		
		// Thành tiền
		$this->add(array(
		    'name'			=> 'price_total',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		  => 'form-control text-danger mask_currency',
		        'value'       => 0,
		        'data-value'  => 0,
		        'readonly'    => 'readonly',
		    )
		));
		
		// Ghi chú sales
		$this->add(array(
		    'name'			=> 'sale_note',
		    'type'			=> 'Text',
		    'attributes'	=> array(
				'class'		  => 'form-control',
		    )
		));

		// Vận chuyển
		$this->add(array(
		    'name'			=> 'transport_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "transport" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));

		// Bảo hành
		$this->add(array(
		    'name'			=> 'status_guarantee_id',
		    'type'			=> 'Checkbox',
		    'options'		=> array(
				'label_attributes' => array(
		            'class'		=> 'checkbox-inline',
				),
			)
		));

		// Mã cũ
		$this->add(array(
		    'name'			=> 'code_old',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
		    ),
		));

		// Loại đơn sản xuất
		$this->add(array(
		    'name'			=> 'production_type_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "production-type" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));

		// tên ng nhận
		$this->add(array(
		    'name'			=> 'name_received',
		    'type'			=> 'Text',
		    'attributes'	=> array(
				'class'		=> 'form-control',
		    ),
		));

		// sđt ng nhận
		$this->add(array(
		    'name'			=> 'phone_received',
		    'type'			=> 'Text',
		    'attributes'	=> array(
				'class'		=> 'form-control',
		    ),
		));

		// Địa chỉ nhận hàng
		$this->add(array(
		    'name'			=> 'address_received',
		    'type'			=> 'Text',
		    'attributes'	=> array(
				'class'		=> 'form-control',
		    ),
		));
	}
}