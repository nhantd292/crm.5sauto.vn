<?php
namespace Admin\Filter\Contract;

use Zend\InputFilter\InputFilter;

class EditNote extends InputFilter {
	
	public function __construct($options = null){
	    // Ghi chú
	    $this->add(array(
	        'name'		=> 'sale_note',
	        'required'	=> false,
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
	    // Ghi chú
	    $this->add(array(
	        'name'		=> 'production_note',
	        'required'	=> false,
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