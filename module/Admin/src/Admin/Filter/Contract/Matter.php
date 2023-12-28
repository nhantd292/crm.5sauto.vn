<?php
namespace Admin\Filter\Contract;

use Zend\InputFilter\InputFilter;

class Matter extends InputFilter {
    
	public function __construct($options = null){
	    
		// Ngày phát vật phẩm
		$this->add(array(
		    'name'		=> 'date',
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
	    
		// Danh sách vật phẩm
		$this->add(array(
		    'name'		=> 'matter_ids',
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