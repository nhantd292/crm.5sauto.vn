<?php

namespace Admin\Controller;

use kcfinder\zipFolder;
use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;
use ZendX\System\UserInfo;

class FormDataController extends ActionController{

    public function init() {
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\FormDataTable';
        $this->_options['formName'] = 'formAdminFormData';

        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__ . $this->_params['action']);
        $this->_params['ssFilter']['order_by']              = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'date';
        $this->_params['ssFilter']['order']                 = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';
        $this->_params['ssFilter']['filter_status']         = $ssFilter->filter_status;
        $this->_params['ssFilter']['filter_keyword']        = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['filter_date_begin']     = $ssFilter->filter_date_begin;
        $this->_params['ssFilter']['filter_date_end']       = $ssFilter->filter_date_end;
        $this->_params['ssFilter']['filter_active']         = $ssFilter->filter_active;
        $this->_params['ssFilter']['filter_contact_coin']   = $ssFilter->filter_contact_coin;
        $this->_params['ssFilter']['filter_product']        = $ssFilter->filter_product;
        $this->_params['ssFilter']['filter_event']          = $ssFilter->filter_event;
        $this->_params['ssFilter']['filter_sale_branch']    = $ssFilter->filter_sale_branch;
        $this->_params['ssFilter']['filter_sale_group']     = $ssFilter->filter_sale_group;
        $this->_params['ssFilter']['filter_location_city']    = $ssFilter->filter_location_city;
        $this->_params['ssFilter']['filter_user_id']          = $ssFilter->filter_user_id;
        $this->_params['ssFilter']['filter_sales_id']         = $ssFilter->filter_sales_id;
        $this->_params['ssFilter']['filter_marketer_id']      = $ssFilter->filter_marketer_id;
        $this->_params['ssFilter']['filter_product_id']       = $ssFilter->filter_product_id;
        $this->_params['ssFilter']['filter_product_group_id'] = $ssFilter->filter_product_group_id;

        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage']               = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : 50;
        $this->_paginator['currentPageNumber']              = $this->params()->fromRoute('page', 1);
        $this->_params['paginator'] = $this->_paginator;

        // Lấy dữ liệu post của form
        $this->_params['data'] = array_merge($this->getRequest()->getPost()->toArray(), $this->getRequest()->getFiles()->toArray());
        
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
//        $this->_viewModel['encode_phone'] = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'encode-phone')), array('task' => 'list-all')), array('key' => 'content', 'value' => 'status'));

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
            $ssFilter->filter_product           = $data['filter_product'];
            $ssFilter->filter_event             = $data['filter_event'];
            $ssFilter->filter_sale_branch       = $data['filter_sale_branch'];
            $ssFilter->filter_sale_group        = $data['filter_sale_group'];
            $ssFilter->filter_location_city     = $data['filter_location_city'];
            $ssFilter->filter_user_id           = $data['filter_user_id'];
            $ssFilter->filter_sales_id          = $data['filter_sales_id'];
            $ssFilter->filter_marketer_id       = $data['filter_marketer_id'];
            $ssFilter->filter_product_id        = $data['filter_product_id'];
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

        $myForm    = new \Admin\Form\Search\FormData($this->getServiceLocator(), $this->_params['ssFilter']);
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
        $this->_viewModel['location_city']      = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache'));
        $this->_viewModel['location_district']  = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 2), array('task' => 'cache'));
        $this->_viewModel['group']              = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['branch']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['form_data_result']   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'form-data-result')), array('task' => 'cache'));
        $this->_viewModel['history_status']     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'history-status')), array('task' => 'cache'));
        $this->_viewModel['marketing_channel']  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'marketing-channel')), array('task' => 'cache'));
        $this->_viewModel['product_group']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-group')), array('task' => 'cache'));
        $this->_viewModel['product']            = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'list-item'));
        $this->_viewModel['sale_history_type']   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-type')), array('task' => 'cache'));
        $this->_viewModel['caption']            = 'Danh sách data Marketing';

        return new ViewModel($this->_viewModel);
    }
    
    public function newAction() {
        $aclInfo        = new \ZendX\System\UserInfo();
        $this->_params['permissionInfo'] = $aclInfo->getPermissionInfo();
        $this->_params['permissionListInfo'] = $aclInfo->getPermissionListInfo();
        
        $myForm    = new \Admin\Form\Search\FormData($this->getServiceLocator());
        $myForm->setData($this->_params['ssFilter']);

        $items = $this->getTable()->listItem(array('data' => array('user_id' => ""), 'ssFilter' => $this->_params['ssFilter']), array('task' => 'list-new'));
        
        $this->_viewModel['myForm']             = $myForm;
        $this->_viewModel['items']              = $items;

        $this->_viewModel['count']              = $this->getTable()->countItem($this->_params, array('task' => 'list-new'));
        $this->_viewModel['user']               = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_group']         = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-group')), array('task' => 'cache'));
        $this->_viewModel['location_city']      = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache'));
        $this->_viewModel['location_district']  = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 2), array('task' => 'cache'));
        $this->_viewModel['sale_branch']        = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['form_data_result']   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'form-data-result')), array('task' => 'cache'));
        $this->_viewModel['history_status']     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'history-status')), array('task' => 'cache'));
        $this->_viewModel['level']              = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'contact-level')), array('task' => 'cache-alias'));
        $this->_viewModel['type_c3']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'contact-type-c3')), array('task' => 'cache-alias'));
        $this->_viewModel['marketing_channel']  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'marketing-channel')), array('task' => 'cache'));
        $this->_viewModel['caption']            = 'Danh sách data khách hàng';
        return new ViewModel($this->_viewModel);
    }

    public function shareAction() {
        $ssFilter = new Container(__CLASS__);
        $users    = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-sale'));
        $myForm   = new \Admin\Form\FormData\Share($this->getServiceLocator());
        $admin = $this->_userInfo->getUserInfo('id');

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
                foreach ($list_data_id as $item_data) {
                    $items[] = array(
                        'contact_id' => $item_data->contact_id,
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

    public function addAction() {
        $myForm = new \Admin\Form\FormData($this, array('action' => 'add'));
        if($this->getRequest()->isPost()){
            $myForm->setInputFilter(new \Admin\Filter\FormData());
            $myForm->setData($this->_params['data']);
            $sale_branch_id  = $this->_userInfo->getUserInfo('sale_branch_id');
            $controlAction = $this->_params['data']['control-action'];

            if($myForm->isValid()){
                $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('phone' => $this->_params['data']['phone'], 'sale_branch_id' => $sale_branch_id), array('task' => 'by-phone'));
                if (!empty($contact)){
                    // Thêm data trùng
                    $this->_params['data']['contact_coin'] = 1;
                    $this->_params['data']['contact_id']   = $contact['id'];
                    $result = $this->getServiceLocator()->get('Admin\Model\FormDataTable')->saveItem($this->_params, array('task' => 'add-item'));
                    $this->flashMessenger()->addMessage('Thêm mới data thành công');
                }
                else{
                    // tồn tại data trong kho
                    $item_coin_phone = $this->getTable()->getItem(array('phone' => $this->_params['data']['phone'], 'branch_id'=> $sale_branch_id), array('task' => 'by-condition'));
                    if(!empty($item_coin_phone)){
                            $this->_params['data']['contact_coin'] = 1;
                            $result = $this->getServiceLocator()->get('Admin\Model\FormDataTable')->saveItem($this->_params, array('task' => 'add-item'));
                            $this->getServiceLocator()->get('Admin\Model\FormDataTable')->saveItem(array('contact_coin' => 1, 'phone' => $this->_params['data']['phone'], 'branch_id' => $sale_branch_id), array('task' => 'update-contact-coin'));
                            $this->flashMessenger()->addMessage('Thêm mới data thành công');
                    }
                    else{
                        $result = $this->getServiceLocator()->get('Admin\Model\FormDataTable')->saveItem($this->_params, array('task' => 'add-item'));
                        $this->flashMessenger()->addMessage('Thêm mới data thành công');
                    }
                }

                if($controlAction == 'save-new') {
                    $this->goRoute(array('action' => 'add'));
                } else if($controlAction == 'save') {
                    if($result != ''){
                        $this->goRoute(array('action' => 'edit', 'id' => $result));
                    }
                    else{
                        $this->goRoute(array('action' => 'add'));
                    }
                } else {
                    $this->goRoute();
                }
            }
        }

        $this->_viewModel['myForm']	    = $myForm;
        $this->_viewModel['caption']    = 'Thêm mới data Marketing';
        return new ViewModel($this->_viewModel);
    }

    public function editAction() {
        $myForm = $this->getForm();

        $curent_user_id  = $this->_userInfo->getUserInfo('id');
        $sale_branch_id  = $this->_userInfo->getUserInfo('sale_branch_id');
        $phone_code = true;

        $item = array();
        $item_id = $this->params('id');
        if (!empty($item_id)) {
            $this->_params['data']['id'] = $item_id;
            $item = $this->getTable()->getItem($this->_params['data']);
            if (!empty($item)) {
                if($curent_user_id == $item->marketer_id) {
                    $phone_code = false;
                }
                if (!$this->getRequest()->isPost()) {
                    $item->phone = $phone_code ? substr_replace($item->phone, "***", 4, 3): $item->phone;
                    $myForm->setData($item);
                }
            }
        }
        if ($this->getRequest()->isPost()) {
            $myForm->setInputFilter(new \Admin\Filter\FormData(array('id' => $this->_params['data']['id'], 'data' => $this->_params['data'], 'route' => $this->_params['route'])));
            $myForm->setData($this->_params['data']);
            $controlAction = $this->_params['data']['control-action'];

            if ($myForm->isValid()) {
                $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('phone' => $this->_params['data']['phone'], 'sale_branch_id' => $sale_branch_id), array('task' => 'by-phone'));
                if (!empty($contact)){
                    $this->flashMessenger()->addMessage('Data đã tồn tại trong liên hệ - không thể cập nhật lại thông tin');
                    $this->goRoute(array('action' => 'edit', 'id' => $item_id));
                }
                else{
                    // tồn tại data trong kho
                    $item_coin_phone = $this->getTable()->getItem(array('phone' => $this->_params['data']['phone'], 'branch_id'=> $sale_branch_id), array('task' => 'by-condition'));
                    $this->_params['data']['phone'] = $phone_code ? $item->phone : $this->_params['data']['phone'];
                    if(!empty($item_coin_phone) && $item_coin_phone['phone'] != $this->_params['data']['phone']){
                        // data marketer đó đã từng nhập
                        $item_coin_phone_mkt = $this->getTable()->getItem(array('phone' => $this->_params['data']['phone'], 'marketer_id' => $curent_user_id), array('task' => 'by-condition'));
                        // check data này có trùng với data của marketer khác và cùng ngày không
                        $param_date = substr($item['date'], 0, 10);
                        $item_coin_other = $this->getTable()->countItem(array('phone' => $this->_params['data']['phone'], 'marketer_id' => $curent_user_id, 'date' => $param_date), array('task' => 'list-data-coin'));

                        if (!empty($item_coin_phone_mkt)){
                            $this->flashMessenger()->addMessage('Data đã tồn tại trong kho của bạn - không thể cập nhật lại số điện thoại');
                        }
                        else if ($item_coin_other > 0){
                            $this->flashMessenger()->addMessage('Data trùng trong ngày với data của nhân viên khác - không thể cập nhật lại số điện thoại');
                        }
                        else{
                            $this->_params['data']['contact_coin'] = 1;
                            $this->getServiceLocator()->get('Admin\Model\FormDataTable')->saveItem($this->_params, array('task' => 'edit-item'));
                            // Cập nhật lại trạng thái trùng của các data trung.
                            $this->getServiceLocator()->get('Admin\Model\FormDataTable')->saveItem(array('contact_coin' => 1, 'phone' => $this->_params['data']['phone'], 'branch_id' => $sale_branch_id), array('task' => 'update-contact-coin'));
                            $this->flashMessenger()->addMessage('Data đã được cập nhật thành công');
                        }
                    }
                    else{
                        $this->getServiceLocator()->get('Admin\Model\FormDataTable')->saveItem($this->_params, array('task' => 'edit-item'));
                        $this->flashMessenger()->addMessage('Data đã được cập nhật thành công');
                    }

                    if($controlAction == 'save-new') {
                        $this->goRoute(array('action' => 'add'));
                    } else if($controlAction == 'save') {
                        $this->goRoute(array('action' => 'edit', 'id' => $item_id));
                    } else {
                        $this->goRoute();
                    }
                }
            }
        }

        $this->_viewModel['myForm']     = $myForm;
        $this->_viewModel['item']       = $item;
        $this->_viewModel['location_city']      = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache'));
        $this->_viewModel['location_district']  = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 2), array('task' => 'cache'));
        $this->_viewModel['caption']    = 'Sửa data marketing';
        return new ViewModel($this->_viewModel);
    }

    public function historyAddAction() {
        $myForm = new \Admin\Form\Contact\History($this->getServiceLocator());

        if (!empty($this->_params['data']['id'])) {
            $contact            = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $this->_params['data']['id']));
            $formData           = $this->getServiceLocator()->get('Admin\Model\FormDataTable')->getItem(array('contact_id' => $contact['id']), array('task' => 'by-contact'));

            $myForm->setData($contact);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }

        if ($this->getRequest()->isPost()) {
            if ($this->_params['data']['modal'] == 'success') {
                $this->_params['contact'] = $contact;
                $myForm->setInputFilter(new \Admin\Filter\Contact\History($this->_params));
                $myForm->setData($this->_params['data']);

                if ($myForm->isValid()) {
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);

                    $this->_params['item'] = $contact;
                    $this->_params['form_data'] = $formData;
                    $this->_params['settings'] = $this->_settings;

                    // Update lịch sử chăm sóc cho khách hàng
                    $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($this->_params, array('task' => 'add-history'));

                    $this->_params['data']['contact_id'] = $contact['id'];
                    $result = $this->getServiceLocator()->get('Admin\Model\HistoryTable')->saveItem($this->_params, array('task' => 'contact-add-history'));

                    $this->_params['data']['contact_id'] = $contact['id'];
                    $result = $this->getServiceLocator()->get('Admin\Model\HistoryLevelTable')->saveItem($this->_params, array('task' => 'contact-add-level'));

                    // Cập nhập contact
                    $this->_params['item'] = $contact;
                    $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($this->_params, array('task' => 'add-history'));

                    $this->flashMessenger()->addMessage('Thêm lịch sử chăm sóc thành công');
                    echo 'success';
                    return $this->response;
                }
            } else {
                $myForm->setData($this->_params['data']);
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }

        $this->_viewModel['myForm']     = $myForm;
        $this->_viewModel['contact']    = $contact;
        $this->_viewModel['settings']   = $this->_settings;
        $this->_viewModel['userInfo']   = $this->_userInfo->getUserInfo();
        $this->_viewModel['caption']    = 'Thêm lịch sử chăm sóc';

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function managerAction() {
        $myForm = new \Admin\Form\FormData\Manager($this->getServiceLocator());

        if (!empty($this->_params['data']['id'])) {
            $item = $this->getServiceLocator()->get('Admin\Model\FormDataTable')->getItem(array('id' => $this->_params['data']['id']));
            $myForm->setData($item);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }

        if ($this->getRequest()->isPost()) {
            if ($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\FormData\Manager($this->_params));
                $myForm->setData($this->_params['data']);

                if ($myForm->isValid()) {
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);

                    // Cập nhật lại thông tin quản lý khách hàng
                    $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($this->_params, array('task' => 'form_data-update'));

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

        $this->_viewModel['myForm']     = $myForm;
        $this->_viewModel['contact']    = $contact;
        $this->_viewModel['caption']    = 'Xác nhận quản lý khách hàng';

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function viewAction() {
        $item = $this->getTable()->getItem(array('id' => $this->_params['data']['id']));
        
        if(!empty($item)) {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
    
        $this->_viewModel['item']    	            = $item;
        $this->_viewModel['user']                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_group']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-group')), array('task' => 'cache'));
        $this->_viewModel['sale_history_action']    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-action')), array('task' => 'cache'));
        $this->_viewModel['sale_history_result']    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-result')), array('task' => 'cache'));
        $this->_viewModel['source_group']           = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'source-group')), array('task' => 'cache'));
        $this->_viewModel['product_interest']       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-interest')), array('task' => 'cache'));
        $this->_viewModel['type_c3']                = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'contact-type-c3')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['level']                  = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'contact-level')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['sale_contact_type']      = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contact-type')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['location_city']          = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache'));
        $this->_viewModel['location_district']      = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 2), array('task' => 'cache'));
        $this->_viewModel['sex']                    = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sex')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['marketing_channel']     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'marketing-channel')), array('task' => 'cache'));
        $this->_viewModel['caption']                = 'Xem thông tin chi tiết khách hàng';
    
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    public function exportAction() {
        $date               = new \ZendX\Functions\Date();

        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item', 'paginator' => false));

        $user               = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $location_city          = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache'));
        $location_district      = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 2), array('task' => 'cache'));
        $marketing_channel      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'marketing-channel')), array('task' => 'cache'));

        $this->_viewModel['location_district']  = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 2), array('task' => 'cache'));


        //Include PHPExcel
        require_once PATH_VENDOR . '/Excel/PHPExcel.php';

        // Config
        $config = array(
            'sheetData' => 0,
            'headRow' => 1,
            'startRow' => 2,
            'startColumn' => 0,
        );

        // Column
        $arrColumn = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ');

        // Data Export
        $arrData = array(
            array('field' => 'created', 'title' => 'Ngày', 'type' => 'date', 'format' => 'd/m/Y'),
            array('field' => 'name', 'title' => 'Tên'),
            array('field' => 'phone', 'title' => 'Điện thoại', 'type' => 'phone'),
            array('field' => 'address', 'title' => 'Địa chỉ', 'type' => 'address'),
            array('field' => 'city_id', 'title' => 'Tỉnh thành', 'type' => 'data_source', 'data_source' => $location_city, 'data_source_field' => 'name'),
            array('field' => 'district_id', 'title' => 'Quận huyện', 'type' => 'data_source', 'data_source' => $location_district, 'data_source_field' => 'name'),
            array('field' => 'note', 'title' => 'Tên xe - Năm sản xuất', 'type' => 'note'),
            array('field' => 'content', 'title' => 'Ghi chú', 'type' => 'content'),
            array('field' => 'marketer_id', 'title' => 'Nhân viên marketing', 'type' => 'data_source', 'data_source' => $user, 'data_source_field' => 'name'),
            array('field' => 'marketing_channel_id', 'title' => 'Kênh marketing', 'type' => 'data_source', 'data_source' => $marketing_channel, 'data_source_field' => 'name'),
            array('field' => 'sales_id', 'title' => 'Nhân viên sale chăm sóc', 'type' => 'data_source', 'data_source' => $user, 'data_source_field' => 'name'),
        );

        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator($this->_userInfo->getUserInfo('fullname'))
            ->setLastModifiedBy($this->_userInfo->getUserInfo('username'))
            ->setTitle("Export");

        // Dữ liệu tiêu đề cột
        $startColumn = $config['startColumn'];
        foreach ($arrData as $key => $data) {
            $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[$startColumn] . $config['headRow'], $data['title']);
            $objPHPExcel->getActiveSheet()->getStyle($arrColumn[$startColumn] . $config['headRow'])->getFont()->setBold(true);
            $startColumn++;
        }

        // Dữ liệu data
        $startRow = $config['startRow'];
        foreach ($items as $item) {
            $startColumn = $config['startColumn'];
            foreach ($arrData as $key => $data) {
                switch ($data['type']) {
                    case 'date':
                        $formatDate = $data['format'] ? $data['format'] : 'd/m/Y';
                        $value = $date->formatToView($item[$data['field']], $formatDate);
                        break;
                    case 'data_source':
                        $field = $data['data_source_field'] ? $data['data_source_field'] : 'name';
                        $value = $data['data_source'][$item[$data['field']]][$field];
                        break;
                    case 'data_source_list':
                        $field = $data['data_source_field'] ? $data['data_source_field'] : 'name';
                        $list  = $item[$data['field']] ? unserialize($item[$data['field']]) : array();
                        $value = '';
                        if (!empty($list)) {
                            $list = array_keys($list);
                            asort($list);
                            $index = 0;
                            foreach ($list as $key => $val) {
                                $index++;
                                if ($index == 1) {
                                    $value .= $data['data_source'][$val][$field];
                                } else {
                                    $value .= ', ' . $data['data_source'][$val][$field];
                                }
                            }
                        }
                        break;
                    case 'branch':
                        $training_location_id = $item['training_class_id'] ? $training_class[$item['training_class_id']]['training_location_id'] : '';
                        $value  = $training_location[$training_location_id]['document_id'] ? $company_branch[$training_location[$training_location_id]['document_id']]['name'] : '';
                        break;
                    case 'options':
                        $options = $item[$data['field']] ? unserialize($item[$data['field']]) : array();
                        $value   = $options[$data['data_source']][$data['data']];
                        break;
                    case 'options_list':
                        $options = $item[$data['field']] ? unserialize($item[$data['field']]) : array();
                        $value   = implode(',', $options);
                        break;
                    default:
                        $value = $item[$data['field']];
                }

                $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[$startColumn] . $startRow, $value);
                $startColumn++;
            }
            $startRow++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Export.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;

        return $this->response;
    }

    // Nhập data mới
    public function importAction() {
        $date = new \ZendX\Functions\Date();
        $myForm = new \Admin\Form\FormData\Import($this->getServiceLocator());
        $myForm->setInputFilter(new \Admin\Filter\FormData\Import($this->_params));

        $this->_viewModel['caption']    = 'Import data từ file excel';
        $this->_viewModel['myForm']        = $myForm;
        $viewModel = new ViewModel($this->_viewModel);

        $user_mkt = $this->getServiceLocator()->get('Admin\Model\UserTable')->getItem(array('id' => $this->_params['data']['marketer_id']));
        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($this->getRequest()->isPost()) {
                // lấy contact trùng
                $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('phone' => $this->_params['data']['phone']), array('task' => 'by-phone'));
                // tồn tại data trong kho
                $item_coin_phone = $this->getTable()->getItem(array('phone' => $this->_params['data']['phone']), array('task' => 'by-phone'));
                // data marketer đó đã từng nhập
                $item_coin_phone_mkt = $this->getTable()->getItem(array('phone' => $this->_params['data']['phone'], 'marketer_id' => $this->_params['data']['marketer_id']), array('task' => 'by-condition'));
                // check data này có trùng với data của marketer khác và cùng ngày không
                $param_date = $date->formatToData($this->_params['data']['date']);
                $item_coin_other = $this->getTable()->countItem(array('phone' => $this->_params['data']['phone'], 'marketer_id' => $this->_params['data']['marketer_id'], 'date' => $param_date), array('task' => 'list-data-coin'));

                $check_date = $date->check_date_format_to_data($this->_params['data']['date']);
                if($check_date == true){
                    $product_group = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(array('name' => $this->_params['data']['product_group'], 'code' => 'product-group'), array('task' => 'by-custom-name'));
                    if(!empty($product_group)){
                        $this->_params['data']['product_group_id'] = $product_group['id'];
                        if(!empty($contact)){
                            // Thêm data trùng
                            $this->_params['data']['branch_id'] = $user_mkt['sale_branch_id'];
                            $this->_params['data']['group_id'] = $user_mkt['sale_group_id'];
                            $this->_params['data']['contact_coin'] = 1;
                            $this->_params['data']['contact_id']   = $contact['id'];
                            $this->getTable()->saveItem($this->_params, array('task' => 'import-insert'));
                            echo 'Thành công';
                        }
                        else {

                                if ($item_coin_phone) {
                                    $this->_params['data']['branch_id'] = $user_mkt['sale_branch_id'];
                                    $this->_params['data']['group_id'] = $user_mkt['sale_group_id'];
                                    $this->_params['data']['contact_coin'] = 1;
                                    $this->getTable()->saveItem($this->_params, array('task' => 'import-insert'));
                                    $this->getServiceLocator()->get('Admin\Model\FormDataTable')->saveItem(array('contact_coin' => 1, 'phone' => $this->_params['data']['phone']), array('task' => 'update-contact-coin'));
                                    echo 'Thành công';
                                } else {
                                    $this->_params['data']['branch_id'] = $user_mkt['sale_branch_id'];
                                    $this->_params['data']['group_id'] = $user_mkt['sale_group_id'];
                                    $this->getTable()->saveItem($this->_params, array('task' => 'import-insert'));
                                    echo 'Thành công';
                                }
                            }
                        }
                    else{
                        echo 'Không có Nhóm SP quan tâm';
                    }
                }
                else{
                    echo "Không đúng định dạng ngày tháng";
                }
                return $this->response;
            }
        } else {
            if ($this->getRequest()->isPost()) {
                $myForm->setData($this->_params['data']);
                if ($myForm->isValid()) {
                    if (!empty($this->_params['data']['file_import']['tmp_name'])) {
                        $upload         = new \ZendX\File\Upload();
                        $file_import    = $upload->uploadFile('file_import', PATH_FILES . '/import/', array());
                    }
                    $viewModel->setVariable('file_import', $file_import);
                    $viewModel->setVariable('import', true);

                    require_once PATH_VENDOR . '/Excel/PHPExcel/IOFactory.php';
                    $objPHPExcel = \PHPExcel_IOFactory::load(PATH_FILES . '/import/' . $file_import);

                    $sheetData = $objPHPExcel->getActiveSheet(1)->toArray(null, true, true, true);
                    $viewModel->setVariable('sheetData', $sheetData);
                }
            }
        }

        return $viewModel;
    }

    public function editResultAction() {
        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($this->getRequest()->isPost()) {
                $this->getTable()->saveItem($this->_params, array('task' => 'edit-result'));
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }

        return $this->response;
    }

    public function deleteAction() {
        if($this->getRequest()->isPost()) {
            if(!empty($this->_params['data']['cid'])) {
                $data_update = [];

                // Cập nhật lại số điện thoại về trong ngày trong báo cáo mkt theo giờ
                foreach ($this->_params['data']['cid'] as $key => $value){
                    $item = $this->getTable()->getItem(array('id' => $value, null));
                    if(empty($item['sales_id'])){
                        $item_data['data']['marketer_id'] = $item['marketer_id'];
                        $item_data['data']['product_group_id'] = $item['product_group_id'];
                        $item_data['data']['date'] = $item['date'];

                        $data_update[$key]['marketer_id'] = $item['marketer_id'];
                        $data_update[$key]['product_group_id'] = $item['product_group_id'];
                        $data_update[$key]['date'] =  substr($item['date'], 0, 10);
                    }
                    $this->getServiceLocator()->get('Admin\Model\MarketingReportTable')->saveItem($item_data, array('task' => 'update-number-phone-2')); # ok
                }

                // Xóa data đã chọn
                $cdata = $this->getTable()->deleteItem($this->_params, array('task' => 'delete-item'));

                // Khi xóa data cần cập nhật lại chi phí mkt cho các data khác về cùng ngày
                foreach ($data_update as $key => $value){
                    $report_item = $this->getServiceLocator()->get('Admin\Model\MarketingReportTable')->getItem(array('date' => $value['date'], 'marketer_id' => $value['marketer_id'], 'product_group_id' => $value['product_group_id'], 'type' => 'mkt_report_day_hour'), array('task' => "marketer-date")); # oki
                    if(!empty($report_item)){
                        $params = !empty($report_item['params']) ? unserialize($report_item['params']) : array();
                        $total_cp   = str_replace(',', '', $params['total_cp']);

                        if(!empty($total_cp)){
                            $count_data = $this->getServiceLocator()->get('Admin\Model\FormDataTable')->countItem(array('marketer_id' => $value['marketer_id'] ,'product_group_id' => $value['product_group_id'] ,'date' => $value['date'],), array('task' => 'by-condition'));
                            $cost_ads = (int)($total_cp / $count_data);

                            $params_update = array(
                                'cost_ads'      => $cost_ads,
                                'marketer_id'   => $value['marketer_id'],
                                'product_group_id'    => $value['product_group_id'],
                                'date'          => $value['date'],
                            );
                            $this->getServiceLocator()->get('Admin\Model\FormDataTable')->saveItem($params_update, array('task' => 'update-cost-ads'));
                        }
                    }
                }

                $message = 'Xóa '. $cdata .' phần tử thành công';
                $this->flashMessenger()->addMessage($message);
            }
        }
    
        $this->goRoute(array('action' => 'index'));
    }

    public function updatePhoneAction() {
        $select = array(
            'query' => "SELECT SUBSTRING(date, 1, 10) AS date , marketer_id, product_group_id, COUNT(id) AS total_sdt FROM ".TABLE_FORM_DATA." WHERE date <= '2024-03-14 23:59:59' GROUP BY SUBSTRING(date, 1, 10),marketer_id,product_group_id order by date desc;"
        );
        $items = $this->getTable()->listItem($select, array('task' => 'query'));
        foreach($items as $key => $value){
            $report_item = $this->getServiceLocator()->get('Admin\Model\MarketingReportTable')->getItem(array('date' => $value['date'], 'marketer_id' => $value['marketer_id'], 'product_group_id' => $value['product_group_id'], 'type' => 'mkt_report_day_hour'), array('task' => "marketer-date"));
            if(!empty($report_item)){
                $params = unserialize($report_item['params']);
                $params['total_sdt'] = $value['total_sdt'];

                $data_update['data'] = array(
                    'id' => $report_item['id'],
                    'params' => $params
                );
                $this->getServiceLocator()->get('Admin\Model\MarketingReportTable')->saveItem($data_update, array('task' => 'update-item'));
            }
        }
        echo "<pre>";
        print_r('thành công');
        echo "</pre>";
        exit;
    }
}
