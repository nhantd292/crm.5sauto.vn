<?php
namespace Admin\Form;
use \Zend\Form\Form as Form;

class EduClass extends Form {
	
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
		
		// Name
		$this->add(array(
			'name'			=> 'name',
			'type'			=> 'Text',
			'attributes'	=> array(
				'class'			=> 'form-control',
			),
		));
		
		// Khóa học
		$this->add(array(
		    'name'			=> 'product_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		// Địa điểm học
		$this->add(array(
		    'name'			=> 'location_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( 'where' => array( 'code' => 'edu-location' ) ), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    )
		));
		
		//Phòng học
		$this->add(array(
		    'name'			=> 'room_id',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		          => 'form-control select2 select2_advance',
		        'value'               => '',
		        'data-table'          => TABLE_DOCUMENT,
		        'data-id'             => 'id',
		        'data-text'           => 'name',
		        'data-parent'         => '',
		        'data-parent-field'   => 'document_id',
		    ),
		));
		
		// Ngày khai giảng
		$this->add(array(
		    'name'			=> 'public_date',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control date-picker',
		        'placeholder'	=> 'dd/mm/yyyy'
		    )
		));
		
		// Số học sinh
		$this->add(array(
		    'name'			=> 'student_max',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control mask_number',
		    )
		));
		
		// Ca học
		$this->add(array(
		    'name'			=> 'time',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( 'where' => array( 'code' => 'edu-time' ) ), array('task' => 'cache')), array('key' => 'name', 'value' => 'name')),
		    )
		));
		
		// Giảng viên
		$this->add(array(
		    'name'			=> 'teacher_ids',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		        'multiple'  => 'multiple',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\TeacherTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		// Trợ giảng
		$this->add(array(
		    'name'			=> 'coach_ids',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		        'multiple'  => 'multiple',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\CoachTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		// Ghi chú
		$this->add(array(
		    'name'			=> 'note',
		    'type'			=> 'Textarea',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
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
		        'value_options'	=> array( 1	=> 'Hiển thị', 0 => 'Không hiển thị'),
		    )
		));
		
		// Lịch học
		$this->add(array(
		    'name'			=> 'schedule',
		    'type'			=> 'MultiCheckbox',
		    'options'		=> array(
		        'label_attributes' => array(
		            'class'		=> 'checkbox-inline',
		        ),
		        'value_options'	=> array('T2' => 'Thứ 2', 'T3' => 'Thứ 3', 'T4' => 'Thứ 4', 'T5' => 'Thứ 5', 'T6' => 'Thứ 6', 'T7' => 'Thứ 7', 'CN' => 'Chủ nhật'),
		    ),
		));
		
		// Tình trạng tuyển sinh
		$this->add(array(
		    'name'			=> 'public_status',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option' => '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( 'where' => array( 'code' => 'edu-status' ) ), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name')),
		    )
		));
		
		// Số buổi
		$this->add(array(
		    'name'			=> 'sessions',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control mask_number',
		        'id'			=> 'sessions',
		        'placeholder'	=> 'Số buổi'
		    )
		));
	}
}