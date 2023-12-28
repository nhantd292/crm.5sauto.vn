<?php
namespace Admin\Form;
use kcfinder\zipFolder;
use \Zend\Form\Form as Form;

class User extends Form {

	public function __construct($sm, $save_action = null){
		parent::__construct();

		// FORM Attribute
		$this->setAttributes(array(
			'action'	=> '',
			'method'	=> 'POST',
			'class'		=> 'horizontal-form',
			'role'		=> 'form',
			'name'		=> 'adminForm',
			'id'		=> 'adminForm',
		    'enctype'   => 'multipart/form-data'
		));
		
		// Id
		$this->add(array(
		    'name'			=> 'id',
		    'type'			=> 'Hidden',
		));
		
		// name
		$this->add(array(
		    'name'			=> 'name',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		        'id'			=> 'name',
		        'placeholder'	=> 'Họ và tên',
		    ),
		));
		
		// Username
		$this->add(array(
			'name'			=> 'username',
			'type'			=> 'Text',
			'attributes'	=> array(
				'class'			=> 'form-control',
				'id'			=> 'username',
				'placeholder'	=> 'Tên đăng nhập',
			),
		));
		
		// Mã nhân viên
		$this->add(array(
			'name'			=> 'code',
			'type'			=> 'Text',
			'attributes'	=> array(
				'class'			=> 'form-control',
			),
		));
		
		// Email
		$this->add(array(
		    'name'			=> 'email',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		        'id'			=> 'email',
		        'placeholder'	=> 'Email',
		    ),
		));
		
		// Phone
		$this->add(array(
		    'name'			=> 'phone',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control mask_number',
		        'id'			=> 'phone',
		        'placeholder'	=> 'Điện thoại',
		    ),
		));
		
		// Password
		$this->add(array(
		    'name'			=> 'password',
		    'type'			=> 'Password',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		        'id'			=> 'password',
		        'placeholder'	=> 'Mật khẩu',
		    ),
		));

        $permission_list  = $sm->getServiceLocator()->get('Admin\Model\PermissionTable')->listItem(null, array('task' => 'cache'));
        foreach ($permission_list as $item) {
            $option_per_list[] = array(
                'attributes'   => array("onclick" => "checked(this)"),
                'value'        => $item['code'],
                'label'        => $item['name']
            );
        }
		
		// Nhóm quyền truy cập
		$this->add(array(
		    'name'			=> 'permission_ids',
		    'type'			=> 'MultiCheckbox',
		    'options'		=> array(
		        'label_attributes' => array(
		            'class'		=> 'checkbox-inline',
		        ),
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\PermissionTable')->listItem(null, array('task' => 'cache')), array('key' => 'code', 'value' => 'name')),
//		        'value_options'	=> $option_per_list,
		    ),
		));
		
		// Kho hàng kiotviet
		$this->add(array(
		    'name'			=> 'kov_branch_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\kovBranchesTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'branchName')),
		    ),
		));
		
		// Phòng ban Id
		$company_department = $sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'company-department')), array('task' => 'cache'));
        $option_company_department = [];
        foreach ($company_department as $item) {
            $option_company_department[] = array(
                'attributes'	=> array('data-code' => $item['alias']),
                'value'        	=> $item['alias'],
                'label'        	=> $item['name']
            );
        }
		$this->add(array(
		    'name'			=> 'company_department_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> $option_company_department,
		    ),
		));

		
		// Vị trí chức vụ
		$this->add(array(
		    'name'			=> 'company_position_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'company-position')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));

        // Chức vụ kiêm nhiệm
        $this->add(array(
            'name'			=> 'company_position_care_id',
            'type'			=> 'MultiCheckbox',
            'options'		=> array(
                'label_attributes' => array(
                    'class'		=> 'checkbox-inline',
                ),
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'company-position-care')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name')),
            ),
        ));
		
		// Cơ sở kinh doanh
		$this->add(array(
		    'name'			=> 'sale_branch_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    ),
		));
		
		// Company Group
		$this->add(array(
		    'name'			=> 'sale_group_id',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		          => 'form-control select2 select2_advance',
		        'value'               => '',
		        'data-table'          => TABLE_DOCUMENT,
		        'data-id'             => 'id',
		        'data-text'           => 'name,content',
		        'data-parent'         => '',
		        'data-parent-field'   => 'document_id',
		    ),
		));

		// Nhóm sale theo cơ sở
		$this->add(array(
		    'name'			=> 'branch_sale_group_id',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		          => 'form-control select2 select2_advance',
		        'value'               => '',
		        'data-table'          => TABLE_DOCUMENT,
		        'data-id'             => 'id',
		        'data-text'           => 'name,content',
		        'data-parent'         => '',
		        'data-parent-field'   => 'document_id',
                'data-where_type'     => 'sales',
		    ),
		));
		
		// Multi Company Group
		$this->add(array(
		    'name'			=> 'sale_group_hidden',
		    'type'			=> 'Hidden',
		    'attributes'	=> array(
		        'id'	     => 'sale_group_hidden',
		    ),
		));
		
		// Status
		$this->add(array(
			'name'			=> 'status',
			'type'			=> 'Select',
			'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
			    'value'     => 1,
			),
		    'options'		=> array(
		        'value_options'	=> array( 1	=> 'Hiển thị', 0 => 'Không hiển thị'),
		    )
		));

		// notifi
        $this->add(array(
            'name'			=> 'notifi',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Chọn -',
                'disable_inarray_validator' => true,
                'value_options'	=> ['branch' => "Theo chi nhánh", 'all' => "Tất cả chi nhanh"],
            ),
        ));
		
		// Password Status
		$this->add(array(
			'name'			=> 'password_status',
			'type'			=> 'Select',
			'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
			    'value'     => 1,
			),
		    'options'		=> array(
		        'value_options'	=> array( 1	=> 'Có', 0 => 'Không'),
		    )
		));

        // Mã hóa số điện thoại
        $encodePhones = \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'encode-phone')), array('task' => 'list-all')), array('key' => 'content', 'value' => 'name'));
        $encode_phone = [];
        if($save_action == 'edit'){
            $encode_phone = $encodePhones;
        }
        else{
            foreach($encodePhones as $key => $value){
                $encode_phone[] = [
                    'value' => $key,
                    'label' => $value,
                    'selected' => true,
                ];
            }
        }

        $this->add(array(
            'name'			=> 'encode_phone',
            'type'			=> 'MultiCheckbox',
            'options'		=> array(
                'label_attributes' => array(
                    'class'		=> 'checkbox-inline',
                ),
                'value_options'	=> $encode_phone,
            ),
        ));
	}
}