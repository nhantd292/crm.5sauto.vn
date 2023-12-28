<?php
namespace Admin\Form\Search;
use \Zend\Form\Form as Form;

class KovDiscounts extends Form{
    
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
		        'placeholder'   => 'Chương trình',
		        'class'			=> 'form-control input-sm',
		        'id'			=> 'filter_keyword',
		    ),
		));

        // trạng thái
        $this->add(array(
            'name'			=> 'filter_status',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Trạng thái -',
                'value_options'	=> array('0' => 'Chưa áp dụng', '1' => 'Kích hoạt'),
            )
        ));

        $this->add(array(
            'name'			=> 'filter_date_type',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
                'value'     => 'date'
            ),
            'options'		=> array(
                'value_options'	=> array('date_begin' => 'Ngày bắt đầu', 'date_end' => 'Ngày Kết thúc', 'created' => 'Ngày tạo'),
            )
        ));

		$this->add(array(
		    'name'			=> 'filter_date_begin',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control date-picker',
		        'placeholder'	=> 'Từ ngày',
		        'autocomplete'  => 'off'
		    )
		));

		$this->add(array(
		    'name'			=> 'filter_date_end',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control date-picker',
		        'placeholder'	=> 'Đến ngày',
		        'autocomplete'  => 'off'
		    )
		));
        // Hình thức áp dụng
        $this->add(array(
            'name'			=> 'filter_discounts_type',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Khuyến mãi theo -',
                'disable_inarray_validator' => true,
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'discounts-type')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name')),
            ),
        ));

        if($ssFilter['filter_discounts_type']){
            $type = $sm->get('Admin\Model\DocumentTable')->getItem(array('alias' => $ssFilter['filter_discounts_type']), array('task' => 'by-custom-alias'));
            $discounts_option = \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('document_id' => $type->id)), array('task' => 'list-all')), array('key' => 'alias', 'value' => 'name'));
        }
        $this->add(array(
            'name'			=> 'filter_discounts_option',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Hình thức -',
                'disable_inarray_validator' => true,
                'value_options'	=> $discounts_option,
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