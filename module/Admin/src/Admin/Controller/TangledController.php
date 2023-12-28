<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class ColorGroupController extends ActionController {
    
    public function init() {
        
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\ColorGroupTable';
        $this->_options['formName'] = 'formAdminColorGroup';
        
        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']              = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'date';
        $this->_params['ssFilter']['order']                 = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';
        
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
        }
    
        $this->goRoute();
    }
    
    // Danh sách
    public function indexAction() {
        $myForm	= new \Admin\Form\Search\ColorGroup($this->getServiceLocator(), $this->_params['ssFilter']);
        $myForm->setData($this->_params['ssFilter']);
        
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        $count = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));;
        
        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $items;
        $this->_viewModel['count']                  = $count;
        $this->_viewModel['unit']                   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'unit')), array('task' => 'cache'));
        $this->_viewModel['caption']                = 'Nhóm nguyên liệu - Danh sách';
        
        return new ViewModel($this->_viewModel);
    }
    
    // Thêm mới
    public function addAction() {
        $myForm = $this->getForm();
        if($this->getRequest()->isPost()) {
            $myForm->setInputFilter(new \Admin\Filter\ColorGroup(array('data' => $this->_params['data'], 'route' => $this->_params['route'])));
            
            $myForm->setData($this->_params['data']);
            $controlAction = $this->_params['data']['control-action'];
            $color = $this->getTable()->getItem($this->_params, array('task' => 'by-ajax'));
            $errors = '';
            if (!empty($color)) {
                $errors = 'Dữ liệu đã tồn tại! Vui lòng kiểm tra và nhập lại.';
            } else {
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
        }

        $this->_viewModel['errors']	    = $errors;
        $this->_viewModel['myForm']	    = $myForm;
        $this->_viewModel['caption']    = 'Nhóm nguyên liệu - Thêm mới';
        return new ViewModel($this->_viewModel);
    }
    
    // Sửa nhóm nguyên liệu
    public function editAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $numberFormat = new \ZendX\Functions\Number();
        $myForm = new \Admin\Form\ColorGroup\Edit($this->getServiceLocator(), $this->_params);
        
        if(!empty($this->_params['data']['id'])) {
            $color = $this->getServiceLocator()->get('Admin\Model\ColorGroupTable')->getItem(array('id' => $this->_params['data']['id']));         
            $myForm->setData($color);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }
    
        if($this->getRequest()->isPost()){
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\ColorGroup\Edit($this->_params));
                $myForm->setData($this->_params['data']);
    
                if($myForm->isValid()){
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['item'] = $color;
                    $result = $this->getServiceLocator()->get('Admin\Model\ColorGroupTable')->saveItem($this->_params, array('task' => 'edit-item'));
    
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
        $this->_viewModel['caption']        = 'Sửa nhóm nguyên liệu';
    
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