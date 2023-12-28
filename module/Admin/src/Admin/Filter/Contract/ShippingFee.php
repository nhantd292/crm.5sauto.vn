<?php
namespace Admin\Filter\Contract;

use Zend\InputFilter\InputFilter;

class ShippingFee extends InputFilter {
	
	public function __construct($options = null){
	    // tiền giảm trừ doanh thu
	    $this->add(array(
	        'name'		=> 'shipping_fee',
	        'required'	=> true,
	        'validators'	=> array(
	            array(
	                'name'		=> 'NotEmpty',
	                'options'	=> array(
	                    'messages'	=> array(
	                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Giá trị này không được để trống'
	                    )
	                ),
	                'break_chain_on_failure'	=> true
	            )
	        )
	    ));
	}
}