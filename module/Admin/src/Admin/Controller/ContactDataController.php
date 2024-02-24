<?php

namespace Admin\Controller;

use kcfinder\zipFolder;
use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;
use ZendX\System\UserInfo;

class ContactDataController extends ActionController{

    public function init() {
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\FormDataTable';
        $this->_options['formName'] = 'formAdminFormData';

        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__ . $this->_params['action']);
        $this->_params['ssFilter']['order_by']              = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'created';
        $this->_params['ssFilter']['order']                 = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';
        $this->_params['ssFilter']['filter_status']         = $ssFilter->filter_status;
        $this->_params['ssFilter']['filter_keyword']        = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['filter_date_begin']     = $ssFilter->filter_date_begin;
        $this->_params['ssFilter']['filter_date_end']       = $ssFilter->filter_date_end;
        $this->_params['ssFilter']['filter_active']         = $ssFilter->filter_active;
        $this->_params['ssFilter']['filter_contact_coin']   = $ssFilter->filter_contact_coin;
        $this->_params['ssFilter']['filter_cancel_share']   = $ssFilter->filter_cancel_share;
        $this->_params['ssFilter']['filter_product']        = $ssFilter->filter_product;
        $this->_params['ssFilter']['filter_sale_branch']    = $ssFilter->filter_sale_branch;
        $this->_params['ssFilter']['filter_sale_group']     = $ssFilter->filter_sale_group;
        $this->_params['ssFilter']['filter_location_city']  = $ssFilter->filter_location_city;
        $this->_params['ssFilter']['filter_user_id']        = $ssFilter->filter_user_id;
        $this->_params['ssFilter']['filter_sales_id']       = $ssFilter->filter_sales_id;
        $this->_params['ssFilter']['filter_marketer_id']    = $ssFilter->filter_marketer_id;

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
            $ssFilter->filter_contact_coin      = $data['filter_contact_coin'];
            $ssFilter->filter_cancel_share      = $data['filter_cancel_share'];
            $ssFilter->filter_product           = $data['filter_product'];
            $ssFilter->filter_sale_branch       = $data['filter_sale_branch'];
            $ssFilter->filter_sale_group        = $data['filter_sale_group'];
            $ssFilter->filter_location_city     = $data['filter_location_city'];
            $ssFilter->filter_user_id           = $data['filter_user_id'];
            $ssFilter->filter_sales_id          = $data['filter_sales_id'];
            $ssFilter->filter_marketer_id       = $data['filter_marketer_id'];
        }

        if (!empty($this->_params['route']['id'])) {
            $ssFilter->filter_product = $this->_params['route']['id'];
        }

        $this->goRoute(array('action' => $action));
    }
    // Danh sách
    public function indexAction() {
        $ssFilter       = new Container(__CLASS__);
        $aclInfo        = new \ZendX\System\UserInfo();
        $this->_params['permissionInfo'] = $aclInfo->getPermissionInfo();
        $this->_params['permissionListInfo'] = $aclInfo->getPermissionListInfo();

        $curent_user = $this->_userInfo->getUserInfo();
        $permission_ids = explode(',', $curent_user['permission_ids']);
        if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids)){
            $this->_params['ssFilter']['filter_sale_branch'] = $curent_user['sale_branch_id'];
            $ssFilter->filter_sale_branch = $curent_user['sale_branch_id'];
        }

        $myForm    = new \Admin\Form\Search\FormData($this->getServiceLocator(), $this->_params['ssFilter']);
        $myForm->setData($this->_params['ssFilter']);

        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item-contact'));

        $this->_viewModel['myForm']             = $myForm;
        $this->_viewModel['items']              = $items;
        $this->_viewModel['model']              = $this->getTable();

        $this->_viewModel['count']              = $this->getTable()->countItem($this->_params, array('task' => 'list-item-contact'));
        $this->_viewModel['user']               = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['location_city']      = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache'));
        $this->_viewModel['location_district']  = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 2), array('task' => 'cache'));
        $this->_viewModel['group']              = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['branch']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['form_data_result']   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'form-data-result')), array('task' => 'cache'));
        $this->_viewModel['history_status']     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'history-status')), array('task' => 'cache'));
        $this->_viewModel['marketing_channel']  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'marketing-channel')), array('task' => 'cache'));
        $this->_viewModel['product']            = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'list-item'));
        $this->_viewModel['product_group']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-group')), array('task' => 'cache'));
        $this->_viewModel['caption']            = 'Danh sách data khách hàng';
//        $this->_viewModel['encode_phone'] = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'encode-phone')), array('task' => 'list-all')), array('key' => 'content', 'value' => 'status'));
        return new ViewModel($this->_viewModel);
    }

    // Chia data mkt cho nhân viên sales
    public function shareAction() {
        $ssFilter = new Container(__CLASS__);
        $users    = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-sale'));
        $myForm   = new \Admin\Form\FormData\Share($this->getServiceLocator());

        $this->_viewModel['myForm']         = $myForm;
        $this->_viewModel['caption']        = 'Phân chia data';

        if($this->getRequest()->isPost()){
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\FormData\Share($this->_params));
                $myForm->setData($this->_params['data']);

                $items = [];
                $list_data_id = json_decode($this->_params['data']['list_data_id']);

                if (empty($list_data_id)) {
                    $this->flashMessenger()->addMessage('Chưa chọn data để chia');
                    echo 'success';
                    return $this->response;
                }
                if (empty($this->_params['data']['user_id'])) {
                    $this->flashMessenger()->addMessage('Chưa chọn nhân viên sale');
                    echo 'success';
                    return $this->response;
                }

                foreach ($list_data_id as $item_data) {
                    $items[] = array(
                        'id' => $item_data->id
                    );
                }
                $this->_params['data']['items'] = $items;

                if(!empty($this->_params['data']['user_id']) && !empty($this->_params['data']['items'])){
                    $result = $this->getServiceLocator()->get('Admin\Model\FormDataTable')->shareData($this->_params);
                    $this->flashMessenger()->addMessage('Chia sẻ data thành công');
                    echo 'success';
                    return $this->response;
                }
            }
        }

        $viewModel =  new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }

    // Hủy không chia
    public function cancelShareAction() {
        if($this->getRequest()->isPost()) {
            if(!empty($this->_params['data']['cid'])) {
                $params['data']['cid']          = $this->_params['data']['cid'];
                $params['data']['cancel_share'] = 1;

                // Cập nhật trạng thái hủy không gửi
                $result  = $this->getTable()->saveItem($params, array('task' => 'update-cancel-share'));
                if(!empty($result)){
                    $message = 'Cập nhật thành công trạng thái - Hủy không chia';
                    $this->flashMessenger()->addMessage($message);
                }
            }
        }

        $this->goRoute(array('action' => 'index'));
    }

    // Cho phép chia
    public function restoreShareAction() {
        if($this->getRequest()->isPost()) {
            if(!empty($this->_params['data']['cid'])) {
                $params['data']['cid']          = $this->_params['data']['cid'];
                $params['data']['cancel_share'] = 0;

                // Cập nhật trạng thái cho phép gửi.
                $result  = $this->getTable()->saveItem($params, array('task' => 'update-cancel-share'));
                if(!empty($result)){
                    $message = 'Cập nhật thành công trạng thái - Được phép chia';
                    $this->flashMessenger()->addMessage($message);
                }
            }
        }

        $this->goRoute(array('action' => 'index'));
    }
}
