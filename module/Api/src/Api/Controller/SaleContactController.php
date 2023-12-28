<?php

namespace Api\Controller;

use ZendX\Controller\ApiController;
use JWT\JWT;

class SaleContactController extends ApiController {
    
    public function init() {
        // Lấy dữ liệu post của form
        $this->_params['data'] = $this->getRequest()->getPost()->toArray();
        
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
        
        if($this->_params['action'] != 'login') {
        	if(!empty($this->_params['data']['token'])) {
	        	try {
	        		$token = $this->_params['data']['token'];
		        	$decoded = @JWT::decode($token, APP_KEYJWT, array('HS256'));
		        	
		        	if(empty($decoded)) {
		        		die('token error');
		        	}
				} catch (\Exception $e) {
				    die('token error');
				}
        	} else {
        		die('token error');
        	}
        }
    }
    
    function loginAction() {
    	if($this->getRequest()->isPost()) {
            $contact = $this->getServiceLocator()->get('Api\Model\SaleContactTable')->getItem($this->_params, array('task' => 'login'));
            
            if(!empty($contact)) {
            	JWT::$timestamp = 10;
            	$data = array(
            		'id' => $contact['id'],
            		'name' => $contact['name'],
            		'phone' => $contact['phone'],
            		'email' => $contact['email'],
            	);
            	$jwt = JWT::encode($contact, APP_KEYJWT);
            	$token = array('contact' => $contact, 'token' => $jwt);
            } else {
            	$token = array('contact' => null);
            }
            
            echo json_encode($token);
        }
        
    	return $this->response;
    }
    
    function getAction() {
    	if($this->getRequest()->isPost()) {
            $contact = $this->getServiceLocator()->get('Api\Model\SaleContactTable')->getItem($this->_params, null);
            
            echo json_encode($contact);
        }
        
    	return $this->response;
    }
}














