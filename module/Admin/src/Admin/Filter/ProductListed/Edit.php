<?php
namespace Admin\Filter\ProductListed;

use Zend\InputFilter\InputFilter;

class Edit extends InputFilter {
	
	public function __construct($options = null){
		
        // Giá
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

        // Sản phẩm
        $this->add(array(
            'name'		=> 'product_id',
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

        // Loại sản phẩm
        $this->add(array(
            'name'		=> 'flooring_id',
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

        // Màu rối
		$this->add(array(
		    'name'		=> 'group_tangled_color_id',
		    'required'	=> false,
		));

		// Màu sắc thảm
		$this->add(array(
		    'name'		=> 'group_carpet_color_id',
		    'required'	=> false,
		));

	}
}