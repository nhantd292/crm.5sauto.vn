<?php
namespace Admin\Filter\ContractOwed;

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
	    // Tiêu đề
		$this->add(array(
			'name'		=> 'title',
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

	    // Nội dung
		$this->add(array(
			'name'		=> 'note',
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