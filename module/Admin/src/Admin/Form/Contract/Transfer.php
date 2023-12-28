<?php
namespace Admin\Form\Contract;
use \Zend\Form\Form as Form;

class Transfer extends Form {
	
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
			    'disabled'  => 'disabled'
			),
		));
		
		// Giới tính
		$this->add(array(
		    'name'			=> 'sex',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		        'disabled'  => 'disabled'
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
		        'disabled'  => 'disabled'
		    ),
		));
		
		// Ngày sinh
		$this->add(array(
		    'name'			=> 'birthday',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		  => 'form-control date-picker',
		        'placeholder' => 'dd/mm/yyyy',
		        'disabled'    => 'disabled'
		    )
		));
		
		// Tỉnh thành
		$this->add(array(
		    'name'			=> 'location_city_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		        'disabled'  => 'disabled'
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
		        'disabled'            => 'disabled'
		    ),
		));
		
		// Địa chỉ
		$this->add(array(
		    'name'			=> 'address',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
		        'disabled'  => 'disabled'
		    )
		));
		
		// Facebook
		$this->add(array(
		    'name'			=> 'facebook',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
		        'disabled'  => 'disabled'
		    )
		));
		
		// Nguồn khách hàng
		$this->add(array(
		    'name'			=> 'source_group_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		        'disabled'  => 'disabled'
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-source-group')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    )
		));
		
		// Ghi chú
		$this->add(array(
		    'name'			=> 'note',
		    'type'			=> 'Textarea',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
		        'disabled'  => 'disabled'
		    )
		));
		
		// Đối tượng
		$this->add(array(
		    'name'			=> 'subject_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		        'disabled'  => 'disabled'
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "sale-contact-subject" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		// Trường học
		$this->add(array(
		    'name'			=> 'school_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		        'disabled'  => 'disabled'
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "school" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		// Ngành học
		$this->add(array(
		    'name'			=> 'major_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		        'disabled'  => 'disabled'
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "major" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		// Lý do chuyển nhượng
		$this->add(array(
		    'name'			=> 'note_log',
		    'type'			=> 'Textarea',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
		        'disabled'  => 'disabled'
		    )
		));
	}
}