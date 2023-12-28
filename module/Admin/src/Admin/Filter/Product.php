<?php
namespace Admin\Filter;

use Zend\InputFilter\InputFilter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

class Product extends InputFilter {
	
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
						'table'   => TABLE_PRODUCT,
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

		// Nhóm sản phẩm
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

		// Đơn vị tính
		$this->add(array(
			'name'		=> 'unit_id',
			'required'	=> false,
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
		
		// Giá vốn
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
		        ),
		        array(
		            'name'		=> 'Regex',
		            'options'	=> array(
		                'pattern'   => '/^[0-9,]+$/',
		                'messages'	=> array(
		                    \Zend\Validator\Regex::NOT_MATCH => 'Không đúng định dạng tiền tệ'
		                )
		            ),
		            'break_chain_on_failure'	=> true
		        ),
		    )
		));

		// Giá vốn
		// $this->add(array(
		//     'name'		=> 'listed_price',
		//     'required'	=> false,
		// ));
		
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