<?php
namespace Admin\Form;
use \Zend\Form\Form as Form;

class Teacher extends Form {
	
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
		    'enctype'   => 'multipart/form-data'
		));
		
		// Id
		$this->add(array(
		    'name'			=> 'id',
		    'type'			=> 'Hidden',
		));
		
		// name
		$this->add(array(
		    'name'			=> 'name',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		        'id'			=> 'name',
		        'placeholder'	=> 'Họ và tên',
		    ),
		));
		
		// Username
		$this->add(array(
			'name'			=> 'username',
			'type'			=> 'Text',
			'attributes'	=> array(
				'class'			=> 'form-control',
				'id'			=> 'username',
				'placeholder'	=> 'Tên đăng nhập',
			),
		));
		
		// Mã nhân viên
		$this->add(array(
			'name'			=> 'code',
			'type'			=> 'Text',
			'attributes'	=> array(
				'class'			=> 'form-control',
			),
		));
		
		// Email
		$this->add(array(
		    'name'			=> 'email',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		        'id'			=> 'email',
		        'placeholder'	=> 'Email',
		    ),
		));
		
		// Phone
		$this->add(array(
		    'name'			=> 'phone',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control mask_number',
		        'id'			=> 'phone',
		        'placeholder'	=> 'Điện thoại',
		    ),
		));
		
		// Password
		$this->add(array(
		    'name'			=> 'password',
		    'type'			=> 'Password',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		        'id'			=> 'password',
		        'placeholder'	=> 'Mật khẩu',
		    ),
		));
		
		// Nhóm quyền truy cập
		$this->add(array(
		    'name'			=> 'permission_ids',
		    'type'			=> 'MultiCheckbox',
		    'options'		=> array(
		        'label_attributes' => array(
		            'class'		=> 'checkbox-inline',
		        ),
		        'value_options'	=> array('teacher' => 'Giáo viên'),
		    ),
		));
		
		// Cơ sở làm việc
		$this->add(array(
		    'name'			=> 'company_branch_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'company-branch')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		// Phòng ban
		$this->add(array(
		    'name'			=> 'company_department_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'company-department')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		// Vị trí chức vụ
		$this->add(array(
		    'name'			=> 'company_position_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> array('1534219500200828a7to4w' => 'Giáo viên'),
		    ),
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
		        'value_options'	=> array( 1	=> 'Hiển thị', 0 => 'Không hiển thị'),
		    )
		));
		
		// Password Status
		$this->add(array(
			'name'			=> 'password_status',
			'type'			=> 'Select',
			'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
			    'value'     => 1,
			),
		    'options'		=> array(
		        'value_options'	=> array( 1	=> 'Có', 0 => 'Không'),
		    )
		));
	}
}