<?php
namespace Admin\Form;
use \Zend\Form\Form as Form;

class ContactData extends Form {
	
	public function __construct($sm, $options = null){
		parent::__construct();

        $phone_disable = "";
//		if($options['action'] != 'add'){
//            $phone_disable = "disabled";
//        }

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

        // Name
        $this->add(array(
            'name'			=> 'name',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'		=> 'form-control',
            ),
        ));

		// Phone
		$this->add(array(
		    'name'			=> 'phone',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		     => 'form-control mask_phone',
                'disabled'        => $phone_disable,
		    ),
		));

		// Hãng xe, năm sản xuất
		$this->add(array(
		    'name'			=> 'note',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		     => 'form-control',
		    ),
		));

		// Nội dung cần tư vấn
		$this->add(array(
		    'name'			=> 'content',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		     => 'form-control',
		    ),
		));
		
		// Sex
		$this->add(array(
		    'name'			=> 'sex',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sex')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name')),
		    )
		));

        // Tỉnh thành
        $this->add(array(
            'name'			=> 'city_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Chọn -',
                'disable_inarray_validator' => true,
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache')), array('key' => 'code', 'value' => 'name')),
            ),
        ));

        // Quận huyện
        $this->add(array(
            'name'			=> 'district_id',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'		          => 'form-control select2 select2_advance',
                'value'               => '',
                'data-table'          => TABLE_LOCATIONS,
                'data-id'             => 'code',
                'data-text'           => 'name',
                'data-parent'         => '',
                'data-parent-field'   => 'parent',
                'data-parent-name'    => 'parent',
            ),
        ));

		// Address
		$this->add(array(
		    'name'			=> 'address',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		        'placeholder'	=> 'Địa chỉ'
		    )
		));
		
		// Nguồn biết đến
		$this->add(array(
		    'name'			=> 'marketing_channel_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'marketing-channel')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    )
		));
		
		// Nghề nghiệp
		$this->add(array(
		    'name'			=> 'job',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		     => 'form-control',
		        'placeholder'	 => 'Nghề nghiệp'
		    ),
		));
	}
}