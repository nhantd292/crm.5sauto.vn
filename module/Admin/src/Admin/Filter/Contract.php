<?php
namespace Admin\Filter;

use Zend\InputFilter\InputFilter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

class Contract extends InputFilter {
	
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

        // Phường xã
        $this->add(array(
            'name'		=> 'location_town_id',
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

        // Địa chỉ
        $this->add(array(
            'name'		=> 'address',
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

        // Note GHTK
        $this->add(array(
            'name'		=> 'ghtk_note',
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

//        $this->add(array(
//            'name'		=> 'pick_work_shift',
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

        $this->add(array(
            'name'		=> 'deliver_work_shift',
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
            'name'		=> 'production_type_id',
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

//        $this->add(array(
//            'name'		=> 'size_product_id',
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

//        $this->add(array(
//            'name'		=> 'groupaddressId',
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
	}
}