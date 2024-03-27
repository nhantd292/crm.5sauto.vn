<?php
namespace Admin\Filter;

use Zend\InputFilter\InputFilter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

class Contact extends InputFilter {
	
	public function __construct($options = null){
	    $userInfo      = new \ZendX\System\UserInfo();
        $userInfo      = $userInfo->getUserInfo();
	    
	    $dbAdapter     = GlobalAdapterFeature::getStaticAdapter();
	    $optionId      = $options['id'];
	    $optionData    = $options['data'];
	    $optionItem    = $options['item'];
	    $optionRoute   = $options['route'];

        $required_sales_expected = false;
        if($optionData['history_type_alias'] == DA_CHOT){
            $required_sales_expected = true;
        }

        // Doanh thu tạm tính
        $this->add(array(
            'name'		=> 'sales_expected',
            'required'	=> $required_sales_expected,
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




//        $this->add(array(
//            'name'		=> 'marketer_id',
//            'required'	=> true,
//            'validators'	=> array(
//                array(
//                    'name'		=> 'NotEmpty',
//                    'options'	=> array(
//                        'messages'	=> array(
//                            \Zend\Validator\NotEmpty::IS_EMPTY => 'Giá trị này không được để trống'
//                        )
//                    ),
//                    'break_chain_on_failure'	=> true
//                )
//            )
//        ));
	    
		// Phone
	    if(!empty($optionId)) {
	        $excludePhoneMessages = $optionData['phone'] .' đã tồn tại trong danh sách khách hàng của bạn';
	        $excludePhone = "id != '". $optionId ."' AND phone = '". $optionData['phone'] ."' AND marketer_id = '".$optionItem['marketer_id']."' AND user_id = '".$optionItem['user_id']."'";
	    } else {
	        $excludePhoneMessages = $optionData['phone'] .' đã tồn tại trong danh sách khách hàng của bạn';
	        $excludePhone = "phone = '". $optionData['phone'] ."' AND user_id = '".$userInfo['id']."'";
	    }
		$this->add(array(
			'name'		=> 'phone',
			'required'	=> false,
			'validators'	=> array(
				array(
					'name'		=> 'NotEmpty',
				    'options'	=> array(
				        'messages'	=> array(
				            \Zend\Validator\NotEmpty::IS_EMPTY => 'Giá trị này không được để trống'
				        )
				    ),
					'break_chain_on_failure' => true
				),
			    array(
			        'name'		=> 'Regex',
			        'options'	=> array(
			            'pattern'   => '/^([0-9]{10})+$/',
			            'messages'	=> array(
			                \Zend\Validator\Regex::NOT_MATCH => 'Không đúng định dạng số điện thoại'
			            )
			        ),
			        'break_chain_on_failure' => true
			    ),
			    array(
			        'name'		=> 'DbNoRecordExists',
			        'options'	=> array(
			            'table'   => TABLE_CONTACT,
			            'field'   => 'phone',
			            'adapter' => $dbAdapter,
			            'exclude' => $excludePhone,
			            'messages'	=> array(
			                \Zend\Validator\Db\NoRecordExists::ERROR_RECORD_FOUND => $excludePhoneMessages
			            )
			        ),
			        'break_chain_on_failure' => true
			    )
			)
		));

		// Name
		$this->add(array(
			'name'		=> 'name',
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
				)
			)
		));

		$this->add(array(
			'name'		=> 'contact_group',
			'required'	=> false,
		));
		
		// Sex
		$this->add(array(
		    'name'		=> 'sex',
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

		// Sản phẩm quan tâm
		$this->add(array(
		    'name'		=> 'product_group_id',
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


		// Nguồn khách hàng
		$this->add(array(
		    'name'		=> 'source_group_id',
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
		        )
		    )
		));

		// Nguồn biết đến
		$this->add(array(
		    'name'		=> 'source_known_id',
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
		        )
		    )
		));

		// Email
		$this->add(array(
		    'name'		=> 'email',
		    'required'	=> false,
		    'validators'	=> array(
		        array(
		            'name'		=> 'EmailAddress',
		            'options'	=> array(
		                'messages'	=> array(
		                    \Zend\Validator\EmailAddress::INVALID_FORMAT => 'Không đúng định dạng email',
		                )
		            ),
		            'break_chain_on_failure'	=> true
		        )
		    )
		));
		
		// Phân loại khách hàng
		$this->add(array(
		    'name'		=> 'type',
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
		
		// Nếu
		if($optionData['type'] != 'lost') {
		    $this->add(array(
		        'name'		=> 'lost_id',
		        'required'	=> false,
		    ));
		} else {
		    $this->add(array(
		        'name'		=> 'lost_id',
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

		// Birthday Year
		$this->add(array(
		    'name'		=> 'birthday_year',
		    'required'	=> false
		));
		
		// Tỉnh thành
		$this->add(array(
		    'name'		=> 'location_city_id',
		    'required'	=> false
		));
		
		// Sản phẩm quan tâm
		$this->add(array(
		    'name'		=> 'product_id',
		    'required'	=> false
		));
		
		// Đối tượng
		$this->add(array(
		    'name'		=> 'subject_id',
		    'required'	=> false
		));
		
		// Trường học
		$this->add(array(
		    'name'		=> 'school_name',
		    'required'	=> false
		));
		
		// Chuyên ngành
		$this->add(array(
		    'name'		=> 'major_name',
		    'required'	=> false
		));
		
		// Lớp học
		$this->add(array(
		    'name'		=> 'class_name',
		    'required'	=> false
		));
		
		// Check nhập lịch sử chăm sóc
//		if(!empty($optionData['history_type_id']) || !empty($optionData['history_success']) || !empty($optionData['history_action_id']) || !empty($optionData['history_result_id']) || !empty($optionData['history_return']) || !empty($optionData['history_content'])) {
		    $this->add(array(
		        'name'		=> 'history_action_id',
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
		    
		    $this->add(array(
		        'name'		=> 'history_result_id',
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

		    $this->add(array(
		        'name'		=> 'history_type_id',
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
		    
		    $this->add(array(
		        'name'		=> 'history_return',
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

            $this->add(array(
                'name'		=> 'history_success',
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

		    $this->add(array(
		        'name'		=> 'history_content',
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
		    
		    if($optionData['type'] != 'lost') {
    		    $this->add(array(
    		        'name'		=> 'history_return',
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
//		}
//		else {
//		    $this->add(array(
//		        'name'		=> 'history_action_id',
//		        'required'	=> false,
//		    ));
//
//		    $this->add(array(
//		        'name'		=> 'history_result_id',
//		        'required'	=> false,
//		    ));
//
//		    $this->add(array(
//		        'name'		=> 'history_type_id',
//		        'required'	=> false,
//		    ));
//
//            $this->add(array(
//                'name'		=> 'history_success',
//                'required'	=> false,
//            ));
//		}
	}
}