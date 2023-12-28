<?php
namespace Admin\Filter\Contract;

use Zend\InputFilter\InputFilter;

class EduClassLeaveCancel extends InputFilter {
    
	public function __construct($options = null){
	    
		// Nguyên nhân/Lý do hủy nghỉ học
		$this->add(array(
		    'name'		=> 'leave_cancel_content',
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