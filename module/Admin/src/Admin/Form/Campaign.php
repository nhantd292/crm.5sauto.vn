<?php
namespace Admin\Form;
use \Zend\Form\Form as Form;

class Campaign extends Form {
	
	public function __construct($sm){
		parent::__construct();
		
		$gid      = new \ZendX\Functions\Gid();
		
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
			'attributes'	=> array(
				'class'			=> 'form-control',
				'id'			=> 'id',
				'value'			=>	$gid->getId()
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
		
		// Nhóm được phép truy cập
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
		
		// Fields
		$this->add(array(
		    'name'			=> 'fields',
		    'type'			=> 'Textarea',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		        'style'         => 'height: 300px',
		        'id'			=> 'fields',
		        'placeholder'	=> 'label=Họ và Tên | name=name | type=text | require=true | option=null | list=true',
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
		        'placeholder'	=> 'Thứ tự'
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
		        'empty_option'	=> '- Chọn -',
		        'value_options'	=> array( 1	=> 'Hiển thị', 0 => 'Không hiển thị'),
		    )
		));
	}
}