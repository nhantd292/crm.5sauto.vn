<?php
namespace Admin\Form\Contract;
use \Zend\Form\Form as Form;

class Matter extends Form {
	
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
		
		// Contract Id
		$this->add(array(
		    'name'			=> 'id',
		    'type'			=> 'Hidden',
		));
		
		// Ngày hóa đơn
		$this->add(array(
		    'name'			=> 'date',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		    => 'form-control date-picker',
		        'placeholder'	=> 'dd/mm/yyyy',
		        'value'         => date('d/m/Y'),
		        'data-value'    => date('d/m/Y'),
		    )
		));
		
		// Danh sách vật phẩm chưa phát
		$this->add(array(
		    'name'			=> 'matter_ids',
		    'type'			=> 'MultiCheckbox',
		    'attributes'	=> array(
		        'class'		=> '',
		    ),
		    'options'		=> array(
		        'label_attributes' => array(
		            'class'	=> 'checkbox-inline',
		        ),
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( 'where' => array( 'code' => 'matter' )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    )
		));
	}
}