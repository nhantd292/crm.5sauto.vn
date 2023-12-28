<?php
namespace Admin\Filter;

use Zend\InputFilter\InputFilter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

class EduClass extends InputFilter {
	
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
				),
			    array(
			        'name'		=> 'DbNoRecordExists',
			        'options'	=> array(
			            'table'   => TABLE_EDU_CLASS,
			            'field'   => 'name',
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
		
		// Khóa học
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
		        ),
		    )
		));
		
		// Địa điểm
		$this->add(array(
			'name'		=> 'location_id',
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
		
		// Phòng học
		$this->add(array(
			'name'		=> 'room_id',
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
		
		// Lịch khai giảng
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

		// Số học viên tối ta
		$this->add(array(
		    'name'		=> 'student_max',
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

		// Ca học
		$this->add(array(
		    'name'		=> 'time',
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
		
		// Lịch học
		$this->add(array(
		    'name'		=> 'schedule',
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
		
		// Teacher
		$this->add(array(
		    'name'		=> 'teacher_ids',
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
		
		// Coach
		$this->add(array(
		    'name'		=> 'coach_ids',
		    'required'	=> false,
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
		
		// Tình trạng tuyển sinh
		$this->add(array(
		    'name'		=> 'public_status',
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
		
		// Số buổi học
		$this->add(array(
		    'name'		=> 'sessions',
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
		                'pattern'   => '/^[0-9]{0,10}+$/',
		                'messages'	=> array(
		                    \Zend\Validator\Regex::NOT_MATCH => 'Không đúng định dạng số'
		                )
		            ),
		            'break_chain_on_failure'	=> true
		        ),
		        array(
		            'name'		=> 'GreaterThan',
		            'options'	=> array(
		                'min' => 0,
		                'messages'	=> array(
		                    \Zend\Validator\GreaterThan::NOT_GREATER => 'Giá trị này không được để trống'
		                )
		            ),
		            'break_chain_on_failure'	=> true
		        ),
		    )
		));
	}
}