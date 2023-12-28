<?php
namespace Admin\Form\Search;
use \Zend\Form\Form as Form;

class User extends Form{
    
	public function __construct($sm, $params = NULL){
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
		
		// Status
		$this->add(array(
		    'name'			=> 'filter_status',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Trạng thái -',
		        'value_options'	=> array( 1	=> 'Hiển thị', 0 => 'Không hiển thị'),
		    )
		));
		
		// Nhóm quyền truy cập
		$this->add(array(
		    'name'			=> 'filter_permission',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Quyền truy cập -',
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\PermissionTable')->listItem(null, array('task' => 'cache')), array('key' => 'code', 'value' => 'name')),
		    ),
		));
		
		// Cơ sở làm việc
		$this->add(array(
		    'name'			=> 'filter_company_branch',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Cơ sở làm việc -',
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'company-branch')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		// Phòng ban
		$this->add(array(
		    'name'			=> 'filter_company_department',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Phòng ban -',
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'company-department')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name')),
		    ),
		));
		
		// Vị trí/Chức vụ
		$this->add(array(
		    'name'			=> 'filter_company_position',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Vị trí/Chức vụ -',
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'company-position')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		// Cơ sở kinh doanh
		$this->add(array(
		    'name'			=> 'filter_sale_branch',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Cơ sở kinh doanh -',
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));

        $this->add(array(
            'name'			=> 'filter_kov_branch_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Chọn -',
                'disable_inarray_validator' => true,
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\kovBranchesTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'branchName')),
            ),
        ));
		
		// Đội nhóm kinh doanh
		$this->add(array(
		    'name'			=> 'filter_sale_group',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Đội nhóm -',
		        'value_options'	=> \ZendX\Functions\CreateArray::createSelect($sm->get('Admin\Model\DocumentTable')->listItem(array('data' => array('document_id' => $params['filter_sale_branch'])), array('task' => 'list-parent')), array('key' => 'id', 'value' => 'name,content', 'symbol' => ' - ')),
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