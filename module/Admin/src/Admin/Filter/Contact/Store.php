<?php
namespace Admin\Filter\Contact;

use Zend\InputFilter\InputFilter;

class Store extends InputFilter {
	
	public function __construct($options = null){
	    
		// Nguồn liên hệ
		$this->add(array(
		    'name'		=> 'source_group_id',
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