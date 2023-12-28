<?php

namespace Api\Controller;

use ZendX\Controller\ApiController;
use JWT\JWT;

class OfflineTrainingClassController extends ApiController {
    
    public function init() {
        // Lấy dữ liệu post của form
        $this->_params['data'] = $this->getRequest()->getPost()->toArray();
        
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
    }
    
    function listAction() {
    	if($this->getRequest()->isPost()) {
    		$dateFormat = new \ZendX\Functions\Date();
    		
    		$training_location = $this->getServiceLocator()->get('Api\Model\SaleDocumentTable')->listItem(array('where' => array('code' => 'training-location')), array('task' => 'cache'));
            $training_class = $this->getServiceLocator()->get('Api\Model\OfflineTrainingCLassTable')->listItem($this->_params, array('task' => 'public'));
            
            $result = array();
            if(!empty($training_class)) {
            	foreach ($training_class AS $key => $val) {
            		if(!empty($training_location[$val['training_location_id']]) && !empty($val['public_date'])) {
            			$result[$val['id']] = $val;
            			$result[$val['id']]['public_date'] = $dateFormat->fomartToView($val['public_date']);
            			$result[$val['id']]['training_location_name'] = $training_location[$val['training_location_id']]['name'];
            			$result[$val['id']]['training_location_address'] = $training_location[$val['training_location_id']]['address'];
            			$result[$val['id']]['training_location_phone'] = $training_location[$val['training_location_id']]['phone'];
            		}
            	}
            }
            
            echo json_encode($result);
        }
        
    	return $this->response;
    }
}














