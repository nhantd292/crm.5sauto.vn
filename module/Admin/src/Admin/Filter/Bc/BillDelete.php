<?php
namespace Admin\Filter\Bc;

use Zend\InputFilter\InputFilter;

class BillDelete extends InputFilter {
    
	public function __construct($options = null){
		$this->add(array(
		    'name'		=> 'note_log',
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
		        ),
		    )
		));
	}
}