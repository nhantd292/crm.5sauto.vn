<?php
namespace Admin\Filter;

use Zend\InputFilter\InputFilter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

class Permission extends InputFilter {
	
	public function __construct($options = null){
	    $exclude = null;
	    if(!empty($options['id'])) {
	        $exclude = array(
	            'field' => 'id',
	            'value' => $options['id']
	        );
	    }
	    
	    // Code
	    $this->add(array(
	        'name'		=> 'code',
	        'required'	=> true,
	        'filters'	=> array(
	            array( 'name' 	=> 'StringToLower' ),
	        ),
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
						'table'   => TABLE_PERMISSION,
						'field'   => 'code',
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
	}
}