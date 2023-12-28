<?php
namespace Admin\Form\Search;
use \Zend\Form\Form as Form;

class CheckIn extends Form{
    
	public function __construct($sm, $params){
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


        // Month
        $this->add(array(
            'name'       => 'filter_month',
            'type'       => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
//                'value' => date('m')
            ),
            'options'    => array(
//                'empty_option'	=> '- Tháng -',
                'value_options' => array('01' => '01',
                                         '02' => '02',
                                         '03' => '03',
                                         '04' => '04',
                                         '05' => '05',
                                         '06' => '06',
                                         '07' => '07',
                                         '08' => '08',
                                         '09' => '09',
                                         '10' => '10',
                                         '11' => '11',
                                         '12' => '12'),
            )
        ));

        // Year
        $year = array();
        for ($i = (date('Y') + 1); $i >= 2014; $i--) {
            $year[$i] = $i;
        }
        $this->add(array(
            'name'       => 'filter_year',
            'type'       => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
//                'value' => date('Y')
            ),
            'options'    => array(
                'empty_option'	=> '- Năm -',
                'value_options' => $year,
            )
        ));
		
		// Keyword
		$this->add(array(
		    'name'			=> 'filter_keyword',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'placeholder'   => 'Nhân viên',
		        'class'			=> 'form-control input-sm',
		        'id'			=> 'filter_keyword',
		    ),
		));

        $this->add(array(
            'name'			=> 'filter_date_begin',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'		    => 'form-control date-picker not-push',
                'placeholder'	=> 'Từ ngày',
            )
        ));

        $this->add(array(
            'name'			=> 'filter_date_end',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'		    => 'form-control date-picker not-push',
                'placeholder'	=> 'Đến ngày',
            )
        ));

        // Cơ sở kinh doanh
        $this->add(array(
            'name'			=> 'filter_sale_branch',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Cơ sở -',
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
            ),
        ));

        // Đội nhóm marketing
        $this->add(array(
            'name'			=> 'filter_sale_group',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Đội nhóm marketing -',
                'value_options'	=> \ZendX\Functions\CreateArray::createSelect($sm->get('Admin\Model\DocumentTable')->listItem(array('data' => array('document_id' => $params['filter_sale_branch']),'where' => array('type' => 'marketing')), array('task' => 'list-parent')), array('key' => 'id', 'value' => 'name,content', 'symbol' => ' - ')),
            )
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