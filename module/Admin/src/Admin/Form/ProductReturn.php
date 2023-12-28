<?php
namespace Admin\Form;
use \Zend\Form\Form as Form;

class ProductReturn extends Form {
	
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
		
		// Id
		$this->add(array(
		    'name'			=> 'id',
		    'type'			=> 'Hidden',
		));

		// Tên combo
		$this->add(array(
		    'name'			=> 'name_year',
		    'type'			=> 'Text',
            'required'		=> true,
		    'attributes'	=> array(
				'class'		=> 'form-control',
		    ),
		));
		
		// Ghi chú
		$this->add(array(
		    'name'			=> 'quantity',
            'type'			=> 'Number',
            'required'		=> true,
		    'attributes'	=> array(
				'class'		  => 'form-control',
		    )
		));
	}
}