<?php
namespace Admin\Form\Contract;
use \Zend\Form\Form as Form;

class ConvertOrder extends Form {
	
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
		
		// Modal
		$this->add(array(
		    'name'			=> 'modal',
		    'type'			=> 'Hidden',
		    'attributes'	=> array(
		        'value'		=> 'success',
		    ),
		));
        $this->add(array(
            'name'			=> 'unit_transport',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Chọn -',
                'disable_inarray_validator' => true,
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "transport-unit" )), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name')),
            ),
        ));

        // Trạng thái
//        $this->add(array(
//            'name'			=> 'ghtk_status',
//            'type'			=> 'Select',
//            'attributes'	=> array(
//                'class'		=> 'form-control select2 select2_basic',
//            ),
//            'options'		=> array(
//                'empty_option'	=> '- Chọn -',
//                'disable_inarray_validator' => true,
//                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "ghtk-status" )), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name')),
//            ),
//        ));
        $this->add(array(
            'name'			=> 'ghtk_status',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'		          => 'form-control select2 select2_advance',
                'value'               => '',
                'data-table'          => TABLE_DOCUMENT,
                'data-id'             => 'alias',
                'data-text'           => 'name',
                'data-parent'         => '',
                'data-parent-field'   => 'code',
            ),
        ));

        $this->add(array(
            'name'			=> 'price_transport',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'		=> 'form-control mask_currency',
            ),
        ));

        $this->add(array(
            'name'			=> 'ghtk_code',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'		=> 'form-control',
            ),
        ));

        // Shipper
        $this->add(array(
		    'name'			=> 'shipper_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "shipper" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
	}
}