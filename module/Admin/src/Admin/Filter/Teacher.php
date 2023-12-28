<?php
namespace Admin\Filter;

use Zend\InputFilter\InputFilter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

class Teacher extends InputFilter {
	
	public function __construct($options = null){
	    $exclude = null;
	    $requirePassword = true;
	    if(!empty($options['id'])) {
	        $exclude = array(
		        'field' => 'id',
		        'value' => $options['id']
		    );
	        $requirePassword = false;
	    }
	    
	    // name
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
	            ),
	        )
	    ));
	    
		// Username
		$this->add(array(
			'name'		=> 'username',
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
					'name'		=> 'DbNoRecordExists',
					'options'	=> array(
						'table'   => TABLE_USER,
						'field'   => 'username',
						'adapter' => GlobalAdapterFeature::getStaticAdapter(),
					    'exclude' => $exclude,
					    'messages'	=> array(
					        \Zend\Validator\Db\NoRecordExists::ERROR_RECORD_FOUND => 'Đã tồn tại'
					    )
					),
					'break_chain_on_failure'	=> true
				)
			)
		));
		
		// Email
		/* $this->add(array(
		    'name'		=> 'email',
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
		            'name'		=> 'EmailAddress',
		            'options'	=> array(
		                'messages'	=> array(
		                    \Zend\Validator\EmailAddress::INVALID_FORMAT      => 'Không đúng định dạng email',
		                )
		            ),
		            'break_chain_on_failure'	=> true
		        ),
		        array(
					'name'		=> 'DbNoRecordExists',
					'options'	=> array(
						'table'   => TABLE_USER,
						'field'   => 'email',
						'adapter' => GlobalAdapterFeature::getStaticAdapter(),
					    'exclude' => $exclude,
					    'messages'	=> array(
					        \Zend\Validator\Db\NoRecordExists::ERROR_RECORD_FOUND => 'Đã tồn tại'
					    )
					),
					'break_chain_on_failure'	=> true
				)
		    )
		)); */
		
		// Phone
		/* $this->add(array(
		    'name'		=> 'phone',
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
		            'name'		=> 'DbNoRecordExists',
		            'options'	=> array(
		                'table'   => TABLE_USER,
		                'field'   => 'phone',
		                'adapter' => GlobalAdapterFeature::getStaticAdapter(),
		                'exclude' => $exclude,
		                'messages'	=> array(
		                    \Zend\Validator\Db\NoRecordExists::ERROR_RECORD_FOUND => 'Đã tồn tại'
		                )
		            ),
		            'break_chain_on_failure'	=> true
		        )
		    )
		)); */
		
		// Password
		$this->add(array(
		    'name'		=> 'password',
		    'required'	=> $requirePassword,
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
		
		// Nhóm quyền truy cập
		$this->add(array(
		    'name'		=> 'permission_ids',
		    'required'	=> true,
		    'validators'	=> array(
		        array(
		            'name'		=> 'NotEmpty',
		            'options'	=> array(
		                'messages'	=> array(
		                    \Zend\Validator\NotEmpty::IS_EMPTY => 'Giá trị này không được để trống',
		                )
		            ),
		            'break_chain_on_failure'	=> true
		        )
		    )
		));
		
		// Company Branch
		$this->add(array(
		    'name'		=> 'company_branch_id',
		    'required'	=> true,
		    'validators'	=> array(
		        array(
		            'name'		=> 'NotEmpty',
		            'options'	=> array(
		                'messages'	=> array(
		                    \Zend\Validator\NotEmpty::IS_EMPTY => 'Giá trị này không được để trống',
		                )
		            ),
		            'break_chain_on_failure'	=> true
		        )
		    )
		));
		
		// Company Department
		$this->add(array(
		    'name'		=> 'company_department_id',
		    'required'	=> true,
		    'validators'	=> array(
		        array(
		            'name'		=> 'NotEmpty',
		            'options'	=> array(
		                'messages'	=> array(
		                    \Zend\Validator\NotEmpty::IS_EMPTY => 'Giá trị này không được để trống',
		                )
		            ),
		            'break_chain_on_failure'	=> true
		        )
		    )
		));
		
		// Company Position
		$this->add(array(
		    'name'		=> 'company_position_id',
		    'required'	=> true,
		    'validators'	=> array(
		        array(
		            'name'		=> 'NotEmpty',
		            'options'	=> array(
		                'messages'	=> array(
		                    \Zend\Validator\NotEmpty::IS_EMPTY => 'Giá trị này không được để trống',
		                )
		            ),
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
		            'options'	=> array(
		                'messages'	=> array(
		                    \Zend\Validator\NotEmpty::IS_EMPTY => 'Giá trị này không được để trống',
		                )
		            ),
		            'break_chain_on_failure'	=> true
		        )
		    )
		));
	}
}