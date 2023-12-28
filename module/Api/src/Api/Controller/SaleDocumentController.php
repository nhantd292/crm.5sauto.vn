<?php

namespace Api\Controller;

use ZendX\Controller\ApiController;

class SaleDocumentController extends ApiController {
    
    public function init() {
        // Lấy dữ liệu post của form
        $this->_params['data'] = $this->getRequest()->getPost()->toArray();
        
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
    }
    
    function listAction() {
    	if($this->getRequest()->isPost()) {
    		$result = $this->getServiceLocator()->get('Api\Model\SaleDocumentTable')->listItem($this->_params['data'], array('task' => 'cache-status'));
    
    		echo json_encode($result);
    	}
    
    	return $this->response;
    }
}