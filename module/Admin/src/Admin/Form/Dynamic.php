<?php
namespace Admin\Form;
use \Zend\Form\Form as Form;

class Dynamic extends Form {
	
	public function __construct($sm, $configs = null){
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
		
		// Code
		$this->add(array(
		    'name'			=> 'code',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		        'id'			=> 'code',
		        'placeholder'	=> 'Mã',
		    ),
		));
		
		// Name
		$this->add(array(
			'name'			=> 'name',
			'type'			=> 'Text',
			'attributes'	=> array(
				'class'			=> 'form-control',
				'id'			=> 'name',
				'placeholder'	=> 'Tên',
			),
		));
		
		// Option
		$this->add(array(
		    'name'			=> 'option',
		    'type'			=> 'Textarea',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
		        'style'     => 'height: 500px;'
		    )
		));
		
		// Ordering
		$this->add(array(
		    'name'			=> 'ordering',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'value'         => 255,
		        'class'			=> 'form-control',
		        'id'			=> 'ordering',
		    )
		));
		
		// Status
		$this->add(array(
			'name'			=> 'status',
			'type'			=> 'Select',
			'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
			    'value'     => 1,
			),
		    'options'		=> array(
		        'value_options'	=> array( 1	=> 'Hiển thị', 0 => 'Không hiển thị'),
		    )
		));
		
		// Nhóm quyền truy cập
		$this->add(array(
		    'name'			=> 'permission_ids',
		    'type'			=> 'MultiCheckbox',
		    'options'		=> array(
		        'label_attributes' => array(
		            'class'		=> 'checkbox-inline',
		        ),
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\PermissionTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
	}
}