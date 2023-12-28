<?php
namespace Admin\Filter;

use Zend\InputFilter\InputFilter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

class FormData extends InputFilter {
	
	public function __construct($options = null){
	    $userInfo      = new \ZendX\System\UserInfo();
	     
	    $dbAdapter     = GlobalAdapterFeature::getStaticAdapter();
	    $optionId      = $options['id'];
	    $optionData    = $options['data'];
	    $optionRoute   = $options['route'];
	    
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
		
	   if(empty($options['id'])) {
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
    			)
    		));
	    }
		
		// Kênh marketing
		$this->add(array(
		    'name'		=> 'marketing_channel_id',
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
//		$this->add(array(
//		    'name'		=> 'product_id',
//		    'required'	=> true,
//		    'validators'	=> array(
//		        array(
//		            'name'		=> 'NotEmpty',
//		            'options'	=> array(
//		                'messages'	=> array(
//		                    \Zend\Validator\NotEmpty::IS_EMPTY => 'Giá trị này không được để trống'
//		                )
//		            ),
//		            'break_chain_on_failure'	=> true
//		        )
//		    )
//		));

		// Sản phẩm quan tâm
		$this->add(array(
		    'name'		=> 'product_group_id',
		    'required'	=> false,
//		    'validators'	=> array(
//		        array(
//		            'name'		=> 'NotEmpty',
//		            'options'	=> array(
//		                'messages'	=> array(
//		                    \Zend\Validator\NotEmpty::IS_EMPTY => 'Giá trị này không được để trống'
//		                )
//		            ),
//		            'break_chain_on_failure'	=> true
//		        )
//		    )
		));

		// Giới tính
		$this->add(array(
		    'name'		=> 'sex',
		    'required'	=> false,
		));

		// Tỉnh thành
		$this->add(array(
		    'name'		=> 'city_id',
		    'required'	=> false,
		));
	}
}