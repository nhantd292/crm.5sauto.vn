<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class TangledColorController extends ActionController {
    
    public function init() {
        
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\TangledColorTable';
        $this->_options['formName'] = 'formAdminTangledColor';
        
        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']              = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'date';
        $this->_params['ssFilter']['order']                 = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';
        $this->_params['ssFilter']['filter_keyword']          = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['filter_parent']           = $ssFilter->filter_parent;
        
        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage'] = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : $this->_paginator['itemCountPerPage'];
        $this->_paginator['currentPageNumber'] = $this->params()->fromRoute('page', 1);
        $this->_params['paginator'] = $this->_paginator;
        
        // Lấy dữ liệu post của form
        $this->_params['data'] = array_merge($this->getRequest()->getPost()->toArray(), $this->getRequest()->getFiles()->toArray());
        
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
            $ssFilter->filter_parent            = $data['filter_parent'];
        }
    
        $this->goRoute();
    }
    
    // Danh sách
    public function indexAction() {
        $myForm	= new \Admin\Form\Search\TangledColor($this->getServiceLocator(), $this->_params['ssFilter']);
        $myForm->setData($this->_params['ssFilter']);
        
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        $count = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        
        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $items;
        $this->_viewModel['count']                  = $count;
        $this->_viewModel['group']                  = $this->getServiceLocator()->get('Admin\Model\ColorGroupTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['caption']                = 'Màu rối - Danh sách';
        
        return new ViewModel($this->_viewModel);
    }
    
    // Thêm mới
    public function addAction() {
        $myForm = $this->getForm();
        if($this->getRequest()->isPost()) {
            $myForm->setInputFilter(new \Admin\Filter\TangledColor(array('data' => $this->_params['data'], 'route' => $this->_params['route'])));
            
            $myForm->setData($this->_params['data']);
            $controlAction = $this->_params['data']['control-action'];

            if($myForm->isValid()){
                $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);           
                $result = $this->getTable()->saveItem($this->_params, array('task' => 'add-item'));
                $this->flashMessenger()->addMessage('Dữ liệu đã được cập nhật thành công');

                if($controlAction == 'save-new') {
                    $this->goRoute(array('action' => 'add'));
                } else if($controlAction == 'save') {
                    $this->goRoute(array('action' => 'view', 'id' => $result));
                } else {
                    $this->goRoute();
                }
            }
        }

        $this->_viewModel['myForm']	    = $myForm;
        $this->_viewModel['caption']    = 'Màu rối - Thêm mới';
        return new ViewModel($this->_viewModel);
    }
    
    // Sửa Màu rối
    public function editAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $numberFormat = new \ZendX\Functions\Number();
        $myForm = new \Admin\Form\TangledColor\Edit($this->getServiceLocator(), $this->_params);
        
        if(!empty($this->_params['data']['id'])) {
            $color = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->getItem(array('id' => $this->_params['data']['id']));         
            $myForm->setData($color);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }
        
        if($this->getRequest()->isPost()){
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\TangledColor\Edit($this->_params));
                $myForm->setData($this->_params['data']);
                if($myForm->isValid()){

                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['item'] = $color;
                    $result = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->saveItem($this->_params, array('task' => 'edit-item'));
    
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
    
        $this->_viewModel['myForm']         = $myForm;
        $this->_viewModel['caption']        = 'Sửa Màu rối';
    
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }

    // Xóa
    public function deleteAction() {
        if($this->getRequest()->isPost()) {
            $contract_delete = $this->getTable()->deleteItem($this->_params, array('task' => 'delete-item'));
            $this->flashMessenger()->addMessage('Xóa thành công');
            $this->goRoute();
        }
    }

}