<?php
namespace Admin\Filter;

use Zend\InputFilter\InputFilter;

class PermissionList extends InputFilter {
	
	public function __construct($options = null){
	    
	    // Name
	    $this->add(array(
	        'name'		=> 'name',
	        'required'	=> true,
	        'filters'	=> array(
	            array( 'name' 	=> 'StringTrim' ),
	        ),
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
	        )
	    ));
	    
	    // Module
	    $this->add(array(
	        'name'		=> 'module',
	        'required'	=> true,
	        'filters'	=> array(
	            array( 'name' 	=> 'StringTrim' ),
	        ),
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
	        )
	    ));
	    
	    // Controller
	    $this->add(array(
	        'name'		=> 'controller',
	        'required'	=> true,
	        'filters'	=> array(
	            array( 'name' 	=> 'StringTrim' ),
	        ),
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
	        )
	    ));
	    
	    // Action
	    $this->add(array(
	        'name'		=> 'action',
	        'required'	=> true,
	        'filters'	=> array(
	            array( 'name' 	=> 'StringTrim' ),
	        ),
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
		        ),
		        array(
		            'name'		=> 'NotEmpty',
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
		            'name'		=> 'Digits',
		            'break_chain_on_failure'	=> true
		        ),
		        array(
		            'name'		=> 'NotEmpty',
		            'break_chain_on_failure'	=> true
		        )
		    )
		));
	}
}