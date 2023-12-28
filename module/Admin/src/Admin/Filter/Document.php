<?php
namespace Admin\Filter;

use Zend\InputFilter\InputFilter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

class Document extends InputFilter {
	
	public function __construct($options = null){
	    $configs = $options['configs'];
	    $dbAdapter = GlobalAdapterFeature::getStaticAdapter();
	    
	    $exclude = null;
	    if(!empty($options['id'])) {
	        $exclude = array(
	            'field' => 'id',
	            'value' => $options['id']
	        );
	    }
	    
	    if(empty($options['id'])) {
	        $this->add(array(
	            'name'		=> 'id',
	            'required'	=> false,
	            'validators'	=> array(
	                array(
	                    'name'		=> 'DbNoRecordExists',
	                    'options'	=> array(
	                        'table'   => TABLE_DOCUMENT,
	                        'field'   => 'id',
	                        'adapter' => $dbAdapter,
	                        'exclude' => null,
	                        'messages'	=> array(
	                            \Zend\Validator\Db\NoRecordExists::ERROR_RECORD_FOUND => 'Đã tồn tại'
	                        )
	                    ),
	                    'break_chain_on_failure'	=> true
	                )
	            )
	        ));
	    }
	    
	    foreach ($configs['form']['fields'] AS $field) {
	        $required = ($field['validators']['require'] == 1) ? true : false;
	        $validators = array();
	        
	        if($required == true) {
	            $validators[] = array(
	                'name'		=> 'NotEmpty',
	                'options'	=> array(
	                    'messages'	=> array(
	                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Giá trị này không được để trống'
	                    )
	                ),
	                'break_chain_on_failure'	=> true
	            );
	        }
	        
    	    $this->add(
    	        array(
        	        'name'		   => $field['name'],
        	        'required'	   => $required,
        	        'validators'   => $validators
        	    )
	        );
	    }
	}
}