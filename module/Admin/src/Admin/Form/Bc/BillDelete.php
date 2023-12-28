<?php
namespace Admin\Form\Bc;
use \Zend\Form\Form as Form;

class BillDelete extends Form {
	
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
		
		// Type
		$this->add(array(
		    'name'			=> 'type',
		    'type'			=> 'Hidden',
		));
		
		// Lý do
		$this->add(array(
		    'name'			=> 'note_log',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
		    )
		));
	}
}