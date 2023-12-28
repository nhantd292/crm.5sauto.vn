<?php
namespace Admin\Form\Bc;
use \Zend\Form\Form as Form;

class EditNote extends Form {
	
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
		
		// Ghi chú
		$this->add(array(
		    'name'			=> 'contract_note',
		    'type'			=> 'Textarea',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
		    )
		));
	}
}