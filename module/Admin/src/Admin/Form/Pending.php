<?php
namespace Admin\Form;
use \Zend\Form\Form as Form;

class Pending extends Form {
	
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
		
		// Id
		$this->add(array(
		    'name'			=> 'id',
		    'type'			=> 'Hidden',
		));
		
		// Product type
		$this->add(array(
		    'name'			=> 'product_type',
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
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sex')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name')),
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
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'location-city')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
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
		        'data-parent-name'    => 'document_id',
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
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-source-group')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
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
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "sale-contact-subject" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
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
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "school" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
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
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "major" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		// Ngày đơn hàng
		$this->add(array(
		    'name'			=> 'date',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		    => 'form-control date-picker not-push',
		        'placeholder'	=> 'dd/mm/yyyy',
		        'value'         => date('d/m/Y')
		    )
		));
		
		// Sản phẩm
		$this->add(array(
		    'name'			=> 'product_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic not-push',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		// Lớp học
		$this->add(array(
		    'name'			=> 'edu_class_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\EduClassTable')->listItem(array('status' => 1), array('task' => 'list-all')), array('key' => 'id', 'value' => 'name,student_total,student_max', 'sprintf' => '%s (%s/%s)')),
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
		
		// Hưởng ưu đãi
		$this->add(array(
		    'name'			=> 'price_promotion',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		  => 'form-control mask_currency',
		        'value'       => 0,
		        'data-value'  => 0,
		        'readonly'    => 'readonly',
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
		
		// Lý do giảm giá
		$this->add(array(
		    'name'			=> 'promotion_content',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		  => 'form-control',
		    )
		));
		
		// Điểm test đầu vào ielts - Nghe
		$this->add(array(
		    'name'			=> 'test_ielts_listen',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		  => 'form-control',
		    )
		));

		// Điểm test đầu vào ielts - Nói
		$this->add(array(
		    'name'			=> 'test_ielts_speak',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		  => 'form-control',
		    )
		));

		// Điểm test đầu vào ielts - Đọc
		$this->add(array(
		    'name'			=> 'test_ielts_read',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		  => 'form-control',
		    )
		));

		// Điểm test đầu vào ielts - Viết
		$this->add(array(
		    'name'			=> 'test_ielts_write',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		  => 'form-control',
		    )
		));
		
		// Ghi chú đơn hàng
		$this->add(array(
		    'name'			=> 'contract_note',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		  => 'form-control',
		    )
		));
	}
}