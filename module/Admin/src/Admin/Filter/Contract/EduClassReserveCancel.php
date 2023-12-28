<?php
namespace Admin\Filter\Contract;

use Zend\InputFilter\InputFilter;

class EduClassReserveCancel extends InputFilter {
    
	public function __construct($options = null){
	    
		// Nguyên nhân/Lý do hủy bảo lưu
		$this->add(array(
		    'name'		=> 'reserve_cancel_content',
		    'required'	=> true,
		    'validators' => array(
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