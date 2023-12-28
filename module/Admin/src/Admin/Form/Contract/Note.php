<?php
namespace Admin\Form\Contract;
use \Zend\Form\Form as Form;

class Note extends Form {
	
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
		
		// Kết quả chăm sóc
		$this->add(array(
			'name'			=> 'note',
			'type'			=> 'text-area',
			'attributes'	=> array(
				'class'			=> 'form-control',
				'placeholder'	=> ''
			)
		));
		
	}
}