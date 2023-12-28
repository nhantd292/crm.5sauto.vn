<?php
namespace Admin\Filter\Bc;

use Zend\InputFilter\InputFilter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

class Transfer extends InputFilter {
	
	public function __construct($options = null){
	    $dbAdapter         = GlobalAdapterFeature::getStaticAdapter();
	    $optionData        = $options['data'];
	    $optionId          = $options['data']['id'];
	    
	    // Phone
	    if(!empty($optionId)) {
	        $excludePhoneMessages = $optionData['phone'] .' đã tồn tại trên hệ thống';
	        $excludePhone = "id != '". $optionId ."' AND phone = '". $optionData['phone'] ."'";
	    } else {
	        $excludePhoneMessages = $optionData['phone'] .' đã tồn tại trên hệ thống';
	        $excludePhone = "phone = '". $optionData['phone'] ."'";
	    }
	    
	    // Phone
		$this->add(array(
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
			        'name'		=> 'Regex',
			        'options'	=> array(
			            'pattern'   => '/^([0]{1})([0-9]{9,10})+$/',
			            'messages'	=> array(
			                \Zend\Validator\Regex::NOT_MATCH => 'Không đúng định dạng số điện thoại'
			            )
			        ),
			        'break_chain_on_failure'	=> true
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
			        'break_chain_on_failure'	=> true
			    )
			)
		));
		
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

		// Ngày sinh
		$this->add(array(
		    'name'		=> 'birthday',
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
		
		// Tỉnh thành
		$this->add(array(
		    'name'		=> 'location_city_id',
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