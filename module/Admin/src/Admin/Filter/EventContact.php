<?php
namespace Admin\Filter;

use Zend\InputFilter\InputFilter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

class EventContact extends InputFilter {
	
	public function __construct($options = null){
	    $dbAdapter     = GlobalAdapterFeature::getStaticAdapter();
	    $optionId      = $options['id'];
	    $optionData    = $options['data'];
	    $optionRoute   = $options['route'];
	    
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
	    
	    // Nguồn khách hàng
	    $this->add(array(
	        'name'		=> 'source_group_id',
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
	    
	    // Nguồn biết đến
	    $this->add(array(
	        'name'		=> 'source_known_id',
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
	    
	    // Quận huyện
	    $this->add(array(
	        'name'		=> 'location_district_id',
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
	    
	    // Đối tượng
	    $this->add(array(
	        'name'		=> 'subject_id',
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
	    
	    if($optionData['subject_id'] == '153897020539z686271u83') {
	        // Trường học
	        $this->add(array(
	            'name'		=> 'school_id',
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
	    
	        // Chuyên ngành
	        $this->add(array(
	            'name'		=> 'major_id',
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
	    } else {
	        // Trường học
	        $this->add(array(
	            'name'		=> 'school_id',
	            'required'	=> false,
	        ));
	    
	        // Chuyên ngành
	        $this->add(array(
	            'name'		=> 'major_id',
	            'required'	=> false,
	        ));
	    }
	}
}