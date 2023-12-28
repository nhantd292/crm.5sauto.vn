<?php
namespace Admin\Filter\Contract;

use Zend\InputFilter\InputFilter;

class EduClassMove extends InputFilter {
    
	public function __construct($options = null){
	    
		// Lớp học
		$this->add(array(
		    'name'		=> 'edu_class_id',
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
	    
		// Nguyên nhân/Lý do chuyển lớp
		$this->add(array(
		    'name'		=> 'move_content',
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