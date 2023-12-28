<?php
namespace Admin\Form;
use \Zend\Form\Form as Form;

class Task extends Form {
	
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
				'id'			=> 'name',
				'placeholder'	=> 'Tên',
			),
		));
		
		//Danh mục công việc
		$this->add(array(
		    'name'			=> 'task_category_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\TaskCategoryTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		//Dự án
		$this->add(array(
		    'name'			=> 'task_project_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\TaskProjectTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		// Giờ thực hiện
		$this->add(array(
		    'name'			=> 'main_hour',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control mask_double',
		        'id'			=> 'name',
		        'placeholder'	=> 'Giờ thực hiện',
		    ),
		));
		
		// Trạng thái công việc
		$this->add(array(
		    'name'			=> 'task_status_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "table" => "document", "where" => array( "code" => "task-status" ), "order" => array("ordering" => "ASC", "name" => "ASC"), "view"  => array( "key" => "id", "value" => "name", "sprintf" => "%s" ) ), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    )
		));
		
		// Ngày thực hiện
		$this->add(array(
		    'name'			=> 'date_begin',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control date-picker',
		        'placeholder'	=> 'dd/mm/yyyy'
		    )
		));
		
		// Ngày kết thúc
		$this->add(array(
		    'name'			=> 'date_end',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control date-picker',
		        'placeholder'	=> 'dd/mm/yyyy'
		    )
		));
		
		// Nội dung
		$this->add(array(
		    'name'			=> 'content',
		    'type'			=> 'Textarea',
		    'attributes'	=> array(
		        'class'           => 'form-control',
		        'id'              => 'sign',
		        'style'           => 'height: 120px;'
		    )
		));
		
	}
}