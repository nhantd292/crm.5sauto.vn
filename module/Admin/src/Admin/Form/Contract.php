<?php
namespace Admin\Form;
use \Zend\Form\Form as Form;

class Contract extends Form {
	
	public function __construct($sm, $options){
		parent::__construct();
        $userInfo = new \ZendX\System\UserInfo();
        $sale_branch_id = $userInfo->getUserInfo('sale_branch_id');
		
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
		
		// Phone
		$this->add(array(
		    'name'			=> 'phone',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control mask_phone',
		    ),
		));
		
		// Name
		$this->add(array(
			'name'			=> 'name',
			'type'			=> 'Text',
			'attributes'	=> array(
				'class'		=> 'form-control',
			),
		));

//        // Nhân viên mkt
//        $this->add(array(
//            'name'			=> 'marketer_id',
//            'type'			=> 'Select',
//            'attributes'	=> array(
//                'class'		=> 'form-control select2 select2_basic',
//            ),
//            'options'		=> array(
//                'empty_option'	=> '- Chọn -',
//                'disable_inarray_validator' => true,
//                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\UserTable')->listItem(array("data" => array('sale_branch_id' =>$options['userInfo']['sale_branch_id'])), array('task' => 'list-marketing')), array('key' => 'id', 'value' => 'name')),
//            ),
//        ));

		// Tỉnh thành
		$this->add(array(
		    'name'			=> 'location_city_id',
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
		    'name'			=> 'location_district_id',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		          => 'form-control select2 select2_advance',
		        'value'               => '',
		        'data-table'          => TABLE_LOCATIONS,
		        'data-id'             => 'code',
		        'data-text'           => 'fullname',
		        'data-parent'         => '',
		        'data-parent-field'   => 'parent',
				'data-parent-name'    => 'location_city_id',
		    ),
		));

        // phường xã
        $this->add(array(
            'name'          => 'location_town_id',
            'type'          => 'Text',
            'attributes'    => array(
                'class'               => 'form-control select2 select2_advance',
                'value'               => '',
                'data-table'          => TABLE_LOCATIONS,
                'data-id'             => 'code',
                'data-text'           => 'fullname',
                'data-parent'         => '',
                'data-parent-field'   => 'parent',
                'data-parent-name'    => 'location_district_id',
            ),
        ));
		
		// Địa chỉ
		$this->add(array(
		    'name'			=> 'address',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
		    )
		));
		
		// Thành tiền
		$this->add(array(
		    'name'			=> 'price_total',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		  => 'form-control text-danger mask_currency',
		        'value'       => 0,
		        'data-value'  => 0,
		        'readonly'    => 'readonly',
		    )
		));
		
		// Ghi chú sales
		$this->add(array(
		    'name'			=> 'sale_note',
		    'type'			=> 'Text',
		    'attributes'	=> array(
				'class'		  => 'form-control',
		    )
		));

		// Ghi chú GHTK
		$this->add(array(
		    'name'			=> 'ghtk_note',
		    'type'			=> 'Text',
		    'attributes'	=> array(
				'class'		  => 'form-control',
//                'value' => 'Cho Kiểm Tra Và Đồng Kiểm, Có Vấn Đề Gì Gọi Cho Shop, Không Tự Ý Hủy Đơn 0769638925',
                'value' => ''
		    )
		));

//        // Kích thước đóng hàng
//        $this->add(array(
//            'name'			=> 'size_product_id',
//            'type'			=> 'Select',
//            'attributes'	=> array(
//                'class'		=> 'form-control select2 select2_basic',
//            ),
//            'options'		=> array(
//                'empty_option'	=> '- Kích thước đóng hàng -',
//                'disable_inarray_validator' => true,
//                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'size-product')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
//            )
//        ));


        // Kho gửi hàng
//        $groupaddress = json_decode($sm->ghtk_call('/services/shipment/list_pick_add'), true)['data'];
//        $inventorys = \ZendX\Functions\CreateArray::create($groupaddress, array('key' => 'pick_address_id', 'value' => 'pick_name,address', 'sprintf' =>'%s - %s'));

//        $branch = $sm->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(array('id' => $sale_branch_id));
//        $warehouse_id = explode(',', $branch['inventory_ids']);
//        $this->add(array(
//            'name'			=> 'groupaddressId',
//            'type'			=> 'Select',
//            'attributes'	=> array(
//                'class'		=> 'form-control select2 select2_basic',
//            ),
//            'options'		=> array(
//                'empty_option'	=> '- Kho gửi hàng -',
//                'disable_inarray_validator' => true,
////                'value_options'	=> $inventorys,
//                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'warehouses', 'id' => $warehouse_id)), array('task' => 'list-all-multil')), array('key' => 'id', 'value' => 'name,phone,address', 'sprintf' =>'%s - %s - %s')),
//            )
//        ));

//        $this->add(array(
//            'name'			=> 'pick_work_shift',
//            'type'			=> 'Select',
//            'attributes'	=> array(
//                'class'		=> 'form-control select2 select2_basic',
//            ),
//            'options'		=> array(
////                'empty_option'	=> '- Kho gửi hàng -',
//                'disable_inarray_validator' => true,
//                'value_options'	=> ['1'=>"Sáng", '2'=>"Chiều", '3'=>"Tối", ],
//            )
//        ));

        $this->add(array(
            'name'			=> 'deliver_work_shift',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Thời gian giao hàng-',
                'disable_inarray_validator' => true,
                'value_options'	=> ['1' => "Sáng", '2' => "Chiều", '3' => "Tối", ],
            )
        ));


        // Loại đơn sản xuất
        $this->add(array(
            'name'			=> 'production_type_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Chọn -',
                'disable_inarray_validator' => true,
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "production-type" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
            ),
        ));
	}
}