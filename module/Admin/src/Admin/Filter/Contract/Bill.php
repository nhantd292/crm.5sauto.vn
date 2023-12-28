<?php
namespace Admin\Filter\Contract;

use Zend\InputFilter\InputFilter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

class Bill extends InputFilter {
    
	public function __construct($options = null){
	    $dbAdapter         = GlobalAdapterFeature::getStaticAdapter();
	    $optionData        = $options['data'];
	    $optionContract    = $options['contract'];
	    $optionContact     = $options['contact'];
	    
        $excludeCodeMessages = $optionData['code'] .' đã tồn tại trên hệ thống';
        $excludeCode = "code = '". $optionData['code'] ."'";
	    
	    // Mã hóa đơn
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
	                    'table'   => TABLE_BILL,
	                    'field'   => 'code',
	                    'adapter' => $dbAdapter,
	                    'exclude' => $excludeCode,
	                    'messages'	=> array(
	                        \Zend\Validator\Db\NoRecordExists::ERROR_RECORD_FOUND => $excludeCodeMessages
	                    )
	                ),
	                'break_chain_on_failure' => true
	            )
	        )
	    ));
	    
	    // Ngày hóa đơn
	    $this->add(array(
	        'name'		=> 'date',
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
	        )
	    ));
	    
	    // Phân loại
	    $this->add(array(
	        'name'		=> 'type',
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
	        )
	    ));
	    
	    // Hình thức hóa đơn
	    $this->add(array(
	        'name'		=> 'bill_type_id',
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
	        )
	    ));
	    
	    // Nội dung/Ghi chú
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
	            ),
	        )
	    ));
	    
		// Tiền thu
	    if($optionData['type'] == 'Thu') {
    		$this->add(array(
    		    'name'		=> 'paid_price',
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
    		    )
    		));
	    }
	    
		// Tiền chi
	    if($optionData['type'] == 'Chi') {
    		$this->add(array(
    		    'name'		=> 'accrued_price',
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
    		    )
    		));
	    }
	}
}