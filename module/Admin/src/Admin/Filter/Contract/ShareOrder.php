<?php
namespace Admin\Filter\Contract;

use Zend\InputFilter\InputFilter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

class ShareOrder extends InputFilter {
	
	public function __construct($options = null){
	    $dbAdapter     = GlobalAdapterFeature::getStaticAdapter();
	    $optionData    = $options['data'];
	    $optionRoute   = $options['route'];
	    
		// Người được chuyển
		$this->add(array(
		    'name'		=> 'user_id',
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