<?php
namespace Admin\Filter;

use Zend\InputFilter\InputFilter;

class TaskProject extends InputFilter {
	
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
		
		// Company Branch
		$this->add(array(
		    'name'		=> 'company_branch_id',
		    'required'	=> false,
		));
		
		// Company Department
		$this->add(array(
		    'name'		=> 'company_department_id',
		    'required'	=> false,
		));
	}
}