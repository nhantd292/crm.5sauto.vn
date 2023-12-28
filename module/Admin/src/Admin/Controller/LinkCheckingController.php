<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class LinkCheckingController extends ActionController {
    
    public function init() {
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\LinkCheckingTable';
        $this->_options['formName'] = 'formAdminLinkChecking';
        
        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__);
        $this->_params['ssFilter']['filter_keyword']                = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['filter_date_begin']             = $ssFilter->filter_date_begin;
        $this->_params['ssFilter']['filter_date_end']               = $ssFilter->filter_date_end;
        
        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage']   = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : $this->_paginator['itemCountPerPage'];
        $this->_paginator['currentPageNumber']  = $this->params()->fromRoute('page', 1);
        $this->_params['paginator']             = $this->_paginator;
        
        // Lấy dữ liệu post của form
        $this->_params['data'] = array_merge($this->getRequest()->getPost()->toArray(), $this->getRequest()->getFiles()->toArray());
        
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
    }
    
    public function filterAction() {
        $ssFilter	= new Container(__CLASS__);
        $data = $this->_params['data'];
    
        if($this->getRequest()->isPost()) {
    
            $ssFilter->pagination_option        = intval($data['pagination_option']);
            $ssFilter->filter_keyword           = $data['filter_keyword'];
            $ssFilter->filter_date_begin        = $data['filter_date_begin'];
            $ssFilter->filter_date_end          = $data['filter_date_end'];
        }
        
        $this->goRoute();
    }
    
    public function indexAction() {
        $ssFilter = new Container(__CLASS__);
        $ssFilter->currentPageNumber = $this->_paginator['currentPageNumber'];
        $myForm	= new \Admin\Form\Search\LinkChecking($this->getServiceLocator(), $this->_params['ssFilter']);

        $curent_user = $this->_userInfo->getUserInfo();
        $permission_ids = explode(',', $curent_user['permission_ids']);
        if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids) && !in_array(MANAGER, $permission_ids)){
            if(in_array(GDCN, $permission_ids)){
                $this->_params['ssFilter']['filter_sale_branch_id'] = $curent_user['sale_branch_id'];
            }
            else{
                $this->_params['ssFilter']['filter_marketer_id'] = $curent_user['id'];
            }
        }

        $myForm->setData($this->_params['ssFilter']);
        
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));

        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $items;
        $this->_viewModel['count']                  = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['user']                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['marketing_channel']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'marketing-channel')), array('task' => 'cache'));
        $this->_viewModel['product_group']          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-group')), array('task' => 'cache'));
        $this->_viewModel['campaign']               = $this->getServiceLocator()->get('Admin\Model\CampaignTable')->listItem(array('where' => array('status' => 1)), array('task' => 'cache'));
        $this->_viewModel['userInfo']               = $this->_userInfo->getUserInfo();
        $this->_viewModel['caption']                = 'Link tracking - Danh sách';
        return new ViewModel($this->_viewModel);
    }
    
    public function addAction(){
        $myForm = $this->getForm();
    
        if ($this->getRequest()->isPost()) {
            $myForm->setInputFilter(new \Admin\Filter\LinkChecking(array('id' => $this->_params['data']['id'], 'data' => $this->_params['data'], 'route' => $this->_params['route'])));
            $myForm->setData($this->_params['data']);
    
            $controlAction = $this->_params['data']['control-action'];
            if ($myForm->isValid()) {
                $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
    
                $this->getServiceLocator()->get('Admin\Model\LinkCheckingTable')->saveItem($this->_params, array('task' => 'add-item'));
    
                $this->flashMessenger()->addMessage('Dữ liệu đã được thêm thành công');
                if ($controlAction == 'save-new') {
                    $this->goRoute(array('action' => 'add'));
                } else {
                    $this->goRoute();
                }
            }
        }
    
        $this->_viewModel['myForm']     = $myForm;
        $this->_viewModel['caption']    = 'Link tracking - Thêm mới';
        return new ViewModel($this->_viewModel);
    }
    
    public function editAction(){
        $ssFilter = new Container(__CLASS__);
        $myForm = new \Admin\Form\Checking\Edit($this->getServiceLocator());
        
        if(!empty($this->_params['data']['id'])) {
            $link = $this->getServiceLocator()->get('Admin\Model\LinkCheckingTable')->getItem(array('id' => $this->_params['data']['id']));
            $myForm->setData($link);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }
        if ($this->getRequest()->isPost()) {
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setData($this->_params['data']);
                if ($myForm->isValid()) {
                    $this->_params['item'] = $link;
                    $this->getServiceLocator()->get('Admin\Model\LinkCheckingTable')->saveItem($this->_params, array('task' => 'edit-item'));
        
                    $this->flashMessenger()->addMessage('Dữ liệu đã được cập nhật thành công');
                    echo 'success';
                    return $this->response;
                }
            } else {
                $myForm->setData($this->_params['data']);
            }
        }
    
        $this->_viewModel['myForm']     = $myForm;
        $this->_viewModel['caption']    = 'Link tracking - Sửa';
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }

    public function introduceAction(){
        $this->_viewModel['params_template']	= $this->_params['template'];
        return new ViewModel($this->_viewModel);
    }
}
