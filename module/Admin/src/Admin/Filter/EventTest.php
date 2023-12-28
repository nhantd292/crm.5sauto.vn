<?php
namespace Admin\Filter;

use Zend\InputFilter\InputFilter;

class EventTest extends InputFilter {
	
	public function __construct($options = null){
	    $exclude = null;
	    if(!empty($options['id'])) {
	        $exclude = array(
	            'field' => 'id',
	            'value' => $options['id']
	        );
	    }
	    
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
	    
		// Ngày diễn ra
		$this->add(array(
			'name'		=> 'public_date',
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
				),
			    array(
			        'name'		=> 'Regex',
			        'options'	=> array(
			            'pattern'   => '/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})+$/',
			            'messages'	=> array(
			                \Zend\Validator\Regex::NOT_MATCH => 'Không đúng định dạng ngày tháng dd/mm/yyyy'
			            )
			        ),
			        'break_chain_on_failure'	=> true
			    )
			)
		));
		
		// Cơ sở
		$this->add(array(
			'name'		=> 'company_branch_id',
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
		
		// Giảng viên
		$this->add(array(
		    'name'		=> 'teacher_ids',
		    'required'	=> false,
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
	}
}