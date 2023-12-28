<?php
namespace Admin\Filter;

use Zend\InputFilter\InputFilter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

class Setting extends InputFilter {
	
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
				)
			)
		));
		
		// Code
		$this->add(array(
		    'name'		=> 'code',
		    'required'	=> false,
		    'validators'	=> array(
		        array(
					'name'		=> 'Regex',
					'options'	=> array(
					    'pattern'   => '#^[a-zA-Z.]*$#',
				        'messages'	=> array(
				            \Zend\Validator\Regex::NOT_MATCH => 'Chỉ cấp nhận các chữ không dấu và dấu . để phân cách các từ'
				        )
				    ),
					'break_chain_on_failure'	=> true
				),
		        array(
					'name'		=> 'DbNoRecordExists',
					'options'	=> array(
						'table'   => TABLE_SETTING,
						'field'   => 'code',
						'adapter' => GlobalAdapterFeature::getStaticAdapter(),
					    'exclude' => $exclude,
					    'messages'	=> array(
					        \Zend\Validator\Db\NoRecordExists::ERROR_RECORD_FOUND => 'Giá trị này đã tồn tại'
					    )
					),
					'break_chain_on_failure'	=> true
				)
		    )
		));
	}
}