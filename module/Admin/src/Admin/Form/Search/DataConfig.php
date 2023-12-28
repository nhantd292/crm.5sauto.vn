<?php
namespace Admin\Form\Search;
use \Zend\Form\Form as Form;

class DataConfig extends Form{
    
	public function __construct($sm, $params){
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
		        'value_options'	=> array('date' => 'Ngày tiếp nhận', 'created' => 'Ngày tạo', 'history_created' => 'Ngày chăm sóc', 'history_return' => 'Ngày hẹn chăm sóc lại'),
		    )
		));
		
		// Đội nhóm
		$sale_group_id = $userInfo['sale_group_id'];
		$sale_group_ids = !empty($userInfo['sale_group_ids']) ? explode(',', $userInfo['sale_group_ids']) : null;
		$group = $sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-group')), array('task' => 'cache'));
		$group_data = array();
		if(!empty($params['filter_sale_branch'])) {
			foreach ($group AS $key => $val) {
				if($val['document_id'] == $params['filter_sale_branch']) {
				    if(!empty($sale_group_ids)) {
				        if(in_array($val['id'], $sale_group_ids)) {
        					$group_data[] = $val;
				        }
				    } elseif (!empty($sale_group_id)) {
				        if($val['id'] == $sale_group_id) {
				            $group_data[] = $val;
				        }
				    } else {
				        $group_data[] = $val;
				    }
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
		$user = $sm->get('Admin\Model\UserTable')->listItem($params, array('task' => 'cache'));
		$user_data = array();
		if(!empty($params['filter_sale_group'])) {
		    foreach ($user AS $key => $val) {
	            if($val['sale_group_id'] == $params['filter_sale_group']) {
	                if(!empty($userInfo['sale_group_ids'])) {
                        $user_data[] = $val;
	                } else {
	                    if (!empty($userInfo['sale_group_id'])) {
    	                    if ($val['id'] == $userInfo['id']) {
        	                    $user_data[] = $val;
        	                }
	                    } else {
	                        $user_data[] = $val;
	                    }
	                }
		        }
		    }
		}
		$this->add(array(
		    'name'			=> 'filter_user',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Nhân viên -',
		        'value_options'	=> \ZendX\Functions\CreateArray::create($user_data, array('key' => 'id', 'value' => 'name')),
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
		
	}
}