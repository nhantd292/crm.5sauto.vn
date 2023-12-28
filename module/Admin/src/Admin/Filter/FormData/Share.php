<?php
namespace Admin\Filter\FormData;

use Zend\InputFilter\InputFilter;

class Share extends InputFilter {
	
	public function __construct($options = null){
		
	    $this->add(array(
		    'name'		=> 'user_id',
		    'required'	=> true,
		    'validators'	=> array(
		        array(
		            'name'		=> 'NotEmpty',
		            'options'	=> array(
		                'messages'	=> array(
		                    \Zend\Validator\NotEmpty::IS_EMPTY => 'Giá trị này không được để trống',
		                )
		            ),
		            'break_chain_on_failure'	=> true
		        )
		    )
		));

		$this->add(array(
		    'name'		=> 'sale_level',
		    'required'	=> true,
		    'validators'	=> array(
		        array(
		            'name'		=> 'NotEmpty',
		            'options'	=> array(
		                'messages'	=> array(
		                    \Zend\Validator\NotEmpty::IS_EMPTY => 'Giá trị này không được để trống',
		                )
		            ),
		            'break_chain_on_failure'	=> true
		        )
		    )
		));

	}
}