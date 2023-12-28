<?php
namespace Admin\Form\Search;
use \Zend\Form\Form as Form;

class Evaluate extends Form{
    
	public function __construct($sm, $params = null){
	    $action   = $params['action'];
	    $ssFilter = $params['ssFilter'];
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

        // Phân loại ngày
        $this->add(array(
            'name'			=> 'filter_date_type',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'value_options'	=> array('1' => 'Ngày đánh giá', '2' => 'Ngày giao hàng'),

            )
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

        $user_care	= \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\UserTable')->listItem(array('company_department_id' => 'care'), array('task' => 'list-user-department')), array('key' => 'id', 'value' => 'name'));
        $user_sales	= \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\UserTable')->listItem(array('company_department_id' => 'sales'), array('task' => 'list-user-department')), array('key' => 'id', 'value' => 'name'));
        $user_data = array_merge($user_sales, $user_care);

		$this->add(array(
		    'name'			=> 'filter_user_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Nhân viên -',
		        'value_options'	=> $user_data,
		    )
		));
		
		// Phân loại sản phẩm
		$this->add(array(
		    'name'			=> 'filter_level_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Mức độ hài lòng -',
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'evalua-level')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name')),

            )
		));

		// Nhân viên kỹ thuật
		$this->add(array(
		    'name'			=> 'filter_technical_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Nhân viên -',
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "technical" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),

            )
		));

		// Nhân viên kỹ thuật
		$this->add(array(
		    'name'			=> 'filter_tailors_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Nhân viên -',
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "tailors" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),

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



        // action sale
        $this->add(array(
            'name'			=> 'action_new',
            'type'			=> 'Hidden',
            'attributes'	=> array(
                'value'   => 'sale',
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
	}
}