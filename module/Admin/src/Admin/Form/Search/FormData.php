<?php
namespace Admin\Form\Search;
use \Zend\Form\Form as Form;

class FormData extends Form{
    
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
		
		// Keyword
		$this->add(array(
		    'name'			=> 'filter_keyword',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'placeholder'   => 'Từ khóa',
		        'class'			=> 'form-control input-sm',
		        'id'			=> 'filter_keyword',
		    ),
		));
		
		// Bắt đầu từ ngày
		$this->add(array(
		    'name'			=> 'filter_date_begin',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control date-picker',
		        'placeholder'	=> 'Từ ngày',
		        'autocomplete'  => 'off'
		    )
		));
		
		// Ngày kết thúc
		$this->add(array(
		    'name'			=> 'filter_date_end',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control date-picker',
		        'placeholder'	=> 'Đến ngày',
		        'autocomplete'  => 'off'
		    )
		));
        // sale tiếp nhận
        $this->add(array(
            'name'			=> 'filter_sales_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Sales tiếp nhận -',
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\UserTable')->listItem(array('company_department_id' => 'sales'), array('task' => 'list-user-department')), array('key' => 'id', 'value' => 'name')),
            )
        ));

        // Nhân viên marketing
        $this->add(array(
            'name'			=> 'filter_marketer_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Nhân viên marketing -',
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\UserTable')->listItem(array('company_department_id' => 'marketing'), array('task' => 'list-user-department')), array('key' => 'id', 'value' => 'name')),
            )
        ));

        // Sản phẩm quan tâm
//        $this->add(array(
//            'name'			=> 'filter_product_id',
//            'type'			=> 'Select',
//            'attributes'	=> array(
//                'class'		=> 'form-control select2 select2_basic',
//            ),
//            'options'		=> array(
//                'empty_option'	=> '- Sản phẩm quan tâm -',
//                'disable_inarray_validator' => true,
//                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'list-item')), array('key' => 'id', 'value' => 'name')),
//            )
//        ));

        // Nhóm sản phẩm quan tâm
        $this->add(array(
            'name'			=> 'filter_product_group_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Sản phẩm quan tâm -',
                'disable_inarray_validator' => true,
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-group')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
            )
        ));
		
		// Tỉnh thành
		$this->add(array(
		    'name'			=> 'filter_location_city',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Tỉnh thành -',
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache')), array('key' => 'code', 'value' => 'name')),
            )
		));

        // Cơ sở kinh doanh
        $this->add(array(
            'name'			=> 'filter_sale_branch',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Cơ sở -',
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
            ),
        ));

        // Đội nhóm marketing
        $this->add(array(
            'name'			=> 'filter_sale_group',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Đội nhóm marketing -',
                'value_options'	=> \ZendX\Functions\CreateArray::createSelect($sm->get('Admin\Model\DocumentTable')->listItem(array('data' => array('document_id' => $params['filter_sale_branch']),'where' => array('type' => 'marketing')), array('task' => 'list-parent')), array('key' => 'id', 'value' => 'name,content', 'symbol' => ' - ')),
            )
        ));
		
		// Trạng thái quản lý
		$this->add(array(
		    'name'			=> 'filter_active',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Tình trạng -',
		        'value_options'	=> array( 'unactive' => 'Chưa phân cho sales', 'active' => 'Đã phân cho sales'),
		    )
		));

		// Trạng thái trùng
		$this->add(array(
		    'name'			=> 'filter_contact_coin',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- trạng thái trùng -',
		        'value_options'	=> array( '0' => 'Không trùng', '1' => 'Trùng data'),
		    )
		));

		// Trạng thái share data
		$this->add(array(
		    'name'			=> 'filter_cancel_share',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Trạng thái share -',
		        'value_options'	=> array( '0' => 'Được phép chia', '1' => 'Hủy không chia'),
		    )
		));
		
		// Submit
		$this->add(array(
		    'name'			=> 'filter_submit',
		    'type'			=> 'Submit',
		    'attributes'	=> array(
		        'value'     => 'Tìm',
		        'class'		=> 'btn btn-sm green',
		    ),
		));
		
		// Xóa
		$this->add(array(
		    'name'			=> 'filter_reset',
		    'type'			=> 'Submit',
		    'attributes'	=> array(
		        'value'     => 'Xóa',
		        'class'		=> 'btn btn-sm red',
		    ),
		));
		
		// Order
		$this->add(array(
		    'name'			=> 'order',
		    'type'			=> 'Hidden',
		));
		
		// Order By
		$this->add(array(
		    'name'			=> 'order_by',
		    'type'			=> 'Hidden',
		));
	}
}