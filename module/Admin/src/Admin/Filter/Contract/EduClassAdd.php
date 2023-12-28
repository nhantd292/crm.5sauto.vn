<?php
namespace Admin\Filter\Contract;

use Zend\InputFilter\InputFilter;

class EduClassAdd extends InputFilter {
    
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
		                    \Zend\Validator\NotEmpty::IS_EMPTY => 'Phải chọn ít nhất 1 vật phẩm'
		                )
		            ),
		            'break_chain_on_failure'	=> true
		        )
		    )
		));
	}
}