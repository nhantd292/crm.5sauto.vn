<?php
namespace Admin\Form;
use \Zend\Form\Form as Form;

class PermissionList extends Form {
	
	public function __construct(){
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
		    ),
		));

		// desc
		$this->add(array(
		    'name'			=> 'desc',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		        'id'			=> 'desc',
		    ),
		));
		
		// Module
		$this->add(array(
		    'name'			=> 'module',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		        'id'			=> 'module',
		    ),
		));
		
		// Controller
		$this->add(array(
		    'name'			=> 'controller',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		        'id'			=> 'controller',
		    ),
		));
		
		// Action
		$this->add(array(
		    'name'			=> 'action',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		        'id'			=> 'action',
		    ),
		));
		
		// Ordering
		$this->add(array(
		    'name'			=> 'ordering',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'value'         => 255,
		        'class'			=> 'form-control',
		        'id'			=> 'ordering',
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
		        'empty_option'	=> '- Chọn -',
		        'value_options'	=> array( 1	=> 'Hiển thị', 0 => 'Không hiển thị'),
		    )
		));
	}
}