<?php
namespace Report\Form\Sales;
use \Zend\Form\Form as Form;

class Sales extends Form{
    
	public function __construct($sm, $params = null){

        $products = $params['products'];
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
                'value_options'	=> $products,
		    )
		));

        $this->add(array(
            'name'			=> 'product_group_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Sản phẩm quan tâm -',
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-group')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
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
                'data-where_type'     => 'sales',
		    ),
		));

        // Nhân viên sales
        $this->add(array(
            'name'			=> 'sale_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Nhân viên sales -',
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\UserTable')->listItem(array('company_department_id' => 'sales', 'sale_group_id' => $params['sale_group_id']), array('task' => 'list-user-department')), array('key' => 'id', 'value' => 'name')),
            )
        ));

        // Nhân viên giục đơn
        $this->add(array(
            'name'			=> 'delivery_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Nhân viên giục đơn -',
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\UserTable')->listItem(array('company_department_id' => 'giuc-don', 'sale_group_id' => $params['sale_group_id']), array('task' => 'list-user-department')), array('key' => 'id', 'value' => 'name')),
            )
        ));

        $this->add(array(
            'name'			=> 'production_type_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Loại đơn -',
                'disable_inarray_validator' => true,
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "production-type" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
            ),
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