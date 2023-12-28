<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class LocationController extends ActionController {
    
    public function init() {
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\ContractTable';
        $this->_options['formName'] = 'formAdminTransport';
        
        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__);
        $this->_params['ssFilter']['filter_keyword']             = !empty($ssFilter->filter_keyword) ? $ssFilter->filter_keyword : null;
        $this->_params['ssFilter']['filter_date_begin']          = !empty($ssFilter->filter_date_begin) ? $ssFilter->filter_date_begin : date('d/m/Y');
        $this->_params['ssFilter']['filter_date_end']            = !empty($ssFilter->filter_date_end) ? $ssFilter->filter_date_end : date('d/m/Y');
        $this->_params['ssFilter']['transport_service']          = !empty($ssFilter->transport_service) ? $ssFilter->transport_service : null;
        $this->_params['ssFilter']['transport_status']           = !empty($ssFilter->transport_status) ? $ssFilter->transport_status : null;
        $this->_params['ssFilter']['transport_type']            = !empty($ssFilter->transport_type) ? $ssFilter->transport_type : null;
        $this->_params['ssFilter']['order_by']                   = !empty($ssFilter->order_by) ? $ssFilter->order_by : null;
        $this->_params['ssFilter']['order']                      = !empty($ssFilter->order) ? $ssFilter->order : null;
        
        
        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage']   = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : $this->_paginator['itemCountPerPage'];
        $this->_paginator['currentPageNumber']  = $this->params()->fromRoute('page', 1);
        $this->_params['paginator']             = $this->_paginator;
        
        // Lấy dữ liệu post của form
        $this->_params['data'] = array_merge($this->getRequest()->getPost()->toArray(), $this->getRequest()->getFiles()->toArray());

        // lấy dữ liệu từ url
        $this->_params['route'] = $this->params()->fromRoute();
        
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
    }
    
    public function filterAction() {
        $ssFilter	= new Container(__CLASS__);
        $data = $this->_params['data'];
    
        if($this->getRequest()->isPost()) {
            $ssFilter['filter_keyword']         = !empty($data['filter_keyword']) ? $data['filter_keyword'] : null;
            $ssFilter['filter_date_begin']      = !empty($data['filter_date_begin']) ? $data['filter_date_begin'] : date('d/m/Y');
            $ssFilter['filter_date_end']        = !empty($data['filter_date_end']) ? $data['filter_date_end'] : date('d/m/Y');
            $ssFilter['transport_service']      = !empty($data['transport_service']) ? $data['transport_service'] : null;
            $ssFilter['transport_status']       = !empty($data['transport_status']) ? $data['transport_status'] : null;
            $ssFilter['transport_type']        = !empty($data['transport_type']) ? $data['transport_type'] : null;
            $ssFilter['order_by']               = !empty($data['order_by']) ? $data['order_by'] : null;
            $ssFilter['order']                  = !empty($data['order']) ? $data['order'] : null;
            $ssFilter->pagination_option        = intval($data['pagination_option']);
            $ssFilter->order_by                 = $data['order_by'];
        }
        
        $this->goRoute();
    }
    
    public function indexAction() {
        $ssFilter = new Container(__CLASS__);
        $ssFilter->currentPageNumber = $this->_paginator['currentPageNumber'];
        
        $myForm	= new \Admin\Form\Transport($this);
        $myForm->setData($this->_params['ssFilter']);
        
        // lấy dữ liệu
        $items                  = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        // echo '<pre>'; print_r($items->toArray()); echo '</pre>'; die;
        $transport_service      = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'transport-service')), array('task' => 'cache')),  array('key' => 'alias', 'value' => 'name'));
        $transport_status       = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'transport-status')), array('task' => 'cache')),  array('key' => 'alias', 'value' => 'name'));

        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $items;
        $this->_viewModel['transport_service']      = $transport_service;
        $this->_viewModel['transport_status']       = $transport_status;
        $this->_viewModel['count']                  = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['location_city']          = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache'));
        $this->_viewModel['location_district']      = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 2), array('task' => 'cache'));

        // $this->_viewModel['user']                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['caption']                = 'Vận chuyển - Danh sách';
        return new ViewModel($this->_viewModel);
    }
    
    //edit
    public function editAction(){
        $ssFilter = new Container(__CLASS__);
        if(!empty($this->_params['data']['id'])) {
            $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $this->_params['data']['id']));
            $contact_options = !empty($contact['options']) ? unserialize($contact['options']) : array();
            $options_history = !empty($contact_options['history']) ? $contact_options['history'] : array();
            $contact = array_merge($contact, $options_history, $contact_options);
            unset($contact['history_return']);
            unset($contact['history_status_id']);
            unset($contact['call_status']);
            unset($contact['reason_refusing']);
            unset($contact['history_content']);
            $item = $this->getTable()->getItem(array('id' => $this->_params['data']['id']));

            $myForm = new \Admin\Form\Contact\Edit($this->getServiceLocator(), array('contact' => $contact));
            $myForm->setData($contact);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }
        if($this->getRequest()->isPost()){
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\Contact\Edit($this->_params));
                $myForm->setData($this->_params['data']);

                if($myForm->isValid()){
                    //$this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['item'] = $contact;
    
                    // Cập nhập contact
                    $this->_params['data']['id'] = $contact['id'];

                    // Cập nhật thông tin khách hàng
                    $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($this->_params, array('task' => 'edit-item'));
                 
                    //Thêm đơn hàng
                    if(!empty($this->_params['data']['contract_product']['code'][0])){
                    $this->_params['data']['id'] = $contact['id'];
                        $contract_address       = $this->_params['data']['contract_address'];
                        $address                = explode('_', $contract_address);

                        //Lấy địa chỉ đường nhà
                        $street                 = $address[0];

                        //Lấy quận huyện
                        $idDistrict['id']       = $address[1];
                        $district               = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem($idDistrict);
                        $districtName           = $district['name'];

                        //Lấy tỉnh thành
                        $idCity['id']           = $address[2];
                        $city                   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem($idCity);
                        $cityName               = $city['name'];

                        $this->_params['data']['street']    = $street;
                        $this->_params['data']['district']  = $districtName;
                        $this->_params['data']['city']      = $cityName;
                        $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params, array('task' => 'add-contract'));
                    }

                    // Update lịch sử chăm sóc cho khách hàng nếu tồn tại nội dung chăm sóc
                    if(!empty($this->_params['data']['history_status_id'])) {
                        $this->getServiceLocator()->get('Admin\Model\HistoryTable')->saveItem($this->_params, array('task' => 'add-item'));
                    }
                    
                    // Thêm lịch sử thay đổi level
                    if(!empty($this->_params['data']['level']) && $this->_params['data']['level'] != $contact['level']) {
                        $this->getServiceLocator()->get('Admin\Model\HistoryLevelTable')->saveItem($this->_params, array('task' => 'add-item'));
                    }

                    $this->flashMessenger()->addMessage('Sửa thông tin khách hàng thành công');
                    echo 'success';
                    return $this->response;
                }
            } else {
                $myForm->setData($this->_params['data']);
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
    
        $this->_viewModel['myForm']     = $myForm;
        $this->_viewModel['item']       = $item;
        $this->_viewModel['contact']    = $contact;
        $this->_viewModel['caption']    = 'Cập nhập data - Thêm lịch sử chăm sóc';
        $this->_viewModel['products']   = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
    
    // view controller
    public function viewAction() {
        $item = $this->getTable()->getItem(array('id' => $this->_params['route']['id']));
        if(empty($item)) {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
        $contact_info = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $item['contact_id']));
    
        $this->_viewModel['item']    	            = $item;
        $this->_viewModel['contact']                = is_array($contact_info) && count($contact_info)? $contact_info:null;
        $this->_viewModel['location_city']          = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache'));
        $this->_viewModel['location_district']      = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 2), array('task' => 'cache'));
        $this->_viewModel['caption']                = 'Xem thông tin chi tiết đơn hàng';
        
        $viewModel = new ViewModel($this->_viewModel);
        return $viewModel;
    }
}
















