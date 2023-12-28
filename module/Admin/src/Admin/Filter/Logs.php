<?php
namespace Admin\Filter;

use Zend\InputFilter\InputFilter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

class Logs extends InputFilter {
	
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
			            'table'   => TABLE_COMPANY_POSITION,
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
		
		// Ordering
		$this->add(array(
		    'name'		=> 'ordering',
		    'required'	=> true,
		    'validators'	=> array(
		        array(
		            'name'		=> 'Digits',
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
		            'break_chain_on_failure'	=> true
		        )
		    )
		));
	}
}