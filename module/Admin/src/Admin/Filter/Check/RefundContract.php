<?php
namespace Admin\Filter\Check;

use Zend\InputFilter\InputFilter;

class RefundContract extends InputFilter {
	
	public function __construct($options = null){
	    // Ghi chú
	    $this->add(array(
	        'name'		=> 'history_content',
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