<?php
namespace Admin\Filter\FormData;

use Zend\InputFilter\InputFilter;

class Manager extends InputFilter {
	
	public function __construct($options = null){
		
	    $this->add(array(
		    'name'		=> 'contact_id',
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