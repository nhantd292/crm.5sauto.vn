<?php
namespace Admin\Filter\Production;

use Zend\InputFilter\InputFilter;

class GuaranteeAdd extends InputFilter {
	
	public function __construct($options = null){
		$this->add(array(
		    'name'		=> 'guarantee_date',
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
		    'name'		=> 'guarantee_note',
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