<?php

namespace Admin\Controller;

use kcfinder\zipFolder;
use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;
use ZendX\System\UserInfo;

class MarketingAdsController extends ActionController{

    public function init() {
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\MarketingAdsTable';
        $this->_options['formName'] = 'formAdminMarketingAds';

        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__ . $this->_params['action']);
        $this->_params['ssFilter']['order_by']                  = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'date';
        $this->_params['ssFilter']['order']                     = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';
        $this->_params['ssFilter']['filter_status']             = $ssFilter->filter_status;
        $this->_params['ssFilter']['filter_keyword']            = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['filter_date_begin']         = $ssFilter->filter_date_begin;
        $this->_params['ssFilter']['filter_date_end']           = $ssFilter->filter_date_end;
        $this->_params['ssFilter']['filter_active']             = $ssFilter->filter_active;
        $this->_params['ssFilter']['filter_sale_branch']        = $ssFilter->filter_sale_branch;
        $this->_params['ssFilter']['filter_marketer_id']        = $ssFilter->filter_marketer_id;
        $this->_params['ssFilter']['filter_product_group_id']   = $ssFilter->filter_product_group_id;

        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage']               = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : 50;
        $this->_paginator['currentPageNumber']              = $this->params()->fromRoute('page', 1);
        $this->_params['paginator'] = $this->_paginator;

        // Lấy dữ liệu post của form
        $this->_params['data'] = array_merge($this->getRequest()->getPost()->toArray(), $this->getRequest()->getFiles()->toArray());
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;

    }

    public function filterAction() {
        if ($this->getRequest()->isPost()) {
            $action = !empty($this->getRequest()->getPost('filter_action')) ? $this->getRequest()->getPost('filter_action') : 'index';
            
            $ssFilter	= new Container(__CLASS__ . $action);
            $data = $this->_params['data'];
            
            $ssFilter->pagination_option        = intval($data['pagination_option']);
            $ssFilter->order_by                 = $data['order_by'];
            $ssFilter->order                    = $data['order'];
            $ssFilter->filter_status            = $data['filter_status'];
            $ssFilter->filter_keyword           = $data['filter_keyword'];
            $ssFilter->filter_date_begin        = $data['filter_date_begin'];
            $ssFilter->filter_date_end          = $data['filter_date_end'];
            $ssFilter->filter_active            = $data['filter_active'];
            $ssFilter->filter_sale_branch       = $data['filter_sale_branch'];
            $ssFilter->filter_marketer_id       = $data['filter_marketer_id'];
            $ssFilter->filter_product_group_id  = $data['filter_product_group_id'];
        }

        if (!empty($this->_params['route']['id'])) {
            $ssFilter->filter_product = $this->_params['route']['id'];
        }

        $this->goRoute(array('action' => $action));
    }

    public function indexAction() {
        $curent_user = $this->_userInfo->getUserInfo();
        $permission_ids = explode(',', $curent_user['permission_ids']);
        if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids) && !in_array(MANAGER, $permission_ids)){
            if(in_array(GDCN, $permission_ids)){
                $this->_params['ssFilter']['filter_sale_branch'] = $curent_user['sale_branch_id'];
            }
            elseif (in_array(GROUP_MKT_LEADER, $permission_ids)){
                $this->_params['ssFilter']['filter_sale_branch'] = $curent_user['sale_branch_id'];
                $this->_params['ssFilter']['filter_sale_group'] = $curent_user['sale_group_id'];
            }
            else{
                $this->_params['ssFilter']['filter_marketer_id'] = $curent_user['id'];
            }
        }

        $myForm    = new \Admin\Form\Search\MarketingAds($this->getServiceLocator(), $this->_params['ssFilter']);
        $myForm->setData($this->_params['ssFilter']);

        // Danh sách những data đã có doanh thu
        $items_sales = $this->getTable()->listItem($this->_params, array('task' => 'list-data-sales'));

        // Danh sách data
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));

        $this->_viewModel['myForm']             = $myForm;
        $this->_viewModel['items']              = $items;
        $this->_viewModel['items_sales']        = $items_sales;

        $this->_viewModel['model']              = $this->getTable();

        $this->_viewModel['count']              = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['user']               = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['branch']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['product_group']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-group')), array('task' => 'cache'));
        $this->_viewModel['caption']            = 'Báo cáo chi phí ADS';

        return new ViewModel($this->_viewModel);
    }

    public function addAction() {
        $myForm = new \Admin\Form\MarketingAds($this, array('action' => 'add'));

        if($this->getRequest()->isPost()){
            $myForm->setInputFilter(new \Admin\Filter\MarketingAds());
            $myForm->setData($this->_params['data']);
            $curent_user_id  = $this->_userInfo->getUserInfo('id');
            $controlAction = $this->_params['data']['control-action'];
            if($myForm->isValid()){
                $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                $result = $this->getServiceLocator()->get('Admin\Model\MarketingAdsTable')->saveItem($this->_params, array('task' => 'add-item'));
                $this->flashMessenger()->addMessage('Thêm mới chi phí thành công');

                if($controlAction == 'save-new') {
                    $this->goRoute(array('action' => 'add'));
                } else if($controlAction == 'save') {
                    $this->goRoute(array('action' => 'edit', 'id' => $result));
                } else {
                    $this->goRoute();
                }
            }
        }

        $this->_viewModel['myForm']	    = $myForm;
        $this->_viewModel['caption']    = 'Thêm mới chi phí ADS';
        return new ViewModel($this->_viewModel);
    }

    public function editAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $myForm = $this->getForm();
        $item_id = $this->params('id');
        if (!empty($item_id)) {
            $this->_params['data']['id'] = $item_id;
            $item = $this->getTable()->getItem($this->_params['data']);
            if (!empty($item)) {
                if (!$this->getRequest()->isPost()) {
                    $item['from_date'] = $dateFormat->formatToView($item['from_date']);
                    $item['to_date'] = $dateFormat->formatToView($item['to_date']);
                    $myForm->setData($item);
                }
            }
            else {
                return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'not-found'));
            }
        }
        if ($this->getRequest()->isPost()) {
            $myForm->setInputFilter(new \Admin\Filter\MarketingAds(array('id' => $this->_params['data']['id'], 'data' => $this->_params['data'], 'route' => $this->_params['route'])));
            $myForm->setData($this->_params['data']);
            $controlAction = $this->_params['data']['control-action'];

            if ($myForm->isValid()) {
                $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                $this->_params['item'] = $item;
                $this->getServiceLocator()->get('Admin\Model\MarketingAdsTable')->saveItem($this->_params, array('task' => 'edit-item'));
                $this->flashMessenger()->addMessage('Chi phí ads đã được cập nhật');

                if($controlAction == 'save-new') {
                    $this->goRoute(array('action' => 'add'));
                } else if($controlAction == 'save') {
                    $this->goRoute(array('action' => 'edit', 'id' => $item_id));
                } else {
                    $this->goRoute();
                }
            }
        }

        $this->_viewModel['myForm']     = $myForm;
        $this->_viewModel['item']       = $item;
        $this->_viewModel['caption']    = 'Sửa báo cáo chi phí ADS';
        return new ViewModel($this->_viewModel);
    }

    public function deleteAction() {
        if($this->getRequest()->isPost()) {
            if(!empty($this->_params['data']['cid'])) {
                // Xóa data đã chọn
                $cdata = $this->getTable()->deleteItem($this->_params, array('task' => 'delete-item'));
                $message = 'Xóa '. $cdata .' phần tử thành công';
                $this->flashMessenger()->addMessage($message);
            }
        }

        $this->goRoute(array('action' => 'index'));
    }
}
