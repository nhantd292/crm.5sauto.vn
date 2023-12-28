<?php
namespace Admin\Form\Contract;
use \Zend\Form\Form as Form;

class Reserve extends Form {
	
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
		
		// Contract Id
		$this->add(array(
		    'name'			=> 'contract_id',
		    'type'			=> 'Hidden',
		));
		
		// Số buổi đã học
		$this->add(array(
		    'name'			=> 'reserve_sessions',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control mask_number',
		        'maxlength' => 2
		    ),
		));
		
		// Bảo lưu từ ngày
		$this->add(array(
		    'name'			=> 'reserve_date_begin',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		    => 'form-control date-picker',
		        'placeholder'	=> 'dd/mm/yyyy',
		        'value'         => date('d/m/Y'),
		        'data-value'    => date('d/m/Y'),
		    )
		));
		
		// Bảo lưu đến ngày
		$this->add(array(
		    'name'			=> 'reserve_date_end',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		    => 'form-control date-picker',
		        'placeholder'	=> 'dd/mm/yyyy',
		    )
		));
		
		// Content
		$this->add(array(
		    'name'			=> 'content',
		    'type'			=> 'Textarea',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
		        'style'     => 'height: 60px;'
		    )
		));
		
		// Cơ sở thu chi
		$this->add(array(
		    'name'			=> 'branch_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\CompanyBranchTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		// Ngày hóa đơn
		$this->add(array(
		    'name'			=> 'bill_date',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		    => 'form-control date-picker',
		        'placeholder'	=> 'dd/mm/yyyy',
		        'value'         => date('d/m/Y'),
		        'data-value'    => date('d/m/Y'),
		    )
		));
		
		// Số phiếu thu
		$this->add(array(
		    'name'			=> 'bill_paid_number',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
		    )
		));
		
		// Số tiền thu
		$this->add(array(
		    'name'			=> 'bill_paid_price',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control mask_currency',
		    )
		));
		
		// Phân loại phiếu thu
		$this->add(array(
		    'name'			=> 'bill_paid_type_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( "table" => "document", "where" => array( "code" => "bill-type-paid", "alias" => "surcharge" ), "order" => array("ordering" => "ASC", "created" => "ASC", "name" => "ASC"), "view"  => array( "key" => "id", "value" => "name", "sprintf" => "%s" ) ), array('task' => 'list-all')), array('key' => 'alias', 'value' => 'name')),
		    )
		));
		
		// Phân loại phụ phí
		$this->add(array(
		    'name'			=> 'bill_surcharge_type_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( "table" => "document", "where" => array( "code" => "bill-type-surcharge", "alias" => "reserve" ), "order" => array("ordering" => "ASC", "created" => "ASC", "name" => "ASC"), "view"  => array( "key" => "id", "value" => "name", "sprintf" => "%s" ) ), array('task' => 'list-all')), array('key' => 'id', 'value' => 'name')),
		    )
		));
		
		// Nội dung
		$this->add(array(
		    'name'			=> 'bill_content',
		    'type'			=> 'Textarea',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
		        'style'     => 'height: 50px;'
		    )
		));
	}
}