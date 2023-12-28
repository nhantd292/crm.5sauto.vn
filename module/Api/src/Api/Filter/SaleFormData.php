<?php
namespace Api\Filter;

class SaleFormData {
	protected $_error;
	
	public function __construct($data = null){
		$number = new \ZendX\Functions\Number();
		
	    $dataForm = $data['form'];
	    $dataParam = $data['data'];
	    
	    if(empty($dataForm)) {
	        $this->_error['form_id'] = 'Không tìm thấy form đăng ký';
	    } else {
	        $configFields = new \ZendX\Functions\Form($dataForm['fields']);
	        $configFields = $configFields->getSetting();
	        
    	    $valid = new \Zend\Validator\NotEmpty();
    	    foreach ($configFields AS $config) {
    	        if($config['require'] == 'true') {
        	        if (!$valid->isValid($dataParam[$config['name']])) {
        	            $this->_error[$config['name']] = "Giá trị này bắt buộc phải nhập";
        	        }
    	        }
    	    }
    	    
    	    // Validate dữ liệu liên hệ
    	    if (!$valid->isValid($dataParam['name'])) {
    	    	$this->_error['name'] = "Giá trị này bắt buộc phải nhập";
    	    }
    	    if (!$valid->isValid($dataParam['phone'])) {
    	    	$this->_error['phone'] = "Giá trị này bắt buộc phải nhập";
    	    }
    	    if(!empty($dataParam['phone'])) {
	    	    $validNumber = new \Zend\Validator\Digits();
	    	    if (!$validNumber->isValid($number->fomartToData($dataParam['phone']))) {
	    	    	$this->_error['phone'] = "Không đúng định dạng số điện thoại";
	    	    }
		    }
		    if (!$valid->isValid($dataParam['email'])) {
		    	$this->_error['email'] = "Giá trị này bắt buộc phải nhập";
		    }
    	    if(!empty($dataParam['email'])) {
    	    	$validEmail = new \Zend\Validator\EmailAddress();
    	    	if (!$validEmail->isValid($dataParam['email'])) {
    	    		$this->_error['email'] = "Email không đúng định dạng";
    	    	}
    	    }
    	    if (!$valid->isValid($dataParam['location_city_id'])) {
    	    	$this->_error['location_city_id'] = "Giá trị này bắt buộc phải nhập";
    	    }
	    }
	}
	
	public function getError() {
	    return $this->_error;
	} 
}