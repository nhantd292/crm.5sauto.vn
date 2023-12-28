<?php
namespace Admin\Filter\Contract;

use Zend\InputFilter\InputFilter;

class Import extends InputFilter {
	
	public function __construct($options = null){
	    // File Import
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
	}
}