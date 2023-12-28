<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class ContractOwedController extends ActionController {
    
    public function init() {
        
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\ContractOwedTable';
        $this->_options['formName'] = 'formAdminContract';

        // Thiết lập session filter
        $action = str_replace('-', '_', $this->_params['action']);
        $ssFilter = new Container(__CLASS__. $action);

        $this->_params['ssFilter']['order_by']              = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'date';
        $this->_params['ssFilter']['order']                 = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';
        $this->_params['ssFilter']['filter_keyword']        = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['filter_date_begin']     = $ssFilter->filter_date_begin;
        $this->_params['ssFilter']['filter_date_end']       = $ssFilter->filter_date_end;
        $this->_params['ssFilter']['filter_date_type']      = $ssFilter->filter_date_type;
        $this->_params['ssFilter']['filter_sale_branch']    = $ssFilter->filter_sale_branch;
        $this->_params['ssFilter']['filter_sale_group']     = $ssFilter->filter_sale_group;
        $this->_params['ssFilter']['filter_user']           = $ssFilter->filter_user;
        $this->_params['ssFilter']['filter_status_type']    = $ssFilter->filter_status_type;
        $this->_params['ssFilter']['filter_status']         = $ssFilter->filter_status;
        $this->_params['ssFilter']['filter_shipper_id']     = $ssFilter->filter_shipper_id;
        $this->_params['ssFilter']['filter_production_type_id']= $ssFilter->filter_production_type_id;
        $this->_params['ssFilter']['filter_owed']           = $ssFilter->filter_owed;

        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage'] = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : $this->_paginator['itemCountPerPage'];
        $this->_paginator['currentPageNumber'] = $this->params()->fromRoute('page', 1);
        $this->_params['paginator'] = $this->_paginator;
        
        // Lấy dữ liệu post của form
        $this->_params['data'] = array_merge($this->getRequest()->getPost()->toArray(), $this->getRequest()->getFiles()->toArray());
        
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
//        $this->_viewModel['encode_phone'] = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'encode-phone')), array('task' => 'list-all')), array('key' => 'content', 'value' => 'status'));

    }
    
    // Tìm kiếm
    public function filterAction() {
    
        if($this->getRequest()->isPost()) {
            $action = !empty($this->_params['data']['filter_action']) ? $this->_params['data']['filter_action'] : 'index';
            $ssFilter	= new Container(__CLASS__ . $action);
            $data = $this->_params['data'];
    
            $ssFilter->pagination_option        = intval($data['pagination_option']);
            $ssFilter->order_by                 = $data['order_by'];
            $ssFilter->order                    = $data['order'];
            $ssFilter->filter_keyword           = $data['filter_keyword'];
            $ssFilter->filter_date_begin        = $data['filter_date_begin'];
            $ssFilter->filter_date_end          = $data['filter_date_end'];
            $ssFilter->filter_date_type         = $data['filter_date_type'];
            $ssFilter->filter_status_type       = $data['filter_status_type'];
            $ssFilter->filter_status            = $data['filter_status'];
            $ssFilter->filter_shipper_id        = $data['filter_shipper_id'];
            $ssFilter->filter_production_type_id= $data['filter_production_type_id'];
            $ssFilter->filter_owed              = $data['filter_owed'];

            $ssFilter->filter_user              = $data['filter_user'];
            
            if(!empty($data['filter_sale_group'])) {
                if($ssFilter->filter_sale_group != $data['filter_sale_group']) {
                    $ssFilter->filter_sale_group = $data['filter_sale_group'];
                }
            } else {
                $ssFilter->filter_sale_group = $data['filter_sale_group'];
            }
            
            if(!empty($data['filter_sale_branch'])) {
                if($ssFilter->filter_sale_branch != $data['filter_sale_branch']) {
                    $ssFilter->filter_sale_group = null;
                    $ssFilter->filter_sale_branch = $data['filter_sale_branch'];
                }
            } else {
                $ssFilter->filter_sale_group = null;
                $ssFilter->filter_sale_branch = $data['filter_sale_branch'];
            }

            if(empty($data['filter_status_type'])){
                $ssFilter->filter_status = null;
            }
            
        }

        $this->goRoute(['action' => $action]);
    }
    
    // Danh sách
    public function indexAction() {
        $ssFilter       = new Container(__CLASS__.'index');
        $curent_user = $this->_userInfo->getUserInfo();
        $permission_ids = explode(',', $curent_user['permission_ids']);
        if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids) && !in_array(MANAGER, $permission_ids)){
            if(in_array(GDCN, $permission_ids)){
                $this->_params['ssFilter']['filter_sale_branch'] = $curent_user['sale_branch_id'];
                $ssFilter->filter_sale_branch = $curent_user['sale_branch_id'];
            }
        }

        $myForm	= new \Admin\Form\Search\ContractOwed($this->getServiceLocator(), $this->_params['ssFilter']);
        $myForm->setData($this->_params['ssFilter']);

        $this->_params['ssFilter']['filter_owed'] = !empty($this->_params['ssFilter']['filter_owed']) ? $this->_params['ssFilter']['filter_owed'] : 'yes';

        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));

        $count = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));;
        
        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $items;
        $this->_viewModel['count']                  = $count;
        $this->_viewModel['user']                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['bill']                   = $this->getServiceLocator()->get('Admin\Model\BillTable')->listItem(null, array('task' => 'by-contract'));
        $this->_viewModel['bill_type']              = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'bill-type')), array('task' => 'cache'));
        $this->_viewModel['sale_group']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['transport']              = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'transport')), array('task' => 'cache'));
        $this->_viewModel['type_of_carpet']         = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'type-of-carpet')), array('task' => 'cache'));
        $this->_viewModel['carpet_color']           = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'carpet-color')), array('task' => 'cache'));
        $this->_viewModel['tangled_color']          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'tangled-color')), array('task' => 'cache'));
        $this->_viewModel['row_seats']              = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'row-seats')), array('task' => 'cache'));
        $this->_viewModel['carpet_color']           = $this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['tangled_color']          = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['flooring']               = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'flooring')), array('task' => 'cache'));
        $this->_viewModel['production_department']  = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-department')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['product']                = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['kovProduct']             = $this->getServiceLocator()->get('Admin\Model\KovProductsTable')->listItem(null, array('task' => 'cache'));

        $this->_viewModel['status_product']         = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-department')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['status_check']           = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-check')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['status_accounting']      = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-acounting')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['shipper']                = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-positons-care')), array('key' => 'id', 'value' => 'name'));

        return new ViewModel($this->_viewModel);
    }

    // Danh sách đơn hàng hủy không lưu kho
    public function warehouseCancelAction() {
        $ssFilter       = new Container(__CLASS__.'warehouseCancel');
        $curent_user = $this->_userInfo->getUserInfo();
        $permission_ids = explode(',', $curent_user['permission_ids']);
        if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids) && !in_array(MANAGER, $permission_ids)){
            if(in_array(GDCN, $permission_ids)){
                $this->_params['ssFilter']['filter_sale_branch'] = $curent_user['sale_branch_id'];
                $ssFilter->filter_sale_branch = $curent_user['sale_branch_id'];
            }
        }

        $myForm	= new \Admin\Form\Search\Contract($this->getServiceLocator(), $this->_params);
        $myForm->setData($this->_params['ssFilter']);

        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item-warehouse-cancel'));
        $count = $this->getTable()->countItem($this->_params, array('task' => 'list-item-warehouse-cancel'));

        $userInfo = new \ZendX\System\UserInfo();
        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $items;
        $this->_viewModel['count']                  = $count;
        $this->_viewModel['user']                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['bill']                   = $this->getServiceLocator()->get('Admin\Model\BillTable')->listItem(null, array('task' => 'by-contract'));
        $this->_viewModel['bill_type']              = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'bill-type')), array('task' => 'cache'));
        $this->_viewModel['sale_group']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['transport']              = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'transport')), array('task' => 'cache'));
        $this->_viewModel['carpet_color']           = $this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['tangled_color']          = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['flooring']               = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'flooring')), array('task' => 'cache'));
        $this->_viewModel['production_department']  = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-department')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['product']                = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['shipper']                = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-positons-care')), array('key' => 'id', 'value' => 'name'));

        $this->_viewModel['caption']                = 'Danh sách hàng có sẵn';

        return new ViewModel($this->_viewModel);
    }

    // Danh sách đơn hàng có sãn đã bán hết
    public function warehouseSoldAction() {
        $ssFilter       = new Container(__CLASS__.'warehouseSold');
        $curent_user = $this->_userInfo->getUserInfo();
        $permission_ids = explode(',', $curent_user['permission_ids']);
        if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids) && !in_array(MANAGER, $permission_ids)){
            if(in_array(GDCN, $permission_ids)){
                $this->_params['ssFilter']['filter_sale_branch'] = $curent_user['sale_branch_id'];
                $ssFilter->filter_sale_branch = $curent_user['sale_branch_id'];
            }
        }

        $myForm	= new \Admin\Form\Search\Contract($this->getServiceLocator(), $this->_params);
        $myForm->setData($this->_params['ssFilter']);

        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item-warehouse-sold'));
        $count = $this->getTable()->countItem($this->_params, array('task' => 'list-item-warehouse-sold'));
//        $item2 = $this->getTable()->listItem($this->_params, array('task' => 'list-item-warehouse-sold', 'paginator' => false));
//        $count = 0;
//        foreach ($item2 AS $key => $value) {
//            $options = unserialize($value['options']);
//            if(count($options['product']) > 0){
//                $number_product = 0;
//                foreach ($options['product'] as $keyp => $valuep){
//                    $number_product += $valuep['numbers'];
//                }
//                if($number_product == 0){
//                    $count++;
//                }
//            }
//            else{
//                $count++;
//            }
//        }

        $userInfo = new \ZendX\System\UserInfo();
        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $items;
        $this->_viewModel['count']                  = $count;
        $this->_viewModel['user']                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['bill']                   = $this->getServiceLocator()->get('Admin\Model\BillTable')->listItem(null, array('task' => 'by-contract'));
        $this->_viewModel['bill_type']              = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'bill-type')), array('task' => 'cache'));
        $this->_viewModel['sale_group']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['transport']              = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'transport')), array('task' => 'cache'));
        $this->_viewModel['carpet_color']           = $this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['tangled_color']          = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['flooring']               = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'flooring')), array('task' => 'cache'));
        $this->_viewModel['production_department']  = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-department')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['product']                = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['shipper']                = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-positons-care')), array('key' => 'id', 'value' => 'name'));

        $this->_viewModel['caption']                = 'Danh sách hàng có sẵn';

        return new ViewModel($this->_viewModel);
    }
    
    // Thêm giảm trừ doanh thu
    public function editReduceAction() {
        $myForm = new \Admin\Form\ContractOwed\EditReduce($this->getServiceLocator(), $this->_params);
    
        if(!empty($this->_params['data']['id'])) {
            $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->_params['data']['id']));
            $contract_options = !empty($contract['options']) ? unserialize($contract['options']) : array();
            $contract = array_merge($contract, $contract_options);
            $myForm->setData($contract);

            if($contract['lock']){
                return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'lock', 'type' => 'modal'));
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }
    
        if($this->getRequest()->isPost()){
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\ContractOwed\EditReduce($this->_params));
                $myForm->setData($this->_params['data']);
    
                if($myForm->isValid()){
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['item'] = $contract;
    
                    $result = $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params, array('task' => 'update-reduce'));
    
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
        $this->_viewModel['contract']   = $contract;
        $this->_viewModel['caption']    = 'Thêm giảm từ doanh thu';
    
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }

    // Thêm hóa đơn
    public function billAddAction() {
        $dateFormat = new \ZendX\Functions\Date();
        
        if(!empty($this->_params['data']['id'])) {
            $contract   = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->_params['data']['id']));
            $contact    = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']));
            $contact['birthday_year'] = !empty($contact['birthday_year']) ? $contact['birthday_year'] : null;

            if($contract['lock']){
                return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'lock', 'type' => 'modal'));
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }
        
        if($this->getRequest()->isPost()){
            $myForm = new \Admin\Form\Contract\Bill($this->getServiceLocator());
            $myForm->setData($this->_params['data']);
            
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\Contract\Bill(array('data' => $this->_params['data'], 'contract' => $contract, 'contact' => $contact)));
                if($myForm->isValid()){
                    $this->_params['data']      = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['contract']  = $contract;
                    $this->_params['contact']   = $contact;
                    
                    if(!empty($this->_params['data']['paid_price']) || !empty($this->_params['data']['accrued_price'])) {
                        // Thêm hóa đơn
                        $this->_params['data']['paid_type_id'] = ['data']['type'];
                        $bill = $this->getServiceLocator()->get('Admin\Model\BillTable')->saveItem($this->_params, array('task' => 'add-item'));
                        
                        // Cập nhật lại thông tin thanh toán Đơn hàng
                        $number = new \ZendX\Functions\Number();
                        
                        $price_paid     = $contract['price_paid'] + $number->formatToNumber($this->_params['data']['paid_price']);
                        $price_accrued  = $contract['price_accrued'] + $number->formatToNumber($this->_params['data']['accrued_price']);
                        $price_owed     = $contract['price_total'] - $price_paid + $price_accrued;
                        
                        $arrContract = array();
                        $arrContract['id'] = $contract['id'];
                        $arrContract['price_paid'] = $price_paid;
                        $arrContract['price_accrued'] = $price_accrued;
                        $arrContract['price_owed'] = $price_owed;
                        $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem(array('data' => $arrContract), array('task' => 'update-bill-add'));
                    }
            
                    $this->flashMessenger()->addMessage('Thêm hóa đơn thành công');
                    echo 'success';
                    return $this->response;
                }
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
        
        $this->_viewModel['myForm']     = $myForm;
        $this->_viewModel['caption']    = 'Thêm hóa đơn';
        $this->_viewModel['contract']   = $contract;
        $this->_viewModel['contact']    = $contact;
        
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
        
        return $viewModel;
    }

    // Cập nhật thanh toán từ giao vận
    public function importFromShippingAction()
    {
        $myForm = new \Admin\Form\ContractOwed\Import($this->getServiceLocator(), $this->_params);
        $myForm->setInputFilter(new \Admin\Filter\ContractOwed\Import($this->_params));
        $this->_viewModel['caption'] = 'Nhập thanh toán từ giao vận';
        $this->_viewModel['myForm']  = $myForm;
        $viewModel                   = new ViewModel($this->_viewModel);

        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($this->getRequest()->isPost()) {
                if(empty($this->_params['data']['bill_code']) && empty($this->_params['data']['code'])) {
                    echo 'Trống mã vận đơn và đơn hàng';
                }else {
                    $itemByBillCode = $this->getTable()->getItem(array('bill_code' => $this->_params['data']['bill_code']), array('task' => 'by-bill-code'));
                    if (empty($itemByBillCode)) {
                        $itemByBillCode = $this->getTable()->getItem(array('code' => $this->_params['data']['code']), array('task' => 'by-code'));
                    }
                    $billCode = $this->getServiceLocator()->get('Admin\Model\BillTable')->getItem(array('bill_code' => $this->_params['data']['bill_code']), array('task' => 'by-bill-code'));
                    if (empty($billCode)) {
                        $billCode = $this->getServiceLocator()->get('Admin\Model\BillTable')->getItem(array('contract_code' => $this->_params['data']['code']), array('task' => 'by-contract-code'));
                    }

                    if (empty($itemByBillCode)) {
                        echo 'Sai mã vận đơn và mã đơn hàng';
                    } else {
                        if($itemByBillCode['lock']){
                            echo "Đơn hàng đã khóa";
                        }
                        else {
                            $this->_params['data']['id'] = $itemByBillCode['id'];
                            $this->_params['data']['contact_id'] = $itemByBillCode['contact_id'];
                            $this->_params['data']['user_id'] = $itemByBillCode['user_id'];
                            $this->_params['data']['sale_branch_id'] = $itemByBillCode['sale_branch_id'];
                            $this->_params['data']['sale_group_id'] = $itemByBillCode['sale_group_id'];

                            if (empty($billCode)) {
                                $this->getServiceLocator()->get('Admin\Model\BillTable')->saveItem($this->_params,
                                    array('task' => 'import-insert'));
                                // Cập nhật lại thông tin thanh toán Đơn hàng
                                $number = new \ZendX\Functions\Number();

                                $price_paid = $itemByBillCode['price_paid'] + $number->formatToNumber($this->_params['data']['paid_price']);
                                $price_owed = $itemByBillCode['price_total'] - $price_paid;

                                $arrContract = array();
                                $arrContract['id'] = $itemByBillCode['id'];
                                $arrContract['price_paid'] = $price_paid;
                                $arrContract['price_owed'] = $price_owed;
                                $arrContract['price_reduce_sale'] = $this->_params['data']['price_reduce_sale'];
                                $this->getTable()->saveItem(array('data' => $arrContract, 'item' => $itemByBillCode),
                                    array('task' => 'update-price'));
                                echo 'Hoàn thành';
                            } else {
                                echo 'Đã nhập thanh toán trước đó';
                            }
                        }
                    }
                }
                return $this->response;
            }
        }
        else {
            if ($this->getRequest()->isPost()) {
                $myForm->setData($this->_params['data']);
                if ($myForm->isValid()) {
                    if (!empty($this->_params['data']['file_import']['tmp_name'])) {
                        $upload      = new \ZendX\File\Upload();
                        $file_import = $upload->uploadFile('file_import', PATH_FILES . '/import/', array());
                    }
                    $viewModel->setVariable('file_import', $file_import);
                    $viewModel->setVariable('import', true);

                    require_once PATH_VENDOR . '/Excel/PHPExcel/IOFactory.php';
                    $objPHPExcel = \PHPExcel_IOFactory::load(PATH_FILES . '/import/' . $file_import);

                    $sheetData = $objPHPExcel->getActiveSheet(1)->toArray(null, true, true, true);
                    $viewModel->setVariable('sheetData', $sheetData);
                    $historyId = $this->getServiceLocator()->get('Admin\Model\HistoryImportTable')->saveItem($this->_params, array('task' => 'add-item'));
                    $viewModel->setVariable('historyId', $historyId);
                }
            }
        }

        return $viewModel;
    }

    // Xác nhận - Hủy không gửi
    public function cancelAction() {
        if($this->getRequest()->isPost()) {
            if(!empty($this->_params['data']['cid'])) {
                $params['data']['cid']                = $this->_params['data']['cid'];
                $params['data']['field_status_name']  = 'production_department_type';
                $params['data']['field_status_value'] = STATUS_CONTRACT_PRODUCT_NOT_SEND;

                // Cập nhật trạng thái kế toán cho đơn hàng : Hủy không gửi.
                $result = $this->getTable()->updateItem($params, array('task' => 'update-item-status'));

                $message = 'Đã cập nhật '. $result .' đơn hàng về trạng thái - Hủy không gửi';
                $this->flashMessenger()->addMessage($message);
            }
        }

        $this->goRoute(array('action' => 'index'));
    }

    // Sửa trạng thái thủ công - chỉ dành cho admin
    public function editStatusAction() {
        $myForm = new \Admin\Form\ContractOwed\EditStatus($this->getServiceLocator(), $this->_params);

        if(!empty($this->_params['data']['id'])) {
            $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->_params['data']['id']));
            $myForm->setData($contract);

            if($contract['lock']){
                return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'lock', 'type' => 'modal'));
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }

        if($this->getRequest()->isPost()){
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setData($this->_params['data']);

                if($myForm->isValid()){
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $result = $this->getServiceLocator()->get('Admin\Model\ContractTable')->updateItem($this->_params, array('task' => 'update-status'));

                    $this->flashMessenger()->addMessage('Cập nhật dữ liệu thành công');
                    echo 'success';
                    return $this->response;
                }
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }

        $this->_viewModel['myForm']     = $myForm;
        $this->_viewModel['contract']   = $contract;
        $this->_viewModel['caption']    = 'Sửa trạng thái';

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Xem chi tiết Đơn hàng
    public function viewAction() {
        if(!empty($this->_params['data']['id'])) {
            $item = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->_params['data']['id']));
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }

        $this->_viewModel['item']                       = $item;
        $this->_viewModel['contact']                    = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $item['contact_id']));
        $this->_viewModel['user']                       = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_group']                 = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']                = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['sale_history_action']        = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-action')), array('task' => 'cache'));
        $this->_viewModel['sale_history_result']        = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-result')), array('task' => 'cache'));
        $this->_viewModel['sale_source_group']          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-source-group')), array('task' => 'cache'));
        $this->_viewModel['sale_source_known']          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-source-known')), array('task' => 'cache'));
        $this->_viewModel['sale_contact_type']          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contact-type')), array('task' => 'cache-alias'));
        $this->_viewModel['sale_contact_subject']       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contact-subject')), array('task' => 'cache-alias'));
        $this->_viewModel['sale_lost']                  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-lost')), array('task' => 'cache'));
        $this->_viewModel['location_city']              = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache'));
        $this->_viewModel['location_district']          = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 2), array('task' => 'cache'));
        $this->_viewModel['sex']                        = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sex')), array('task' => 'cache-alias'));
        $this->_viewModel['bill']                       = $this->getServiceLocator()->get('Admin\Model\BillTable')->listItem(null, array('task' => 'by-contract'));
        $this->_viewModel['bill_type']                  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'bill-type')), array('task' => 'cache'));
        $this->_viewModel['transport']                  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'transport')), array('task' => 'cache'));
        $this->_viewModel['type_of_carpet']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'type-of-carpet')), array('task' => 'cache'));
        $this->_viewModel['carpet_color']               = $this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['tangled_color']              = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['row_seats']                  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'row-seats')), array('task' => 'cache'));
        $this->_viewModel['flooring']                   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'flooring')), array('task' => 'cache'));
        $this->_viewModel['production_department']      = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-department')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['status']                     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status')), array('task' => 'cache'));
        $this->_viewModel['production_type']            = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-type')), array('task' => 'cache')), array('key' => 'id', 'value' => 'object'));
        $this->_viewModel['caption']                    = 'Xem chi tiết đơn hàng';
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Import trạng thái kế toán
    public function importStatusAction()
    {
        $myForm = new \Admin\Form\Check\Import($this->getServiceLocator(), $this->_params);
        $myForm->setInputFilter(new \Admin\Filter\Check\Import($this->_params));
        $this->_viewModel['caption'] = 'Nhập trạng thái từ kế toán';
        $this->_viewModel['myForm']  = $myForm;
        $viewModel                   = new ViewModel($this->_viewModel);

        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($this->getRequest()->isPost()) {
                if($this->_params['data']['status_acounting_id'] != STATUS_CONTRACT_ACOUNTING_RETURN) {
                    $itemByBillCode = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('bill_code' => $this->_params['data']['bill_code']), array('task' => 'by-bill-code'));
                    if (empty($itemByBillCode)) {
                        $itemByBillCode = $this->getTable()->getItem(array('code' => $this->_params['data']['code']), array('task' => 'by-code'));
                    }

                    $statusName = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(array('alias' => $this->_params['data']['status_acounting_id'], 'code' => 'status-acounting'), array('task' => 'by-custom-alias'));

                    if (empty($itemByBillCode)) {
                        echo 'Sai mã vận đơn và mã đơn hàng';
                    } else {
                        if ($itemByBillCode['lock']) {
                            echo 'Đơn hàng đã khóa';
                        } else {
                            if (empty($statusName)) {
                                echo 'Trạng thái không tồn tại';
                            } else {
                                $this->_params['data']['id'] = $itemByBillCode['id'];
                                $this->_params['data']['status_acounting_id'] = $statusName['alias'];
                                $check = $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params, array('task' => 'update-status'));
                                echo 'Hoàn thành';
                            }
                        }
                    }
                }
                else{
                    echo 'Trạng thái "Đã nhận hoàn" cần xác nhận';
                }

                return $this->response;
            }
        }
        else {
            if ($this->getRequest()->isPost()) {
                $myForm->setData($this->_params['data']);
                if ($myForm->isValid()) {
                    if (!empty($this->_params['data']['file_import']['tmp_name'])) {
                        $upload      = new \ZendX\File\Upload();
                        $file_import = $upload->uploadFile('file_import', PATH_FILES . '/import/', array());
                    }
                    $viewModel->setVariable('file_import', $file_import);
                    $viewModel->setVariable('import', true);

                    require_once PATH_VENDOR . '/Excel/PHPExcel/IOFactory.php';
                    $objPHPExcel = \PHPExcel_IOFactory::load(PATH_FILES . '/import/' . $file_import);

                    $sheetData = $objPHPExcel->getActiveSheet(1)->toArray(null, true, true, true);
                    $viewModel->setVariable('sheetData', $sheetData);
                    $historyId = $this->getServiceLocator()->get('Admin\Model\HistoryImportTable')->saveItem($this->_params, array('task' => 'add-item'));
                    $viewModel->setVariable('historyId', $historyId);
                }
            }
        }

        return $viewModel;
    }

    public function printMultiAction() {
        $items      = $this->getServiceLocator()->get('Admin\Model\ContractTable')->listItem(array('ids' => $this->_params['data']['cid']), array('task' => 'list-print-multi'));
        $items      = $items->toArray();

        $contact      = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $items['contact_id']), null);

        // Tính % khuyến mãi của từng sản phẩm
        foreach($items as $key => $value){
            $options  = unserialize($value['options']);
            $products = $options['product'];
            foreach($products as $k => $product ){
                $dataParams = [];
                $dataParams['product'] = $product['product_id'];
                $dataParams['carpet_color'] = $product['carpet_color_id'];
                $dataParams['tangled_color'] = $product['tangled_color_id'];
                $dataParams['flooring'] = $product['flooring_id'];
                $dataParams['type'] = 'price';
                $listed_percenter = $this->getServiceLocator()->get('Admin\Model\ProductListedTable')->getListedPercenter($dataParams);
                $products[$k]['percenter'] = $listed_percenter;
            }
            $options['product'] = $products;
            $items[$key]['options'] = serialize($options);
        }

        if(empty($items)) {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }

        $this->_viewModel['items']                      = $items;
        $this->_viewModel['contact']                    = $contact;
//        $this->_viewModel['arr_listed']                 = $arr_listed;
        $this->_viewModel['user']                       = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['bill']                       = $this->getServiceLocator()->get('Admin\Model\BillTable')->listItem(null, array('task' => 'by-contract'));
        $this->_viewModel['bill_type']                  = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-bill-type')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['transport']                  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'transport')), array('task' => 'cache'));

        $this->_viewModel['type_of_carpet']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'type-of-carpet')), array('task' => 'cache'));

        $this->_viewModel['carpet_color']               = $this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['tangled_color']              = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem(null, array('task' => 'cache'));

        $this->_viewModel['row_seats']                  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'row-seats')), array('task' => 'cache'));
        $this->_viewModel['flooring']                   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'flooring')), array('task' => 'cache'));
        $this->_viewModel['production_department']      = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-department')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['status']                     = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));

        $this->_viewModel['production_department']  = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-department')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['product']                = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));

        $this->_viewModel['status_product']         = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-department')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['status_check']           = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-check')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['status_accounting']      = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-acounting')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));




        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Sửa giá vốn
    public function editCostPriceAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $numberFormat = new \ZendX\Functions\Number();
        $myForm = new \Admin\Form\ContractOwed\EditCodePrice($this->getServiceLocator(), $this->_params);

        if(!empty($this->_params['data']['id'])) {
            $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->_params['data']['id']));
            $contract_options = !empty($contract['options']) ? unserialize($contract['options']) : array();
            $contract = array_merge($contract, $contract_options);
            $contract['date'] = $dateFormat->formatToView($contract['date']);

            $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']));

            $myForm->setData($contract);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }

        if($this->getRequest()->isPost()){
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\ContractOwed\EditCodePrice($this->_params));
                $myForm->setData($this->_params['data']);

                if($myForm->isValid()){
                    $this->_params['item'] = $contract;
                    $this->_params['contact'] = $contact;
                    if($contract['status_check_id'] == 'thanh-cong'){
                        $this->getTable()->saveItem($this->_params, array('task' => 'edit-code-price'));
                        $this->flashMessenger()->addMessage('Cập nhật dữ liệu thành công');
                    }
                    else{
                        $this->flashMessenger()->addMessage('Cập nhật thất bại! (Đơn hàng chưa ở trạng thái thành công)');
                    }
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
        $this->_viewModel['contract']       = $contract;
        $this->_viewModel['contact']        = $contact;
        $this->_viewModel['product_type']   = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache-active'));

        $this->_viewModel['carpet_color']   = $this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['tangled_color']  = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['row_seats']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'row-seats')), array('task' => 'cache'));
        $this->_viewModel['flooring']       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'flooring')), array('task' => 'cache'));
        $this->_viewModel['caption']        = 'Sửa Đơn giá vốn';

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Xác nhận - Đã nhận hoàn - version1 xác thực nhiều đơn hàng cùng lúc
    public function return_v1Action() { // returnAction
        if($this->getRequest()->isPost()) {
            if(!empty($this->_params['data']['cid'])) {
                $params['data']['cid']                = $this->_params['data']['cid'];
                $params['data']['field_status_name']  = 'status_acounting_id';
                $params['data']['field_status_value'] = STATUS_CONTRACT_ACOUNTING_RETURN;

                // Cập nhật trạng thái kế toán cho đơn hàng : Đã nhận hoàn.
                $result = $this->getServiceLocator()->get('Admin\Model\ContractTable')->updateItem($params, array('task' => 'update-item-status'));
                // Lưu các sản phẩm vào kho hàng hoàn
                foreach($params['data']['cid']   as $cid){
                    $contract_item = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $cid));
                    $pro_item = unserialize($contract_item['options'])['product'];
                    foreach($pro_item as $pitem){
                        $check_type = $this->getServiceLocator()->get('Admin\Model\KovProductsTable')->getItem(array('id' => $pitem['product_id']));
                        if($check_type['product_type'] == 2){ // nếu sản phẩm thuộc sản phẩm sản xuất
                            // kiểm tra kho hàng hoàn đã tồn tại chưa nếu chưa thì tạo mới nếu có rồi thì update lại số lượng
                            $check_pro = $this->getServiceLocator()->get('Admin\Model\ProductReturnTable')->getItem(array('productId' => $pitem['product_id'], 'branchId' => $pitem['branch_id'], 'name_year' =>$pitem['product_name']));
                            if(empty($check_pro)){
                                $result = $this->getServiceLocator()->get('Admin\Model\ProductReturnTable')->saveItem(array('data' => $pitem), array('task' => 'add-item'));
                            }
                            else{
                                $result = $this->getServiceLocator()->get('Admin\Model\ProductReturnTable')->saveItem(array('data' => ['quantity' => $check_pro['quantity'] + $pitem['numbers']], 'item' => $check_pro), array('task' => 'edit-item'));
                            }
                        }
                    }
                }

                $message = 'Đã cập nhật '. $result .' đơn hàng về trạng thái - Đã nhận hoàn';
                $this->flashMessenger()->addMessage($message);
            }
        }

        $this->goRoute(array('action' => 'index'));
    }

    // Xác nhận đã nhận hoàn - có xác thực hàng nhận
    public function returnAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $numberFormat = new \ZendX\Functions\Number();
        $myForm = new \Admin\Form\ContractOwed\ReturnConfirm($this, $this->_params);
        $param_data = $this->_params['data'];

        if(!empty($param_data['id'])) {
            $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $param_data['id']));
            $contract_options = !empty($contract['options']) ? unserialize($contract['options']) : array();
            $contract = array_merge($contract, $contract_options);
            $contract['date'] = $dateFormat->formatToView($contract['date']);

            $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']));

            $myForm->setData($contract);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }

        if($this->getRequest()->isPost()){
            if($param_data['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\ContractOwed\ReturnConfirm($this->_params));
                $myForm->setData($param_data);

                if($myForm->isValid()){
                    $params['data']['cid']                = [$param_data['id']];
                    $params['data']['field_status_name']  = 'status_acounting_id';
                    $params['data']['field_status_value'] = STATUS_CONTRACT_ACOUNTING_RETURN;

                    // Cập nhật trạng thái kế toán cho đơn hàng : Đã nhận hoàn.
                    $this->getServiceLocator()->get('Admin\Model\ContractTable')->updateItem($params, array('task' => 'update-item-status'));

                    $products = [];
                    $products2 = [];
                    for($i = 0; $i < count($param_data['product_id']); $i++){
                        $number_return = $param_data['numbers'][$i] - $param_data['numbers_cancle'][$i] > 0 ? $param_data['numbers'][$i] - $param_data['numbers_cancle'][$i] : 0;

                            $check_type = $this->getServiceLocator()->get('Admin\Model\KovProductsTable')->getItem(array('id' => $param_data['product_id'][$i]));

                            $pros['product_id'] = $check_type['id'];
                            $pros['product_code'] = $check_type['code'];
                            $pros['full_name'] = $check_type['fullName'];
                            $pros['branch_id'] = $param_data['branch_id'][$i];
                            $pros['cost'] = $param_data['cost'][$i];
                            $pros['price'] = $param_data['price'][$i];
                            $pros['numbers'] = $param_data['numbers'][$i];
                            $pros['numbers_cancle'] = $param_data['numbers_cancle'][$i];

                            if($check_type['product_type'] == 2){ // nếu sản phẩm thuộc sản phẩm sản xuất
                                if($number_return > 0) {
                                    $data['branch_id'] = $param_data['branch_id'][$i];
                                    $data['product_id'] = $param_data['product_id'][$i];
                                    $data['product_name'] = $param_data['product_name'][$i];
                                    $data['sale_branch_id'] = $contract['sale_branch_id'];
                                    $data['contract_code'] = $contract['code'];
                                    $data['contract_id'] = $contract['id'];
                                    $data['numbers'] = $number_return;
                                    $this->getServiceLocator()->get('Admin\Model\ProductReturnTable')->saveItem(array('data' => $data), array('task' => 'add-item'));
                                }

                                // Tạo bảng hàng hoàn sản xuất trên crm
                                $products2[] = $pros;
                                $data_return2['contract_id'] = $param_data['id'];
                                $data_return2['type'] = 2;
                                $data_return2['products'] = $products2;
                            }
                            else{
                                if($contract['shipped'] == 1){
                                    $pid = $param_data['product_id'][$i];
                                    $product = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/products/'.$pid);
                                    $product = json_decode($product, true);
                                    $inventories = $product['inventories'];
                                    $index = 0;
                                    foreach($inventories as $ki => $vi){
                                        if($vi['branchId'] == $param_data['branch_id'][$i]){
                                            $inventories_data[$index] = $inventories[$ki];
                                            $inventories_data[$index]['onHand'] += $number_return;
                                            $index++;
                                        }
                                    }
                                    $product_data['branchId'] = $param_data['branch_id'][$i];
                                    $product_data['id'] = $pid;
                                    $product_data['inventories'] = $inventories_data;
                                    if($number_return > 0) {
                                        $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/products/' . $pid, $product_data, 'PUT');
                                    }

                                    // Tạo bảng hàng hoàn có sẵn trên crm
                                    $products[] = $pros;
                                    $data_return['contract_id'] = $param_data['id'];
                                    $data_return['type'] = 1;
                                    $data_return['products'] = $products;
                                }
                            }

                    }
                    if(count($data_return['products']) > 0){
                        $this->getServiceLocator()->get('Admin\Model\ProductReturnKovTable')->saveItem(array('data' => $data_return), array('task' => 'add-item'));
                    }
                    if(count($data_return2['products']) > 0){
                        $this->getServiceLocator()->get('Admin\Model\ProductReturnKovTable')->saveItem(array('data' => $data_return2), array('task' => 'add-item'));
                    }

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
        $this->_viewModel['contract']       = $contract;
        $this->_viewModel['contact']        = $contact;
        $this->_viewModel['caption']        = 'Xác nhận đã nhận hoàn và nhập kho hàng hoàn';

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // dùng một lần
//    public function updateReturnAction(){
//        $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->listItem(array('ssFilter' => array('filter_date_begin' => '01/07/2021',)),array('task' => 'list-item-warehouse', 'paginator' => false));
//
//        foreach ($contracts as $contract) {
//            $products = unserialize($contract['options'])['product'];
//            $productss = [];
//            $data_return = [];
//            $productss2 = [];
//            $data_return2 = [];
//            foreach ($products as $item) {
//                $check_type = $this->getServiceLocator()->get('Admin\Model\KovProductsTable')->getItem(array('id' => $item['product_id']));
//
//                $pros['product_id'] = $check_type['id'];
//                $pros['product_code'] = $check_type['code'];
//                $pros['full_name'] = $check_type['fullName'];
//                $pros['branch_id'] = $item['branch_id'];
//                $pros['cost'] = $item['cost'];
//                $pros['price'] = $item['price'];
//                $pros['numbers'] = $item['numbers'];
//                $pros['numbers_cancle'] = 0;
//
//                if($check_type['product_type'] == 2){
//                    // Tạo bảng hàng hoàn sản xuất trên crm
//                    $productss2[] = $pros;
//
//                    $data_return2['contract_id'] = $contract['id'];
//                    $data_return2['type'] = 2;
//                    $data_return2['products'] = $productss2;
//                }
//                if ($check_type['product_type'] == 1) {
//                    if ($contract['shipped'] == 1) {
//                        // Tạo bảng hàng hoàn có sẵn trên crm
//                        $productss[] = $pros;
//
//                        $data_return['contract_id'] = $contract['id'];
//                        $data_return['type'] = 1;
//                        $data_return['products'] = $productss;
//
//                    }
//                }
//            }
//            if(count($data_return['products']) > 0){
//                $this->getServiceLocator()->get('Admin\Model\ProductReturnKovTable')->saveItem(array('data' => $data_return), array('task' => 'add-item'));
//            }
//            if(count($data_return2['products']) > 0){
//                $this->getServiceLocator()->get('Admin\Model\ProductReturnKovTable')->saveItem(array('data' => $data_return2), array('task' => 'add-item'));
//            }
//        }
//    }
}