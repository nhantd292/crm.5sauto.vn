<?php
namespace Admin\Filter\FormData;

use Zend\InputFilter\InputFilter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

class Import2 extends InputFilter {
	
	public function __construct($options = null){
	    $dbAdapter     = GlobalAdapterFeature::getStaticAdapter();
	    $optionId      = $options['id'];
	    $optionData    = $options['data'];
	    $optionRoute   = $options['route'];
	    
		//File Import
		$this->add(array(
			'name'		=> 'file_import',
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
					'name'		=> 'FileExtension',
					'options'	=> array(
						'extension'		=> array('xlsx'),
						'messages'	=> array(
							\Zend\Validator\File\Extension::FALSE_EXTENSION => 'Chỉ chấp nhập định dạng excel .xlsx'
						)
					),
					'break_chain_on_failure'	=> true
				),
			)
		));

		// marketer
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
	}
}