<?php
namespace Admin\Form\Search;
use \Zend\Form\Form as Form;

class Contract extends Form{ 
    
	public function __construct($sm, $params = null){
	    $action   = $params['action'];
	    $ssFilter = $params['ssFilter'];
	    $categories = $params['categories'];
	    $products = $params['products'];
		parent::__construct();
		
		$userInfo = new \ZendX\System\UserInfo();
		$userInfo = $userInfo->getUserInfo();
		
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

		// Cơ sở
		$this->add(array(
		    'name'			=> 'filter_unit_transport',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- ĐV vận chuyển -',
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'transport-unit')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name')),
		    )
		));
        $this->add(array(
            'name'			=> 'filter_production_type_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Loại đơn -',
                'disable_inarray_validator' => true,
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "production-type" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
            ),
        ));

        // Shipper
//        $this->add(array(
//            'name'			=> 'filter_shipper_id',
//            'type'			=> 'Select',
//            'attributes'	=> array(
//                'class'		=> 'form-control select2 select2_basic',
//            ),
//            'options'		=> array(
//                'empty_option'	=> '- Nhân viên giao hàng -',
//                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-positons-care')), array('key' => 'id', 'value' => 'name')),
//            )
//        ));
        // đơn đã xuất kho
        $this->add(array(
            'name' => 'filter_status_shipped',
            'type' => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
            ),
            'options' => array(
                'value_options' => array('' => 'Xác nhận xuất kho', '0' => 'Chưa xuất kho', '1' => 'Đã xuất kho'),
            )
        ));
        $this->add(array(
            'name' => 'filter_care_status',
            'type' => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
            ),
            'options' => array(
                'empty_option'	=> '- Phân cho chăm sóc -',
                'value_options' => array('-1' => 'Chưa chia', '1' => 'Đã chia'),
            )
        ));
        $this->add(array(
            'name' => 'filter_marketer_status',
            'type' => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
            ),
            'options' => array(
                'empty_option'	=> '- Có marketer -',
                'value_options' => array('-1' => 'Không có mkt', '1' => 'Có mkt'),
            )
        ));

		// Cơ sở
		$this->add(array(
		    'name'			=> 'filter_sale_branch',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Cơ sở kinh doanh -',
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    )
		));
		
		// Đội nhóm
		$sale_group_id = $userInfo['sale_group_id'];
		$sale_group_ids = !empty($userInfo['sale_group_ids']) ? explode(',', $userInfo['sale_group_ids']) : null;
		$group = $sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
		$group_data = array();
		if(!empty($ssFilter['filter_sale_branch'])) {
			foreach ($group AS $key => $val) {
				if($val['document_id'] == $ssFilter['filter_sale_branch']) {
			        $group_data[] = $val;
				}
			}
		}
		$this->add(array(
		    'name'			=> 'filter_sale_group',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Đội nhóm -',
		        'value_options'	=> \ZendX\Functions\CreateArray::createSelect($group_data, array('key' => 'id', 'value' => 'name,content', 'symbol' => ' - '))
		    )
		));

        $user_care	= \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\UserTable')->listItem(array('company_department_id' => 'care', 'sale_branch_id' => $ssFilter['filter_sale_branch']), array('task' => 'list-user-department')), array('key' => 'id', 'value' => 'name'));
        $user_sales	= \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\UserTable')->listItem(array('company_department_id' => 'sales', 'sale_branch_id' => $ssFilter['filter_sale_branch']), array('task' => 'list-user-department')), array('key' => 'id', 'value' => 'name'));
        $user_data = array_merge($user_sales, $user_care);

		$this->add(array(
		    'name'			=> 'filter_user',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Nhân viên -',
		        'value_options'	=> $user_data,
		    )
		));

		$this->add(array(
		    'name'			=> 'filter_delivery_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- NV Giục đơn -',
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\UserTable')->listItem(array('company_department_id' => 'giuc-don', 'sale_group_id' => $ssFilter['filter_sale_group']), array('task' => 'list-user-department')), array('key' => 'id', 'value' => 'name'))
		    )
		));
		
		// Phân Nhóm sản phẩm
		$this->add(array(
		    'name'			=> 'filter_category',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Nhóm sản phẩm -',
		        'value_options'	=> $categories,
		    )
		));


        // Shipper
        $this->add(array(
            'name'			=> 'filter_shipper_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Nhân viên giao hàng -',
                'disable_inarray_validator' => true,
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "shipper" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
            ),
        ));

		$this->add(array(
		    'name'			=> 'filter_product',
		    'type'			=> 'Select',
		    'attributes'	=> array(
                'multiple'	=> true,
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
//		        'empty_option'	=> '- Sản phẩm-',
//		        'value_options'	=> $products,
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\KovProductsTable')->listItem([], array('task' => 'cache')), array('key' => 'id', 'value' => 'fullName')),
            )
		));

        // Bộ phận
        $this->add(array(
            'name'			=> 'filter_status_type',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Bộ phận -',
                'disable_inarray_validator' => true,
                'value_options'	=> array('status_id' => 'Sales', 'ghtk_status' => 'Giục đơn', 'status_acounting_id' => 'Kế toán', ),
            ),
        ));

        $list_status = [];
        if($ssFilter['filter_status_type'] == 'ghtk_status'){
            $list_status = \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "ghtk-status" )), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name'));
            if($ssFilter['filter_unit_transport'] == 'viettel')
                $list_status = \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "viettel-status" )), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name'));
            if($ssFilter['filter_unit_transport'] == 'ghn')
                $list_status = \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "ghn-status" )), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name'));
        }
        if($ssFilter['filter_status_type'] == 'status_acounting_id'){
            $list_status = \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "status-acounting" )), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name'));
        }
        if($ssFilter['filter_status_type'] == 'status_id'){
            $list_status = \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "status" )), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name'));
        }

        // Trạng thái theo bộ phận
        $this->add(array(
            'name'			=> 'filter_status',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Trạng thái -',
                'disable_inarray_validator' => true,
                'value_options'	=> $list_status,
            ),
        ));

        $list_contract_type = \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "production-type" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name'));



        // Đồng bộ kiotviet
        $this->add(array(
            'name' => 'filter_send_ghtk',
            'type' => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
            ),
            'options' => array(
                'empty_option'	=> '- Giao hàng tiết kiệm -',
                'value_options' => array('-1' => 'Chưa đồng bộ', '1' => 'Đã đồng bộ'),
            )
        ));

        // Xác nhận hoàn
        $this->add(array(
            'name' => 'filter_returned',
            'type' => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
            ),
            'options' => array(
                'empty_option'	=> '- Xác nhận hoàn -',
                'value_options' => array('-1' => 'Chưa nhận hoàn', '1' => 'Đã nhận hoàn'),
            )
        ));

        // các đơn lỗi đồng bộ kiotviet
        $this->add(array(
            'name' => 'filter_update_kov_false',
            'type' => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
            ),
            'options' => array(
                'empty_option'	=> '- Cập nhật kiotviet -',
                'value_options' => array('-1' => 'Cập nhật thành công', '1' => 'Cập nhật lỗi'),
            )
        ));

        $this->add(array(
            'name' => 'filter_status_lock',
            'type' => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
            ),
            'options' => array(
                'value_options' => array('' => 'Trạng thái khóa', '0' => 'Chưa khóa', '1' => 'Đã khóa'),
            )
        ));

        // Đơn trùng
        $this->add(array(
            'name' => 'filter_coincider',
            'type' => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
            ),
            'options' => array(
                'empty_option'	=> '- Trùng đơn -',
                'value_options' => array('1' => 'Đơn hàng trùng', '-1' => 'Đơn hàng không trùng'),
            )
        ));

        // Loại ngày
        $this->add(array(
            'name' => 'filter_date_type',
            'type' => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
            ),
            'options' => array(
//                'empty_option'	=> '- Ngày lên đơn -',
                'value_options' => array('date' => 'Ngày lên đơn', 'shipped_date' => 'Ngày xuất kho', 'not_shipped' => 'Chưa có ngày xuất kho'),
            )
        ));

        $this->add(array(
            'name'			=> 'filter_marketer_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- NV marketing -',
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\UserTable')->listItem(array('company_department_id' => 'marketing'), array('task' => 'list-user-department')), array('key' => 'id', 'value' => 'name')),
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
		
		// Action
		$this->add(array(
			'name'			=> 'filter_action',
			'type'			=> 'Hidden',
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

        // action new
        $this->add(array(
            'name'			=> 'action_new',
            'type'			=> 'Hidden',
            'attributes'	=> array(
                'value'   => 'new',
            ),
        ));
        // action index
        $this->add(array(
            'name'			=> 'action_index',
            'type'			=> 'Hidden',
            'attributes'	=> array(
                'value'   => 'index',
            ),
        ));
        // action index
        $this->add(array(
            'name'			=> 'action_shipping',
            'type'			=> 'Hidden',
            'attributes'	=> array(
                'value'   => 'shipping',
            ),
        ));
	}
}