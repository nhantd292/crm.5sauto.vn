<?php
namespace Admin\Filter;

use Zend\InputFilter\InputFilter;

class Login extends InputFilter {
	
	public function __construct($options = null){
		
	    // username
	    $this->add(array(
	        'name'		=> 'username',
	        'required'	=> true,
	        'validators'	=> array(
	            array(
	                'name'		=> 'NotEmpty',
	                'options'	=> array(
	                    'messages'	=> array(
	                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Giá trị này không được để trống'
	                    )
	                ),
	                'break_chain_on_failure' => true
	            )
	        )
	    ));
	    
	    // password
	    $this->add(array(
	        'name'		=> 'password',
	        'required'	=> true,
	        'validators'	=> array(
	            array(
	                'name'		=> 'NotEmpty',
	                'options'	=> array(
	                    'messages'	=> array(
	                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Giá trị này không được để trống'
	                    )
	                ),
	                'break_chain_on_failure' => true
	            )
	        )
	    ));
	    
	}
}