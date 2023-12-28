<?php
namespace Admin\Form;
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
		        'class'		     => 'form-control mask_phone',
		    ),
		));

		// Biển số xe
		$this->add(array(
		    'name'			=> 'license_plate',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		     => 'form-control',
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
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sex')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name')),
		    )
		));

		$this->add(array(
		    'name'			=> 'contact_group',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'contact-group')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    )
		));

        // Nhóm sản phẩm quan tâm
        $this->add(array(
            'name'			=> 'product_group_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Sản phẩm quan tâm -',
                'disable_inarray_validator' => true,
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-group')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
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
		
		// Birthday Year
		$birthday_year = array();
		for ($i = date('Y') - 10; $i >= 1950; $i--) {
		    $birthday_year[$i] = $i;
		}
		$this->add(array(
		    'name'			=> 'birthday_year',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'  => '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> $birthday_year,
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
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache')), array('key' => 'code', 'value' => 'name')),
		    ),
		));

        // Quận huyện
		$this->add(array(
		    'name'			=> 'location_district_id',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		          => 'form-control select2 select2_advance',
		        'value'               => '',
		        'data-table'          => TABLE_LOCATIONS,
		        'data-id'             => 'code',
		        'data-text'           => 'name',
		        'data-parent'         => '',
		        'data-parent-field'   => 'parent',
		        'data-parent-name'    => 'parent',
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
		        'class'			=> 'form-control',
		    )
		));
		
		// Chứng minh thư
		$this->add(array(
		    'name'			=> 'identify',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		    )
		));
		
		// Nguồn khách hàng
		$this->add(array(
		    'name'			=> 'source_group_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-source-group')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    )
		));
		
		// Nguồn biết đến
		$this->add(array(
		    'name'			=> 'source_known_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-source-known')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    )
		));
		
		// Tên xe - năm sản xuất
		$this->add(array(
		    'name'			=> 'note',
		    'type'			=> 'Textarea',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
                'rows'      => 5,
		    )
		));

		// Nội dung cần tư vấn
		$this->add(array(
		    'name'			=> 'content',
		    'type'			=> 'Textarea',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
                'rows'      => 5,
		    )
		));

		// Nghề nghiệp
		$this->add(array(
		    'name'			=> 'job',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
		    )
		));
		
		// Phân loại khách hàng
		$this->add(array(
		    'name'			=> 'type',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'  => '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "sale-contact-type" )), array('task' => 'cache-public')), array('key' => 'alias', 'value' => 'name')),
		    )
		));
		
		// History Result - Lý do mất
		$this->add(array(
		    'name'			=> 'lost_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'  => '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "sale-lost" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    )
		));
		
		// Sản phẩm quan tâm
		$this->add(array(
		    'name'			=> 'product_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "product-interest" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		// Ngày hẹn test/đăng ký
		$this->add(array(
		    'name'			=> 'date_return',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control date-picker',
		    )
		));
		
		// Đối tượng
		$this->add(array(
		    'name'			=> 'subject_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "sale-contact-subject" )), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name')),
		    ),
		));
		
		// Trường học
		$this->add(array(
		    'name'			=> 'school_name',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "school" )), array('task' => 'cache')), array('key' => 'name', 'value' => 'name')),
		    ),
		));
		
		// Ngành học
		$this->add(array(
		    'name'			=> 'major_name',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "major" )), array('task' => 'cache')), array('key' => 'name', 'value' => 'name')),
		    ),
		));
		
		// Lớp học
		$this->add(array(
		    'name'			=> 'class_name',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "class" )), array('task' => 'cache')), array('key' => 'name', 'value' => 'name')),
		    ),
		));
		
		// Điểm test
		$this->add(array(
		    'name'			=> 'test_score',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		  => 'form-control',
		    )
		));
		
		// History Action - Hành động
		$this->add(array(
		    'name'			=> 'history_action_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'  => '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "sale-history-action" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    )
		));
		
		// History Result - Kết quả
		$this->add(array(
		    'name'			=> 'history_result_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'  => '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "sale-history-result" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    )
		));

		// History Result - Phân loại
        $history_type = $sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "sale-history-type" )), array('task' => 'cache'));
        $option_history_list = [];
        foreach ($history_type as $item) {
            $option_history_list[] = array(
                'attributes'   => array('data-code' => $item['alias']),
                'value'        => $item['id'],
                'label'        => $item['name']
            );
        }
        // History Result - Phân loại
        $this->add(array(
            'name'			=> 'history_type_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'  => '- Chọn -',
                'disable_inarray_validator' => true,
                'value_options'	=> $option_history_list,
            )
        ));

        // Doanh Doanh số tạm tính
        $this->add(array(
            'name'			=> 'sales_expected',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'		  => 'form-control mask_currency',
            )
        ));
		
		// History Content - Nội dung/Ghi chú
		$this->add(array(
		    'name'			=> 'history_content',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		    )
		));
		
		// History Time Return - Hẹn ngày chăm sóc lại
		$this->add(array(
		    'name'			=> 'history_return',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control date-picker',
		        'placeholder'	=> 'dd/mm/yyyy'
		    )
		));

        // Trạng thái chăm sóc
        $this->add(array(
            'name'			=> 'history_success',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Trạng thái chăm sóc -',
                'value_options'	=> array( 'true' => 'Liên lạc được với khách', 'false' => 'Không liên lạc được khách'),
            )
        ));

        // Trạng thái chăm sóc
        $this->add(array(
            'name'			=> 'marketer_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Nhân viên MKT -',
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\UserTable')->listItem(array('company_department_id' => 'marketing'), array('task' => 'list-user-department')), array('key' => 'id', 'value' => 'name')),
            )
        ));
	}
}