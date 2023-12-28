<?php

namespace Api\Controller;

use ZendX\Controller\ApiController;

class SaleCompanyBranchController extends ApiController {
    
    public function init() {
        // Lấy dữ liệu post của form
        $this->_params['data'] = $this->getRequest()->getPost()->toArray();
        
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
    }
    
    function listAction() {
    	if($this->getRequest()->isPost()) {
            $result = $this->getServiceLocator()->get('Api\Model\SaleCompanyBranchTable')->listItem(null, array('task' => 'cache')); 
            
            echo json_encode($result);
        }
        
    	return $this->response;
    }
}














