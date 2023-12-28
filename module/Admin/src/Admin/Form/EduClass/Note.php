<?php
namespace Admin\Form\EduClass;
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
		
		// Lớp học
		$this->add(array(
		    'name'			=> 'edu_class_id',
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
			'name'			=> 'note_student',
			'type'			=> 'text',
			'attributes'	=> array(
				'class'			=> 'form-control',
			)
		));
	}
}