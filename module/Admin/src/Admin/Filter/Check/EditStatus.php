<?php
namespace Admin\Filter\Check;

use Zend\InputFilter\InputFilter;

class EditStatus extends InputFilter {
	
	public function __construct($options = null){
	    // Ghi chú
	    $this->add(array(
	        'name'		=> 'status_check_id',
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
	    $this->add(array(
	        'name'		=> 'price_transport',
	        'required'	=> false,
	        'validators'	=> array(
	            // array(
	            //     'name'		=> 'NotEmpty',
	            //     'options'	=> array(
	            //         'messages'	=> array(
	            //             \Zend\Validator\NotEmpty::IS_EMPTY => 'Giá trị này không được để trống'
	            //         )
	            //     ),
	            //     'break_chain_on_failure'	=> true
	            // )
	        )
	    ));
	}
}