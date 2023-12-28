<?php
namespace Admin\Form;
use \Zend\Form\Form as Form;

class KovDiscounts extends Form {
	
	public function __construct($sm, $data=null){
		parent::__construct();
		
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

		// Thông tin khuyến mãi
		$this->add(array(
		    'name'			=> 'code',
		    'type'			=> 'Text',
		    'attributes'	=> array(
				'class'		=> 'form-control',
                'placeholder' => 'Mã tự sinh',
		    ),
		));

		$this->add(array(
		    'name'			=> 'name',
		    'type'			=> 'Text',
		    'attributes'	=> array(
				'class'		=> 'form-control',
		    ),
		));

        $this->add(array(
            'name'			=> 'status',
            'type'			=> 'Radio',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic radio-status',
                'value'     => 1,
            ),
            'options'		=> array(
                'disable_inarray_validator' => true,
                'value_options'	=> array('1' => 'Kích hoạt', '2' => 'Chưa áp dụng'),
            )
        ));

		$this->add(array(
		    'name'			=> 'note',
		    'type'			=> 'Text',
		    'attributes'	=> array(
				'class'		  => 'form-control',
		    )
		));

		// Thời gian áp dụng
		$this->add(array(
            'name'			=> 'date_begin',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'			=> 'form-control date-picker',
                'placeholder'	=> 'dd/mm/yyyy',
                'value' => date('d/m/Y'),
            )
        ));

		$this->add(array(
            'name'			=> 'date_end',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'			=> 'form-control date-picker',
                'placeholder'	=> 'dd/mm/yyyy',
                'value' => date('t/m/Y'),
            )
        ));

		// Phạm vi áp dụng
        $this->add(array(
            'name'			=> 'discounts_range_branchs',
            'type'			=> 'Radio',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic radio-status',
                'value' => 'all',
            ),
            'options'		=> array(
                'disable_inarray_validator' => true,
                'value_options'	=> array('all' => 'Toàn hệ thống', 'some' => 'Chọn chi nhánh'),
            ),
        ));
        $this->add(array(
            'name'			=> 'discounts_range_branchs_detail',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'     => 'form-control select2_basic discounts_range_branchs_detail',
                'multiple'  => 'multiple',
                'disabled'  => 'disabled',
            ),
            'options'		=> array(
                'disable_inarray_validator' => true,
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\KovBranchesTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'branchName')),
            ),
        ));

        $this->add(array(
            'name'			=> 'discounts_range_sales',
            'type'			=> 'Radio',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic radio-status',
                'value' => 'all',
            ),
            'options'		=> array(
                'disable_inarray_validator' => true,
                'value_options'	=> array('all' => 'Toàn bộ người bán', 'some' => 'Chọn người bán'),
            ),
        ));
        $this->add(array(
            'name'			=> 'discounts_range_sales_detail',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'     => 'form-control select2_basic discounts_range_sales_detail',
                'multiple'  => 'multiple',
                'disabled'  => 'disabled',
            ),
            'options'		=> array(
                'disable_inarray_validator' => true,
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'name, username')),
            ),
        ));


        $this->add(array(
            'name'			=> 'discounts_range_customers',
            'type'			=> 'Radio',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic radio-status',
                'value' => 'all',
            ),
            'options'		=> array(
                'disable_inarray_validator' => true,
                'value_options'	=> array('all' => 'Toàn bộ khách hàng', 'some' => 'Chọn nhóm khách hàng'),
            ),
        ));
        $this->add(array(
            'name'			=> 'discounts_range_customers_detail',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'     => 'form-control select2_basic discounts_range_customers_detail',
                'multiple'  => 'multiple',
                'disabled'  => 'disabled',
            ),
            'options'		=> array(
                'disable_inarray_validator' => true,
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'contact-group')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
            ),
        ));

		// Hình thức áp dụng
        $this->add(array(
            'name'			=> 'discounts_type',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
                'value' => 'hoa-don',
            ),
            'options'		=> array(
//                'empty_option'	=> '- chọn -',
                'disable_inarray_validator' => true,
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'discounts-type')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name')),
            ),
        ));

        $discounts_option_arr = [];
        if($data['discounts_type']){
            $type = $sm->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(array('alias' => $data['discounts_type']), array('task' => 'by-custom-alias'));
            $discounts_option_arr = \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('document_id' => $type->id)), array('task' => 'list-all')), array('key' => 'alias', 'value' => 'name'));
        }
        $attribute = array(
            'class'		=> 'form-control select2 select2_basic',
        );
        if($data['discounts_option']){
            $attribute['value'] = $data['discounts_option'];
        }

        $this->add(array(
            'name'			=> 'discounts_option',
            'type'			=> 'Select',
            'attributes'	=> $attribute,
            'options'		=> array(
                'disable_inarray_validator' => true,
                'value_options'	=> $discounts_option_arr
            ),
        ));
	}
}