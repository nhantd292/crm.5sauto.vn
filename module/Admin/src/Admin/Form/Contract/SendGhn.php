<?php
namespace Admin\Form\Contract;
use \Zend\Form\Form as Form;

class SendGhn extends Form {
	
	public function __construct($sm, $options){
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
                'empty_option'	=> '- Chọn -',
                'disable_inarray_validator' => true,
                'value_options'	=> array(
                    'CHOXEMHANGKHONGTHU' => 'Cho xem hàng không cho thử',
                    'CHOTHUHANG' => 'Cho thử hàng',
                    'KHONGCHOXEMHANG' => 'Không cho xem hàng',
                )
            ),
        ));

        // Ca lấy hàng
        $shifts = json_decode($sm->ghn_call("/shift/date", [], 'GET', $options['token']), true);
        $shifts_array = \ZendX\Functions\CreateArray::create($shifts['data'], array('key' => 'id', 'value' => 'title'));

        $this->add(array(
            'name'			=> 'pick_shift',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Ca lấy hàng -',
                'disable_inarray_validator' => true,
                'value_options'	=> $shifts_array,
            )
        ));

        // Cửa hàng
        $shops = json_decode($sm->ghn_call("/shop/all", [], 'GET', $options['token']), true);
        $shops_array = \ZendX\Functions\CreateArray::create($shops['data']['shops'], array('key' => '_id', 'value' => 'name'));

        $this->add(array(
            'name'			=> 'shopid',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- ID Shop -',
                'disable_inarray_validator' => true,
                'value_options'	=> $shops_array,
            )
        ));

	}
}