<?php
namespace Admin\Form\Contract;
use \Zend\Form\Form as Form;

class Contact extends Form {
	
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
		
		// Phone
		$this->add(array(
		    'name'			=> 'phone',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control mask_phone',
		    ),
		));
		
		// Name
		$this->add(array(
			'name'			=> 'name',
			'type'			=> 'Text',
			'attributes'	=> array(
				'class'		=> 'form-control',
			),
		));
		
		// Sex
		$this->add(array(
		    'name'			=> 'sex',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sex')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name')),
		    )
		));
		
		// Email
		$this->add(array(
		    'name'			=> 'email',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
		    ),
		));
		
		// Ngày sinh
		$this->add(array(
		    'name'			=> 'birthday',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		  => 'form-control date-picker',
		        'placeholder' => 'dd/mm/yyyy',
		    )
		));
		
		// Tỉnh thành
		$this->add(array(
		    'name'			=> 'location_city_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'location-city')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		// Quận huyện
		$this->add(array(
		    'name'			=> 'location_district_id',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		          => 'form-control select2 select2_advance',
		        'value'               => '',
		        'data-table'          => TABLE_DOCUMENT,
		        'data-id'             => 'id',
		        'data-text'           => 'name',
		        'data-parent'         => '',
		        'data-parent-field'   => 'document_id',
		        'data-parent-name'    => 'location_city_id',
		    ),
		));

		// Address
		$this->add(array(
		    'name'			=> 'address',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		        'placeholder'	=> 'Địa chỉ cụ thể'
		    )
		));
		
		// Facebook
		$this->add(array(
		    'name'			=> 'facebook',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
		    )
		));
		
		// Chứng minh thư
		$this->add(array(
		    'name'			=> 'identify',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
		    )
		));
		
		// Ghi chú
		$this->add(array(
		    'name'			=> 'note',
		    'type'			=> 'Textarea',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		    )
		));
	}
}