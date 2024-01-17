<?php
namespace Admin\Form\Contract;
use \Zend\Form\Form as Form;

class EditPricePaid extends Form {
	
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
		
		// đã thanh toán
		$this->add(array(
		    'name'			=> 'price_paid',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control mask_currency',
		    )
		));

		// trạng thái kế thoán
        $this->add(array(
            'name'			=> 'status_acounting_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Chọn -',
                'disable_inarray_validator' => true,
                'value_options'	=> array('khieu-lai' => 'Khiếu lại', 'da-doi-soat' => 'Đã đối soát'),
            ),
        ));
	}
}