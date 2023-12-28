<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class DataConfigController extends ActionController {
    
    public function init() {
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\DataConfigTable';
        $this->_options['formName'] = 'formAdminDataConfig';
        
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
        
        $myForm	= new \Admin\Form\Search\DataConfig($this->getServiceLocator(), $this->_params['ssFilter']);
        $myForm->setData($this->_params['ssFilter']);

        $curent_user = $this->_userInfo->getUserInfo();
        $permission_ids = explode(',', $curent_user['permission_ids']);
        if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids) && !in_array(MANAGER, $permission_ids)){
            if(in_array(GDCN, $permission_ids)  || in_array('saleadmin', $permission_ids)){
                $this->_params['ssFilter']['filter_sale_branch_id'] = $curent_user['sale_branch_id'];
            }
        }
        
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));

        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $items;
        $this->_viewModel['count']                  = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['user']                   = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\UserTable')->listItem($this->_params, array('task' => 'list-sale')), array('key' => 'id', 'value' => 'name'));
        $this->_viewModel['userInfo']               = $this->_userInfo->getUserInfo();
        $this->_viewModel['caption']                = 'Cấu hình chia data tự động - Danh sách';
        return new ViewModel($this->_viewModel);
    }
    
    public function formAction(){
        $myForm   = new \Admin\Form\DataConfig($this->getServiceLocator());

        $task    = 'add-item';
        $caption = 'Cấu hình chia data tự động - Thêm mới';
        $item    = array();
        if (!empty($this->params('id'))) {
            $this->_params['data']['id'] = $this->params('id');
            $item                        = $this->getTable()->getItem($this->_params['data']);

            if (!empty($item)) {
                $myForm->setData($item);
                $task    = 'edit-item';
                $caption = 'Cấu hình chia data tự động - Sửa';
            }
        }

        if ($this->getRequest()->isPost()) {
            $myForm->setInputFilter(new \Admin\Filter\DataConfig(array('id' => $this->_params['data']['id'], 'data' => $this->_params['data'], 'route' => $this->_params['route'])));
            $myForm->setData($this->_params['data']);
            $controlAction = $this->_params['data']['control-action'];

            if ($myForm->isValid()) {
                $this->_params['data']['type'] = 'auto_share_data';
                if(!empty($this->_params['data']['user_branch_ids'])){
                    $this->getServiceLocator()->get('Admin\Model\DataConfigTable')->saveItem($this->_params, array('task' => $task));
                    $this->flashMessenger()->addMessage('Dữ liệu đã được thêm thành công');
                }
                else{
                    $this->flashMessenger()->addMessage('Chưa cấu hình nhân sự được chọn');
                }

                if ($controlAction == 'save-new') {
                    $this->goRoute(array('action' => 'add'));
                } else {
                    $this->goRoute();
                }
            }
        }
    
        $this->_viewModel['myForm']     = $myForm;
        $this->_viewModel['caption']    = $caption;
        return new ViewModel($this->_viewModel);
    }

}
