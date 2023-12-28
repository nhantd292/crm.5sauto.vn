<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class TaskProjectContentController extends ActionController {
    
    public function init() {
        
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\TaskProjectContentTable';
        $this->_options['formName'] = 'formAdminTaskProjectContent';
        
        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']              = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'name';
        $this->_params['ssFilter']['order']                 = !empty($ssFilter->order) ? $ssFilter->order : 'ASC';
        $this->_params['ssFilter']['filter_status']         = $ssFilter->filter_status;
        $this->_params['ssFilter']['filter_keyword']        = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['filter_task_project']   = $ssFilter->filter_task_project;
        
        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage'] = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : 100;
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
            $ssFilter->filter_task_project  = $data['filter_task_project'];
        }
    
        $this->goRoute();
    }
    
    public function indexAction() {
        if(!empty($this->_params['route']['id'])) {
            $ssFilter = new Container(__CLASS__);
            $ssFilter->filter_task_project = $this->_params['route']['id'];
            $this->_params['ssFilter']['filter_task_project'] = $ssFilter->filter_task_project;
        }
        $project    = $this->getServiceLocator()->get('Admin\Model\TaskProjectTable')->getItem(array('id' => $this->_params['ssFilter']['filter_task_project']), null);
        if(empty($project)) {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'task-project', 'action' => 'index'));
        }
        
        $myForm	= new \Admin\Form\Search\TaskProjectContent($this->getServiceLocator());
        $myForm->setData($this->_params['ssFilter']);
        
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        
        $this->_viewModel['myForm']	            = $myForm;
        $this->_viewModel['items']              = $items;
        $this->_viewModel['count']              = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['user']               = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['status']             = array( 0 => 'Chưa triển khai', 1 => 'Đã hoàn thành', 2 => 'Đang thực hiện');
        $this->_viewModel['content_status']     = array( 0 => 'Chưa thực hiện', 1 => 'Đã hoàn thành', 2 => 'Chưa hoàn thành');
        $this->_viewModel['camera_status']      = array( 0 => 'Chưa thực hiện', 1 => 'Đã hoàn thành', 2 => 'Chưa hoàn thành');
        $this->_viewModel['editor_status']      = array( 0 => 'Chưa thực hiện', 1 => 'Đã hoàn thành', 2 => 'Chưa hoàn thành');
        $this->_viewModel['youtube_status']     = array( 0 => 'Chưa thực hiện', 1 => 'Đã hoàn thành', 2 => 'Chưa hoàn thành');
        $this->_viewModel['facebook_status']    = array( 0 => 'Chưa thực hiện', 1 => 'Đã hoàn thành', 2 => 'Chưa hoàn thành');
        $this->_viewModel['params']             = $this->_params;
        $this->_viewModel['caption']            = $project['name'] .' - Nội dung - Danh sách';
        return new ViewModel($this->_viewModel);
    }
    
    public function formAction() {
        $ssFilter = new Container(__CLASS__);
        $project  = $this->getServiceLocator()->get('Admin\Model\TaskProjectTable')->getItem(array('id' => $ssFilter->filter_task_project), null);
        if(empty($project)) {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'task-project', 'action' => 'index'));
        }
        
        $myForm = $this->getForm();
        
        $task = 'add-item';
        $caption = $project['name'] .' - Nội dung - Thêm mới';
        $item = array();
        if(!empty($this->params('id'))) {
            $date = new \ZendX\Functions\Date();
            $this->_params['data']['id'] = $this->params('id');
            $item = $this->getTable()->getItem($this->_params['data']);
            $item['date']           = $date->formatToView($item['date']);
            $item['content_date']   = $date->formatToView($item['content_date']);
            $item['camera_date']    = $date->formatToView($item['camera_date']);
            $item['editor_date']    = $date->formatToView($item['editor_date']);
            $item['youtube_date']   = $date->formatToView($item['youtube_date']);
            $item['facebook_date']  = $date->formatToView($item['facebook_date']);
            if(!empty($item)) {
                $myForm->setInputFilter(new \Admin\Filter\TaskProjectContent(array('id' => $this->_params['data']['id'])));
                $myForm->bind($item);
                $task = 'edit-item';
                $caption = $project['name'] .' - Nội dung - Sửa';
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
