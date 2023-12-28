<?php
namespace Admin\Form\Search;
use \Zend\Form\Form as Form;

class Material extends Form{
    
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
                'value' => date('m')
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
                'value' => date('Y')
            ),
            'options'    => array(
                'empty_option'	=> '- Năm -',
                'value_options' => $year,
            )
        ));

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