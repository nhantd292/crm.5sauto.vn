<?php

namespace Api\Controller;

use ZendX\Controller\ApiController;

class SaleFormDataController extends ApiController {
    
    public function init() {
        // Lấy dữ liệu post của form
        $this->_params['data'] = $this->getRequest()->getPost()->toArray();
        
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
    }
    
    function addAction() {
    	if($this->getRequest()->isPost()) {
            $this->_params['form'] = $this->getServiceLocator()->get('Api\Model\SaleFormTable')->getItem(array('id' => $this->_params['data']['form_id'])); 
            $filter = new \Api\Filter\SaleFormData($this->_params);
            
            $result = array();
            if(!empty($filter->getError())) {
                $result['error'] = $filter->getError();
            } else {
                $result['save'] = $this->getServiceLocator()->get('Api\Model\SaleFormDataTable')->saveItem($this->_params, array('task' => 'add-item'));
            }
            
            echo json_encode($result);
        }
        
    	return $this->response;
    }
}














