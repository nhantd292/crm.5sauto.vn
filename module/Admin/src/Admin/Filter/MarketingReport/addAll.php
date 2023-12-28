<?php
namespace Admin\Filter\MarketingReport;

use Zend\InputFilter\InputFilter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

class addAll extends InputFilter {
	
	public function __construct($options = null){
	    $dbAdapter     = GlobalAdapterFeature::getStaticAdapter();

        // Month
//        $this->add(array(
//            'name'		=> 'month',
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

        // year
//        $this->add(array(
//            'name'		=> 'year',
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

        // Từ ngày
        $this->add(array(
            'name'		=> 'from_date',
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

        // Đến ngày
        $this->add(array(
            'name'		=> 'to_date',
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