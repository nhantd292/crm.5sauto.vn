<?php
namespace Admin\Form\FormData;
use \Zend\Form\Form as Form;

class Import2 extends Form {
	
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
			'enctype'		=> 'multipart/form-data'
		));
		
		// Id
		$this->add(array(
		    'name'			=> 'id',
		    'type'			=> 'Hidden',
		));
		
		// File import
		$this->add(array(
			'name'			=> 'file_import',
			'type'			=> 'File',
			'attributes'	=> array(
				'class'		=> 'form-control',
			),
		));

        // kênh biết đến
        $this->add(array(
            'name'			=> 'marketing_channel_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Chọn -',
                'disable_inarray_validator' => true,
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'marketing-channel')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
            )
        ));
		
		// Submit
		$this->add(array(
		    'name'			=> 'submit',
		    'type'			=> 'Submit',
		    'attributes'	=> array(
		        'value'     => 'Bắt đầu import',
		        'class'		=> 'btn btn-sm green',
		        'style'     => 'border: 1px solid #35aa47;'
		    ),
		));
	}
}