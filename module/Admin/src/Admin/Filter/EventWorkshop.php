<?php
namespace Admin\Filter;

use Zend\InputFilter\InputFilter;

class EventWorkshop extends InputFilter {
	
	public function __construct($options = null){
		// Name
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
		
		// Giá tiền dự kiên
		$this->add(array(
			'name'		=> 'price_expected',
			'required'	=> false,
			'validators'	=> array(
				array(
					'name'		=> 'Regex',
					'options'	=> array(
						'pattern'   => '/^[0-9,]{2,10}+$/',
						'messages'	=> array(
							\Zend\Validator\Regex::NOT_MATCH => 'Không đúng định dạng số'
						)
					),
					'break_chain_on_failure'	=> true
				),
			)
		));
		
		// Ordering
		$this->add(array(
		    'name'		=> 'ordering',
		    'required'	=> true,
		    'validators'	=> array(
		        array(
		            'name'		=> 'Digits',
		            'break_chain_on_failure'	=> true
		        )
		    )
		));
		
		// Status
		$this->add(array(
		    'name'		=> 'status',
		    'required'	=> true,
		    'validators'	=> array(
		        array(
		            'name'		=> 'NotEmpty',
		            'break_chain_on_failure'	=> true
		        )
		    )
		));
		
		$this->add(array(
		    'name'		=> 'time',
		    'required'	=> false,
		));
	}
}