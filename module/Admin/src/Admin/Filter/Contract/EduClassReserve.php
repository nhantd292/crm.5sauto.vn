<?php
namespace Admin\Filter\Contract;

use Zend\InputFilter\InputFilter;

class EduClassReserve extends InputFilter {
    
	public function __construct($options = null){
	    
		// Số buổi đã học
		$this->add(array(
		    'name'		=> 'reserve_sessions',
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
	    
		// Bảo lưu từ ngày
		$this->add(array(
		    'name'		=> 'reserve_date_begin',
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
	    
		// Bạn cuối bảo lưu
		$this->add(array(
		    'name'		=> 'reserve_date_end',
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
	    
		// Nguyên nhân/Lý do bảo lưu
		$this->add(array(
		    'name'		=> 'reserve_content',
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