<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class SettingController extends ActionController {
    
    public function init() {
        
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\SettingTable';
        $this->_options['formName'] = 'formAdminSetting';
        
        // Thiết lập session filter
        $ssFilter	= new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']          = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'left';
        $this->_params['ssFilter']['order']             = !empty($ssFilter->order) ? $ssFilter->order : 'ASC';
        $this->_params['ssFilter']['filter_status']     = $ssFilter->filter_status;
        $this->_params['ssFilter']['filter_keyword']    = $ssFilter->filter_keyword;
        
        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage'] = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : 50;
        $this->_paginator['currentPageNumber'] = $this->params()->fromRoute('page', 1);
        $this->_params['paginator'] = $this->_paginator;
        
        // Lấy dữ liệu post của form
        $this->_params['data'] = array_merge($this->getRequest()->getPost()->toArray(), $this->getRequest()->getFiles()->toArray());
        $this->_params['route'] = $this->params()->fromRoute();
        
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
    }
    
    public function filterAction() {
        if(empty($this->params()->fromRoute('code'))) {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'no-access'));
        }
    
        if($this->getRequest()->isPost()) {
            $ssFilter	= new Container(__CLASS__);
            $data = $this->_params['data'];
    
            $ssFilter->pagination_option    = intval($data['pagination_option']);
            
            $ssFilter->order_by             = $data['order_by'];
            $ssFilter->order                = $data['order'];
    
            $ssFilter->filter_status        = $data['filter_status'];
            $ssFilter->filter_keyword       = $data['filter_keyword'];
        }
    
        return $this->redirect()->toRoute('routeAdminNested/index', array(
            'controller' => $this->_params['controller'],
            'code' => $this->params()->fromRoute('code')
        ));
    }
    
    public function indexAction() {
        $myForm	= new \Admin\Form\Search\Setting($this->getServiceLocator());
        $myForm->setData($this->_params['ssFilter']);
        
        if(empty($this->params()->fromRoute('code'))) {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'no-access'));
        }
        
        $node = $this->getTable()->getItem(array('code' => $this->params()->fromRoute('code')), array('task' => 'code'));
        $this->_params['node'] = $node;
        if(empty($node)) {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
        
        $item_first = $this->getTable()->listItem($this->_params, array('task' => 'list-item', 'item_first' => true));
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        $count = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        
        $this->_viewModel['myForm']	    = $myForm;
        $this->_viewModel['item_first'] = $item_first->current();
        $this->_viewModel['items']      = $items;
        $this->_viewModel['count']      = $count;
        $this->_viewModel['level']      = $this->getServiceLocator()->get('Admin\Model\SettingTable')->itemInSelectbox($this->_params, array('task' => 'list-level'));
        $this->_viewModel['caption']    = 'Cấu hình - Danh sách';
        return new ViewModel($this->_viewModel);
    }
    
    public function addAction() {
        $myForm			= $this->getForm();
        
        if(empty($this->_params['route']['code']) || empty($this->_params['route']['type']) || empty($this->_params['route']['reference'])) {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'no-access'));
        }
    
        if($this->getRequest()->isPost()){
            $myForm->setData($this->_params['data']);
    
            $controlAction = $this->_params['data']['control-action'];
            if($myForm->isValid()){
                $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                $result = $this->getTable()->saveItem($this->_params, array('task' => 'add-item'));
    
                $this->flashMessenger()->addMessage('Dữ liệu đã được cập nhật thành công');

                if($controlAction == 'save-new') {
                    return $this->redirect()->toRoute('routeAdminNested/add', array(
                        'controller' => $this->_params['controller'],
                        'code' => $this->params()->fromRoute('code'),
                        'type' => $this->params()->fromRoute('type'),
                        'reference' => $this->params()->fromRoute('reference')
                    ));
                } else if($controlAction == 'save') {
                    return $this->redirect()->toRoute('routeAdminNested/default', array(
                        'controller' => $this->_params['controller'],
                        'action' => 'edit',
                        'id' => $result,
                        'code' => $this->params()->fromRoute('code')
                    ));
                } else {
                    return $this->redirect()->toRoute('routeAdminNested/index', array(
                        'controller' => $this->_params['controller'],
                        'code' => $this->params()->fromRoute('code')
                    ));
                }
            }
        }
        
        $nodeReference = $this->getTable()->getItem(array('id' => $this->_params['route']['reference']));
        if(empty($nodeReference)) {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
    
        $caption = 'Cấu hình - Thêm mới';
        
        $this->_viewModel['myForm']	    = $myForm;
        $this->_viewModel['caption']    = $caption;
        return new ViewModel($this->_viewModel);
    }
    
    public function editAction() {
        $myForm			= $this->getForm();

        if(!empty($this->_params['route']['type'])) {
            $item = $this->getTable()->getItem(array('id' => $this->_params['route']['type']));
            if(!empty($item)) {
                $myForm->setInputFilter(new \Admin\Filter\Setting(array('id' => $item['id'])));
                $myForm->bind($item);
            } else {
                return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
        
        if($this->getRequest()->isPost()){
            $myForm->setData($this->_params['data']);
    
            $controlAction = $this->_params['data']['control-action'];
            if($myForm->isValid()){
                $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                $result = $this->getTable()->saveItem($this->_params, array('task' => 'edit-item'));
    
                $this->flashMessenger()->addMessage('Dữ liệu đã được cập nhật thành công');
    
                if($controlAction == 'save') {
                    return $this->redirect()->toRoute('routeAdminNested/default', array(
                        'controller' => $this->_params['controller'],
                        'action' => 'edit',
                        'id' => $result,
                        'code' => $this->params()->fromRoute('code')
                    ));
                } else {
                    return $this->redirect()->toRoute('routeAdminNested/index', array(
                        'controller' => $this->_params['controller'],
                        'code' => $this->params()->fromRoute('code')
                    ));
                }
            }
        }
        
        $caption = 'Cấu hình - Sửa';
        
        $this->_viewModel['myForm']	    = $myForm;
        $this->_viewModel['caption']    = $caption;
        return new ViewModel($this->_viewModel);
    }
    
    public function copyAction() {
        $myForm			= $this->getForm();
        
        if(!empty($this->_params['route']['type'])) {
            $item = $this->getTable()->getItem(array('id' => $this->_params['route']['type']));
            if(!empty($item)) {
                $myForm->setInputFilter(new \Admin\Filter\Setting(array('id' => $item['id'])));
                $myForm->bind($item);
            } else {
                return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
        
        if($this->getRequest()->isPost()){
            $myForm->setData($this->_params['data']);
    
            $controlAction = $this->_params['data']['control-action'];
            if($myForm->isValid()){
                $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                $this->_params['route']['reference'] = $item['id'];
                $this->_params['route']['type'] = 'after';
                $result = $this->getTable()->saveItem($this->_params, array('task' => 'add-item'));
    
                $this->flashMessenger()->addMessage('Dữ liệu đã được cập nhật thành công');
    
                if($controlAction == 'save') {
                    return $this->redirect()->toRoute('routeAdminNested/default', array(
                        'controller' => $this->_params['controller'],
                        'action' => 'edit',
                        'id' => $result,
                        'code' => $this->params()->fromRoute('code')
                    ));
                } else {
                    return $this->redirect()->toRoute('routeAdminNested/index', array(
                        'controller' => $this->_params['controller'],
                        'code' => $this->params()->fromRoute('code')
                    ));
                }
            }
        }
        
        $caption = 'Cấu hình - Copy';
        
        $this->_viewModel['myForm']	    = $myForm;
        $this->_viewModel['caption']    = $caption;
        return new ViewModel($this->_viewModel);
    }
    
    public function moveAction() {
        if($this->getRequest()->isXmlHttpRequest()) {
            if($this->getRequest()->isPost()) {
                $this->getTable()->moveItem($this->_params['data']);
                $message = 'Thứ tự phần tử đã được cập nhật thành công';
                $this->flashMessenger()->addMessage($message);
            }
        
            return $this->response;
        }
        
        if(!empty($this->_params['route']['id'])) {
            $item = $this->getTable()->getItem(array('id' => $this->_params['route']['id']));
            if(empty($item)) {
                return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
            }
            $setting = $this->getServiceLocator()->get('Admin\Model\SettingTable')->listItem();
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
        
        if($this->getRequest()->isPost()){
            $controlAction = $this->_params['data']['control-action'];
            
            switch ($this->_params['data']['type']) {
                case 'left':
                    $this->getTable()->moveLeft($item['id'], $this->_params['data']['parent']);
                    break;
                case 'before':
                    $this->getTable()->moveBefore($item['id'], $this->_params['data']['parent']);
                    break;
                case 'after':
                    $this->getTable()->moveAfter($item['id'], $this->_params['data']['parent']);
                    break;
                default:
                    $this->getTable()->moveRight($item['id'], $this->_params['data']['parent']);
                    break;
            }
            
            $this->flashMessenger()->addMessage('Dữ liệu đã được cập nhật thành công');

            if($controlAction == 'save') {
                return $this->redirect()->toRoute('routeAdminNested/default', array(
                    'controller' => $this->_params['controller'],
                    'action' => 'edit',
                    'id' => $item['id'],
                    'code' => $this->params()->fromRoute('code')
                ));
            } else {
                return $this->redirect()->toRoute('routeAdminNested/index', array(
                    'controller' => $this->_params['controller'],
                    'code' => $this->params()->fromRoute('code')
                ));
            }
        }
        
        $caption = 'Cấu hình - Move';
        
        $this->_viewModel['item']       = $item;
        $this->_viewModel['setting']    = \ZendX\Functions\CreateArray::create($setting, array('key' => 'id', 'value' => 'name', 'level' => true, 'level_start' => 1));
        $this->_viewModel['caption']    = $caption;
        return new ViewModel($this->_viewModel);
    }
    
    public function deleteAction() {
        if(!empty($this->_params['route']['type'])) {
            $item = $this->getTable()->getItem(array('id' => $this->_params['route']['type']));
            if(empty($item)) {
                $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
        
        if($this->getRequest()->isPost()){
            $controlAction = $this->_params['data']['control-action'];
            
            switch ($this->_params['data']['type']) {
                case 'only':
                    $this->getTable()->removeNodeOnly($item['id']);
                    break;
                case 'branch':
                default:
                    $this->getTable()->removeBranch($item['id']);
                    break;
            }
            
            $this->flashMessenger()->addMessage('Dữ liệu đã được xóa thành công');

            if($controlAction == 'save') {
                return $this->redirect()->toRoute('routeAdminNested/default', array(
                    'controller' => $this->_params['controller'],
                    'action' => 'edit',
                    'id' => $item['id'],
                    'code' => $this->params()->fromRoute('code')
                ));
            } else {
                return $this->redirect()->toRoute('routeAdminNested/index', array(
                    'controller' => $this->_params['controller'],
                    'code' => $this->params()->fromRoute('code')
                ));
            }
        }
        
        $caption = 'Cấu hình - Xóa';
        
        $this->_viewModel['item']       = $item;
        $this->_viewModel['caption']    = $caption;
        return new ViewModel($this->_viewModel);
    }
    
    public function statusAction() {
        if($this->getRequest()->isXmlHttpRequest()) {
            $this->getTable()->changeStatus($this->_params['data'], array('task' => 'change-status'));
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'no-access'));
        }
        
        return $this->response;
    }

    public function orderingAction() {
        if($this->getRequest()->isPost()) {
            if(!empty($this->_params['data']['cid']) && !empty($this->_params['data']['ordering'])) {
                $result = $this->getTable()->changeOrdering($this->_params['data'], array('task' => 'change-ordering'));
                $message = 'Sắp xếp '. $result .' phần tử thành công';
                $this->flashMessenger()->addMessage($message);
            }
        }
    
        return $this->redirect()->toRoute('routeAdminNested/index', array(
            'controller' => $this->_params['controller'],
            'code' => $this->_params['data']['code']
        ));
    }
}
