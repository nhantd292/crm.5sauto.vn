<?php
namespace Admin\Form;
use \Zend\Form\Form as Form;

class TaskProjectContent extends Form {
	
	public function __construct($sm){
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
		
		// Id
		$this->add(array(
		    'name'			=> 'id',
		    'type'			=> 'Hidden',
		));
		
		// Name
		$this->add(array(
			'name'			=> 'name',
			'type'			=> 'Text',
			'attributes'	=> array(
				'class'			=> 'form-control',
				'id'			=> 'name',
				'placeholder'	=> 'Tên',
			),
		));
		
		// Ngày cập nhật
		$this->add(array(
		    'name'			=> 'date',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control date-picker',
		        'placeholder'	=> 'dd/mm/yyyy'
		    )
		));
		
		// Status
		$this->add(array(
			'name'			=> 'status',
			'type'			=> 'Select',
			'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
			),
		    'options'		=> array(
		        'value_options'	=> array( 0 => 'Chưa triển khai', 1 => 'Đã hoàn thành', 2 => 'Đang thực hiện'),
		    )
		));
		
		// Kịch bản: bởi ai
		$this->add(array(
		    'name'			=> 'content_by',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\UserTable')->listItem(array('company_department_id' => $userInfo['company_department_id']), array('task' => 'cache-department')), array('key' => 'id', 'value' => 'name')),
		    )
		));
		
		// Kịch bản: link
		$this->add(array(
		    'name'			=> 'content_link',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		    )
		));
		
		// Kịch bản: Ngày thực hiện
		$this->add(array(
		    'name'			=> 'content_date',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control date-picker',
		        'placeholder'	=> 'dd/mm/yyyy'
		    )
		));
		
		// Kịch bản: Trạng thái
		$this->add(array(
		    'name'			=> 'content_status',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'value_options'	=> array( 0 => 'Chưa thực hiện', 1 => 'Đã hoàn thành', 2 => 'Chưa hoàn thành'),
		    )
		));
		
		// Quay: bởi ai
		$this->add(array(
		    'name'			=> 'camera_by',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\UserTable')->listItem(array('company_department_id' => $userInfo['company_department_id']), array('task' => 'cache-department')), array('key' => 'id', 'value' => 'name')),
		    )
		));
		
		// Quay: Ngày thực hiện
		$this->add(array(
		    'name'			=> 'camera_date',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control date-picker',
		        'placeholder'	=> 'dd/mm/yyyy'
		    )
		));
		
		// Quay: Trạng thái
		$this->add(array(
		    'name'			=> 'camera_status',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'value_options'	=> array( 0 => 'Chưa thực hiện', 1 => 'Đã hoàn thành', 2 => 'Chưa hoàn thành'),
		    )
		));
		
		// Dựng: bởi ai
		$this->add(array(
		    'name'			=> 'editor_by',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\UserTable')->listItem(array('company_department_id' => $userInfo['company_department_id']), array('task' => 'cache-department')), array('key' => 'id', 'value' => 'name')),
		    )
		));
		
		// Dựng: Ngày thực hiện
		$this->add(array(
		    'name'			=> 'editor_date',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control date-picker',
		        'placeholder'	=> 'dd/mm/yyyy'
		    )
		));
		
		// Dựng: Trạng thái
		$this->add(array(
		    'name'			=> 'editor_status',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'value_options'	=> array( 0 => 'Chưa thực hiện', 1 => 'Đã hoàn thành', 2 => 'Chưa hoàn thành'),
		    )
		));
		
		// Youtube: upload bởi ai
		$this->add(array(
		    'name'			=> 'youtube_by',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\UserTable')->listItem(array('company_department_id' => $userInfo['company_department_id']), array('task' => 'cache-department')), array('key' => 'id', 'value' => 'name')),
		    )
		));
		
		// Youtube: link
		$this->add(array(
		    'name'			=> 'youtube_link',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		    )
		));
		
		// Youtube: Ngày thực hiện
		$this->add(array(
		    'name'			=> 'youtube_date',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control date-picker',
		        'placeholder'	=> 'dd/mm/yyyy'
		    )
		));
		
		// Youtube: Trạng thái
		$this->add(array(
		    'name'			=> 'youtube_status',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'value_options'	=> array( 0 => 'Chưa thực hiện', 1 => 'Đã hoàn thành', 2 => 'Chưa hoàn thành'),
		    )
		));
		
		// Youtube: View
		$this->add(array(
		    'name'			=> 'youtube_view',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control mask_currency',
		    )
		));
		
		// Youtube: Comment
		$this->add(array(
		    'name'			=> 'youtube_comment',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control mask_currency',
		    )
		));
		
		// Youtube: Like
		$this->add(array(
		    'name'			=> 'youtube_like',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control mask_currency',
		    )
		));
		
		// Youtube: Dislike
		$this->add(array(
		    'name'			=> 'youtube_dislike',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control mask_currency',
		    )
		));
		
		// Facebook: upload bởi ai
		$this->add(array(
		    'name'			=> 'facebook_by',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\UserTable')->listItem(array('company_department_id' => $userInfo['company_department_id']), array('task' => 'cache-department')), array('key' => 'id', 'value' => 'name')),
		    )
		));
		
		// Facebook: link
		$this->add(array(
		    'name'			=> 'facebook_link',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control',
		    )
		));
		
		// Facebook: Ngày thực hiện
		$this->add(array(
		    'name'			=> 'facebook_date',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control date-picker',
		        'placeholder'	=> 'dd/mm/yyyy'
		    )
		));
		
		// Facebook: Trạng thái
		$this->add(array(
		    'name'			=> 'facebook_status',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'value_options'	=> array( 0 => 'Chưa thực hiện', 1 => 'Đã hoàn thành', 2 => 'Chưa hoàn thành'),
		    )
		));
		
		// Facebook: Like
		$this->add(array(
		    'name'			=> 'facebook_like',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control mask_currency',
		    )
		));
		
		// Facebook: Comment
		$this->add(array(
		    'name'			=> 'facebook_comment',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control mask_currency',
		    )
		));
		
		// Facebook: Share
		$this->add(array(
		    'name'			=> 'facebook_share',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control mask_currency',
		    )
		));
	}
}