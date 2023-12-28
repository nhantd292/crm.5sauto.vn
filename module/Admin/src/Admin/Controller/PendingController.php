<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class PendingController extends ActionController {
    
    public function init() {
        
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\PendingTable';
        $this->_options['formName'] = 'formAdminPending';
        
        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']                  = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'date';
        $this->_params['ssFilter']['order']                     = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';
        $this->_params['ssFilter']['filter_keyword']            = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['filter_date_begin']         = $ssFilter->filter_date_begin;
        $this->_params['ssFilter']['filter_date_end']           = $ssFilter->filter_date_end;
        $this->_params['ssFilter']['filter_date_type']          = $ssFilter->filter_date_type;
        $this->_params['ssFilter']['filter_product'] 	        = $ssFilter->filter_product;
        $this->_params['ssFilter']['filter_edu_class']          = $ssFilter->filter_edu_class;
        $this->_params['ssFilter']['filter_sale_branch']        = $ssFilter->filter_sale_branch;
        
        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage'] = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : $this->_paginator['itemCountPerPage'];
        $this->_paginator['currentPageNumber'] = $this->params()->fromRoute('page', 1);
        $this->_params['paginator'] = $this->_paginator;
        
        // Lấy dữ liệu post của form
        $this->_params['data'] = $this->getRequest()->getPost()->toArray();
        
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
    }
    
    // Tìm kiếm
    public function filterAction() {
    
        if($this->getRequest()->isPost()) {
            $ssFilter	= new Container(__CLASS__);
            $data = $this->_params['data'];
    
            $ssFilter->pagination_option        = intval($data['pagination_option']);
            $ssFilter->order_by                 = $data['order_by'];
            $ssFilter->order                    = $data['order'];
            $ssFilter->filter_keyword           = $data['filter_keyword'];
            $ssFilter->filter_date_begin        = $data['filter_date_begin'];
            $ssFilter->filter_date_end          = $data['filter_date_end'];
            $ssFilter->filter_date_type         = $data['filter_date_type'];
            $ssFilter->filter_product 	        = $data['filter_product'];
            $ssFilter->filter_edu_class 	    = $data['filter_edu_class'];
            $ssFilter->filter_sale_branch 	    = $data['filter_sale_branch'];
        }
    
        $this->goRoute();
    }
    
    // Danh sách
    public function indexAction() {
        $myForm	= new \Admin\Form\Search\Pending($this->getServiceLocator(), $this->_params['ssFilter']);
        $myForm->setData($this->_params['ssFilter']);
        
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        
        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $items;
        $this->_viewModel['count']                  = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['user']                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_group']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['contract_type']          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contract-type')), array('task' => 'cache'));
        $this->_viewModel['contract_use_status']    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contract-use-status')), array('task' => 'cache'));
        $this->_viewModel['product']                = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['edu_class']              = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['caption']                = 'Danh sách yêu cầu chờ xử lý';
        
        return new ViewModel($this->_viewModel);
    }
    
    // Xác nhận duyệt
    public function acceptAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $myForm = new \Admin\Form\Pending\Accept($this->getServiceLocator());
    
        if(!empty($this->_params['data']['id'])) {
            $contract   = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->_params['data']['id']));
            $contact    = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']));
            $contact['birthday'] = !empty($contact['birthday']) ? $dateFormat->formatToView($contact['birthday']) : null;
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }
    
        if($this->getRequest()->isPost()){
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\Pending\Accept($this->_params));
                $myForm->setData($this->_params['data']);
    
                if($myForm->isValid()){
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['contract'] = $contract;
                    $this->_params['contact'] = $contact;
    
                    $result = $this->getServiceLocator()->get('Admin\Model\PendingTable')->saveItem($this->_params, array('task' => 'accept'));
    
                    $this->flashMessenger()->addMessage('Cập nhật dữ liệu thành công');
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
        $this->_viewModel['contract']   = $contract;
        $this->_viewModel['contact']    = $contact;
        $this->_viewModel['caption']    = 'Xác nhận duyệt';
    
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
    
    // Không duyệt
    public function unAcceptAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $myForm = new \Admin\Form\Pending\UnAccept($this->getServiceLocator());
    
        if(!empty($this->_params['data']['id'])) {
            $contract   = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->_params['data']['id']));
            $contact    = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']));
            $contact['birthday'] = !empty($contact['birthday']) ? $dateFormat->formatToView($contact['birthday']) : null;
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }
    
        if($this->getRequest()->isPost()){
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\Pending\UnAccept($this->_params));
                $myForm->setData($this->_params['data']);
    
                if($myForm->isValid()){
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['contract'] = $contract;
                    $this->_params['contact'] = $contact;
    
                    $result = $this->getServiceLocator()->get('Admin\Model\PendingTable')->saveItem($this->_params, array('task' => 'unaccept'));
    
                    $this->flashMessenger()->addMessage('Cập nhật dữ liệu thành công');
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
        $this->_viewModel['contract']   = $contract;
        $this->_viewModel['contact']    = $contact;
        $this->_viewModel['caption']    = 'Xác nhận không duyệt';
    
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
}


