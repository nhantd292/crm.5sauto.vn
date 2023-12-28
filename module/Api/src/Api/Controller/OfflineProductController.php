<?php

namespace Api\Controller;

use ZendX\Controller\ApiController;
use JWT\JWT;

class OfflineProductController extends ApiController {
    
    public function init() {
        // Lấy dữ liệu post của form
        $this->_params['data'] = $this->getRequest()->getPost()->toArray();
        
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
    }
    
    function listAction() {
    	if($this->getRequest()->isPost()) {
            $training_class = $this->getServiceLocator()->get('Api\Model\OfflineProductTable')->listItem($this->_params, array('task' => 'cache-status'));
            
            echo json_encode($training_class);
        }
        
    	return $this->response;
    }
}














