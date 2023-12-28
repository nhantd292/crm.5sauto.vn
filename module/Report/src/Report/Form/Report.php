<?php
namespace Report\Form;
use \Zend\Form\Form as Form;

class Report extends Form{
    
	public function __construct($sm, $params = null){
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
		
		// Ngày bắt đầu
		$this->add(array(
		    'name'			=> 'date_begin',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control date-picker',
		        'placeholder'	=> 'Từ ngày'
		    )
		));
		
		// Ngày kết thúc
		$this->add(array(
		    'name'			=> 'date_end',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'			=> 'form-control date-picker',
		        'placeholder'	=> 'Đến ngày'
		    )
		));

        // Loại đơn
        $this->add(array(
            'name'			=> 'production_type_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Loại đơn -',
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-type')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
            )
        ));

        $this->add(array(
            'name'			=> 'product_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Sản phẩm -',
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\KovProductsTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'fullName')),
            )
        ));

        // Loại đơn
        $this->add(array(
            'name'			=> 'color_group_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Nhóm nguyên liệu -',
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\ColorGroupTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
            )
        ));

        // Phân loại bảo hành
//        $this->add(array(
//            'name' => 'contract_type_bh',
//            'type' => 'Select',
//            'attributes' => array(
//                'class' => 'form-control select2 select2_basic',
//            ),
//            'options' => array(
//                'value_options' => array('' => 'Bảo hành', 'BH-' => 'Đơn bảo hành'),
//            )
//        ));

        // Phân loại đơn hàng Thường - Bảo hành
        $this->add(array(
            'name' => 'contract_type_bh',
            'type' => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
            ),
            'options' => array(
                'value_options' => array('' => 'Bảo hành', '0' => 'Đơn Thường', '1' => 'Đơn bảo hành'),
            )
        ));
		
		// Danh mục sản phẩm
		$this->add(array(
		    'name'			=> 'product_cat_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Sản phẩm -',
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'list-item')), array('key' => 'id', 'value' => 'name')),
		    )
		));

		// Shipper
		$this->add(array(
		    'name'			=> 'shipper_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
                'multiple'	=> true,
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Nhân viên giao hàng -',
//		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'shipper')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name'))),
            	'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-positons-care')), array('key' => 'id', 'value' => 'name'))
            ),
		));

		// Shipper
		$this->add(array(
		    'name'			=> 'shipper_id_new',
		    'type'			=> 'Select',
		    'attributes'	=> array(
                'multiple'	=> true,
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Nhân viên giao hàng -',
            	'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-positons-care')), array('key' => 'id', 'value' => 'name')),
            )
		));

		// Cơ sở
		$this->add(array(
		    'name'			=> 'sale_branch_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Cơ sở -',
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    )
		));
		
		// Đội nhóm
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

        // Nhân viên marketing
        $this->add(array(
            'name'			=> 'marketer_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Nhân viên marketing -',
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\UserTable')->listItem(array('company_department_id' => 'marketing'), array('task' => 'list-user-department')), array('key' => 'id', 'value' => 'name')),
            )
        ));

        // Nhân viên sales
        $this->add(array(
            'name'			=> 'sale_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Nhân viên sales -',
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\UserTable')->listItem(array('company_department_id' => 'sales'), array('task' => 'list-user-department')), array('key' => 'id', 'value' => 'name')),
            )
        ));

        // Nhân viên care
        $this->add(array(
            'name'			=> 'care_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Nhân viên chăm sóc -',
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\UserTable')->listItem(array('company_department_id' => 'care'), array('task' => 'list-user-department')), array('key' => 'id', 'value' => 'name')),
            )
        ));
		
		// Tỉnh thành
		$this->add(array(
		    'name'			=> 'location_city_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Tỉnh thành -',
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'location-city')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    )
		));
		
		// Điều kiện lọc theo thời gian
		$this->add(array(
			'name'			=> 'date_type',
			'type'			=> 'Select',
			'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
				'value'     => 'created'
			),
			'options'		=> array(
				'value_options'	=> array('thangnay' => 'Tháng này', 'thangtruoc' => 'Tháng trước', 'homnay' => 'Hôm nay', 'homqua' => 'Hôm qua', 'tuantruoc' => 'Tuần trước'),
			)
		));

		$this->add(array(
			'name'			=> 'product_type',
			'type'			=> 'Select',
			'attributes'	=> array(
				'class'		=> 'form-control select2 select2_basic',
				'value'     => 'created'
			),
			'options'		=> array(
                'empty_option'	=> '- Loại SP -',
				'value_options'	=> array('1' => 'Hàng bán sẵn', '2' => 'Hàng sản xuất'),
			)
		));

        // Keyword
        $this->add(array(
            'name'			=> 'code',
            'type'			=> 'Text',
            'attributes'	=> array(
                'placeholder'   => 'Mã đơn hàng',
                'class'			=> 'form-control input-sm',
                'id'			=> 'filter_keyword',
            ),
        ));

        // Month
        $this->add(array(
            'name'       => 'month',
            'type'       => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
                'value' => date('m')
            ),
            'options'    => array(
            //                'empty_option'	=> '- Tháng -',
            'value_options' => array('01' => 'Tháng 01',
                         '02' => 'Tháng 02',
                         '03' => 'Tháng 03',
                         '04' => 'Tháng 04',
                         '05' => 'Tháng 05',
                         '06' => 'Tháng 06',
                         '07' => 'Tháng 07',
                         '08' => 'Tháng 08',
                         '09' => 'Tháng 09',
                         '10' => 'Tháng 10',
                         '11' => 'Tháng 11',
                         '12' => 'Tháng 12'),
            )
        ));

		$arrYear = array();
		for ($i = date('Y')+1; $i > 2011 ; $i--){
		    $arrYear[] = array(
		        'id' => $i,
		        'name' => $i,
		    );
		}
		// Năm
		$this->add(array(
		    'name'			=> 'year',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
//		        'empty_option'	=> '- Năm -',
		        'value_options'	=> \ZendX\Functions\CreateArray::create($arrYear, array('key' => 'id', 'value' => 'name')),
		    )
		));
		
		// Submit
		$this->add(array(
		    'name'			=> 'submit',
		    'type'			=> 'Button',
		    'options' => array(
                'label' => 'Lọc',
            ),
		    'attributes'	=> array(
		        'class'		=> 'btn btn-sm green',
		    ),
		));
	}
}