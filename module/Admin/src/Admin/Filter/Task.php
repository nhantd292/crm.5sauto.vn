<?php
namespace Admin\Filter;

use Zend\InputFilter\InputFilter;

class Task extends InputFilter {
	
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
		
		// Danh mục công việc
		$this->add(array(
			'name'		=> 'task_category_id',
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
		
		// Danh mục công việc
		$this->add(array(
			'name'		=> 'task_project_id',
			'required'	=> false,
		));
		
		//Giờ thực hiện
		$this->add(array(
		    'name'		=> 'main_hour',
		    'required'	=> true,
		    'validators'	=> array(
		        array(
		            'name'		=> 'Regex',
		            'options'	=> array(
		                'pattern'   => '/^[0-9.]{0,10}+$/',
		                'messages'	=> array(
		                    \Zend\Validator\Regex::NOT_MATCH => 'Không đúng định dạng số'
		                )
		            ),
		            'break_chain_on_failure'	=> true
		        ),
		    )
		));
		
		// Ngày bắt đầu
		$this->add(array(
		    'name'		=> 'date_begin',
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
		
		// Ngày kết thúc
		$this->add(array(
		    'name'		=> 'date_end',
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
