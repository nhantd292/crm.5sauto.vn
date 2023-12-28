<?php
namespace Admin\Filter\TangledColor;

use Zend\InputFilter\InputFilter;

class Edit extends InputFilter {
	
	public function __construct($options = null){

        // Tên
        $this->add(array(
            'name'		=> 'name',
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

        // Đơn vị tính
		$this->add(array(
			'name'		=> 'parent',
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