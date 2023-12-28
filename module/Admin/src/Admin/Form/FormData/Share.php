<?php
namespace Admin\Form\FormData;
use \Zend\Form\Form as Form;

class Share extends Form {
	
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
			'enctype'		=> 'multipart/form-data'
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
		
		// Số lượng data chọn để chia
		$this->add(array(
			'name'			=> 'numbers_data',
			'type'			=> 'Text',
			'required'		=> true,
			'attributes'	=> array(
				'class'		=> 'form-control',
				'readonly'	=> 'readonly'
			),
		));

		// Số lượng data chọn để chia
		$this->add(array(
			'name'			=> 'list_data_id',
			'type'			=> 'Hidden',
			'required'		=> fakse,
		));

		// Hiển thị level nhân viên
		$this->add(array(
		    'name'			=> 'sale_level',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Tất cả -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-level')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name')),
		    )
		));

		// Hiển thị checkbox all
		$this->add(array(
		    'name'			=> 'options',
		    'type'			=> 'Checkbox',
		    'options'		=> array(
		        'label_attributes' => array(
		            'class'		=> 'checkbox-inline',
		        ),
		    'value_options'	=> 'share-all',
		    ),
		));

		// Chọn nhân viên
		$this->add(array(
		    'name'			=> 'user_hidden',
		    'type'			=> 'Hidden',
		    'attributes'	=> array(
		        'id'	     => 'user_hidden',
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
                'empty_option'	=> '- Chọn -',
                'disable_inarray_validator' => true,
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
            ),
        ));

        // Company Group
        $this->add(array(
            'name'			=> 'sale_group_id',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'		          => 'form-control select2 select2_advance',
                'value'               => '',
                'data-table'          => TABLE_DOCUMENT,
                'data-id'             => 'id',
                'data-text'           => 'name,content',
                'data-parent'         => '',
                'data-parent-field'   => 'document_id',
                'data-where_type'     => 'sales',
            ),
        ));

	}
}