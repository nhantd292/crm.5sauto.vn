<?php
namespace Admin\Filter;

use Zend\InputFilter\InputFilter;

class EventDemo extends InputFilter {
	
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
		
		// Cơ sở kinh doanh
		$this->add(array(
			'name'		=> 'sale_branch_id',
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

		// Giảng viên
		$this->add(array(
			'name'		=> 'teacher_ids',
			'required'	=> false,
		));
		
		// Coache
		$this->add(array(
			'name'		=> 'coach_ids',
			'required'	=> false,
		));
	}
}