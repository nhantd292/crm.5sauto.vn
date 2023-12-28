<?php
namespace ZendX\System;

class Authenticate {
	
	protected $_authService;
	protected $_msgError;
	
	public function __construct(\Zend\Authentication\AuthenticationService $authService) {
		$this->_authService	= $authService;
	}
	
	public function login($arrParams = null, $options = null) {
    	$validator = new \Zend\Validator\EmailAddress();
        if ($validator->isValid($arrParams['username'])) {
            $this->_authService->getAdapter()->setIdentityColumn('email');
        }

		$this->_authService->getAdapter()->setIdentity($arrParams['username']);
		$this->_authService->getAdapter()->setCredential($arrParams['password']);
		 
		$result	= $this->_authService->authenticate();
		
		if(!$result->isValid()){
			$this->_msgError	= 'Tài khoản hoặc mật khẩu không chính xác';
			return false;
		} else {
			$data	= $this->_authService->getAdapter()->getResultRowObject(null, array('password'));
			$this->_authService->getStorage()->write($data);
			return true;
		}
	}
	
	public function getError($arrParams = null, $options = null){
		return $this->_msgError;
	}
	
	public function logout($arrParams = null, $options = null){
		$this->_authService->clearIdentity();
	}
}