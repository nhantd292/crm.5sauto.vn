<?php
namespace Admin\Filter\Contract;

use Zend\InputFilter\InputFilter;

class SendGhtk extends InputFilter {
	
	public function __construct($options = null){

//		$this->add(array(
//		    'name'		=> 'groupaddressId',
//		    'required'	=> true,
//		    'validators'	=> array(
//		        array(
//		            'name'		=> 'NotEmpty',
//		            'options'	=> array(
//		                'messages'	=> array(
//		                    \Zend\Validator\NotEmpty::IS_EMPTY => 'Giá trị này không được để trống',
//		                )
//		            ),
//		            'break_chain_on_failure'	=> true
//		        )
//		    )
//		));

		$this->add(array(
		    'name'		=> 'list_data_id',
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
		    'name'		=> 'pick_address_id',
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
		    'name'		=> 'is_freeship',
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
		    'name'		=> 'tags',
		    'required'	=> false,
		));

	}
}