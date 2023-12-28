<?php
namespace Admin\Form\FormData;
use \Zend\Form\Form as Form;

class Event extends Form {
	
	public function __construct($sm){
		parent::__construct();
		
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
		
		// Form
		$this->add(array(
		    'name'			=> 'filter_form',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		    	'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\FormTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		// Event
		$this->add(array(
		    'name'			=> 'filter_event',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		    	'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\EventTable')->listItem(array('ssFilter' => array('filter_status' => 1), 'data' => array('public' => true)), array('task' => 'list-all', 'type' => 'workshop')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		// Submit
		$this->add(array(
		    'name'			=> 'submit',
		    'type'			=> 'Submit',
		    'attributes'	=> array(
		        'value'     => 'Bắt đầu chuyển',
		        'class'		=> 'btn btn-sm green',
		        'style'     => 'border: 1px solid #35aa47;'
		    ),
		));
	}
}