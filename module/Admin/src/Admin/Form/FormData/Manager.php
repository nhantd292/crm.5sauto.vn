<?php
namespace Admin\Form\FormData;
use \Zend\Form\Form as Form;

class Manager extends Form {
	
	public function __construct($sm){
		parent::__construct();
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
		
		// Người quản lý
		$this->add(array(
		    'name'			=> 'contact_id',
		    'type'			=> 'Hidden',
		));
	}
}