<?php
namespace Report\Form\Marketing;
use \Zend\Form\Form as Form;

class Overview extends Form{
    
	public function __construct($sm, $params = null){
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
		
		// Ngày bắt đầu
		$this->add(array(
		    'name'			=> 'date_begin',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control date-picker',
		        'placeholder'	=> 'Từ ngày'
		    )
		));
		
		// Ngày kết thúc
		$this->add(array(
		    'name'			=> 'date_end',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control date-picker',
		        'placeholder'	=> 'Đến ngày'
		    )
		));

		// Danh mục sản phẩm
		$this->add(array(
		    'name'			=> 'product_cat_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Sản phẩm -',
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'list-item')), array('key' => 'id', 'value' => 'name')),
		    )
		));

		// Cơ sở
		$this->add(array(
		    'name'			=> 'sale_branch_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Cơ sở -',
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    )
		));
		
		// Đội nhóm
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
                'data-where_type'     => 'marketing',
		    ),
		));

        // Nhân viên marketing
        $this->add(array(
            'name'			=> 'marketer_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Nhân viên marketing -',
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\UserTable')->listItem(array('company_department_id' => 'marketing'), array('task' => 'list-user-department')), array('key' => 'id', 'value' => 'name')),
            )
        ));
		
		// Submit
		$this->add(array(
		    'name'			=> 'submit',
		    'type'			=> 'Button',
		    'options' => array(
                'label' => 'Lọc',
            ),
		    'attributes'	=> array(
		        'class'		=> 'btn btn-sm green',
		    ),
		));
	}
}