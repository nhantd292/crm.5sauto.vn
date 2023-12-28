<?php
namespace Admin\Form;
use \Zend\Form\Form as Form;

class EventTest extends Form {
	
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
		
		// Type
		$this->add(array(
		    'name'			=> 'type',
		    'type'			=> 'Hidden',
		    'attributes'	=> array(
		        'value'		=> 'test',
		    ),
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
		
		// public_date
		$this->add(array(
			'name'			=> 'public_date',
			'type'			=> 'Text',
			'attributes'	=> array(
				'class'			=> 'form-control date-picker',
				'placeholder'	=> 'dd/mm/yyyy'
			)
		));
		
		// Company Branch
		$this->add(array(
			'name'			=> 'company_branch_id',
			'type'			=> 'Select',
			'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
			),
			'options'		=> array(
				'empty_option'	=> '- Chọn -',
				'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\CompanyBranchTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
			),
		));
		
		// Giảng viên
		$this->add(array(
			'name'			=> 'teacher_ids',
			'type'			=> 'Select',
			'attributes'	=> array(
				'class'           => 'form-control select2_basic',
				'multiple'        => 'multiple',
			),
			'options'		=> array(
				'empty_option'	=> '- Chọn -',
				'value_options'	=> \ZendX\Functions\CreateArray::createSelect($sm->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'fullname, company_group_name, company_department_name, company_position_name, company_branch_name')),
			),
		));
		
		// Giá tiền dự kiến
		$this->add(array(
			'name'			=> 'price_expected',
			'type'			=> 'Text',
			'attributes'	=> array(
				'class'			=> 'form-control mask_currency',
				'id'			=> 'ordering',
				'placeholder'	=> 'Giá tiền dự kiến'
			)
		));
		
		// Ordering
		$this->add(array(
		    'name'			=> 'ordering',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'value'         => 255,
		        'class'			=> 'form-control',
		        'id'			=> 'ordering',
		        'placeholder'	=> 'Thứ tự'
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
	}
}