<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class TaskController extends ActionController {
	
    public function init() {
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\TaskTable';
        $this->_options['formName'] = 'formAdminTask';
        
        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']              = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'created';
        $this->_params['ssFilter']['order']                 = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';
        $this->_params['ssFilter']['filter_keyword']        = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['filter_task_category']  = $ssFilter->filter_task_category;
        $this->_params['ssFilter']['filter_task_status']    = $ssFilter->filter_task_status;
        $this->_params['ssFilter']['filter_year']     		= $ssFilter->filter_year ? $ssFilter->filter_year : date('Y');
        $this->_params['ssFilter']['filter_date_begin']     = $ssFilter->filter_date_begin ? $ssFilter->filter_date_begin : date('d/m/Y', strtotime('-'.(date('w') - 1).' days'));
        $this->_params['ssFilter']['filter_date_end']       = $ssFilter->filter_date_end ? $ssFilter->filter_date_end : date('d/m/Y', strtotime('+'.(7 - date('w')).' days'));
        $this->_params['ssFilter']['filter_user']           = $ssFilter->filter_user ? $ssFilter->filter_user : $this->_userInfo->getUserInfo('id');
        $this->_params['ssFilter']['filter_type_list']    	= !empty($ssFilter->filter_type_list) ? $ssFilter->filter_type_list : 'week';
        
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
            $ssFilter->pagination_option 	= intval($data['pagination_option']);
    
            $ssFilter->order_by         	= $data['order_by'];
            $ssFilter->order            	= $data['order'];
    
            $ssFilter->filter_keyword       = $data['filter_keyword'];
            $ssFilter->filter_task_category = $data['filter_task_category'];
            $ssFilter->filter_task_status   = $data['filter_task_status'];
            $ssFilter->filter_date_begin    = $data['filter_date_begin'];
            $ssFilter->filter_date_end      = $data['filter_date_end'];
            $ssFilter->filter_type_list     = $data['filter_type_list'];
            $ssFilter->filter_user      	= $data['filter_user'];
            if($ssFilter->filter_type_list == 'week') {
            	$ssFilter->filter_date_begin = date('d/m/Y', strtotime('-'.(date('w') - 1).' days'));
            	$ssFilter->filter_date_end = date('d/m/Y', strtotime('+'.(7 - date('w')).' days'));
            }
            
            if(!empty($data['filter_week_year'])) {
            	$filter_date = explode('-', $data['filter_week_year']);
            	
            	$ssFilter->filter_date_begin    = $filter_date[0];
            	$ssFilter->filter_date_end      = $filter_date[1];
            }
            
            if(!empty($data['filter_year'])) {
            	if($ssFilter->filter_year != $data['filter_year']) {
            		$ssFilter->filter_year	= $data['filter_year'];
	            	if($data['filter_year'] == date('Y')) {
	            		$ssFilter->filter_date_begin = date('d/m/Y', strtotime('-'.(date('w') - 1).' days'));
	            		$ssFilter->filter_date_end = date('d/m/Y', strtotime('+'.(7 - date('w')).' days'));
	            	} else {
		            	$date = date_create($ssFilter->filter_year .'-01-01');
		            	date_sub($date, date_interval_create_from_date_string(date_format($date, 'N') - 1 . ' days'));
		            	$ssFilter->filter_date_begin = date_format($date, 'd/m/Y');
		            	date_add($date, date_interval_create_from_date_string('6 days'));
		            	$ssFilter->filter_date_end = date_format($date, 'd/m/Y');
	            	}
            	} else {
            		$ssFilter->filter_year	= $data['filter_year'];
            	}
            }
        }
    
        $this->goRoute();
    }
    
    public function indexAction() {
        $myForm	= new \Admin\Form\Search\Task($this->getServiceLocator());
        $myForm->setData($this->_params['ssFilter']);
        
        // Lấy dữ liệu
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
		
        $this->_viewModel['myForm']	            = $myForm;
        $this->_viewModel['items']              = $items;
        $this->_viewModel['count']              = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['user']               = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['task_category']      = $this->getServiceLocator()->get('Admin\Model\TaskCategoryTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['task_project']       = $this->getServiceLocator()->get('Admin\Model\TaskProjectTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['task_status']        = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "table" => "document", "where" => array( "code" => "task-status" ), "order" => array("ordering" => "ASC", "name" => "ASC"), "view"  => array( "key" => "id", "value" => "name", "sprintf" => "%s" ) ), array('task' => 'cache'));
        $this->_viewModel['task_status_color']  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "table" => "document", "where" => array( "code" => "task-status" ), "order" => array("ordering" => "ASC", "color" => "ASC"), "view"  => array( "key" => "id", "value" => "color", "sprintf" => "%s" ) ), array('task' => 'cache'));
        $this->_viewModel['caption']        = 'Công việc - Danh sách';
        $viewModel = new ViewModel($this->_viewModel);
        
        if(!empty($this->_params['ssFilter']['filter_type_list'])) {
        	if($this->_params['ssFilter']['filter_type_list'] != 'list'){
        		$viewModel->setTemplate('admin/task/task-' . $this->_params['ssFilter']['filter_type_list']);
        	}
        }
        
        return $viewModel;
    }
    
    public function formAction() {
        $myForm			= $this->getForm();
        
        $task = 'add-item';
        $caption = 'Công việc - Thêm mới';
        $item = array();
        if(!empty($this->params('id'))) {
            $this->_params['data']['id'] = $this->params('id');
            $item = $this->getTable()->getItem($this->_params['data']);
            if(!empty($item)) {
                $myForm->setInputFilter(new \Admin\Filter\Task(array('id' => $this->_params['data']['id'])));
                $myForm->bind($item);
                $task = 'edit-item';
                $caption = 'Công việc - Sửa';
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













