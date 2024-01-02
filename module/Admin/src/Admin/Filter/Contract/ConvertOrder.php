<?php
namespace Admin\Filter\Contract;

use Zend\InputFilter\InputFilter;

class ConvertOrder extends InputFilter {
	
	public function __construct($options = null){
        $unit_transport = $options['data']['unit_transport'];
        $require = $unit_transport == '5sauto' ? true : false;
	    // Đơn vị vận chuyển
	    $this->add(array(
	        'name'		=> 'unit_transport',
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
	    // Mã đơn hàng vận chuyển
	    $this->add(array(
	        'name'		=> 'ghtk_code',
	        'required'	=> !$require,
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
	    // Nhân viên giao hàng
	    $this->add(array(
	        'name'		=> 'shipper_id',
	        'required'	=> $require,
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
	    // Trạng thái đơn hàng
	    $this->add(array(
	        'name'		=> 'ghtk_status',
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
	    // cước vận chuyển
	    $this->add(array(
	        'name'		=> 'price_transport',
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