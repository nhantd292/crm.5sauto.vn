<?php
namespace Admin\Form\Search;
use \Zend\Form\Form as Form;

class Task extends Form{
    
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
		
		// Danh mục công việc
		$this->add(array(
		    'name'			=> 'filter_task_category',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn danh mục -',
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\TaskCategoryTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    )
		));
		
		// Trạng thái công việc
		$this->add(array(
		    'name'			=> 'filter_task_status',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn trạng thái -',
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( "table" => "document", "where" => array( "code" => "task-status" ), "order" => array("ordering" => "ASC", "name" => "ASC"), "view"  => array( "key" => "id", "value" => "name", "sprintf" => "%s" ) ), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    )
		));
		
		// Ngày thực hiện
		$this->add(array(
		    'name'			=> 'filter_date_begin',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control date-picker',
		        'placeholder'	=> 'Ngày bắt đầu'
		    )
		));
		
		// Ngày kết thúc
		$this->add(array(
		    'name'			=> 'filter_date_end',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control date-picker',
		        'placeholder'	=> 'Ngày kết thúc'
		    )
		));
		
		// Người dùng
		$this->add(array(
		    'name'			=> 'filter_user',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn nhân viên -',
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    )
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
		
		// Tìm kiếm theo năm
		$date = date_create('2016-01-01');
		date_sub($date, date_interval_create_from_date_string(date_format($date, 'N') - 1 . ' days'));
		$start_week = date_format($date, 'd/m/Y');
		date_add($date, date_interval_create_from_date_string('6 days'));
		$end_week = date_format($date, 'd/m/Y');
		$arrYear = array(
				$start_week . '-' . $end_week => 'Tuần 1 '  . '(' .$start_week . ' - ' . $end_week . ')'
		);
		for ($i = 2; $i <= 53; $i++) {
			date_add($date, date_interval_create_from_date_string('1 days'));
			if(date_format($date, 'Y') > 2016) {
				break;
			}
			$start_week = date_format($date, 'd/m/Y');
			date_add($date, date_interval_create_from_date_string('6 days'));
			$end_week = date_format($date, 'd/m/Y');
		
			$arrYear[$start_week . '-' . $end_week] = 'Tuần ' . $i . ' (' .$start_week . ' - ' . $end_week . ')';
		}
		
		// Lấy ngày bắt đầu và kết thúc tuần hiện tại
		$arrYear = array();
		for ($i = 2015; $i <= date('Y') + 2; $i++) {
			$arrYear[$i] = $i;
		}
		$this->add(array(
			'name'			=> 'filter_year',
			'type'			=> 'Select',
			'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
			),
			'options'		=> array(
				'value_options'	=> $arrYear
			)
		));
		
		
		// Select tuần của năm đang chọn
		$this->add(array(
			'name'			=> 'filter_week_year',
			'type'			=> 'Select',
			'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
			),
			'options'		=> array(
				'value_options'	=> array()
			)
		));
		
		// Danh sách view hiển thị
		$this->add(array(
				'name'			=> 'filter_type_list',
				'type'			=> 'Select',
				'attributes'	=> array(
					'class'		=> 'form-control select2 select2_basic',
					'value'		=> 'list',
				),
				'options'		=> array(
					'value_options'	=> array('list' => 'Danh sách', 'week' => 'Tuần'),
				)
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
	}
}