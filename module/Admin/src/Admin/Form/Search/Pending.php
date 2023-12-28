<?php
namespace Admin\Form\Search;
use \Zend\Form\Form as Form;

class Pending extends Form{ 
    
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

		// Cơ sở
		$this->add(array(
		    'name'			=> 'filter_sale_branch',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Cơ sở kinh doanh -',
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch'), 'key' => $userInfo['sale_branch_id']), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    )
		));
		
		// Sản phẩm
		$this->add(array(
			'name'			=> 'filter_product',
			'type'			=> 'Select',
			'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
			),
			'options'		=> array(
				'empty_option'	=> '- Sản phẩm -',
				'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
			)
		));
		
		// Lớp học
		$edu_class = $sm->get('Admin\Model\EduClassTable')->listItem(null, array('task' => 'cache'));
		$edu_class_data = array();
		//if(!empty($params['filter_product'])) {
		    foreach ($edu_class AS $key => $val) {
		        $edu_class_data[$val['id']] = $val['name'] .' ('. $val['student_total'] .'/'. $val['student_max'] .')';
		    }
		//}
		$this->add(array(
			'name'			=> 'filter_edu_class',
			'type'			=> 'Select',
			'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
			),
			'options'		=> array(
				'empty_option'	=> '- Lớp học -',
				'value_options'	=> $edu_class_data,
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