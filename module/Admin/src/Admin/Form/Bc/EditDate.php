<?php
namespace Admin\Form\Bc;
use \Zend\Form\Form as Form;

class EditDate extends Form {
	
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
		
		// Ngày đăng ký thi
		$this->add(array(
		    'name'			=> 'date_register',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		    => 'form-control date-picker',
		        'placeholder'	=> 'dd/mm/yyyy',
		    )
		));
		
		// Ngày thi speaking
		$this->add(array(
		    'name'			=> 'date_speaking',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		    => 'form-control date-picker',
		        'placeholder'	=> 'dd/mm/yyyy',
		    )
		));
		
		// Lý do
		$this->add(array(
		    'name'			=> 'note_log',
		    'type'			=> 'Textarea',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
		    )
		));
	}
}