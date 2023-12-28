<?php
namespace Admin\Filter\Contact;

use Zend\InputFilter\InputFilter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

class ChangePassword extends InputFilter {
    
    protected $_optionId;
    protected $_optionData;
    protected $_optionRoute;
	
	public function __construct($options = null){
	    $dbAdapter                = GlobalAdapterFeature::getStaticAdapter();
	    $this->_optionId          = $options['id'];
	    $this->_optionData        = $options['data'];
	    $this->_optionRoute       = $options['route'];
	    
	    // Mật khẩu
	    $this->add(array(
	        'name'		=> 'password',
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