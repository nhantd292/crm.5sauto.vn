<?php
namespace Admin\Form\Contract;
use \Zend\Form\Form as Form;

class SendGhn extends Form {
	
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
			'enctype'		=> 'multipart/form-data'
		));
		
		// Id
		$this->add(array(
		    'name'			=> 'id',
		    'type'			=> 'Hidden',
		));

		// Modal
		$this->add(array(
		    'name'			=> 'modal',
		    'type'			=> 'Hidden',
		    'attributes'	=> array(
		        'value'		=> 'success',
		    ),
		));
		
		// Số lượng data chọn để chia
		$this->add(array(
			'name'			=> 'numbers_data',
			'type'			=> 'Text',
			'required'		=> true,
			'attributes'	=> array(
				'class'		=> 'form-control',
				'readonly'	=> 'readonly'
			),
		));

		// Danh sách id đơn hàng
		$this->add(array(
			'name'			=> 'list_data_id',
			'type'			=> 'Hidden',
			'required'		=> false,
		));

        $this->add(array(
            'name'			=> 'required_note',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
//                'empty_option'	=> '- Chọn -',
                'disable_inarray_validator' => true,
                'value_options'	=> array(
                    'CHOXEMHANGKHONGTHU' => 'Cho xem hàng không cho thử',
                    'CHOTHUHANG' => 'Cho thử hàng',
                    'KHONGCHOXEMHANG' => 'Không cho xem hàng',
                )
            ),
        ));

        // Kho gửi hàng
        $shifts = json_decode($sm->ghn_call("/shift/date"), [], 'GET', );
        $inventorys = \ZendX\Functions\CreateArray::create($groupaddress['data'], array('key' => 'groupaddressId', 'value' => 'name,address', 'sprintf' =>'%s - %s'));

        $this->add(array(
            'name'			=> 'groupaddressId',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Kho gửi hàng -',
                'disable_inarray_validator' => true,
                'value_options'	=> $inventorys,
            )
        ));

	}
}