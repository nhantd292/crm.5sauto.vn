<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class ProductionController extends ActionController {
    public function init() {
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\ContractTable';
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
        $this->_params['ssFilter']['filter_bill_code']      = $ssFilter->filter_bill_code;
        $this->_params['ssFilter']['filter_sale_branch']    = $ssFilter->filter_sale_branch;
        $this->_params['ssFilter']['filter_sale_group']     = $ssFilter->filter_sale_group;
        $this->_params['ssFilter']['filter_user']           = $ssFilter->filter_user;
        $this->_params['ssFilter']['filter_debt']           = $ssFilter->filter_debt;
        $this->_params['ssFilter']['filter_product_type'] 	= $ssFilter->filter_product_type;
        $this->_params['ssFilter']['filter_product'] 	    = $ssFilter->filter_product;
        $this->_params['ssFilter']['filter_guarantee']      = $ssFilter->filter_guarantee;
        $this->_params['ssFilter']['filter_status_type']    = $ssFilter->filter_status_type;
        $this->_params['ssFilter']['filter_status']         = $ssFilter->filter_status;
        $this->_params['ssFilter']['filter_shipper_id']     = $ssFilter->filter_shipper_id;
        $this->_params['ssFilter']['filter_technical_id']   = $ssFilter->filter_technical_id;
        $this->_params['ssFilter']['filter_tailors_id']     = $ssFilter->filter_tailors_id;
        $this->_params['ssFilter']['filter_coincider']      = $ssFilter->filter_coincider;
        $this->_params['ssFilter']['filter_production_type_id']  = $ssFilter->filter_production_type_id;
        $this->_params['ssFilter']['filter_carpet_color']   = $ssFilter->filter_carpet_color;
        $this->_params['ssFilter']['filter_tangled_color']  = $ssFilter->filter_tangled_color;
        $this->_params['ssFilter']['filter_flooring']       = $ssFilter->filter_flooring;
        $this->_params['ssFilter']['filter_status_store']   = $ssFilter->filter_status_store;
        $this->_params['ssFilter']['filter_status_guarantee_id']  = $ssFilter->filter_status_guarantee_id;
        $this->_params['ssFilter']['filter_status_shipped'] = $ssFilter->filter_status_shipped;

        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage'] = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : $this->_paginator['itemCountPerPage'];
        $this->_paginator['currentPageNumber'] = $this->params()->fromRoute('page', 1);
        $this->_params['paginator'] = $this->_paginator;
        
        // Lấy dữ liệu post của form
        $this->_params['data'] = array_merge($this->getRequest()->getPost()->toArray(), $this->getRequest()->getFiles()->toArray());
        
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
        $this->_viewModel['curent_user']  = $this->_userInfo->getUserInfo();
//        $this->_viewModel['encode_phone'] = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'encode-phone')), array('task' => 'list-all')), array('key' => 'content', 'value' => 'status'));
    }
    
    // Tìm kiếm
    public function filterAction() {
        if($this->getRequest()->isPost()) {
            $action = !empty($this->_params['data']['filter_action']) ? $this->_params['data']['filter_action'] : 'index';
            $action1 = str_replace('-', '_', $action);
            $ssFilter	= new Container(__CLASS__ . $action1);
            $data = $this->_params['data'];
    
            $ssFilter->pagination_option        = intval($data['pagination_option']);
            $ssFilter->order_by                 = $data['order_by'];
            $ssFilter->order                    = $data['order'];
            $ssFilter->filter_keyword           = $data['filter_keyword'];
            $ssFilter->filter_date_begin        = $data['filter_date_begin'];
            $ssFilter->filter_date_end          = $data['filter_date_end'];
            $ssFilter->filter_bill_code         = $data['filter_bill_code'];
            $ssFilter->filter_date_type         = $data['filter_date_type'];
            $ssFilter->filter_debt 	            = $data['filter_debt'];
            $ssFilter->filter_product 	        = $data['filter_product'];
            $ssFilter->filter_guarantee 	    = $data['filter_guarantee'];
            $ssFilter->filter_status_type 	    = $data['filter_status_type'];
            $ssFilter->filter_status 	        = $data['filter_status'];
            $ssFilter->filter_shipper_id 	    = $data['filter_shipper_id'];
            $ssFilter->filter_technical_id 	    = $data['filter_technical_id'];
            $ssFilter->filter_tailors_id 	    = $data['filter_tailors_id'];
            $ssFilter->filter_coincider 	    = $data['filter_coincider'];
            $ssFilter->filter_production_type_id= $data['filter_production_type_id'];
            $ssFilter->filter_status_guarantee_id = $data['filter_status_guarantee_id'];
            $ssFilter->filter_user              = $data['filter_user'];
            $ssFilter->filter_carpet_color      = $data['filter_carpet_color'];
            $ssFilter->filter_tangled_color     = $data['filter_tangled_color'];
            $ssFilter->filter_flooring          = $data['filter_flooring'];
            $ssFilter->filter_status_store      = $data['filter_status_store'];
            $ssFilter->filter_status_shipped    = $data['filter_status_shipped'];

            if($data['filter_product_type'] != $ssFilter->filter_product_type) {
                $ssFilter->filter_product_type 	= $data['filter_product_type'];
                $ssFilter->filter_product = '';
            }
            
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
            
            if($ssFilter['filter_date_type'] == 'date_debt') {
                if(empty($ssFilter->filter_date_begin)) {
                    $ssFilter->filter_date_begin = date('01/m/Y');
                    $ssFilter->filter_date_end = date('t/m/Y');
                }
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

        $myForm	= new \Admin\Form\Search\Contract($this, $this->_params);
        $myForm->setData($this->_params['ssFilter']);
        
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-production-item'));
        $count = $this->getTable()->countItem($this->_params, array('task' => 'list-production-item'));
        
        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['ssFilter']	            = $this->_params['ssFilter'];
        $this->_viewModel['items']                  = $items;
        $this->_viewModel['count']                  = $count;
        $this->_viewModel['user']                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['bill']                   = $this->getServiceLocator()->get('Admin\Model\BillTable')->listItem(null, array('task' => 'by-contract'));
        $this->_viewModel['bill_type']              = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'bill-type')), array('task' => 'cache'));
        $this->_viewModel['sale_group']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['transport']              = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'transport')), array('task' => 'cache'));
        $this->_viewModel['type_of_carpet']         = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'type-of-carpet')), array('task' => 'cache'));
        $this->_viewModel['row_seats']              = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'row-seats')), array('task' => 'cache'));

        $this->_viewModel['carpet_color']           = $this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['tangled_color']          = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['flooring']               = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'flooring')), array('task' => 'cache'));
        $this->_viewModel['production_department']  = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-department')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['product']                = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['kovProduct']             = $this->getServiceLocator()->get('Admin\Model\KovProductsTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['ProductReturn']          = $this->getServiceLocator()->get('Admin\Model\ProductReturnTable')->listItem(null, array('task' => 'cache'));

        $this->_viewModel['status_product']         = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-department')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['status_check']           = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-check')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['status_accounting']      = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-acounting')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));

        $this->_viewModel['caption']                = 'Đơn hàng - Danh sách';
        
        return new ViewModel($this->_viewModel);
    }

    // Danh sách đơn hàng mới chưa xử lý sản xuất
    public function newAction() {
        $ssFilter = new Container(__CLASS__. 'new');
        $ssFilter->currentPageNumber = $this->_paginator['currentPageNumber'];

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

        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item-new'));
        $count = $this->getTable()->countItem($this->_params, array('task' => 'list-item-new'));;

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
        $this->_viewModel['row_seats']              = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'row-seats')), array('task' => 'cache'));
        $this->_viewModel['carpet_color']           = $this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['tangled_color']          = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['flooring']               = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'flooring')), array('task' => 'cache'));
        $this->_viewModel['production_department']  = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-department')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['product']                = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['kovProduct']             = $this->getServiceLocator()->get('Admin\Model\KovProductsTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['ProductReturn']          = $this->getServiceLocator()->get('Admin\Model\ProductReturnTable')->listItem(null, array('task' => 'cache'));

        $this->_viewModel['status_product']         = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-department')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['status_check']           = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-check')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['status_accounting']      = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-acounting')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));

        $this->_viewModel['caption']                = 'Đơn hàng - Danh sách';

        return new ViewModel($this->_viewModel);
    }

    // Danh sách đơn hàng kỹ thuật - thợ may
    public function technicalTailorsAction() {
        $ssFilter       = new Container(__CLASS__.'technical_tailors');
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

        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-production-item'));
        $count = $this->getTable()->countItem($this->_params, array('task' => 'list-production-item'));

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

        $this->_viewModel['technical']              = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "technical" )), array('task' => 'cache'));
        $this->_viewModel['tailors']                = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "tailors" )), array('task' => 'cache'));

        $this->_viewModel['caption']                = 'Đơn hàng - Danh sách';

        return new ViewModel($this->_viewModel);
    }

    // Danh sách đơn hàng mới chưa xử lý sản xuất
    public function guaranteeAction() {
        $myForm	= new \Admin\Form\Search\Guarantee($this->getServiceLocator(), $this->_params['ssFilter']);
        $myForm->setData($this->_params['ssFilter']);
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
        $this->_viewModel['flooring']               = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'flooring')), array('task' => 'cache'));
        $this->_viewModel['production_department']  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-department')), array('task' => 'cache'));
        $this->_viewModel['status']                 = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status')), array('task' => 'cache'));
        $this->_viewModel['caption']                = 'Đơn hàng bảo hành - Danh sách';

        return new ViewModel($this->_viewModel);
    }

    // Sửa ghi chú
    public function guaranteeAddAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $numberFormat = new \ZendX\Functions\Number();
        $myForm = new \Admin\Form\Production\GuaranteeAdd($this->getServiceLocator(), $this->_params);
    
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
                $myForm->setInputFilter(new \Admin\Filter\Production\GuaranteeAdd($this->_params));
                $myForm->setData($this->_params['data']);
    
                if($myForm->isValid()){
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['item'] = $contract;
                    $result = $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params, array('task' => 'update-guarantee'));
    
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
        $this->_viewModel['caption']    = 'Thêm bảo hành';
    
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }

    // Xác nhận trạng - thái đã giao hàng
    public function transportedAction() {
        if($this->getRequest()->isPost()) {
            if(!empty($this->_params['data']['cid'])) {
                $list_product_type_contract =  \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "production-type" )), array('task' => 'cache')), array('key' => 'alias', 'value' => 'id'));
		        $id_don_tinh = $list_product_type_contract[DON_TINH];

                $cid = $this->_params['data']['cid'];
                $cid_update = array();
                foreach ($cid as $id){
                    $contract = $this->getTable()->getItem(array('id' => $id));
                    // Chỉ lấy ra những đơn hàng là đơn tỉnh đã sản xuất mới được cập nhật trạng thái đã giao hàng.
                    if($contract['production_department_type'] == STATUS_CONTRACT_PRODUCT_PRODUCTED && $contract['production_type_id'] == $id_don_tinh && empty($contract['lock'])){
                        $cid_update[] = $contract['id'];
                    }
                }
                if(count($cid_update) > 0){
                    $params['data']['cid']                = $cid_update;
                    $params['data']['field_status_name']  = 'production_department_type';
                    $params['data']['field_status_value'] = STATUS_CONTRACT_PRODUCT_POST;

                    // Cập nhật trạng thái sản xuất cho đơn hàng : Đã giao hàng.
                    $this->getTable()->updateItem($params, array('task' => 'update-item-status'));
                }
                $message = 'Đã cập nhật '. count($cid_update) .' đơn hàng về trạng thái - Đã giao hàng';
                $this->flashMessenger()->addMessage($message);
            }
        }

        $this->goRoute(array('action' => 'index'));
    }

    // Xác nhận đã xuất kho
    public function shippedAction() {
        if($this->getRequest()->isPost()) {
            if(!empty($this->_params['data']['cid'])) {
                $cid = $this->_params['data']['cid'];
                $count_update = 0;
                foreach ($cid as $id){
                    $contract = $this->getTable()->getItem(array('id' => $id));
                    // Chỉ lấy ra những đơn hàng có trạng thái sản xuất là đã giao hàng.
                    if($contract['production_department_type'] == STATUS_CONTRACT_PRODUCT_POST && $contract['shipped'] == 0 && !empty($contract['id_kov'])){
                        // Tạo hóa đơn kov
                        $result = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/orders/'.$contract['id_kov']);
                        $result = json_decode($result, true);
                        if(isset($result['id'])){
                            $invoiceDetails = $result['orderDetails'];
                            foreach($invoiceDetails as $key => $value){
                                unset($invoiceDetails[$key]['viewDiscount']);
                            }
                            $invoiceOrderSurcharges = $result['invoiceOrderSurcharges'];
                            foreach($invoiceOrderSurcharges as $key => $value){
                                $invoiSurcharges[$key]['id'] = $value['id'];
                                $invoiSurcharges[$key]['code'] = $value['surchargeCode'];
                                $invoiSurcharges[$key]['price'] = $value['price'];
                            }

                            $invoi_data['branchId']         = $result['branchId'];
                            $invoi_data['customerId']       = $result['customerId'];
                            $invoi_data['discount']         = $result['discount'];
                            $invoi_data['totalPayment']     = $result['totalPayment'];
                            $invoi_data['soldById']         = $result['soldById'];
                            $invoi_data['orderId']          = $result['id'];
                            $invoi_data['invoiceDetails']   = $invoiceDetails;
                            $invoi_data['deliveryDetail']   = array(
                                'status' => 2,
                                'surchages' => $invoiSurcharges
                            );
                            
                            $result_kov = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/invoices', $invoi_data, 'POST');
                            $result_kov = json_decode($result_kov, true);

                            if(isset($result_kov['id'])){
                                $params['data']['id']       = $id;
                                $params['data']['shipped']  = 1;
                                $count_update += 1;
                                $this->getTable()->saveItem($params, array('task' => 'update-shipped'));
                            }
                            else{
                                $mesage = $result_kov['responseStatus']['message'];
                                $this->flashMessenger()->addMessage($mesage);
                            }
                        }
                    }
                }
                $message = ' Đã xác nhận '. $count_update .' đơn hàng xuất kho';
                $this->flashMessenger()->addMessage($message);
            }
        }
        $this->goRoute(array('action' => 'index'));
    }

    // Cập nhật trạng thái Đơn hàng
    public function updateExcelAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $numberFormat = new \ZendX\Functions\Number();
        $myForm = new \Admin\Form\Production\EditStatus($this->getServiceLocator(), $this->_params);
        
        $this->_viewModel['myForm']                     = $myForm;
        $this->_viewModel['contract']                   = $contract;
        $this->_viewModel['contact']                    = $contact;
        $this->_viewModel['transport']                  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'transport')), array('task' => 'cache'));
        $this->_viewModel['type_of_carpet']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'type-of-carpet')), array('task' => 'cache'));
        $this->_viewModel['carpet_color']               = $this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['tangled_color']              = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['row_seats']                  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'row-seats')), array('task' => 'cache'));
        $this->_viewModel['flooring']                   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'flooring')), array('task' => 'cache'));
        $this->_viewModel['status']                     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status')), array('task' => 'cache'));
        $this->_viewModel['production_type']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-type')), array('task' => 'cache'));
        $this->_viewModel['product_type']               = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-type')), array('task' => 'cache'));
        $this->_viewModel['unit']                       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'unit')), array('task' => 'cache'));
        $this->_viewModel['product']                    = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['colorGroup']                 = $this->getServiceLocator()->get('Admin\Model\ColorGroupTable')->listItem(null, array('task' => 'cache'));
        
        $this->_viewModel['caption']                    = 'Cập nhật trạng thái đơn hàng';
    
        $viewModel = new ViewModel($this->_viewModel);
    
        return $viewModel;
    }

    // Sửa Đơn hàng
    public function editAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $numberFormat = new \ZendX\Functions\Number();

        $curent_user = $this->_userInfo->getUserInfo();
        $permission_ids = explode(',', $curent_user['permission_ids']);
        if(in_array(SYSTEM, $permission_ids) || in_array(ADMIN, $permission_ids)){
            $this->_params['data']['is_system_admin'] = true;
        }

        $myForm = new \Admin\Form\Production\Edit($this->getServiceLocator(), $this->_params);
        
        if(!empty($this->_params['data']['id'])) {
            $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->_params['data']['id']));
            $contract_options = !empty($contract['options']) ? unserialize($contract['options']) : array();
            $contract = array_merge($contract, $contract_options);
            $contract['date'] = $dateFormat->formatToView($contract['date']);
//            $contract['production_date'] = $dateFormat->formatToView($contract['production_date']);
            $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']));

            if($contract['lock']){
                return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'lock', 'type' => 'modal'));
            }
            
            $myForm->setData($contract);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }
    
        if($this->getRequest()->isPost()){
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\Production\Edit($this->getServiceLocator(), $this->_params));
                $myForm->setData($this->_params['data']);
    
                if($myForm->isValid()){
                    $this->_params['item'] = $contract;
                    $this->_params['contact'] = $contact;

                    // Hủy đơn hàng trên Kiotviet.
                    if($this->_params['data']['production_department_type'] == STATUS_CONTRACT_PRODUCT_CANCEL){
                        $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/orders/' . $contract['id_kov'], null, 'DELETE' );
                    }
                    $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params, array('task' => 'update-production'));
                    $this->flashMessenger()->addMessage('Cập nhật dữ liệu thành công');
                    echo 'success';
                    return $this->response;
                }
                else{
                    $this->_viewModel['contract_product'] = $this->_params['data']['contract_product'];
                }

            } else {
                $myForm->setData($this->_params['data']);
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
    
        $this->_viewModel['myForm']                     = $myForm;
        $this->_viewModel['contract']                   = $contract;
        $this->_viewModel['contact']                    = $contact;
        $this->_viewModel['transport']                  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'transport')), array('task' => 'cache'));
        $this->_viewModel['type_of_carpet']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'type-of-carpet')), array('task' => 'cache'));
        $this->_viewModel['carpet_color']               = $this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['tangled_color']              = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['row_seats']                  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'row-seats')), array('task' => 'cache'));
        $this->_viewModel['flooring']                   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'flooring')), array('task' => 'cache'));
        $this->_viewModel['status']                     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status')), array('task' => 'cache'));
        $this->_viewModel['production_type']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-type')), array('task' => 'cache'));
        $this->_viewModel['product_type']               = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-type')), array('task' => 'cache'));
        $this->_viewModel['unit']                       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'unit')), array('task' => 'cache'));
        $this->_viewModel['product']                    = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['colorGroup']                 = $this->getServiceLocator()->get('Admin\Model\ColorGroupTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['kovProduct']             = $this->getServiceLocator()->get('Admin\Model\KovProductsTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['ProductReturn']          = $this->getServiceLocator()->get('Admin\Model\ProductReturnTable')->listItem(null, array('task' => 'cache'));
        
        $this->_viewModel['caption']                    = 'Cập nhật thông tin sản xuất';
    
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }

    // Nhập thợ kỹ thuật, thợ may cho đơn hàng
    public function editTechnicalTailorsAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $numberFormat = new \ZendX\Functions\Number();

        $curent_user = $this->_userInfo->getUserInfo();
        $permission_ids = explode(',', $curent_user['permission_ids']);
        if(in_array(SYSTEM, $permission_ids) || in_array(ADMIN, $permission_ids)){
            $this->_params['data']['is_system_admin'] = true;
        }

        $myForm = new \Admin\Form\Production\Edit($this->getServiceLocator(), $this->_params);

        if(!empty($this->_params['data']['id'])) {
            $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->_params['data']['id']));
            $contract_options = !empty($contract['options']) ? unserialize($contract['options']) : array();
            $contract = array_merge($contract, $contract_options);
            $contract['date'] = $dateFormat->formatToView($contract['date']);
            $contract['production_date'] = $dateFormat->formatToView($contract['production_date']);
            $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']));
            $myForm->setData($contract);

            if($contract['lock']){
                return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'lock', 'type' => 'modal'));
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }

        if($this->getRequest()->isPost()){
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\Production\Edit($this->getServiceLocator(), $this->_params));
                $myForm->setData($this->_params['data']);

                if($myForm->isValid()){

                    $this->_params['item'] = $contract;
                    $this->_params['contact'] = $contact;

                    $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params, array('task' => 'update-production-technical'));
                    $this->flashMessenger()->addMessage('Cập nhật dữ liệu thành công');
                    echo 'success';
                    return $this->response;
                }
                else{
                    $this->_viewModel['contract_product'] = $this->_params['data']['contract_product'];
                }

            } else {
                $myForm->setData($this->_params['data']);
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }

        $this->_viewModel['myForm']                     = $myForm;
        $this->_viewModel['contract']                   = $contract;
        $this->_viewModel['contact']                    = $contact;

        $this->_viewModel['technical']                  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "technical" )), array('task' => 'cache'));
        $this->_viewModel['tailors']                    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "tailors" )), array('task' => 'cache'));

        $this->_viewModel['transport']                  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'transport')), array('task' => 'cache'));
        $this->_viewModel['type_of_carpet']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'type-of-carpet')), array('task' => 'cache'));
        $this->_viewModel['carpet_color']               = $this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['tangled_color']              = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['row_seats']                  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'row-seats')), array('task' => 'cache'));
        $this->_viewModel['flooring']                   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'flooring')), array('task' => 'cache'));
        $this->_viewModel['status']                     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status')), array('task' => 'cache'));
        $this->_viewModel['production_type']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-type')), array('task' => 'cache'));
        $this->_viewModel['product_type']               = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-type')), array('task' => 'cache'));
        $this->_viewModel['unit']                       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'unit')), array('task' => 'cache'));
        $this->_viewModel['product']                    = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['colorGroup']                 = $this->getServiceLocator()->get('Admin\Model\ColorGroupTable')->listItem(null, array('task' => 'cache'));

        $this->_viewModel['caption']                    = 'Cập nhật thông tin sản xuất';

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }
    
    public function printMultiAction() {
        if($this->params('id'))
            $items      = $this->getServiceLocator()->get('Admin\Model\ContractTable')->listItem(array('ids' => [$this->params('id')]), array('task' => 'list-print-multi'));
        else
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
        $this->_viewModel['kovProduct']                 = $this->getServiceLocator()->get('Admin\Model\KovProductsTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['ProductReturn']          = $this->getServiceLocator()->get('Admin\Model\ProductReturnTable')->listItem(null, array('task' => 'cache'));

        $this->_viewModel['status_product']         = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-department')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['status_check']           = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-check')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['status_accounting']      = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-acounting')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['product_type_contract']  =  \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "production-type" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name'));
        $this->_viewModel['location_city']          = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache'));
        $this->_viewModel['location_district']      = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 2), array('task' => 'cache'));
        $this->_viewModel['location_town']          = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 3), array('task' => 'cache'));
        $this->_viewModel['shippers']               = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'shipper')), array('task' => 'cache'));

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }

    public function printProductionAction() {
        $items      = $this->getTable()->listItem(array('ids' => $this->_params['data']['cid']), array('task' => 'list-print-multi'));
        $items      = $items->toArray();

        if(empty($items)) {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }
    
        $this->_viewModel['items']                      = $items;
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
        $this->_viewModel['product_type']               = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-type')), array('task' => 'cache'));
        $this->_viewModel['status']                     = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['product']                    = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['kovProduct']                 = $this->getServiceLocator()->get('Admin\Model\KovProductsTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['ProductReturn']          = $this->getServiceLocator()->get('Admin\Model\ProductReturnTable')->listItem(null, array('task' => 'cache'));

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
    
    public function exportAction() {
        $dateFormat             = new \ZendX\Functions\Date();
        $items                  = $this->getTable()->listItem(array('ids' => $this->_params['data']['cid']), array('task' => 'list-item-export'));
//        $items                  = $this->getTable()->listItem($this->_params, array('task' => 'list-item', 'paginator' => false));

        $user                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $sale_group             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $sale_branch            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $contract_type          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contract-type')), array('task' => 'cache'));
        $sale_history_action    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-action')), array('task' => 'cache'));
        $sale_history_result    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-result')), array('task' => 'cache'));
        $transport              = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'transport')), array('task' => 'cache'));
        $location_city          = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache'));
        $location_district      = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 2), array('task' => 'cache'));

        $status_product         = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-department')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $status_check           = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-check')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $status_accounting      = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-acounting')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $products               = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
//        $productkov             = $this->getServiceLocator()->get('Admin\Model\KovProductsTable')->listItem(null, array('task' => 'cache'));



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
        $arrColumn = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ','BA','BB','BC','BD','BE','BF','BG','BH','BI','BJ','BK','BL','BM','BN','BO','BP','BQ','BR','BS','BT','BU','BV','BW','BX','BY','BZ');
        
        // Data Export
        $arrData = array(
            array('field' => 'stt', 'title' => 'STT'),
//            array('field' => 'bill_code', 'title' => 'MÃ VẬN ĐƠN'),
            array('field' => 'code', 'title' => 'MÃ ĐƠN HÀNG'),
            array('field' => 'name', 'title' => 'TÊN KH'),
            array('field' => 'phone', 'title' => 'SĐT'),
            array('field' => 'address', 'title' => 'ĐỊA CHỈ'),
//            array('field' => 'user_id', 'title' => 'Nhân viên', 'type' => 'data_source', 'data_source' => $user),
            array(
                'field' =>'product',
                'title' => 'SỐ LƯỢNG + NỘI DUNG',
                'type' => 'custom_serialize',
                'data_custom_field' => 'options',
            ),
//            array('field' => 'name', 'title' => 'TÊN NGƯỜI NHẬN'),
//            array('field' => 'weight', 'title' => 'CÂN NẶNG'),
//            array('field' => 'phone', 'title' => 'SỐ ĐIỆN THOẠI'),
            array('field' => 'price_owed', 'title' => 'SỐ TIỀN THU HỘ'),
//            array('field' => 'transport_id', 'title' => 'DỊCH VỤ VẬN CHUYỂN', 'type' => 'data_source', 'data_source' => $transport),
//            array('field' => 'note_order', 'title' => 'GHI CHÚ ĐỐI VỚI CÁC ĐƠN HÀNG ĐẦU NHẬN TT'),
//            array('field' => 'view_product', 'title' => 'CHO KHÁCH XEM HÀNG'),
        );
        
        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();
        
        // Set document properties
        $objPHPExcel->getProperties()->setCreator($this->_userInfo->getUserInfo('name'))
        							 ->setLastModifiedBy($this->_userInfo->getUserInfo('username'))
        							 ->setTitle("Don_san_xuat_".date('d-m-Y'));
        
        // Dữ liệu tiêu đề cột
        $startColumn = $config['startColumn'];
        foreach ($arrData AS $key => $data) {
            $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[$startColumn] . $config['headRow'], $data['title']);
            $objPHPExcel->getActiveSheet()->getStyle($arrColumn[$startColumn] . $config['headRow'])->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension($arrColumn[$startColumn])->setAutoSize(true);
            $startColumn++;
        }
        
        // Dữ liệu data
        $startRow = $config['startRow'];
        $i = 1;
//echo "<pre>";
//print_r($items->toArray());
//echo "</pre>";
//exit;
        foreach ($items AS $item) {
            $item['stt'] = $i;
            $options = unserialize($item['options']);
            $contact_options = unserialize($item['contact_options']);

            $item['name'] = !empty($options['contract_received']['name']) ? $options['contract_received']['name'] : $item['contact_name'];
            $item['address'] = !empty($options['contract_received']['address']) ? $options['contract_received']['address'] : $contact_options['address'];
            $item['phone'] = !empty($options['contract_received']['phone']) ? $options['contract_received']['phone'] : $item['contact_phone'];
            $item['product_name'] = $options['product_name'];
            $startColumn = $config['startColumn'];
            foreach ($arrData AS $key => $data) {
                switch ($data['type']) {
                    case 'date':
                        $formatDate = $data['format'] ? $data['format'] : 'd/m/Y';
                        $value = $dateFormat->formatToView($item[$data['field']], $formatDate);
                        break;
                    case 'data_source':
                        $field = $data['data_source_field'] ? $data['data_source_field'] : 'name';
                        $value = $data['data_source'][$item[$data['field']]][$field];
                        break;
                   	case 'data_serialize':
                        $data_serialize = $item[$data['data_serialize_field']] ? unserialize($item[$data['data_serialize_field']]) : array();
                        $value = $data_serialize[$data['field']];
                        
                        if(!empty($data['data_source'])) {
                            $field = $data['data_source_field'] ? $data['data_source_field'] : 'name';
                            $value = $data['data_source'][$data_serialize[$data['field']]][$field];
                        }
                        if(!empty($data['data_date_format'])) {
                            $value = $dateFormat->formatToView($data_serialize[$data['field']], $data['data_date_format']);
                        }
                        break;
                    case 'custom_serialize':
                        $value = '';
                        $data_custom = $item[$data['data_custom_field']] ? unserialize($item[$data['data_custom_field']]) : array();
                        $key = 0;
                        foreach ($data_custom[$data['field']] AS $key_f => $value_f) {
                            if($item['kov_status']){
                                $value .= (++$key).'. '.$value_f['full_name'].' x '.($value_f['numbers']?:1).' '.$value_f['product_name']. ', '.PHP_EOL;
                            }
                            else{
                                $value .= (++$key).'. '.$products[$value_f['product_id']]->name.' x '.($value_f['numbers']?:1).' '.$value_f['product_name']. ', '.PHP_EOL;
                            }
                        }
                        break;
                    default:
                        $value = $item[$data['field']];
                }
                $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[$startColumn] . $startRow, $value);
                $objPHPExcel->setActiveSheetIndex($config['sheetData'])->getStyle($arrColumn[$startColumn] . $startRow)->getAlignment()->setWrapText(true);
                $startColumn++;
            }
            $startRow++;
            $i++;
        }
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.'Don_san_xuat_'.date('d-m-Y').'.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header ('Cache-Control: cache, must-revalidate');
        header ('Pragma: public');
        
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
        
        return $this->response;
    }

    public function exportv2Action() {
        $dateFormat             = new \ZendX\Functions\Date();

        $items                  = $this->getTable()->listItem($this->_params, array('task' => 'list-item', 'paginator' => false));

        $user                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $sale_group             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $sale_branch            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $contract_type          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contract-type')), array('task' => 'cache'));
        $sale_history_action    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-action')), array('task' => 'cache'));
        $sale_history_result    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-result')), array('task' => 'cache'));
        $transport              = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'transport')), array('task' => 'cache'));
        $location_city          = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache'));
        $location_district      = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 2), array('task' => 'cache'));

        $status_product         = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-department')), array('task' => 'cache'));
        $status_check           = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-check')), array('task' => 'cache'));
        $status_accounting      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-acounting')), array('task' => 'cache'));


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
        $arrColumn = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ','BA','BB','BC','BD','BE','BF','BG','BH','BI','BJ','BK','BL','BM','BN','BO','BP','BQ','BR','BS','BT','BU','BV','BW','BX','BY','BZ');

        // Data Export
        $arrData = array(
            array('field' => 'stt', 'title' => 'STT'),
            array('field' => 'production_department_type', 'title' => 'Trạng thái sản xuất', 'type' => 'data_source', 'data_source' => $status_product),
            array('field' => 'bill_code', 'title' => 'MÃ VẬN ĐƠN'),
            array('field' => 'code', 'title' => 'MÃ SỐ ĐƠN'),
            array('field' => 'date', 'title' => 'Ngày', 'type' => 'date'),
            array('field' => 'user_id', 'title' => 'Nhân viên', 'type' => 'data_source', 'data_source' => $user),
            array('field' => 'contact_name', 'title' => 'Tên khách hàng'),
            array('field' => 'contact_phone', 'title' => 'Điện thoại'),
            array('field' => 'address', 'title' => 'ĐỊA CHỈ'),
            array('field' => 'vat', 'title' => 'VAT'),
            array('field' => 'price_total', 'title' => 'Tổng tiền'),
            array('field' => 'transport_id', 'title' => 'Vận chuyển', 'type' => 'data_source', 'data_source' => $transport),
            array('field' => 'note', 'title' => 'Tên xe - Năm sản xuất', 'type' => 'data_serialize', 'data_serialize_field' => 'options'),

            array('field' => 'product_name', 'title' => 'SỐ LƯỢNG + NỘI DUNG'),
            array('field' => 'name', 'title' => 'TÊN NGƯỜI NHẬN'),
            array('field' => 'weight', 'title' => 'CÂN NẶNG'),
            array('field' => 'phone', 'title' => 'SỐ ĐIỆN THOẠI'),
            array('field' => 'price_owed', 'title' => 'SỐ TIỀN THU HỘ'),
            array('field' => 'note_order', 'title' => 'GHI CHÚ ĐỐI VỚI CÁC ĐƠN HÀNG ĐẦU NHẬN TT'),
            array('field' => 'view_product', 'title' => 'CHO KHÁCH XEM HÀNG'),
        );

        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator($this->_userInfo->getUserInfo('name'))
        							 ->setLastModifiedBy($this->_userInfo->getUserInfo('username'))
        							 ->setTitle("Don_san_xuat_".date('d-m-Y'));

        // Dữ liệu tiêu đề cột
        $startColumn = $config['startColumn'];
        foreach ($arrData AS $key => $data) {
            $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[$startColumn] . $config['headRow'], $data['title']);
            $objPHPExcel->getActiveSheet()->getStyle($arrColumn[$startColumn] . $config['headRow'])->getFont()->setBold(true);
            $startColumn++;
        }

        // Dữ liệu data
        $startRow = $config['startRow'];
        $i = 1;

        foreach ($items AS $item) {
            $item['stt'] = $i;
            $options = unserialize($item['options']);
            $contact_options = unserialize($item['contact_options']);

            $item['name'] = !empty($options['contract_received']['name']) ? $options['contract_received']['name'] : $item['contact_name'];
            $item['address'] = !empty($options['contract_received']['address']) ? $options['contract_received']['address'] : $contact_options['address'];
            $item['phone'] = !empty($options['contract_received']['phone']) ? $options['contract_received']['phone'] : $item['contact_phone'];
            $item['product_name'] = $options['product_name'];
            $startColumn = $config['startColumn'];
            foreach ($arrData AS $key => $data) {
                switch ($data['type']) {
                    case 'date':
                        $formatDate = $data['format'] ? $data['format'] : 'd/m/Y';
                        $value = $dateFormat->formatToView($item[$data['field']], $formatDate);
                        break;
                    case 'data_source':
                        $field = $data['data_source_field'] ? $data['data_source_field'] : 'name';
                        $value = $data['data_source'][$item[$data['field']]][$field];
                        break;
                   	case 'data_serialize':
                        $data_serialize = $item[$data['data_serialize_field']] ? unserialize($item[$data['data_serialize_field']]) : array();
                        $value = $data_serialize[$data['field']];

                        if(!empty($data['data_source'])) {
                            $field = $data['data_source_field'] ? $data['data_source_field'] : 'name';
                            $value = $data['data_source'][$data_serialize[$data['field']]][$field];
                        }
                        if(!empty($data['data_date_format'])) {
                            $value = $dateFormat->formatToView($data_serialize[$data['field']], $data['data_date_format']);
                        }
                        break;
                    default:
                        $value = $item[$data['field']];
                }

                $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[$startColumn] . $startRow, $value);
                $startColumn++;
            }
            $startRow++;
            $i++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.'Don_san_xuat_'.date('d-m-Y').'.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header ('Cache-Control: cache, must-revalidate');
        header ('Pragma: public');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;

        return $this->response;
    }

    public function exportTemplateAction() {
        $items          = $this->getTable()->listItem(array('ids' => $this->_params['data']['cid']), array('task' => 'list-item-export'));
        $products       = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));

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
        $arrColumn = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ','BA','BB','BC','BD','BE','BF','BG','BH','BI','BJ','BK','BL','BM','BN','BO','BP','BQ','BR','BS','BT','BU','BV','BW','BX','BY','BZ');

        // Data Export
        $arrData = array(
            array('field' => 'code', 'title' => 'MÃ ĐƠN HÀNG'),
            array('field' =>'product','title' => 'MÃ SẢN PHẨM'),
            array('field' =>'product','title' => 'TÊN SẢN PHẨM'),
            array('field' =>'technical','title' => 'THỢ KỸ THUẬT'),
            array('field' =>'tailors','title' => 'THỢ MAY'),
        );

        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator($this->_userInfo->getUserInfo('name'))
            ->setLastModifiedBy($this->_userInfo->getUserInfo('username'))
            ->setTitle("Don_san_xuat_".date('d-m-Y'));

        // Dữ liệu tiêu đề cột
        $startColumn = $config['startColumn'];
        foreach ($arrData AS $key => $data) {
            $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[$startColumn] . $config['headRow'], $data['title']);
            $objPHPExcel->getActiveSheet()->getStyle($arrColumn[$startColumn] . $config['headRow'])->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension($arrColumn[$startColumn])->setAutoSize(true);
            $startColumn++;
        }

        // Dữ liệu data
        $startRow = $config['startRow'];
        foreach ($items AS $item) {
            $contract_product = unserialize($item['options'])['product'];

//            $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[0] . $startRow, $item['code']);
//            if(count($contract_product) > 1){
//                $objPHPExcel->getActiveSheet()->mergeCells($arrColumn[0].$startRow.':'.$arrColumn[0].($startRow+count($contract_product)-1));
//            }
            foreach($contract_product as $product){
                $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[0] . $startRow, $item['code']);
                $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[1] . $startRow, $product['product_id']);
                $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[2] . $startRow, $products[$product['product_id']]['name']);
                $startRow++;
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.'Nhap_tho_ky_thuat_tho_may_'.date('d-m-Y').'.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header ('Cache-Control: cache, must-revalidate');
        header ('Pragma: public');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;

        return $this->response;
    }

    public function importTechnicalAction()
    {
        $dateFormat = new \ZendX\Functions\Date();
        $myForm = new \Admin\Form\Contact\Import($this->getServiceLocator(), $this->_params);
        $myForm->setInputFilter(new \Admin\Filter\Contact\Import($this->_params));

        $this->_viewModel['caption'] = 'Import thợ kỹ thuật, thợ may';
        $this->_viewModel['myForm']  = $myForm;
        $viewModel                   = new ViewModel($this->_viewModel);

        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($this->getRequest()->isPost()) {
                if (empty($this->_params['data']['code'])) {
                    echo 'Không có đơn hàng';
                } elseif (empty($this->_params['data']['product_id'])) {
                    echo 'Không có mã sản phẩm';
                } else {
                    $contract   = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('code' => $this->_params['data']['code']), array('task' => 'by-code'));
                    if(!empty($contract)){
                        if($contract['lock']){
                            echo 'Đơn hàng đã khóa';
                        }
                        else {
                            $options = unserialize($contract['options']);
                            $products = $options['product'];
                            $technicals = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'technical')),
                                array('task' => 'cache')), array('key' => 'name', 'value' => 'id'));
                            $tailors = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'tailors')),
                                array('task' => 'cache')), array('key' => 'name', 'value' => 'id'));

                            if (!empty($this->_params['data']['technical_id'])) {
                                $technical_arr = explode(',', $this->_params['data']['technical_id']);
                                foreach ($technical_arr as $tech_name) {
                                    if (!empty($technicals[trim($tech_name)])) {
                                        $technical_id[] = $technicals[trim($tech_name)];
                                    }
                                }
                                $technical_id = !empty($technical_id) ? implode(',', $technical_id) : '';
                            }
                            if (!empty($this->_params['data']['tailors_id'])) {
                                $tailors_arr = explode(',', $this->_params['data']['tailors_id']);
                                foreach ($tailors_arr as $tailors_name) {
                                    if (!empty($tailors[trim($tailors_name)])) {
                                        $tailors_id[] = $tailors[trim($tailors_name)];
                                    }
                                }
                                $tailors_id = !empty($tailors_id) ? implode(',', $tailors_id) : '';
                            }
                            if (!empty($technical_id) || !empty($tailors_id)) {
                                foreach ($products as $key => $product) {
                                    if ($product['product_id'] == $this->_params['data']['product_id']) {
                                        $products[$key]['technical_id'] = $technical_id;
                                        $products[$key]['tailors_id'] = $tailors_id;
                                        break;
                                    }
                                }
                                $options['product'] = $products;

                                $this->_params['data']['options'] = serialize($options);
                                $this->_params['data']['id'] = $contract['id'];

                                $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params,
                                    array('task' => 'import-technical'));
                                echo 'Hoàn thành';
                            } else {
                                echo "Không có dữ liệu";
                            }
                        }
                    }
                    else{
                        echo 'Sai mã đơn hàng';
                    }
                }
                return $this->response;
            }
        } else {
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
                }
            }
        }

        return $viewModel;
    }

    public function exportTechnicalAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $items          = $this->getTable()->listItem(array('ids' => $this->_params['data']['cid']), array('task' => 'list-item-export'));
        $products       = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));

        $user                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $transport              = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'transport')), array('task' => 'cache'));
        $carpet_color           = $this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem(null, array('task' => 'cache'));
        $tangled_color          = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem(null, array('task' => 'cache'));
        $flooring               = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'flooring')), array('task' => 'cache'));
        $production_department  = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-department')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $status_check           = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-check')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $status_accounting      = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-acounting')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $technical              = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "technical" )), array('task' => 'cache'));
        $tailors                = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "tailors" )), array('task' => 'cache'));

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
        $arrColumn = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ','BA','BB','BC','BD','BE','BF','BG','BH','BI','BJ','BK','BL','BM','BN','BO','BP','BQ','BR','BS','BT','BU','BV','BW','BX','BY','BZ');
        // Data Export
        $arrData = array('STT','Trạng thái','Mã số đơn','Ngày','Nhân viên','Tên khách hàng','Điện thoại','Địa chỉ','Tổng tiền','Hàng sẵn có','Sản phẩm','Số lượng','Tên xe - Năm SX','Thương hiệu','Mã sản phẩm','Loại sản phẩm','Thợ kỹ thuật','Thợ may','Ghi chú sales');
        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();
        // Set document properties
        $objPHPExcel->getProperties()->setCreator($this->_userInfo->getUserInfo('name'))
            ->setLastModifiedBy($this->_userInfo->getUserInfo('username'))
            ->setTitle("Don_san_xuat_".date('d-m-Y'));
        // Dữ liệu tiêu đề cột
        $startColumn = $config['startColumn'];
        foreach ($arrData AS $key => $data) {
            $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[$startColumn] . $config['headRow'], $data);
            $objPHPExcel->getActiveSheet()->getStyle($arrColumn[$startColumn] . $config['headRow'])->getFont()->setBold(true);
//            $objPHPExcel->getActiveSheet()->getColumnDimension($arrColumn[$startColumn])->setAutoSize(true);
            $startColumn++;
        }

        // Dữ liệu data
        $startRow = $config['startRow'];
        $stt = 0;
        foreach ($items AS $item) {
            $contract_product = unserialize($item['options'])['product'];
            $contact_options = $item->contact_options ? unserialize($item['contact_options']) : array();
            $address = !empty($options['contract_received']['address']) ? $options['contract_received']['address'] : $contact_options['address'];
            $transport = $item->transport_id ? $transport[$item->transport_id]['name'] : '';
            $options = $item->options ? unserialize($item['options']) : array();

            $rows_span = $startRow+count($contract_product)-1;

            $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[0] . $startRow, ++$stt);
            $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[1] . $startRow, 'Sản xuất: '.$production_department[$item->production_department_type]->name.PHP_EOL.'Giục đơn: '.$status_check[$item->status_check_id]->name.PHP_EOL.'Kế toán:'.$status_accounting[$item->status_acounting_id]->name);
            $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[2] . $startRow, $item->code);
            $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[3] . $startRow, $dateFormat->formatToView($item->date));
            $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[4] . $startRow, $user[$item->user_id]['name']);
            $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[5] . $startRow, $item->contact_name);
            $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[6] . $startRow, $item->contact_phone);

            $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[7] . $startRow, $address);
            $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[8] . $startRow, $item->price_total);
            $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[9] . $startRow, $item->stock);
            $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[18] . $startRow, $options['sale_note']);

            foreach($arrData as $key => $value){
                $objPHPExcel->setActiveSheetIndex($config['sheetData'])->getStyle($arrColumn[$key] . $startRow)->getAlignment()->setWrapText(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension($arrColumn[$key])->setAutoSize(true);
            }
            if(count($contract_product) > 1){
                foreach($arrData as $key => $value){
                    if($key <= 9 || $key == 18){
                        $objPHPExcel->getActiveSheet()->mergeCells($arrColumn[$key].$startRow.':'.$arrColumn[$key].$rows_span);
                    }
                }
            }

            foreach($contract_product as $product){
                $technical_html = '';
                $tailors_html   = '';
                if(!empty($product['technical_id'])){
                    $technical_id = explode(',', $product['technical_id']);
                    foreach($technical_id as $valuet){
                        $technical_html .= $technical[$valuet]['name'].', ';
                    }
                }
                if(!empty($product['tailors_id'])){
                    $tailors_id = explode(',', $product['tailors_id']);
                    foreach($tailors_id as $valuet){
                        $tailors_html .= $tailors[$valuet]['name'].', ';
                    }
                }

                $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[10] . $startRow, $products[$product['product_id']]['name']);
                $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[11] . $startRow, $product['numbers']);
                $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[12] . $startRow, $product['product_name']);
                $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[13] . $startRow, $carpet_color[$product['carpet_color_id']]['name']);
                $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[14] . $startRow, $tangled_color[$product['tangled_color_id']]['name']);
                $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[15] . $startRow, $flooring[$product['flooring_id']]['name']);
                $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[16] . $startRow, $technical_html);
                $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[17] . $startRow, $tailors_html);
                $startRow++;
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.'Don_hang_tho_thi_cong_'.date('d-m-Y').'.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header ('Cache-Control: cache, must-revalidate');
        header ('Pragma: public');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;

        return $this->response;
    }
}


