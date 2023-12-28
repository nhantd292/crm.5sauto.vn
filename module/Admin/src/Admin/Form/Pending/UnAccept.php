<?php
namespace Admin\Form\Pending;
use \Zend\Form\Form as Form;

class UnAccept extends Form {
	
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
		
		// Pending type
		$this->add(array(
		    'name'			=> 'pending_type',
		    'type'			=> 'Hidden',
		));
		
		// Lý do không duyệt
		$this->add(array(
		    'name'			=> 'content',
		    'type'			=> 'Textarea',
		    'attributes'	=> array(
		        'class'		    => 'form-control',
		    )
		));
	}
}