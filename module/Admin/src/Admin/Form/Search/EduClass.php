<?php
namespace Admin\Form\Search;
use \Zend\Form\Form as Form;

class EduClass extends Form{
    
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
		
		// Keyword
		$this->add(array(
		    'name'			=> 'filter_keyword',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'placeholder'   => 'Từ khóa',
		        'class'			=> 'form-control input-sm',
		        'id'			=> 'filter_keyword',
		    ),
		));
		
		// Bắt đầu từ ngày
		$this->add(array(
		    'name'			=> 'filter_date_begin',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control date-picker',
		        'placeholder'	=> 'Từ ngày',
		        'autocomplete'  => 'off'
		    )
		));
		
		// Ngày kết thúc
		$this->add(array(
		    'name'			=> 'filter_date_end',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control date-picker',
		        'placeholder'	=> 'Đến ngày',
		        'autocomplete'  => 'off'
		    )
		));
		
		// Phân loại ngày tìm kiếm
		$this->add(array(
		    'name'			=> 'filter_date_type',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		        'value'     => 'date'
		    ),
		    'options'		=> array(
		        'value_options'	=> array('public_date' => 'Ngày khai giảng', 'end_date' => 'Ngày kết thúc', 'created' => 'Ngày tạo'),
		    )
		));
		
		// Status
		$this->add(array(
		    'name'			=> 'filter_status',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Trạng thái -',
		        'value_options'	=> array( 1	=> 'Hiển thị', 0 => 'Không hiển thị'),
		    )
		));
		
		// Sản phẩm
		$this->add(array(
		    'name'			=> 'filter_product',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Sản phẩm -',
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		// Địa điểm học
		$this->add(array(
		    'name'			=> 'filter_location',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Địa điểm học -',
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( 'where' => array( 'code' => 'edu-location' ) ), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    )
		));
		
		// Phòng học
		$room = $sm->get('Admin\Model\DocumentTable')->listItem(array( 'where' => array( 'code' => 'edu-room' ) ), array('task' => 'cache'));
		$filter_room = array();
		foreach ($room AS $key => $val) {
		    if($val['document_id'] == $params['filter_location']) {
		         $filter_room[$val['id']] = $val['name'];
		    }
		}
		$this->add(array(
		    'name'			=> 'filter_room',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Phòng học -',
		        'value_options'	=> $filter_room,
		    )
		));
		
		// Ca học
		$this->add(array(
		    'name'			=> 'filter_time',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Ca học -',
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( 'where' => array( 'code' => 'edu-time' ) ), array('task' => 'cache')), array('key' => 'name', 'value' => 'name')),
		    )
		));
		
		// Giảng viên
		$this->add(array(
		    'name'			=> 'filter_teacher',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Giảng viên -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\TeacherTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		// Trợ giảng
		$this->add(array(
		    'name'			=> 'filter_coach',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Trợ giảng -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\CoachTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		// Submit
		$this->add(array(
		    'name'			=> 'filter_submit',
		    'type'			=> 'Submit',
		    'attributes'	=> array(
		        'value'     => 'Tìm',
		        'class'		=> 'btn btn-sm green',
		    ),
		));
		
		// Xóa
		$this->add(array(
		    'name'			=> 'filter_reset',
		    'type'			=> 'Submit',
		    'attributes'	=> array(
		        'value'     => 'Xóa',
		        'class'		=> 'btn btn-sm red',
		    ),
		));
		
		// Order
		$this->add(array(
		    'name'			=> 'order',
		    'type'			=> 'Hidden',
		));
		
		// Order By
		$this->add(array(
		    'name'			=> 'order_by',
		    'type'			=> 'Hidden',
		));
	}
}