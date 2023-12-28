<?php
namespace Admin\Form\Search;
use \Zend\Form\Form as Form;

class KovProduct extends Form{
    
	public function __construct($sm, $categories){
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
		        'placeholder'   => 'Tên/Mã sản phẩm',
		        'class'			=> 'form-control input-sm',
		        'id'			=> 'filter_keyword',
		    ),
		));

        // Nhóm hàng
        $this->add(array(
            'name'			=> 'filter_categoryId',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Nhóm hàng -',
                'value_options'	=> $categories,
            )
        ));

        // Đánh giá thợ may
        $this->add(array(
            'name'			=> 'filter_evaluate',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Đánh giá thợ may -',
                'value_options'	=> array('1' => 'Có', '2' => 'Không'),
            )
        ));

        // Đánh giá thợ may
        $this->add(array(
            'name'			=> 'filter_tailors',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Loại sản phẩm -',
                'value_options'	=> array('1' => 'Hàng có sẵn', '2' => 'Hàng sản xuất'),
            )
        ));

        // Kho hàng
        $this->add(array(
            'name'			=> 'filter_branches',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
//                'empty_option'	=> '- Kho -',
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\kovBranchesTable')->listItem(null, array('task' => 'list-all')), array('key' => 'id', 'value' => 'branchName')),
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