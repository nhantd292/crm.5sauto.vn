<?php
namespace Admin\Form;
use \Zend\Form\Form as Form;

class DataConfig extends Form {
	
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
		
		// Id
		$this->add(array(
		    'name'			=> 'id',
		    'type'			=> 'Hidden',
		));
		
		// Tiêu đề
		$this->add(array(
		    'name'			=> 'title',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
		    ),
		));

        // Cơ sở kinh doanh
        $this->add(array(
            'name'			=> 'sale_branch_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
//                'empty_option'	=> '- Chọn -',
                'disable_inarray_validator' => true,
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
            ),
        ));

        // Số lượng data chọn để chia
        $this->add(array(
            'name'			=> 'number',
            'type'			=> 'Number',
            'required'		=> true,
            'attributes'	=> array(
                'class'		=> 'form-control',
            ),
        ));

        // Chọn nhân viên
        $this->add(array(
            'name'			=> 'options',
            'type'			=> 'Hidden',
            'attributes'	=> array(
//                'id'	     => 'user_hidden',
                'class'		=> 'form-control',
            ),
        ));


        // Password Status
        $this->add(array(
            'name'			=> 'status',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
                'value'     => 1,
            ),
            'options'		=> array(
                'value_options'	=> array( 1	=> 'Bật', 0 => 'Tắt'),
            )
        ));
	}
}