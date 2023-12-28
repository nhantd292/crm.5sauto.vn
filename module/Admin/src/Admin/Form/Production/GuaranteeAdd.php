<?php
namespace Admin\Form\Production;
use \Zend\Form\Form as Form;

class GuaranteeAdd extends Form {
	
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
        
        // Ngày bảo hành
		$this->add(array(
		    'name'			=> 'guarantee_date',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		    => 'form-control date-picker not-push',
		        'placeholder'	=> 'dd/mm/yyyy',
		        'value'         => date('d/m/Y')
		    )
		));
        
		// Nội dung bảo hành
		$this->add(array(
		    'name'			=> 'guarantee_note',
		    'type'			=> 'Textarea',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
		    )
		));
	}
}