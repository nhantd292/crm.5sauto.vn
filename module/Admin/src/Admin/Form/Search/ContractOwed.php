<?php
namespace Admin\Form\Search;
use \Zend\Form\Form as Form;

class ContractOwed extends Form{ 
    
	public function __construct($sm, $params = null){
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
		
		// Phân loại ngày tìm kiếm
		$this->add(array(
		    'name'			=> 'filter_date_type',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		        'value'     => 'date'
		    ),
		    'options'		=> array(
		        'value_options'	=> array('date' => 'Ngày đơn hàng', 'created' => 'Ngày tạo', 'leave_date' => 'Ngày nghỉ học', 'date_debt' => 'đơn hàng nợ nộp tiền trong tháng'),
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
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    )
		));
		
		// Đội nhóm
		$sale_group_id = $userInfo['sale_group_id'];
		$sale_group_ids = !empty($userInfo['sale_group_ids']) ? explode(',', $userInfo['sale_group_ids']) : null;
		$group = $sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
		$group_data = array();
		if(!empty($params['filter_sale_branch'])) {
			foreach ($group AS $key => $val) {
				if($val['document_id'] == $params['filter_sale_branch']) {
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

        // nhân viên
        $user_care	= \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\UserTable')->listItem(array('company_department_id' => 'care'), array('task' => 'list-user-department')), array('key' => 'id', 'value' => 'name'));
        $user_sales	= \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\UserTable')->listItem(array('company_department_id' => 'sales'), array('task' => 'list-user-department')), array('key' => 'id', 'value' => 'name'));
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

        // Shipper
        $this->add(array(
            'name'			=> 'filter_shipper_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Nhân viên giao hàng -',
//                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'shipper')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name'))
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-positons-care')), array('key' => 'id', 'value' => 'name')),
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
                'value_options'	=> array('production_department_type' => 'Sản xuất', 'status_check_id' => 'Giục đơn', 'status_acounting_id' => 'Kế toán', ),
            ),
        ));

        // Công nợ
        $this->add(array(
            'name'			=> 'filter_owed',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
//                'empty_option'	=> '- Công nợ -',
                'disable_inarray_validator' => true,
                'value_options'	=> array('yes' => 'Còn công nợ', 'no' => 'Đã thanh toán hết'),
            ),
        ));

        $list_status = [];
        if($params['filter_status_type'] == 'production_department_type'){
            $list_status = \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "production-department" )), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name'));
        }
        if($params['filter_status_type'] == 'status_check_id'){
            $list_status = \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "status-check" )), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name'));
        }
        if($params['filter_status_type'] == 'status_acounting_id'){
            $list_status = \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "status-acounting" )), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name'));
        }

        // Loại đơn
        $this->add(array(
            'name'			=> 'filter_production_type_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Loại đơn -',
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-type')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
            )
        ));

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