<?php
namespace Admin\Form\Production;
use \Zend\Form\Form as Form;

class Edit extends Form {
	
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
		
		// Id
		$this->add(array(
		    'name'			=> 'id',
		    'type'			=> 'Hidden',
		));
		
		// Contact Id
		$this->add(array(
		    'name'			=> 'contact_id',
		    'type'			=> 'Hidden',
		));
		
		// Modal
		$this->add(array(
		    'name'			=> 'modal',
		    'type'			=> 'Hidden',
		    'attributes'	=> array(
		        'value'		=> 'success',
		    ),
		));
		

		// Ngày hoàn thành sản xuất
		$this->add(array(
		    'name'			=> 'production_date',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		    => 'form-control date-picker not-push',
		        'placeholder'	=> 'dd/mm/yyyy',
		        'value'         => date('d/m/Y')
		    )
		));

		// Ghi chú đơn hàng
		$this->add(array(
		    'name'			=> 'production_note',
		    'type'			=> 'Text',
		    'attributes'	=> array(
				'class'		  => 'form-control',
		    )
		));



		// Bộ phận sản xuất
		$production = $sm->get('Admin\Model\ContractTable')->getItem(array('id' => $params['data']['id']));
		if ($production['production_department_type'] == 'da-giao-hang') {
			$disabled = 'disabled';
		} else {
			$disabled = '';
		}
		if($params['data']['is_system_admin']){
            $disabled = '';
        }
		
		$this->add(array(
		    'name'			=> 'production_department_type',
		    'type'			=> 'Select',
		    'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
				'disabled'  => $disabled
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "production-department" )), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name')),
		    ),
		));

		// Nhân viên giao hàng // cũ lấy trong bảng document
//		$this->add(array(
//		    'name'			=> 'shipper_id',
//		    'type'			=> 'Select',
//		    'attributes'	=> array(
//				'class'		=> 'form-control select2 select2_basic',
//		    ),
//		    'options'		=> array(
//		        'empty_option'	=> '- Chọn -',
//		        'disable_inarray_validator' => true,
//		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "shipper" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
//		    ),
//		));

		// Nhân viên giao hàng // mới lấy trong bảng user
		$this->add(array(
		    'name'			=> 'shipper_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-positons-care')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
	}
}