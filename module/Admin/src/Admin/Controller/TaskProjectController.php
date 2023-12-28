<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class TaskProjectController extends ActionController {
    
    public function init() {
        
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\TaskProjectTable';
        $this->_options['formName'] = 'formAdminTaskProject';
        
        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']          = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'created';
        $this->_params['ssFilter']['order']             = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';
        $this->_params['ssFilter']['filter_status']     = $ssFilter->filter_status;
        $this->_params['ssFilter']['filter_keyword']    = $ssFilter->filter_keyword;
        
        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage'] = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : 50;
        $this->_paginator['currentPageNumber'] = $this->params()->fromRoute('page', 1);
        $this->_params['paginator'] = $this->_paginator;
        
        // Lấy dữ liệu post của form
        $this->_params['data'] = $this->getRequest()->getPost()->toArray();
        
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
    }
    
    public function filterAction() {
    
        if($this->getRequest()->isPost()) {
            $ssFilter	= new Container(__CLASS__);
            $data = $this->_params['data'];
    
            $ssFilter->pagination_option    = intval($data['pagination_option']);
    
            $ssFilter->order_by             = $data['order_by'];
            $ssFilter->order                = $data['order'];
    
            $ssFilter->filter_status        = $data['filter_status'];
            $ssFilter->filter_keyword       = $data['filter_keyword'];
        }
    
        $this->goRoute();
    }
    
    public function indexAction() {
        $myForm	= new \Admin\Form\Search\TaskProject($this->getServiceLocator());
        $myForm->setData($this->_params['ssFilter']);
        
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        
        $this->_viewModel['myForm']	    = $myForm;
        $this->_viewModel['items']      = $items;
        $this->_viewModel['count']      = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['user']       = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['company_branch']     = $this->getServiceLocator()->get('Admin\Model\CompanyBranchTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['company_department'] = $this->getServiceLocator()->get('Admin\Model\CompanyDepartmentTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['caption']    = 'Dự án - Danh sách';
        return new ViewModel($this->_viewModel);
    }
    
    public function formAction() {
        $myForm			= $this->getForm();
        
        $task = 'add-item';
        $caption = 'Dự án - Thêm mới';
        $item = array();
        if(!empty($this->params('id'))) {
            $this->_params['data']['id'] = $this->params('id');
            $item = $this->getTable()->getItem($this->_params['data']);
            if(!empty($item)) {
                $myForm->setInputFilter(new \Admin\Filter\TaskCategory(array('id' => $this->_params['data']['id'])));
                $myForm->bind($item);
                $task = 'edit-item';
                $caption = 'Dự án - Sửa';
            }
        }
    
        if($this->getRequest()->isPost()){
            $myForm->setData($this->_params['data']);
    
            $controlAction = $this->_params['data']['control-action'];
            
            if($myForm->isValid()){
                $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                $result = $this->getTable()->saveItem($this->_params, array('task' => $task));
    
                $this->flashMessenger()->addMessage('Dữ liệu đã được cập nhật thành công');
    
                if($controlAction == 'save-new') {
                    $this->goRoute(array('action' => 'form'));
                } else if($controlAction == 'save') {
                    $this->goRoute(array('action' => 'form', 'id' => $result));
                } else {
                    $this->goRoute();
                }
            }
        }
    
        $this->_viewModel['myForm']	    = $myForm;
        $this->_viewModel['item']       = $item;
        $this->_viewModel['caption']    = $caption;
        return new ViewModel($this->_viewModel);
    }
}
