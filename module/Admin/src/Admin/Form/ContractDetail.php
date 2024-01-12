<?php
namespace Admin\Form;
use \Zend\Form\Form as Form;

class ContractDetail extends Form {
	
	public function __construct($sm){
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
		
		// Phone
		$this->add(array(
		    'name'			=> 'phone',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control mask_phone',
		    ),
		));
		
		// Name
		$this->add(array(
			'name'			=> 'name',
			'type'			=> 'Text',
			'attributes'	=> array(
				'class'		=> 'form-control',
			),
		));

        // Nhân viên mkt
        $this->add(array(
            'name'			=> 'marketer_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Chọn -',
                'disable_inarray_validator' => true,
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-marketing')), array('key' => 'id', 'value' => 'name')),
            ),
        ));

		// Tỉnh thành
		$this->add(array(
		    'name'			=> 'location_city_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache')), array('key' => 'code', 'value' => 'name')),
		    ),
		));
		
		// Quận huyện
		$this->add(array(
		    'name'			=> 'location_district_id',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		          => 'form-control select2 select2_advance',
		        'value'               => '',
		        'data-table'          => TABLE_LOCATIONS,
		        'data-id'             => 'code',
		        'data-text'           => 'name',
		        'data-parent'         => '',
		        'data-parent-field'   => 'parent',
				'data-parent-name'    => 'location_city_id',
		    ),
		));

        // phường xã
        $this->add(array(
            'name'          => 'location_town_id',
            'type'          => 'Text',
            'attributes'    => array(
                'class'               => 'form-control select2 select2_advance',
                'value'               => '',
                'data-table'          => TABLE_LOCATIONS,
                'data-id'             => 'code',
                'data-text'           => 'name',
                'data-parent'         => '',
                'data-parent-field'   => 'parent',
                'data-parent-name'    => 'location_district_id',
            ),
        ));
		
		// Địa chỉ
		$this->add(array(
		    'name'			=> 'address',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
		    )
		));
		
		// Thành tiền
		$this->add(array(
		    'name'			=> 'price_total',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		  => 'form-control text-danger mask_currency',
		        'value'       => 0,
		        'data-value'  => 0,
		        'readonly'    => 'readonly',
		    )
		));
		
		// Ghi chú sales
		$this->add(array(
		    'name'			=> 'sale_note',
		    'type'			=> 'Text',
		    'attributes'	=> array(
				'class'		  => 'form-control',
		    )
		));

		// Ghi chú GHTK
		$this->add(array(
		    'name'			=> 'ghtk_note',
		    'type'			=> 'Text',
		    'attributes'	=> array(
				'class'		  => 'form-control',
                'value' => 'Cho Kiểm Tra Và Đồng Kiểm, Có Vấn Đề Gì Gọi Cho Shop, Không Tự Ý Hủy Đơn'
		    )
		));
	}
}