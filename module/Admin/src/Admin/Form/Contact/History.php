<?php
namespace Admin\Form\Contact;
use \Zend\Form\Form as Form;

class History extends Form {
	
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
		
		// Modal
		$this->add(array(
		    'name'			=> 'modal',
		    'type'			=> 'Hidden',
		    'attributes'	=> array(
		        'value'		=> 'success',
		    ),
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
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "sale-history-action" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
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
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "sale-history-result" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
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
		
		// History Time Return 
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
                'value_options'	=> array( 'true' => 'Liên lạc được với khách', 'false' => 'Không liên lạc được với khách'),
            )
        ));

//        $history_type = \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "sale-history-type" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name'));
        $history_type = $sm->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "sale-history-type" )), array('task' => 'cache'));
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
	}
}