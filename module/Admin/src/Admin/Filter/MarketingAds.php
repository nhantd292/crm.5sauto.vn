<?php
namespace Admin\Filter;

use Zend\InputFilter\InputFilter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

class MarketingAds extends InputFilter {
	
	public function __construct($options = null){

		// day
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

		$this->add(array(
		    'name'		=> 'price',
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

		$this->add(array(
		    'name'		=> 'note',
		    'required'	=> false,
		));
	}
}