<?php
namespace Admin\Filter\Contract;

use Zend\InputFilter\InputFilter;

class EditPromotion extends InputFilter {
	
	public function __construct($options = null){
		
	    // Đơn giá
	    $this->add(array(
	        'name'		=> 'price',
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
	    
	    // Lý do sửa
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
	            )
	        )
	    ));
	}
}