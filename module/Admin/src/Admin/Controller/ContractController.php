<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class ContractController extends ActionController {
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
        $this->_params['ssFilter']['filter_date_type']      = $ssFilter->filter_date_type;
        $this->_params['ssFilter']['filter_date_begin']     = $ssFilter->filter_date_begin;
        $this->_params['ssFilter']['filter_date_end']       = $ssFilter->filter_date_end;
        $this->_params['ssFilter']['filter_date_type']      = $ssFilter->filter_date_type;
        $this->_params['ssFilter']['filter_sale_branch']    = $ssFilter->filter_sale_branch;
        $this->_params['ssFilter']['filter_sale_group']     = $ssFilter->filter_sale_group;
        $this->_params['ssFilter']['filter_user']           = $ssFilter->filter_user;
        $this->_params['ssFilter']['filter_product'] 	    = $ssFilter->filter_product;
        $this->_params['ssFilter']['filter_bill_code']      = $ssFilter->filter_bill_code;
        $this->_params['ssFilter']['filter_status_type']    = $ssFilter->filter_status_type;
        $this->_params['ssFilter']['filter_status']         = $ssFilter->filter_status;
        $this->_params['ssFilter']['filter_coincider']      = $ssFilter->filter_coincider;
        $this->_params['ssFilter']['filter_unit_transport'] = $ssFilter->filter_unit_transport;
        $this->_params['ssFilter']['filter_returned']       = $ssFilter->filter_returned;
        $this->_params['ssFilter']['filter_send_ghtk']      = $ssFilter->filter_send_ghtk;
        $this->_params['ssFilter']['filter_category']       = $ssFilter->filter_category;
        $this->_params['ssFilter']['filter_product']        = $ssFilter->filter_product;
        $this->_params['ssFilter']['filter_update_kov_false']        = $ssFilter->filter_update_kov_false;
        $this->_params['ssFilter']['filter_production_type_id']        = $ssFilter->filter_production_type_id;
        $this->_params['ssFilter']['filter_shipper_id']        = $ssFilter->filter_shipper_id;

        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage'] = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : $this->_paginator['itemCountPerPage'];
        $this->_paginator['currentPageNumber'] = $this->params()->fromRoute('page', 1);
        $this->_params['paginator'] = $this->_paginator;
        
        // Lấy dữ liệu post của form
        $this->_params['data'] = array_merge($this->getRequest()->getPost()->toArray(), $this->getRequest()->getFiles()->toArray());

        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
        $this->_viewModel['curent_user']  = $this->_userInfo->getUserInfo();
    }
    
    // Tìm kiếm
    public function filterAction() {
        if($this->getRequest()->isPost()) {
            $data = $this->_params['data'];

            $action = !empty($this->getRequest()->getPost('filter_action')) ? str_replace('-', '_', $this->getRequest()->getPost('filter_action')) : 'index';
            $ssFilter	= new Container(__CLASS__ . $action);

            $ssFilter->pagination_option        = intval($data['pagination_option']);
            $ssFilter->order_by                 = $data['order_by'];
            $ssFilter->order                    = $data['order'];
            $ssFilter->filter_keyword           = $data['filter_keyword'];
            $ssFilter->filter_date_type         = $data['filter_date_type'];
            $ssFilter->filter_date_begin        = $data['filter_date_begin'];
            $ssFilter->filter_date_end          = $data['filter_date_end'];
            $ssFilter->filter_date_type         = $data['filter_date_type'];
            $ssFilter->filter_product 	        = $data['filter_product'];
            $ssFilter->filter_status_type       = $data['filter_status_type'];
            $ssFilter->filter_status            = $data['filter_status'];
            $ssFilter->filter_user              = $data['filter_user'];
            $ssFilter->filter_action            = $data['filter_action'];
            $ssFilter->filter_coincider 	    = $data['filter_coincider'];
            $ssFilter->filter_unit_transport 	= $data['filter_unit_transport'];
            $ssFilter->filter_returned 	        = $data['filter_returned'];
            $ssFilter->filter_send_ghtk 	    = $data['filter_send_ghtk'];
            $ssFilter->filter_category 	        = $data['filter_category'];
            $ssFilter->filter_product 	        = $data['filter_product'];
            $ssFilter->filter_update_kov_false 	= $data['filter_update_kov_false'];
            $ssFilter->filter_production_type_id 	= $data['filter_production_type_id'];
            $ssFilter->filter_shipper_id 	= $data['filter_shipper_id'];

            $ssFilter->filter_sale_group = $data['filter_sale_group'];
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
        $action = str_replace('_', '-', $this->getRequest()->getPost('filter_action'));
        $this->goRoute(['action' => $action]);
    }
    
    // Danh sách đơn hàng sale
    public function indexAction() {
        $ssFilter = new Container(__CLASS__.'index');
        // Phân quyền view
        $curent_user = $this->_userInfo->getUserInfo();
        $permission_ids = explode(',', $curent_user['permission_ids']);
        if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids) && !in_array(MANAGER, $permission_ids)){
            if(in_array(GDCN, $permission_ids) || in_array(SALEADMIN, $permission_ids)){
                $this->_params['ssFilter']['filter_sale_branch'] = $curent_user['sale_branch_id'];
                $ssFilter->filter_sale_branch = $curent_user['sale_branch_id'];
            }
            elseif (in_array(GROUP_SALES_LEADER, $permission_ids)){
                $this->_params['ssFilter']['filter_sale_branch'] = $curent_user['sale_branch_id'];
                $this->_params['ssFilter']['filter_sale_group'] = $curent_user['sale_group_id'];
                $ssFilter->filter_sale_branch = $curent_user['sale_branch_id'];
                $ssFilter->filter_sale_group = $curent_user['filter_sale_group'];
            }
            else{
                $this->_params['ssFilter']['filter_user'] = $curent_user['id'];
            }
        }

        // Lấy danh mục sản phẩm cho vào bộ lọc
//        $categories = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/categories?pageSize=100&hierachicalData=true');
//        $categories = json_decode($categories, true)['data'];
//        $categories = $this->getNameCat($this->addNew($categories), $result);
//        $this->_params['categories'] = $categories;

        // Lấy danh sách sản phẩm đưa vào bộ lọc
        $products = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/products?pageSize=100');
        $products = json_decode($products, true);
        if($products['total'] < $products['pageSize']){
            $product_data = \ZendX\Functions\CreateArray::create($products['data'], array('key' => 'id', 'value' => 'fullName'));
        }
        else{
            $total = $products['total'];
            $pageSize = $products['pageSize'];
            $pageTotal = (int)($total / $pageSize) + 1;
            $product_data = [];
            for ($index = 0; $index < $pageTotal; $index++) {
                $currentItem = $index * $pageSize;
                $products = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/products?pageSize=100&currentItem=' . $currentItem);
                $products = json_decode($products, true);
                $product_data = array_merge($product_data, $products['data']);
            }
            $product_data = \ZendX\Functions\CreateArray::create($product_data, array('key' => 'code', 'value' => 'fullName'));
        }
        $this->_params['products'] = $product_data;

        $myForm	= new \Admin\Form\Search\Contract($this, $this->_params);
        $myForm->setData($this->_params['ssFilter']);
        $user_obj = $this->getServiceLocator()->get('Admin\Model\UserTable')->getItem(['id' => $curent_user['id']]);
        $user_branch = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(['id' => $user_obj['sale_branch_id']]);

        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['count']                  = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['user']                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_group']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['location_city']          = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache'));
        $this->_viewModel['location_district']      = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 2), array('task' => 'cache'));
        $this->_viewModel['location_town']          = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 3), array('task' => 'cache'));
        $this->_viewModel['shippers']               = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'shipper')), array('task' => 'cache'));
        $this->_viewModel['viettelKeyList']         = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'viettel-key', 'key_ghtk_ids' => explode(',', $user_branch['key_viettel_ids']))), array('task' => 'list-all'));
        $this->_viewModel['ghtkKeyList']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'ghtk-key', 'key_ghtk_ids' => explode(',', $user_branch['key_ghtk_ids']))), array('task' => 'list-all'));

        $this->_viewModel['status_check']           = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'ghtk-status')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['status_check_vtp']       = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'viettel-status')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['status_accounting']      = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-acounting')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['status_sales']           = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['caption']                = 'Đơn hàng - Danh sách';
        
        return new ViewModel($this->_viewModel);
    }

    // Danh sách đơn hàng giục đơn
    public function indexShippingAction() {
        $ssFilter = new Container(__CLASS__.'shipping');
        // Phân quyền view
        $curent_user = $this->_userInfo->getUserInfo();
        $permission_ids = explode(',', $curent_user['permission_ids']);
        if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids) && !in_array(MANAGER, $permission_ids)){
            if(in_array(GDCN, $permission_ids) || in_array(SALEADMIN, $permission_ids)){
                $this->_params['ssFilter']['filter_sale_branch'] = $curent_user['sale_branch_id'];
                $ssFilter->filter_sale_branch = $curent_user['sale_branch_id'];
            }
            elseif (in_array(GROUP_SALES_LEADER, $permission_ids)){
                $this->_params['ssFilter']['filter_sale_branch'] = $curent_user['sale_branch_id'];
                $this->_params['ssFilter']['filter_sale_group'] = $curent_user['sale_group_id'];
                $ssFilter->filter_sale_branch = $curent_user['sale_branch_id'];
                $ssFilter->filter_sale_group = $curent_user['filter_sale_group'];
            }
            else{
                $this->_params['ssFilter']['filter_user'] = $curent_user['id'];
            }
        }

        // Lấy danh sách sản phẩm đưa vào bộ lọc
        $products = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/products?pageSize=100');
        $products = json_decode($products, true);
        if($products['total'] < $products['pageSize']){
            $product_data = \ZendX\Functions\CreateArray::create($products['data'], array('key' => 'id', 'value' => 'fullName'));
        }
        else{
            $total = $products['total'];
            $pageSize = $products['pageSize'];
            $pageTotal = (int)($total / $pageSize) + 1;
            $product_data = [];
            for ($index = 0; $index < $pageTotal; $index++) {
                $currentItem = $index * $pageSize;
                $products = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/products?pageSize=100&currentItem=' . $currentItem);
                $products = json_decode($products, true);
                $product_data = array_merge($product_data, $products['data']);
            }
            $product_data = \ZendX\Functions\CreateArray::create($product_data, array('key' => 'code', 'value' => 'fullName'));
        }
        $this->_params['products'] = $product_data;

        $myForm	= new \Admin\Form\Search\Contract($this, $this->_params);
        $myForm->setData($this->_params['ssFilter']);

        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['count']                  = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['user']                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_group']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['location_city']          = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache'));
        $this->_viewModel['location_district']      = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 2), array('task' => 'cache'));
        $this->_viewModel['location_town']          = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 3), array('task' => 'cache'));
        $this->_viewModel['shippers']               = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'shipper')), array('task' => 'cache'));

        $this->_viewModel['status_check']           = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'ghtk-status')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['status_check_vtp']       = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'viettel-status')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['status_accounting']      = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-acounting')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['status_sales']           = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['caption']                = 'Đơn hàng - Danh sách';

        return new ViewModel($this->_viewModel);
    }

    // Danh sách đơn hàng kế toán
    public function indexAccountingAction() {
        $ssFilter = new Container(__CLASS__.'accounting');
        // Phân quyền view
        $curent_user = $this->_userInfo->getUserInfo();
        $permission_ids = explode(',', $curent_user['permission_ids']);
        if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids) && !in_array(MANAGER, $permission_ids)){
            if(in_array(GDCN, $permission_ids) || in_array(SALEADMIN, $permission_ids)){
                $this->_params['ssFilter']['filter_sale_branch'] = $curent_user['sale_branch_id'];
                $ssFilter->filter_sale_branch = $curent_user['sale_branch_id'];
            }
            elseif (in_array(GROUP_SALES_LEADER, $permission_ids)){
                $this->_params['ssFilter']['filter_sale_branch'] = $curent_user['sale_branch_id'];
                $this->_params['ssFilter']['filter_sale_group'] = $curent_user['sale_group_id'];
                $ssFilter->filter_sale_branch = $curent_user['sale_branch_id'];
                $ssFilter->filter_sale_group = $curent_user['filter_sale_group'];
            }
            else{
                $this->_params['ssFilter']['filter_user'] = $curent_user['id'];
            }
        }

        // Lấy danh sách sản phẩm đưa vào bộ lọc
        $products = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/products?pageSize=100');
        $products = json_decode($products, true);
        if($products['total'] < $products['pageSize']){
            $product_data = \ZendX\Functions\CreateArray::create($products['data'], array('key' => 'id', 'value' => 'fullName'));
        }
        else{
            $total = $products['total'];
            $pageSize = $products['pageSize'];
            $pageTotal = (int)($total / $pageSize) + 1;
            $product_data = [];
            for ($index = 0; $index < $pageTotal; $index++) {
                $currentItem = $index * $pageSize;
                $products = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/products?pageSize=100&currentItem=' . $currentItem);
                $products = json_decode($products, true);
                $product_data = array_merge($product_data, $products['data']);
            }
            $product_data = \ZendX\Functions\CreateArray::create($product_data, array('key' => 'code', 'value' => 'fullName'));
        }
        $this->_params['products'] = $product_data;

        $myForm	= new \Admin\Form\Search\Contract($this, $this->_params);
        $myForm->setData($this->_params['ssFilter']);

        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['count']                  = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['user']                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_group']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['location_city']          = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache'));
        $this->_viewModel['location_district']      = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 2), array('task' => 'cache'));
        $this->_viewModel['location_town']          = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 3), array('task' => 'cache'));
        $this->_viewModel['shippers']               = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'shipper')), array('task' => 'cache'));

        $this->_viewModel['status_check']           = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'ghtk-status')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['status_check_vtp']       = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'viettel-status')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['status_accounting']      = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-acounting')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['status_sales']           = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['caption']                = 'Đơn hàng - Danh sách';

        return new ViewModel($this->_viewModel);
    }

    // Thêm mới đơn hàng theo sản phẩm mới
    public function addKovAction() {
        $this->_params['userInfo'] = $this->_userInfo->getUserInfo();
        $numberFormat = new \ZendX\Functions\Number();
//        $myForm = $this->getForm();
        $myForm = new \Admin\Form\Contract($this, $this->_params);

        $contact_item = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $this->params('id')));
        $sales_manager = $this->getServiceLocator()->get('Admin\Model\UserTable')->getItem(array('id' => $contact_item['user_id']));
        if(empty($contact_item)){
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'lock', 'type' => 'not-found'));
        }
        
        if($this->getRequest()->isPost()){
            $myForm->setInputFilter(new \Admin\Filter\Contract(array('data' => $this->_params['data'], 'route' => $this->_params['route'])));
            $myForm->setData($this->_params['data']);
            $controlAction = $this->_params['data']['control-action'];
            $productList = $this->_params['data']['contract_product'];

            if($myForm->isValid()){
                $contract_product = $this->_params['data']['contract_product'];
                $check_emty_data = !empty($contract_product) ? true : false;

                // Lấy ra đơn hàng chưa thành công của khách hàng đang lên đơn
                $contract_coincider = $this->getTable()->listItem(['phone' => $this->_params['data']['phone'], 'ghtk_status_not_success'=> true], array('task' => 'list-params'))->toArray();
                if(!empty($contract_coincider)){
                    $this->_params['data']['coincider_code']    = $contract_coincider[0]['code'];
                }
                for ($i = 0; $i < count($contract_product['product_id']); $i++ ){
                    if(
                        trim($contract_product['product_id'][$i]) == "" ||
                        trim($contract_product['car_year'][$i]) == "" ||
                        (int)trim($contract_product['length'][$i]) == 0 ||
                        (int)trim($contract_product['width'][$i]) == 0 ||
                        (int)trim($contract_product['height'][$i]) == 0 ||
//                        (int)trim($contract_product['price'][$i]) == 0 ||
                        trim($contract_product['price'][$i]) == "" ||
                        (int)trim($contract_product['numbers'][$i]) == 0
                    )$check_emty_data = false;
                }

                if($check_emty_data){
//                    $contact_item = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('phone' => $this->_params['data']['phone']),['task'=>'by-phone']);
//                    if(empty($contact_item)){
//                        $contact_id_new = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($this->_params,['task'=>'add-item']);
//                        $contact_item = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contact_id_new));
//                    }
                    $this->_params['item'] = $contact_item;

                    // TẠO ĐƠN HÀNG LÊN API
                    $result_kov = $this->createOrderKov($this->_params['data']);
                    if((int)$result_kov['id']){
                        $this->_params['data']['id_kov']  = $result_kov['id'];
                        $this->_params['data']['kov_code']  = $result_kov['code'];

                        $result = $this->getTable()->saveItem($this->_params, array('task' => 'add-kov-item'));
                        $this->flashMessenger()->addMessage('Dữ liệu đã được cập nhật thành công');

                        // cập nhật mã đơn hàng trên crm lên ghi chú đơn hàng kov
                        $contract_new = $this->getTable()->getItem(array('id' => $result));
                        $order_data['description'] = $this->_params['data']['sale_note'].'(Đơn hàng đẩy từ CRM '.$contract_new['code'].')';
                        $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/orders/'.$contract_new['id_kov'], $order_data, 'PUT');

                        
                        if($controlAction == 'save-new') {
                            $this->goRoute(array('action' => 'add-kov'));
                        } else if($controlAction == 'save') {
                            $this->goRoute();
                        } else {
                            $this->goRoute();
                        }
                    }
                    else{
                        $mesage = $result_kov['responseStatus']['message'];
                        $this->_viewModel['check_product_id'] = 'Đồng bộ đơn hàng lên hệ thống Kiotviet thất bại: '.$mesage;
                        $this->_viewModel['productList'] = $this->_params['data']['contract_product'];
                        $this->_viewModel['data']  = $this->_params['data'];
                    }
                }
                else{
                    $this->_viewModel['check_product_id'] = 'Cần nhập đầy đủ thông tin của sản phẩm';
                    $this->_viewModel['productList'] = $this->_params['data']['contract_product'];
                    $this->_viewModel['data']  = $this->_params['data'];
                }
            }
            else {
                $this->_viewModel['productList']  = $productList;
                $this->_viewModel['data']  = $this->_params['data'];
            }
        }
        else{
            $this->_viewModel['contactPhone']   = $contact_item['phone'];
            $this->_viewModel['contactId']      = $contact_item['id'];
        }

        $categories = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/categories?pageSize=100&hierachicalData=true');
        $categories = json_decode($categories, true)['data'];
        $this->_viewModel['categories'] = $this->getNameCat($this->addNew($categories), $result);
        
        $this->_viewModel['myForm']	        = $myForm;
        $this->_viewModel['caption']        = 'Tạo đơn hàng';
        return new ViewModel($this->_viewModel);
    }

    // Sửa Đơn hàng
    public function editKovAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $numberFormat = new \ZendX\Functions\Number();

//        $myForm = new \Admin\Form\Contract\ContractEditKov($this->getServiceLocator(), $this->_params);
        $myForm = new \Admin\Form\Contract($this, $this->_params);
        if(!empty($this->params('id'))) {
            $id = $this->params('id');
            $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $id));
            $contract_options = !empty($contract['options']) ? unserialize($contract['options']) : array();
            $contract['date'] = $dateFormat->formatToView($contract['date']);
            $contract = array_merge($contract, $contract_options);

            $contact_old = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']));
            $myForm->setData($contract);
            $this->_viewModel['contract']           = $contract;
            $this->_viewModel['option_product']     = $contract_options['product'];
            if($contract['lock']){
                return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'lock', 'type' => 'not-found'));
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'not-found'));
        }

        // nếu đon hàng đã gửi sang giao hàng tiết kiệm thì không cho sửa nữa
        $curent_user = $this->_userInfo->getUserInfo();
        $permission_ids = explode(',', $curent_user['permission_ids']);

//        if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids) && !empty($contract['send_ghtk'])){
        if(!empty($contract['ghtk_code'])){
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'lock', 'type' => 'not-found'));
        }

        if($this->getRequest()->isPost()){
//            $myForm->setInputFilter(new \Admin\Filter\ContractEditKov(array('data' => $this->_params['data'], 'route' => $this->_params['route'])));
            $myForm->setInputFilter(new \Admin\Filter\Contract(array('data' => $this->_params['data'], 'route' => $this->_params['route'])));
            $myForm->setData($this->_params['data']);
            $controlAction = $this->_params['data']['control-action'];
            if($myForm->isValid()){
                $this->_params['item'] = $contract;
                $this->_params['contact_old'] = $contact_old;

                $contract_product = $this->_params['data']['contract_product'];
                $check_emty_data = true;// kiểm tra thông tin sản phẩm của đơn hàng đã đầy đủ chưa

                for ($i = 0; $i < count($contract_product['product_id']); $i++ ){
                    if(
                        trim($contract_product['product_id'][$i]) == "" ||
                        trim($contract_product['car_year'][$i]) == "" ||
                        (int)trim($contract_product['length'][$i]) == 0 ||
                        (int)trim($contract_product['width'][$i]) == 0 ||
                        (int)trim($contract_product['height'][$i]) == 0 ||
//                        (int)trim($contract_product['price'][$i]) == 0 ||
                        trim($contract_product['price'][$i]) == "" ||
                        (int)trim($contract_product['numbers'][$i]) == 0
                    )$check_emty_data = false;
                }
                if($check_emty_data){
//                    $contact_new = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('phone' => $this->_params['data']['phone']),['task'=>'by-phone']);
//                    if(empty($contact_new)){
//                        $contact_id_new = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($this->_params,['task'=>'add-item']);
//                        $contact_new = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contact_id_new));
//                    }
//                    $this->_params['contact_new'] = $contact_new;
                    $this->_params['contact_new'] = $contact_old;

                    $result_kov = $this->createOrderKov($this->_params['data'], 'PUT');
                    if((int)$result_kov['id']) {
                        // Lấy ra đơn hàng chưa thành công của khách hàng đang lên đơn
                        $contract_coincider = $this->getTable()->listItem(['phone' => $this->_params['data']['phone'], 'ghtk_status_not_success'=> true, 'not_id'=> $id], array('task' => 'list-params'))->toArray();
                        if(!empty($contract_coincider)){
                            $this->_params['data']['coincider_code']    = $contract_coincider[0]['code'];
                        }

                        $result = $this->getTable()->saveItem($this->_params, array('task' => 'edit-kov-item'));
                        $this->flashMessenger()->addMessage('Dữ liệu đã được cập nhật thành công');

                        // cập nhật mã đơn hàng trên crm lên ghi chú đơn hàng kov
                        $contract_new = $this->getTable()->getItem(array('id' => $result));
                        $order_data['description'] = $this->_params['data']['sale_note'].'(Đơn hàng đẩy từ CRM '.$contract_new['code'].')';
                        $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/orders/'.$contract_new['id_kov'], $order_data, 'PUT');

                        if ($controlAction == 'save-new') {
                            $this->goRoute(array('action' => 'add-kov'));
                        } else if ($controlAction == 'save') {
                            $this->goRoute();
                        } else {
                            $this->goRoute();
                        }
                    }
                    else{
                        $mesage = $result_kov['responseStatus']['message'];
                        $this->_viewModel['check_product_id'] = 'Đồng bộ đơn hàng lên hệ thống Kiotviet thất bại: '.$mesage;
                        $this->_viewModel['productList'] = $this->_params['data']['contract_product'];
                    }
                }
                else{
                    $this->_viewModel['check_product_id'] = 'Cần nhập đầy đủ thông tin của sản phẩm';
                    $this->_viewModel['productList'] = $this->_params['data']['contract_product'];
                    $this->_viewModel['total_contract_vat'] = $this->_params['data']['total_contract_vat'];
                }
            }
            else {
                $this->_viewModel['productList']  = $this->_params['data']['contract_product'];
                $this->_viewModel['total_contract_vat']  = $this->_params['data']['total_contract_vat'];
            }
        }

        $categories = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/categories?pageSize=100&hierachicalData=true');
        $categories = json_decode($categories, true)['data'];
        $this->_viewModel['categories'] = $this->getNameCat($this->addNew($categories), $result);

        $this->_viewModel['myForm']	        = $myForm;
        $this->_viewModel['caption']        = 'Sửa đơn hàng '.$contract['code'];
        return new ViewModel($this->_viewModel);
    }

    // Sửa Đơn hàng
    public function editProductAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $numberFormat = new \ZendX\Functions\Number();

//        $myForm = new \Admin\Form\Contract\ContractEditKov($this->getServiceLocator(), $this->_params);
        $myForm = new \Admin\Form\Contract($this, $this->_params);
        if(!empty($this->params('id'))) {
            $id = $this->params('id');
            $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $id));
            
            $contract_options = !empty($contract['options']) ? unserialize($contract['options']) : array();
            $contract['date'] = $dateFormat->formatToView($contract['date']);
            $contract = array_merge($contract, $contract_options);

            $contact_old = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']));
            $myForm->setData($contract);
            $this->_viewModel['contract']           = $contract;
            $this->_viewModel['option_product']     = $contract_options['product'];
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'not-found'));
        }

        // nếu đon hàng đã gửi sang giao hàng tiết kiệm thì không cho sửa nữa
        $curent_user = $this->_userInfo->getUserInfo();
        $permission_ids = explode(',', $curent_user['permission_ids']);

        if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids) && !empty($contract['send_ghtk'])){
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'lock', 'type' => 'not-found'));
        }

        if($this->getRequest()->isPost()){
            $myForm->setInputFilter(new \Admin\Filter\ContractEditKov(array('data' => $this->_params['data'], 'route' => $this->_params['route'])));
            $myForm->setData($this->_params['data']);
            $controlAction = $this->_params['data']['control-action'];
            if($myForm->isValid()){
                $this->_params['item'] = $contract;
                $this->_params['contact_old'] = $contact_old;

                $contract_product = $this->_params['data']['contract_product'];
                $check_emty_data = true;// kiểm tra thông tin sản phẩm của đơn hàng đã đầy đủ chưa

                for ($i = 0; $i < count($contract_product['product_id']); $i++ ){
                    if(
                        trim($contract_product['product_id'][$i]) == "" ||
                        trim($contract_product['car_year'][$i]) == "" ||
                        (int)trim($contract_product['length'][$i]) == 0 ||
                        (int)trim($contract_product['width'][$i]) == 0 ||
                        (int)trim($contract_product['height'][$i]) == 0 ||
//                        (int)trim($contract_product['price'][$i]) == 0 ||
                        trim($contract_product['price'][$i]) == "" ||
                        (int)trim($contract_product['numbers'][$i]) == 0
                    )$check_emty_data = false;
                }
                if($check_emty_data){
                    $contact_new = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('phone' => $this->_params['data']['phone']),['task'=>'by-phone']);
                    if(empty($contact_new)){
                        $contact_id_new = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($this->_params,['task'=>'add-item']);
                        $contact_new = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contact_id_new));
                    }
                    $this->_params['contact_new'] = $contact_new;
                    // Lấy ra đơn hàng chưa thành công của khách hàng đang lên đơn
                    $contract_coincider = $this->getTable()->listItem(['phone' => $this->_params['data']['phone'], 'ghtk_status_not_success'=> true, 'not_id'=> $id], array('task' => 'list-params'))->toArray();
                    if(!empty($contract_coincider)){
                        $this->_params['data']['coincider_code']    = $contract_coincider[0]['code'];
                    }

                    $result = $this->getTable()->saveItem($this->_params, array('task' => 'edit-product-price'));
                    $this->flashMessenger()->addMessage('Dữ liệu đã được cập nhật thành công');

                    if ($controlAction == 'save-new') {
                        $this->goRoute(array('action' => 'add-kov'));
                    } else if ($controlAction == 'save') {
                        $this->goRoute();
                    } else {
                        $this->goRoute();
                    }
                }
                else{
                    $this->_viewModel['check_product_id'] = 'Cần nhập đầy đủ thông tin của sản phẩm';
                    $this->_viewModel['productList'] = $this->_params['data']['contract_product'];
                    $this->_viewModel['total_contract_vat'] = $this->_params['data']['total_contract_vat'];
                }
            }
            else {
                $this->_viewModel['productList']  = $this->_params['data']['contract_product'];
                $this->_viewModel['total_contract_vat']  = $this->_params['data']['total_contract_vat'];
            }
        }

        $categories = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/categories?pageSize=100&hierachicalData=true');
        $categories = json_decode($categories, true)['data'];
        $this->_viewModel['categories'] = $this->getNameCat($this->addNew($categories), $result);

        $this->_viewModel['myForm']	        = $myForm;
        $this->_viewModel['caption']        = 'Cập nhật giá bán đơn hàng '.$contract['code'];
        return new ViewModel($this->_viewModel);
    }

    public function createOrderKov($params, $method = 'POST'){
        $numberFormat = new \ZendX\Functions\Number();

        // Lấy thông tin chi tiết đơn hàng
        $contract_product = $params['contract_product'];
        $contract_product['unit_type'] = array_values($contract_product['unit_type']);
        for($i = 0; $i < count($contract_product['product_id']); $i++){
            if(!empty($contract_product['product_id'][$i])) {
                $product_add[$i]['productId'] = (int)$contract_product['product_id'][$i];
                $product_add[$i]['productCode'] = $contract_product['code'][$i];
                $product_add[$i]['productName'] = $contract_product['full_name'][$i];
                $product_add[$i]['quantity'] = (int)$contract_product['numbers'][$i];
                $product_add[$i]['price'] = $numberFormat->formatToData($contract_product['price'][$i]);
//                $product_add[$i]['price'] = 0;
                $product_add[$i]['note'] = '';
            }
        }
        $surchages = array(
            array(
                'code'  => 'Thuship',
                'price' => $numberFormat->formatToData($params['fee_other']),
            ),
            array(
                'code'  => 'VAT',
                'price' => $numberFormat->formatToData($params['total_contract_vat']),
            ),
        );

        $order_data['branchId']         = (int)$this->_userInfo->getUserInfo('kov_branch_id');
        $order_data['description']      = $params['sale_note'] .'(Đơn hàng đẩy từ CRM)';
        $order_data['orderDetails']     = $product_add;
        $order_data['discount']         = $numberFormat->formatToData($params['total_contract_discount']);
        $order_data['surchages']        = $surchages;

        // Không đồ bộ thanh toán lên kiotviet
        if(!empty($params['price_deposits']) && $method == 'POST'){
            $order_data['totalPayment'] = $numberFormat->formatToData($params['price_deposits']);
            $order_data['method'] = 'Transfer';
        }

        $order_id = '';
        if($method == 'PUT'){
            $contract = $this->getTable()->getItem(['id' => $params['id']]);
            $order_id = '/'.$contract['id_kov'];
        }

        $result_kov = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/orders'.$order_id, $order_data, $method);
        return json_decode($result_kov, true);
    }

    // Xem chi tiết Đơn hàng
    public function viewAction() {
        if(!empty($this->_params['data']['id'])) {
            $item = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->_params['data']['id']));
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }

        $notifi = $this->getServiceLocator()->get('Admin\Model\NotifiTable')->getItem(array('link' => $this->_params['data']['id']), array('task' => 'link'));
        if(!empty($notifi)){
            $notifi_user = $this->getServiceLocator()->get('Admin\Model\NotifiUserTable')->getItem(array('user_id' => $this->_userInfo->getUserInfo('id'), 'notifi_id' => $notifi->id), array('task' => 'notifi'));
            if(!empty($notifi_user)){
                $this->getServiceLocator()->get('Admin\Model\NotifiUserTable')->changeStatus(['data' => array("cid" => [$notifi_user->id], "status" => 0)], array('task' => 'change-status'));
            }
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
        $this->_viewModel['kovProduct']                 = $this->getServiceLocator()->get('Admin\Model\KovProductsTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['caption']                    = 'Xem chi tiết đơn hàng';
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
        
        return $viewModel;
    }
    
    // Xem nhanh Đơn hàng
    public function quickViewAction() {
        if(!empty($this->_params['data']['id'])) {
            $item = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->_params['data']['id']));
        } else {
            //return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
    
        $this->_viewModel['item']                       = $item;
        $this->_viewModel['contact']                    = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $item['contact_id']));
        $this->_viewModel['user']                       = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_group']                 = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']                = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['sale_history_action']        = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-action')), array('task' => 'cache'));
        $this->_viewModel['sale_history_result']        = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-result')), array('task' => 'cache'));
        $this->_viewModel['sale_source_group']          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-source-group')), array('task' => 'cache'));
        $this->_viewModel['sale_contact_type']          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contact-type')), array('task' => 'cache-alias'));
        $this->_viewModel['sale_contact_subject']       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contact-subject')), array('task' => 'cache-alias'));
        $this->_viewModel['sale_lost']                  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-lost')), array('task' => 'cache'));
        $this->_viewModel['location_city']              = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'location-city')), array('task' => 'cache'));
        $this->_viewModel['location_district']          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'location-district')), array('task' => 'cache'));
        $this->_viewModel['product_interest']           = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-interest')), array('task' => 'cache'));
        $this->_viewModel['product']                    = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache-active'));
        $this->_viewModel['sex']                        = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sex')), array('task' => 'cache-alias'));
        $this->_viewModel['caption']                    = 'Xem chi tiết Đơn hàng';
        
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
        
        return $viewModel;
    }

    public function quickProductAction() {
        if(!empty($this->_params['data']['id'])) {
            $item = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/products/'.$this->_params['data']['id']);
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
        $product = json_decode($item, true);
        $inventories = $product['inventories'];
        if(!empty($inventories)){
            $this->_viewModel['inventories'] = $inventories;
        }

        $this->_viewModel['caption'] = 'Chi tiết sản phẩm';
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }
    
    // Sửa Đơn hàng
    public function editAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $numberFormat = new \ZendX\Functions\Number();
        $myForm = new \Admin\Form\Contract\Edit($this->getServiceLocator(), $this->_params);
        
        
        if(!empty($this->_params['data']['id'])) {
            $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->_params['data']['id']));
            $contract_options = !empty($contract['options']) ? unserialize($contract['options']) : array();
            $contract = array_merge($contract, $contract_options);
            $contract['date'] = $dateFormat->formatToView($contract['date']);
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
                $myForm->setInputFilter(new \Admin\Filter\Contract\Edit($this->_params));
                $myForm->setData($this->_params['data']);

                if($myForm->isValid()){
                    $this->_params['item'] = $contract;
                    $this->_params['contact'] = $contact;

                    $contract_product = $this->_params['data']['contract_product'];
                    $check_emty_data = true;// kiểm tra thông tin sản phẩm của đơn hàng đã đầy đủ chưa
                    $check_order = true; // kiểm tra đơn hàng có sản phẩm có gia bán nhỏ hơn giá niêm yết không.

                    for ($i = 0; $i <= count($contract_product['product_id']) - 1; $i++ ){
                        if(
                            trim($contract_product['product_id'][$i]) == "" ||
                            trim($contract_product['product_name'][$i]) == "" ||
                            trim($contract_product['carpet_color_id'][$i]) == "" ||
                            trim($contract_product['tangled_color_id'][$i]) == "" ||
                            trim($contract_product['flooring_id'][$i]) == "" ||
                            trim($contract_product['price'][$i]) == "" ||
                            trim($contract_product['vat'][$i] == "")
                        )$check_emty_data = false;

                        // kiểm tra đơn hàng có sản phẩm nào bán giá nhỏ hơn giá niêm yết không.
                        $listed_price = $numberFormat->formatToData(trim($contract_product['listed_price'][$i]));
                        $pr_price = $numberFormat->formatToData(trim($contract_product['price'][$i]));
                        if($pr_price < $listed_price){
                            $check_order = false;
                        }
                    }

                    // Tạo thông báo khi có sản phẩm bán sai giá niêm yết
                    if($check_order == false){
                        $notifi = $this->getServiceLocator()->get('Admin\Model\NotifiTable')->getItem(array('link' => $this->_params['data']['id']), array('task' => 'link'));
                        if(!empty($notifi)){
                            $notifi_user = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\NotifiUserTable')->listItem(array('ssFilter' => array('filter_status' => 1, 'filter_notifi_id' => $notifi->id)), array('task' => 'list-all')), array('key' => 'id', 'value' => 'id'));
                            if(!empty($notifi_user)){
                                $this->getServiceLocator()->get('Admin\Model\NotifiUserTable')->changeStatus(['data' => array("cid" => array_values($notifi_user), "status" => 1)], array('task' => 'change-status'));
                            }
                        }
                        else{
                            $user_notifi_branch = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(['data' => ['status' => 1, 'notifi' => 'branch', 'sale_branch_id' => $contract['sale_branch_id']]], array('task' => 'list-all')) , array('key' => 'id', 'value' => 'id'));
                            $user_notifi_all    = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(['data' => ['status' => 1, 'notifi' => 'all']], array('task' => 'list-all')) , array('key' => 'id', 'value' => 'id'));
                            $users_notifi       = array_merge(array_values($user_notifi_branch), array_values($user_notifi_all));

                            if(count($users_notifi > 0)){
                                $data_notifi['data'] = array(
                                    'content' => "Đơn hàng ".$contract['code']." có sản phẩm bán giá nhỏ hơn giá niêm yết",
                                    'link' => $contract['id'],
                                );
                                $notifi_id = $this->getServiceLocator()->get('Admin\Model\NotifiTable')->saveItem($data_notifi, array('task' => 'add-item'));
                                if($notifi_id){
                                    foreach($users_notifi as $uid){
                                        $data_notifi_user['data'] = array(
                                            'user_id' => $uid,
                                            'notifi_id' => $notifi_id,
                                        );
                                        $this->getServiceLocator()->get('Admin\Model\NotifiUserTable')->saveItem($data_notifi_user, array('task' => 'add-item'));
                                    }
                                }
                            }
                        }
                    }

                    if($check_emty_data){
                        $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params, array('task' => 'edit-item'));
                        $this->flashMessenger()->addMessage('Cập nhật dữ liệu thành công');
                        echo 'success';
                        return $this->response;
                    }
                    else{
                        $this->_viewModel['check_product_id'] = 'Cần nhập đầy đủ thông tin của sản phẩm';
                    }
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
        $this->_viewModel['caption']        = 'Sửa Đơn hàng';

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }
    
    // Sửa ưu đãi
    public function editPromotionAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $numberFormat = new \ZendX\Functions\Number();
        $myForm = new \Admin\Form\Contract\EditPromotion($this->getServiceLocator(), $this->_params);
    
        if(!empty($this->_params['data']['id'])) {
            $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->_params['data']['id']));
            $contract_options = !empty($contract['options']) ? unserialize($contract['options']) : array();
            $contract = array_merge($contract, $contract_options);
            $contract['date'] = $dateFormat->formatToView($contract['date']);
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
                $myForm->setInputFilter(new \Admin\Filter\Contract\EditPromotion($this->_params));
                $myForm->setData($this->_params['data']);
    
                if($myForm->isValid()){
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['item'] = $contract;
                    $this->_params['contact'] = $contact;
    
                    // Tính lại giá tiền khi thay đổi sản phẩm
                    $price = $numberFormat->formatToNumber($this->_params['data']['price']);
                    $price_promotion = 0;
                    $price_promotion_percent = $numberFormat->formatToNumber($this->_params['data']['price_promotion_percent']);
                    $price_promotion_price = $numberFormat->formatToNumber($this->_params['data']['price_promotion_price']);
                    $price_paid = $contract['price_paid'];
                    $price_accrued = $contract['price_accrued'];
    
                    if(!empty($this->_params['data']['price_promotion_percent'])) {
                        $price_promotion = $numberFormat->formatToNumber($this->_params['data']['price_promotion_percent']) / 100 * $price;
                    }
                    if(!empty($this->_params['data']['price_promotion_price'])) {
                        $price_promotion = $price_promotion + $numberFormat->formatToNumber($this->_params['data']['price_promotion_price']);
                    }
    
                    $price_total = $price - $price_promotion;
                    $price_owed = $price_total - $price_paid + $price_accrued;
    
                    $this->_params['data']['price'] = $price;
                    $this->_params['data']['price_promotion'] = $price_promotion;
                    $this->_params['data']['price_promotion_percent'] = $price_promotion_percent;
                    $this->_params['data']['price_promotion_price'] = $price_promotion_price;
                    $this->_params['data']['price_total'] = $price_total;
                    $this->_params['data']['price_owed'] = $price_owed;
    
                    $result = $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params, array('task' => 'edit-item'));
    
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
        $this->_viewModel['caption']    = 'Sửa ưu đãi';
    
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }

    // Sửa ghi chú
    public function editNoteAction() {
        $myForm = new \Admin\Form\Contract\EditNote($this->getServiceLocator(), $this->_params);

        if(!empty($this->_params['data']['id'])) {
            $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->_params['data']['id']));
            $contract_options = !empty($contract['options']) ? unserialize($contract['options']) : array();
            $myForm->setData($contract_options);

            if($contract['lock']){
                return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'lock', 'type' => 'modal'));
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }

        if($this->getRequest()->isPost()){
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\Contract\EditNote($this->_params));
                $myForm->setData($this->_params['data']);

                if($myForm->isValid()){
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['item'] = $contract;

                    $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params, array('task' => 'update-note'));

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
        $this->_viewModel['caption']    = 'Sửa ghi chú';

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Khôi phục đơn hàng đã xóa
    public function restoreAction() {
        $myForm = new \Admin\Form\Contract\EditNote($this->getServiceLocator(), $this->_params);

        if(!empty($this->_params['data']['id'])) {
            $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->_params['data']['id']));

            if($contract['lock']){
                return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'lock', 'type' => 'modal'));
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }

        if($this->getRequest()->isPost()){
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\Contract\EditNote($this->_params));
                $myForm->setData($this->_params['data']);

                if($myForm->isValid()){
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['item'] = $contract;

                    $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params, array('task' => 'show-delete'));

                    $this->flashMessenger()->addMessage('Khôi phục đơn hàng thành công');
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
        $this->_viewModel['caption']    = 'Khôi phục đơn hàng đã xóa';

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // cập nhật trạng thái sale (với những đơn chưa có trạng thái của các bộ phận khác mới được cập nhật).
    public function editStatusAction() {
        $myForm = new \Admin\Form\Contract\EditStatus($this->getServiceLocator(), $this->_params);

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
                $myForm->setInputFilter(new \Admin\Filter\Contract\EditStatus($this->_params));
                $myForm->setData($this->_params['data']);

                if($myForm->isValid()){
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    if($contract['unit_transport'] != 'ghtk'){
                        $this->getTable()->updateItem($this->_params, array('task' => 'update-ghtk'));
                        $this->flashMessenger()->addMessage('Cập nhật dữ liệu thành công');
                    }
                    else{
                        $this->flashMessenger()->addMessage('Chỉ đơn hàng lẻ mới có thể cập nhật trạng thái giục đơn');
                    }

                    echo 'success';
                    return $this->response;
                }
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }

        $this->_viewModel['myForm']     = $myForm;
        $this->_viewModel['contract']   = $contract;
        $this->_viewModel['caption']    = 'Sửa trạng thái giục đơn';

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Chuyển đơn hàng sang đơn hàng lẻ
    public function convertOrderAction() {
        $myForm = new \Admin\Form\Contract\ConvertOrder($this->getServiceLocator(), $this->_params);

        if(!empty($this->_params['data']['id'])) {
            $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->_params['data']['id']));
            $myForm->setData($contract);

//            if($contract['lock'] || $contract['ghtk_code'] || $contract['ghtk_status'] || $contract['ghtk_result']){
//                return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'lock', 'type' => 'modal'));
//            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }

        if($this->getRequest()->isPost()){
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\Contract\ConvertOrder($this->_params));
                $myForm->setData($this->_params['data']);

                if($myForm->isValid()){
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['item'] = $contract;

                    $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params, array('task' => 'convert-order'));

                    $this->flashMessenger()->addMessage('Cập nhật dữ liệu thành công');
                    echo 'print';
                    return $this->response;
                }
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }

        $this->_viewModel['myForm']     = $myForm;
        $this->_viewModel['contract']   = $contract;
        $this->_viewModel['data']   = $this->_params['data'];
        $this->_viewModel['caption']    = 'Sửa trạng thái';

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }
    
    // Xóa Đơn hàng
    public function deleteAction() {
        $item = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->params('id')));

        if($item['lock']){
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'lock'));
        }
    
        if(empty($item)) {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
    
        if($this->getRequest()->isPost()) {
            // Xóa hoa đồng
            $this->_params['item'] = $item;
            $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/orders/' . $item['id_kov'], null, 'DELETE' );
            // Xóa đơn hàng
//            $contract_delete = $this->getTable()->deleteItem($this->_params, array('task' => 'delete-item'));
            // Xóa tạm thời
            $contract_delete = $this->getTable()->deleteItem($this->_params, array('task' => 'delete-hidden'));

            $this->flashMessenger()->addMessage('Xóa Đơn hàng thành công');
    
            $this->goRoute();
        }
    
        $this->_viewModel['item']               = $item;
        $this->_viewModel['contact']            = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $item['contact_id']));
        $this->_viewModel['user']               = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_group']         = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']        = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['sale_source_group']  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-source-group')), array('task' => 'cache'));
        $this->_viewModel['sex']                = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sex')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['product']            = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache-active'));
        $this->_viewModel['caption']            = 'Đơn hàng - Xóa';
        return new ViewModel($this->_viewModel);
    }

    // Xóa Đơn hàng
    public function cancelAction() {
        $item = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->params('id')));

        if($item['lock']){
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'lock'));
        }
        if(!empty($item['ghtk_code'])){
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'lock'));
        }

        if(empty($item)) {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }

        if($this->getRequest()->isPost()) {
            $this->_params['item'] = $item;
            $a = json_decode($this->kiotviet_call(RETAILER, $this->kiotviet_token, '/orders/' . $item['id_kov'], null, 'DELETE' ), true);
            $msg = 'Hủy sales thành công';
            if(isset($a['responseStatus'])){
                $msg = $a['responseStatus']['message'];
            }
            else{
                $this->getTable()->deleteItem($this->_params, array('task' => 'cancel'));
            }
            $this->flashMessenger()->addMessage($msg);
            $this->goRoute();
        }

        $this->_viewModel['item']               = $item;
        $this->_viewModel['contact']            = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $item['contact_id']));
        $this->_viewModel['user']               = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_group']         = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']        = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['sale_source_group']  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-source-group')), array('task' => 'cache'));
        $this->_viewModel['sex']                = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sex')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['product']            = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache-active'));
        $this->_viewModel['caption']            = 'Đơn hàng - Hủy sale';
        return new ViewModel($this->_viewModel);
    }
    
    // Thêm hóa đơn
    public function billAddAction() {
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
                        $this->getServiceLocator()->get('Admin\Model\BillTable')->saveItem($this->_params, array('task' => 'add-item'));
                        
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

                        $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem(array('data' => $arrContract), array('task' => 'update-bill-add'));
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
    
    // Sửa hóa đơn
    public function billEditAction() {
        $dateFormat = new \ZendX\Functions\Date();
        
        if(!empty($this->_params['data']['id'])) {
            $item       = $this->getServiceLocator()->get('Admin\Model\BillTable')->getItem(array('id' => $this->_params['data']['id']));
            $contract   = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $item['contract_id']));
            $contact    = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']));

            if($contract['lock']){
                return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'lock', 'type' => 'modal'));
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }
        
        if($this->getRequest()->isPost()){
            $myForm = new \Admin\Form\Contract\BillEdit($this->getServiceLocator());
            $myForm->setInputFilter(new \Admin\Filter\Contract\BillEdit(array('data' => $this->_params['data'], 'item' => $item)));
            if ($item['type'] == 'Thu') {
                $caption = 'Sửa phiếu thu';
                $message = 'Sửa phiếu thu thành công';
            } elseif ($item['type'] == 'Chi') {
                $caption = 'Sửa phiếu chi';
                $message = 'Sửa phiếu chi thành công';
            }
            
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setData($this->_params['data']);
                if($myForm->isValid()){
                    $this->_params['data']      = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['contract']  = $contract;
                    $this->_params['contact']   = $contact;
                    $this->_params['item']      = $item;
                    
                    $result = $this->getServiceLocator()->get('Admin\Model\BillTable')->saveItem($this->_params, array('task' => 'contract-edit-item'));
            
                    $this->flashMessenger()->addMessage($message);
                    echo 'success';
                    return $this->response;
                }
            } else {
                $item_options = !empty($item['options']) ? unserialize($item['options']) : array();
                $item = array_merge($item, $item_options);
                $item['date'] = $dateFormat->formatToView($item['date']);
                
                $myForm->setData($item);
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
        
        $this->_viewModel['myForm']     = $myForm;
        $this->_viewModel['caption']    = $caption;
        $this->_viewModel['item']       = $item;
        $this->_viewModel['contract']   = $contract;
        
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
        
        return $viewModel;
    }
    
    // Xóa hóa đơn
    public function billDeleteAction() {
        if(!empty($this->_params['data']['id'])) {
            $item       = $this->getServiceLocator()->get('Admin\Model\BillTable')->getItem(array('id' => $this->_params['data']['id']));
            $contract   = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $item['contract_id']));
            $contact    = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']));

            if($contract['lock']){
                return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'lock', 'type' => 'modal'));
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }
    
        if($this->getRequest()->isPost()){
            $myForm = new \Admin\Form\Contract\BillDelete($this->getServiceLocator());
            $myForm->setInputFilter(new \Admin\Filter\Contract\BillDelete($this->_params));
            $myForm->setData($item);
            
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setData($this->_params['data']);
                if($myForm->isValid()){
                    $this->_params['data']      = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['contract']  = $contract;
                    $this->_params['contact']   = $contact;
                    $this->_params['item']      = $item;
    
                    $result = $this->getServiceLocator()->get('Admin\Model\BillTable')->deleteItem($this->_params, array('task' => 'contract-delete-item'));
    
                    $this->flashMessenger()->addMessage('Xóa hóa đơn thành công');
                    echo 'success';
                    return $this->response;
                }
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
    
        $this->_viewModel['myForm']         = $myForm;
        $this->_viewModel['caption']        = 'Xóa hóa đơn';
        $this->_viewModel['item']           = $item;
        $this->_viewModel['contract']       = $contract;
        $this->_viewModel['bill_type']      = array('paid' => 'Thu', 'accrued' => 'Chi', 'surcharge' => 'Phụ phí');
        $this->_viewModel['paid_type']      = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "table" => "document", "where" => array( "code" => "bill-type-paid" ), "order" => array("ordering" => "ASC", "created" => "ASC", "name" => "ASC"), "view"  => array( "key" => "id", "value" => "name", "sprintf" => "%s" ) ), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['accrued_type']   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "table" => "document", "where" => array( "code" => "bill-type-accrued" ), "order" => array("ordering" => "ASC", "created" => "ASC", "name" => "ASC"), "view"  => array( "key" => "id", "value" => "name", "sprintf" => "%s" ) ), array('task' => 'cache'));
        $this->_viewModel['surcharge_type'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "table" => "document", "where" => array( "code" => "bill-type-surcharge" ), "order" => array("ordering" => "ASC", "created" => "ASC", "name" => "ASC"), "view"  => array( "key" => "id", "value" => "name", "sprintf" => "%s" ) ), array('task' => 'cache'));
    
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
    
    // Chuyển người quản lý
    public function changeUserAction(){
        
        if($this->getRequest()->isXmlHttpRequest()) {
            $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->_params['data']['id']), null);
            $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']), null);

            if($contract['lock']){
                return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'lock', 'type' => 'modal'));
            }
          
            if(!empty($contract)) {
                if($this->getRequest()->isPost()){
                    $bill = $this->getServiceLocator()->get('Admin\Model\BillTable')->listItem(array('data' => array('contract_id' => $contract['id'])), array('task' => 'list-all'));
                    
                    $this->_params['user'] = $this->getServiceLocator()->get('Admin\Model\UserTable')->getItem(array('id' => $this->_params['data']['user_id']));
                    $this->_params['contract'] = $contract;
                    $this->_params['contact'] = $contact;
                    $this->_params['bill'] = $bill;
    
                    $result = $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params, array('task' => 'change-user'));
                }
            }
            
            return $this->response;
        } else {
            if($this->getRequest()->isPost()){
                $myForm = new \Admin\Form\Contract\ChangeUser($this->getServiceLocator(), $this->_params);
                
                if($this->getRequest()->isPost()){
                    $items = $this->getServiceLocator()->get('Admin\Model\ContractTable')->listItem(array('ids' => $this->_params['data']['cid']), array('task' => 'list-item-multi'));
                }
                
                $this->_viewModel['myForm']	                = $myForm;
                $this->_viewModel['caption']                = 'Đơn hàng - Chuyển quyền quản lý';
                $this->_viewModel['items']                  = $items;
                $this->_viewModel['user']                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
                $this->_viewModel['sale_group']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
                $this->_viewModel['sale_branch']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
                $this->_viewModel['product']                = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache-active'));
            } else {
                return $this->redirect()->toRoute('routeAdmin/default', array('controller' => $this->_params['controller'], 'action' => 'index'));
            }
        }
    
        return new ViewModel($this->_viewModel);
    }
    
    public function printMultiAction() {
        $ids        = !empty($this->_params['data']['cid']) ? $this->_params['data']['cid'] : [$this->params('id')];
        $items      = $this->getServiceLocator()->get('Admin\Model\ContractTable')->listItem(array('ids' => $ids), array('task' => 'list-print-multi'));
        $items      = $items->toArray();
        $contact    = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $items['contact_id']), null);

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
        $this->_viewModel['carpet_color']               = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'carpet-color')), array('task' => 'cache'));
        $this->_viewModel['tangled_color']              = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'tangled-color')), array('task' => 'cache'));
        $this->_viewModel['row_seats']                  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'row-seats')), array('task' => 'cache'));
        $this->_viewModel['flooring']                   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'flooring')), array('task' => 'cache'));
        $this->_viewModel['production_department']      = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-department')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['product_type']               = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-type')), array('task' => 'cache'));
        $this->_viewModel['status']                     = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }

    public function importAction()
    {
        $myForm = new \Admin\Form\Contract\Import($this->getServiceLocator(), $this->_params);
        $myForm->setInputFilter(new \Admin\Filter\Contract\Import($this->_params));
        $this->_viewModel['caption'] = 'Đối soát giao hàng tiết kiệm';
        $this->_viewModel['myForm']  = $myForm;
        $viewModel                   = new ViewModel($this->_viewModel);

        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($this->getRequest()->isPost()) {
                if(!empty($this->_params['data']['ghtk_code'])){
                    $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('ghtk_code' => $this->_params['data']['ghtk_code']), array('task' => 'ghtk-code'));
                    if(empty($contract)){
                        $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('code' => $this->_params['data']['code']), array('task' => 'by-code'));
                    }
                    if (empty($contract)) {
                        echo json_encode(array(
                            'status'=> 1,
                            'data' => [],
                            'message' => 'Mã vận đơn không tồn tại',
                        ));
                    } else {
                        $cod_ghtk = (int)str_replace(',', '', $this->_params['data']['cod']);
                        $price_owed = (int)$contract['price_owed'];
                        $price_reduce_sale = (int)$contract['price_reduce_sale'];
                        if (($price_owed - $price_reduce_sale) != $cod_ghtk) {
                            $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem(array('item'=> $contract, 'data' => array('id' => $contract['id'], 'status_acounting_id' => 'khieu-lai', 'price_paid' => $cod_ghtk)), array('task' => 'compare-order'));
                            echo json_encode(array(
                                'status'=> 2,
                                'data' => ['price_owed' => number_format($price_owed), 'price_reduce_sale' => number_format($price_reduce_sale), 'compare' => number_format($price_owed - $price_reduce_sale) ],
                                'message' => 'Khiếu lại',
                            ));
                        } else {
                            $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem(array('item'=> $contract, 'data' => array('id' => $contract['id'], 'status_acounting_id' => 'da-doi-soat', 'price_paid' => $cod_ghtk)), array('task' => 'compare-order'));
                            echo json_encode(array(
                                'status'=> 3,
                                'data' => ['price_owed' => number_format($price_owed), 'price_reduce_sale' => number_format($price_reduce_sale), 'compare' => number_format($price_owed - $price_reduce_sale) ],
                                'message' => 'Đối soát thành công',
                            ));
                        }
                    }
                }
                else{
                    echo json_encode(array(
                        'status'=> 1,
                        'data' => [],
                        'message' => 'Mã vận đơn không tồn tại',
                    ));
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
                }
            }
        }

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
        $shipper                = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'shipper')), array('task' => 'cache')), array('key' => 'id', 'value' => 'object'));
        $products               = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache-active'));

        $carpet_color = $this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem(null, array('task' => 'cache'));
        $tangled_color          = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem(null, array('task' => 'cache'));
        $flooring               = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'flooring')), array('task' => 'cache'));
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
            array('field' => 'bill_code', 'title' => 'MÃ VẬN ĐƠN'),
            array('field' => 'date','type'=>'date', 'title' => 'NGÀY','format'=>'d/m/Y H:i:s'),
            array('field' => 'code', 'title' => 'MÃ ĐƠN HÀNG'),
            array('field' => 'user_id', 'title' => 'Nhân viên', 'type' => 'data_source', 'data_source' => $user),
            array(
                'field' =>'product',
                'title' => 'SỐ LƯỢNG + NỘI DUNG',
                'type' => 'custom_serialize',
                'data_custom_field' => 'options',
            ),
            array('field' => 'sale_note', 'title' => 'GHI CHÚ SALES', 'type' => 'options', 'option_field' => 'sale_note'),
            array('field' => 'shipper_id', 'title' => 'NV GIAO HÀNG', 'type' => 'data_source', 'data_source' => $shipper),
            array('field' => 'name', 'title' => 'TÊN NGƯỜI NHẬN'),
            array('field' => 'weight', 'title' => 'CÂN NẶNG'),
            array('field' => 'address', 'title' => 'ĐỊA CHỈ'),
            array('field' => 'phone', 'title' => 'SỐ ĐIỆN THOẠI'),
            array('field' => 'price_total', 'title' => 'TỔNG TIỀN'),
            array('field' => 'price_listed','type'=>'listed-price', 'title' => 'GIÁ NIÊM YẾT'),
            array('field' => 'transport_id', 'title' => 'DỊCH VỤ VẬN CHUYỂN', 'type' => 'data_source', 'data_source' => $transport),
            array('field' => 'note_order', 'title' => 'GHI CHÚ ĐỐI VỚI CÁC ĐƠN HÀNG ĐẦU NHẬN TT'),
            array('field' => 'view_product', 'title' => 'CHO KHÁCH XEM HÀNG'),
        );
        
        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();
        
        // Set document properties
        $objPHPExcel->getProperties()->setCreator($this->_userInfo->getUserInfo('name'))
        							 ->setLastModifiedBy($this->_userInfo->getUserInfo('username'))
        							 ->setTitle("Don_kinh_doanh_".date('d-m-Y'));
        
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
                        $value = '\''.date($formatDate,strtotime($item[$data['field']]));// $dateFormat->formatToView($item[$data['field']], $formatDate);
                        break;
                    case 'data_source':
                        $field = $data['data_source_field'] ? $data['data_source_field'] : 'name';
                        $value = $data['data_source'][$item[$data['field']]][$field];
                        break;
                    case 'listed-price':
                        $value = array_sum(array_column($options['product'],'listed_price'));
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
                    case 'options':
                        $value = $options[$data['option_field']];
                        break;
                    case 'custom_serialize':
                        $value = '';
                        $data_custom = $item[$data['data_custom_field']] ? unserialize($item[$data['data_custom_field']]) : array();
                        $key = 0;
                        foreach ($data_custom[$data['field']] AS $key_f => $value_f) {
                            $infos = [$carpet_color[$value_f['carpet_color']]['name']?:'Không Làm',$tangled_color[$value_f['tangled_color']]['name']?:'Không Làm',$flooring[$value_f['flooring_id']]['name']?:'Không Làm'];
                            $value .= (++$key).'. '.$products[$value_f['product_id']]->name.' x '.($value_f['numbers']?:1).' '.$value_f['product_name']. ', '.PHP_EOL.implode(', ',$infos) .PHP_EOL;
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
        header('Content-Disposition: attachment;filename="'.'Don_kinh_doanh_'.date('d-m-Y').'.xlsx"');
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

    # xuất file excel import vtp
    public function exportToVTPAction() {
        $dateFormat             = new \ZendX\Functions\Date();
        $items      = $this->getServiceLocator()->get('Admin\Model\ContractTable')->listItem(array('ids' => $this->_params['data']['cid']), array('task' => 'list-print-multi'))->toArray();

        $location_city          = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache'));
        $location_district      = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 2), array('task' => 'cache'));
        $location_town          = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 3), array('task' => 'cache'));
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
            array('field' => 'code', 'title' => 'Mã đơn hàng'),
            array('field' => 'name', 'title' => 'Tên người nhận(*)'),
            array('field' => 'phone', 'title' => 'Số ĐT ngươi nhận(*)'),
            array('field' => 'address', 'title' => 'Địa chỉ(*)'),
            array('field' => 'product_name', 'title' => 'Tên hàng hóa(*)'),
            array('field' => 'product_numbers', 'title' => 'Số lượng'),
            array('field' => 'product_weight', 'title' => 'Trọng lượng(gam)'),
            array('field' => 'product_price', 'title' => 'Giá trị hàng(VND)(*)'),
            array('field' => 'product_total', 'title' => 'Tiền thu hộ COD(VND)'),
            array('field' => 'product_type', 'title' => 'Loại hàng hóa)(*)'),
            array('field' => 'special', 'title' => 'Tính chất đặc biệt'),
            array('field' => 'service', 'title' => 'Dịch vụ(*)'),
            array('field' => 'service_other', 'title' => 'Dịch vụ cộng thêm'),
            array('field' => 'money', 'title' => 'Thu tiền xem hàng'),
            array('field' => 'product_length', 'title' => 'Dài(cm)'),
            array('field' => 'product_width', 'title' => 'Rộng(cm)'),
            array('field' => 'product_height', 'title' => 'Cao(cm)'),
            array('field' => 'user_fee', 'title' => 'Người trả cước'),
            array('field' => 'require_other', 'title' => 'Yêu cầu khác'),
            array('field' => 'delivery_time', 'title' => 'Thời gian giao')
        );

        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator($this->_userInfo->getUserInfo('name'))
        							 ->setLastModifiedBy($this->_userInfo->getUserInfo('username'))
        							 ->setTitle("Don_kinh_doanh_".date('d-m-Y'));

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

        foreach ($items AS $item) {
            $item['stt'] = $i;
            $options = unserialize($item['options']);
            $item['address'] = $item['address'].', '.$location_town[$item['location_town_id']]['name'].', '.$location_district[$item['location_district_id']]['name'].', '.$location_city[$item['location_city_id']]['name'];

            $item['user_fee'] = 'Người gửi trả';
            if ($item['deliver_work_shift'] == 1)
                $item['delivery_time'] = 'Buổi sáng';
            elseif ($item['deliver_work_shift'] == 2)
                $item['delivery_time'] = 'Buổi chiều';
            elseif ($item['deliver_work_shift'] == 3)
                $item['delivery_time'] = 'Buổi tối';
            else
                $item['delivery_time'] = 'Cả ngày';
            $item['product_name'] = '';
            $item['product_numbers'] = $item['product_numbers'] = $item['product_weight'] = $item['product_price'] = $item['product_total'] = $item['product_length'] = $item['product_width'] = $item['product_height'] = 0;

            foreach($options['product'] as $product){
                $item['product_name']       .= $product['full_name'].' + ';
                $item['product_numbers']    += $product['numbers'];
                $item['product_weight']     += $product['weight'];
                $item['product_price']      += $product['total'];
                $item['product_total']      += $product['total'];
                $item['product_length']     += $product['weight'] > 1 ? $product['length'] : 0;
                $item['product_width']      += $product['weight'] > 1 ? $product['width'] : 0;
                $item['product_height']     += $product['weight'] > 1 ? $product['height'] : 0;
            }
            $item['product_weight'] = $item['product_weight'] * 1000;

            $startColumn = $config['startColumn'];
            foreach ($arrData AS $key => $data) {
                $value = $item[$data['field']];
                $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[$startColumn] . $startRow, $value);
                $objPHPExcel->setActiveSheetIndex($config['sheetData'])->getStyle($arrColumn[$startColumn] . $startRow)->getAlignment()->setWrapText(true);
                $startColumn++;
            }
            $startRow++;
            $i++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.'Don_xuat_viettel_post_'.date('d-m-Y').'.xlsx"');
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

    // Cập nhật tổng số lượng sản phẩm của đơn hàng.
    public function updateTotalNumberProductAction() {
        $contracts = $this->getTable()->listItem(null, array('task' => 'list-all'));
        foreach ($contracts as $keys => $contract){
            $this->getTable()->saveItem(array('data' => $contract['id']) , array('task' => 'update-number-product-total'));
        }
        return $this->response;
    }

    public function hiddenAction() {
        $result = $this->getTable()->saveItem($this->_params, array('task' => 'hidden'));
        $this->flashMessenger()->addMessage('Ẩn '.$result.' đơn hàng thành công');
        $this->goRoute(['action' => 'warehouse']);
    }

    public function showAction() {
        $result = $this->getTable()->saveItem($this->_params, array('task' => 'show'));
        $this->flashMessenger()->addMessage('Hiển thị '.$result.' đơn hàng thành công');
        $this->goRoute(['action' => 'warehouse-hidden']);
    }

    // Sửa trạng thái thủ công - chỉ dành cho admin
    public function editStatusAccountAction() {
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

//        if($this->getRequest()->isPost()){
//            if($this->_params['data']['modal'] == 'success') {
//                $myForm->setData($this->_params['data']);
//
//                if($myForm->isValid()){
//                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
//                    $result = $this->getTable()->updateItem($this->_params, array('task' => 'update-status'));
//
//                    $this->flashMessenger()->addMessage('Cập nhật dữ liệu thành công');
//                    echo 'success';
//                    return $this->response;
//                }
//            }
//        } else {
//            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
//        }

        $this->_viewModel['myForm']     = $myForm;
        $this->_viewModel['contract']   = $contract;
        $this->_viewModel['caption']    = 'Sửa trạng thái';

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Đẩy đơn sang ghtk
//    public function sendGhtkAction() {
//        $locations = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(null, array('task' => 'cache'));
//        $contracts_type	= \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "production-type" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'alias'));
//
//        $ids = $this->_params['data']['cid'];
//        $listData_ghtk = [];
//        foreach($ids as $id){
//            $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $id));
//            if($contract['status_id'] == DA_CHOT && $contract['delete'] == 0 && $contracts_type[$contract['production_type_id']] == DON_TINH){
//                $contract['options'] = unserialize($contract['options'])['product'];
//
//                $warehouse = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(array('id' => $contract['groupaddressId']));
//                if(!empty($warehouse)){
//                    $address = explode(',', $warehouse['address']);
//                    $order_item['pick_name']        = $warehouse['name'];
//                    $order_item['pick_province']    = $address[sizeof($address)-1];
//                    $order_item['pick_district']    = $address[sizeof($address)-2];
//                    $order_item['pick_ward']        = $address[sizeof($address)-3];
//                    $order_item['pick_address']     = $address[sizeof($address)-4];
//                    $order_item['pick_tel']         = $warehouse['phone'];
//                }
////                else{
////                    $dclh_list = json_decode($this->ghtk_call('/services/shipment/list_pick_add'), true)['data'];
////                    $dclh = [];
////                    foreach($dclh_list as $key => $value){
////                        if($value['pick_address_id'] == $contract['groupaddressId']){
////                            $dclh = $value;
////                            break;
////                        }
////                    }
////                    $address_dclh = explode(',', $dclh['address']);
////
////                    $order_item['pick_name'] = $dclh['pick_name'];
////                    $order_item['pick_address'] = $dclh['address'];
////                    $order_item['pick_province'] = $address_dclh[sizeof($address_dclh)-1];
////                    $order_item['pick_district'] = $address_dclh[sizeof($address_dclh)-2];
////                    $order_item['pick_ward'] = $address_dclh[sizeof($address_dclh)-3];
////                    $order_item['pick_tel'] = $dclh['pick_tel'];
////                }
//
//                $products = [];
//                $total_weight = 0;
//                $list_name = '';
//                foreach($contract['options'] as $key => $value){
//                    $pname = $value['full_name'].' - '.$value['car_year'];
//                    $list_name .= $pname.', ';
//                    if($value['weight'] > 1){
//                        $pro['name'] = $pname;
//                        $pro['weight'] = $value['weight'];
//                        $pro['quantity'] = $value['numbers'];
//                        $pro['product_code'] = $value['code'];
//                        $pro['length'] = $value['length'];
//                        $pro['width'] = $value['width'];
//                        $pro['height'] = $value['height'];
//                        $total_weight += $value['weight'];
//
//                        $products[] = $pro;
//                    }
//                }
//                $products[0]['name'] = $list_name;
//                $listData_ghtk[$contract['code']]['products'] = $products;
//
//                $order_item['id'] = $contract['code'];
//
//                // Thông tin khách hàng ships giao hàng
//                $order_item['tel']       = $contract['phone'];
//                $order_item['name']      = $contract['name'];
//                $order_item['province']  = $locations[$contract['location_city_id']]->name;
//                $order_item['district']  = $locations[$contract['location_district_id']]->fullname;
//                $order_item['ward']      = $locations[$contract['location_town_id']]->fullname;
//                $order_item['street']    = $contract['address'];
//                $order_item['address']   = $contract['address'];
//                $order_item['hamlet']    = "Khác";
//
//                $order_item['is_freeship'] = "1";
//                $order_item['pick_money'] = $contract['price_owed']; // Tiền hàng ship phải thu
//                $order_item['note'] = $contract['ghtk_note'];
//                $order_item['value'] = $contract['price_total']; // giá trị đóng bảo hiểm
//                $order_item['transport'] = "road"; // road đường bộ, fly đường bay
//                $order_item['deliver_work_shift'] = $contract['deliver_work_shift']; // Thời gian giao hàng
//                if($total_weight >= 20){
//                    $order_item['3pl'] = 1; // Hàng theo kích thước khối lượng lớn BBS
//                }
//
//                $listData_ghtk[$contract['code']]['order'] = $order_item;
//            }
//        }
//
//        foreach ($listData_ghtk as $key => $value){
//            $result = $this->ghtk_call('/services/shipment/order/?ver=1.5', $value, 'POST');
//            $res = json_decode($result, true);
//
//            if($res['success']){
//                $contract_code_success[] = $key;
//
//                $contract_item = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('code' => $key),  array('task' => 'by-code'));
//                $arrParam['id']             = $contract_item['id'];
//                $arrParam['ghtk_code']      = $res['order']['label'];
//                $arrParam['ghtk_result']    = $res['order'];
//                $arrParam['ghtk_status']    = $res['order']['status_id'];
//                $arrParam['price_transport']= $res['order']['fee'];
//                $this->getServiceLocator()->get('Admin\Model\ContractTable')->updateItem(array('data' => $arrParam),  array('task' => 'update-ghtk'));
//            }
//            else{
//                $contract_code_error[] = 'Đơn số : '. $key .' gặp lỗi do '.$res['message'];
//            }
//        }
//
//        if(!empty($contract_code_success)){
//            $this->flashMessenger()->addMessage('Các đơn đã đẩy thành công sang ghtk '.implode(', ', $contract_code_success) );
//        }
//        if(!empty($contract_code_error)){
//            $this->flashMessenger()->addMessage('Chưa đẩy thành công '.implode(', ', $contract_code_error) );
//        }
//
//        $this->goRoute();
//    }

    public function sendGhtkAction() {
        $id_viettel_key = $this->params('id');
        if(!empty($id_viettel_key)){
            $ditem = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(array('id' => $id_viettel_key));
            $ghtk_key = $ditem->alias;
            if(!empty($ghtk_key)){
                $myForm   = new \Admin\Form\Contract\SendGhtk($this);

                $this->_viewModel['myForm']         = $myForm;
                $this->_viewModel['caption']        = 'Đẩy đơn hàng sang GHTK bằng tài khoản: '.$ditem->name;

                if($this->getRequest()->isPost()){
                    if($this->_params['data']['modal'] == 'success') {
                        $myForm->setInputFilter(new \Admin\Filter\Contract\SendGhtk(array('data' => $this->_params['data'],)));
                        $myForm->setData($this->_params['data']);
                        if($myForm->isValid()) {
                            $locations = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(null, array('task' => 'cache'));
                            $contracts_type	= \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "production-type" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'alias'));
                            
                            $ids = json_decode($this->_params['data']['list_data_id'], true);
                            $listData_ghtk = [];
                            foreach($ids as $id){
                                $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $id['id']));
                                if($contract['status_id'] == DA_CHOT && $contract['delete'] == 0 && $contracts_type[$contract['production_type_id']] == DON_TINH){
                                    $contract['options'] = unserialize($contract['options'])['product'];

                                    $warehouse = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(array('id' => $contract['groupaddressId']));
                                    if(!empty($warehouse)){
                                        $address = explode(',', $warehouse['address']);
                                        $order_item['pick_name']        = $warehouse['name'];
                                        $order_item['pick_province']    = $address[sizeof($address)-1];
                                        $order_item['pick_district']    = $address[sizeof($address)-2];
                                        $order_item['pick_ward']        = $address[sizeof($address)-3];
                                        $order_item['pick_address']     = $address[sizeof($address)-4];
                                        $order_item['pick_tel']         = $warehouse['phone'];
                                    }

                                    $products = [];
                                    $total_weight = 0;
                                    $list_name = '';
                                    foreach($contract['options'] as $key => $value){
                                        $pname = $value['full_name'].' - '.$value['car_year'];
                                        $list_name .= $pname.', ';
                                        if($value['weight'] > 1 || count($contract['options']) == 1){
                                            $pro['name'] = $pname;
                                            $pro['weight'] = $value['weight'];
                                            $pro['quantity'] = $value['numbers'];
                                            $pro['product_code'] = $value['code'];
                                            $pro['length'] = $value['length'];
                                            $pro['width'] = $value['width'];
                                            $pro['height'] = $value['height'];
                                            $total_weight += $value['weight'];

                                            $products[] = $pro;
                                        }
                                    }
                                    $products[0]['name'] = $list_name;
                                    $listData_ghtk[$contract['code']]['products'] = $products;

                                    $order_item['id'] = $contract['code'];

                                    // Thông tin khách hàng ships giao hàng
                                    $order_item['tel']       = $contract['phone'];
                                    $order_item['name']      = $contract['name'];
                                    $order_item['province']  = $locations[$contract['location_city_id']]->name;
                                    $order_item['district']  = $locations[$contract['location_district_id']]->fullname;
                                    $order_item['ward']      = $locations[$contract['location_town_id']]->fullname;
                                    $order_item['street']    = $contract['address'];
                                    $order_item['address']   = $contract['address'];
                                    $order_item['hamlet']    = "Khác";

                                    $order_item['is_freeship'] = "1";
                                    $order_item['pick_money'] = $contract['price_owed']; // Tiền hàng ship phải thu
                                    $order_item['note'] = $contract['ghtk_note'];
                                    $order_item['value'] = $contract['price_total']; // giá trị đóng bảo hiểm
                                    $order_item['transport'] = "road"; // road đường bộ, fly đường bay
                                    $order_item['deliver_work_shift'] = $contract['deliver_work_shift']; // Thời gian giao hàng
                                    if($total_weight >= 20){
                                        $order_item['3pl'] = 1; // Hàng theo kích thước khối lượng lớn BBS
                                    }

                                    $listData_ghtk[$contract['code']]['order'] = $order_item;
                                }
                            }

                            foreach ($listData_ghtk as $key => $value){
                                $result = $this->ghtk_call('/services/shipment/order/?ver=1.5', $value, 'POST', $ghtk_key);
                                $res = json_decode($result, true);

                                if($res['success']){
                                    $contract_code_success[] = $key;

                                    $contract_item = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('code' => $key),  array('task' => 'by-code'));
                                    $arrParam['id']             = $contract_item['id'];
                                    $arrParam['ghtk_code']      = $res['order']['label'];
                                    $arrParam['ghtk_result']    = $res['order'];
                                    $arrParam['ghtk_status']    = $res['order']['status_id'];
                                    $arrParam['price_transport']= $res['order']['fee'];
                                    $this->getServiceLocator()->get('Admin\Model\ContractTable')->updateItem(array('data' => $arrParam),  array('task' => 'update-ghtk'));
                                }
                                else{
                                    $contract_code_error[] = 'Đơn số : '. $key .' gặp lỗi do '.$res['message'];
                                }
                            }
                            
                            
                            
                            

                            if(!empty($contract_code_success)){
                                $this->flashMessenger()->addMessage('Các đơn đã đẩy thành công sang Viettel Post '.implode(', ', $contract_code_success) );
                            }
                            if(!empty($contract_code_error)){
                                $this->flashMessenger()->addMessage('Chưa đẩy thành công '.implode(', ', $contract_code_error) );
                            }

                            echo 'success';
                            return $this->response;
                        }
                    }
                }
            }
        }
        $viewModel =  new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }
    
    public function sendViettelPostAction() {
        $id_viettel_key = $this->params('id');
        if(!empty($id_viettel_key)){
            $ditem = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(array('id' => $id_viettel_key));
            $viettel_key = $ditem->alias;
            if(!empty($viettel_key)){
                $this->updateToken($viettel_key);
                $myForm   = new \Admin\Form\Contract\SendViettelPost($this);

                $this->_viewModel['myForm']         = $myForm;
                $this->_viewModel['caption']        = 'Đẩy đơn hàng sang Viettel Post bằng tài khoản: '.$ditem->name;

                if($this->getRequest()->isPost()){
                    if($this->_params['data']['modal'] == 'success') {
                        $myForm->setInputFilter(new \Admin\Filter\Contract\SendViettelPost(array('data' => $this->_params['data'],)));
                        $myForm->setData($this->_params['data']);
                        if($myForm->isValid()) {
                            $locations = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(null, array('task' => 'cache'));

                            $list_data_id   = json_decode($this->_params['data']['list_data_id'], true);
//                            $groupaddressId = $this->_params['data']['groupaddressId'];
//                            $inventorys = json_decode($this->viettelpost('/user/listInventory'), true);
//                            $inventory_item = [];
//                            if (isset($inventorys['data'])) {
//                                foreach ($inventorys['data'] as $ki => $vi) {
//                                    if ($vi['groupaddressId'] == $groupaddressId) {
//                                        $inventory_item = $vi;
//                                        break;
//                                    }
//                                }
//                            }

                            $listData_ghtk = [];
                            foreach($list_data_id as $id) {
                                $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $id['id']));
                                if (empty($contract['ghtk_code'])) {
                                    $products = [];
                                    $total_weight = 0;
                                    $list_name = '';
                                    $contract['options'] = unserialize($contract['options'])['product'];
                                    foreach($contract['options'] as $key => $value){
                                        $list_name .= $value['full_name'].' - '.$value['car_year'].', ';
                                        $value['weight'] = (float)str_replace(',', '.', $value['weight']);
                                        if($value['weight'] > 1 || count($contract['options']) == 1){
                                            $total_weight += $value['weight'] * 1000;
                                            $pro['PRODUCT_NAME'] = $value['full_name'].' - '.$value['car_year'];
                                            $pro['PRODUCT_WEIGHT'] = $value['weight'] * 1000;
                                            $pro['PRODUCT_QUANTITY'] = $value['numbers'];
                                            $pro['PRODUCT_PRICE'] = $value['price'];
                                            $products[] = $pro;
                                        }
                                    }
                                    $order_item['ORDER_NUMBER'] =$contract['code'];
                                    $warehouse = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(array('id' => $contract['groupaddressId']));
                                    if(!empty($warehouse)){
                                        $order_item['SENDER_FULLNAME']  = $warehouse['name'];
                                        $order_item['SENDER_ADDRESS']   = $warehouse['address'];
                                        $order_item['SENDER_PHONE']     = $warehouse['phone'];
                                    }

                                    $order_item['RECEIVER_FULLNAME'] = $contract['name'];
                                    $order_item['RECEIVER_ADDRESS'] = $locations[$contract['location_town_id']]->fullname.', '.$locations[$contract['location_district_id']]->fullname.', '.$locations[$contract['location_city_id']]->fullname;
                                    $order_item['RECEIVER_PHONE'] = $contract['phone'];

                                    # Tính dịch vụ vận chuyển phù hợp
                                    $s_data = array(
                                        "SENDER_ADDRESS" => $order_item['SENDER_ADDRESS'],
                                        "RECEIVER_ADDRESS" => $order_item['RECEIVER_ADDRESS'],
                                        "PRODUCT_TYPE" => "HH",
                                        "PRODUCT_WEIGHT" => $total_weight,
                                        "PRODUCT_PRICE" => $contract['price_total'] - $contract['price_deposits'],
                                        "MONEY_COLLECTION" => $contract['price_total'] - $contract['price_deposits'],
                                        "TYPE" => 1
                                    );
                                    $services = json_decode($this->viettelpost('/order/getPriceAllNlp', $s_data, 'POST'), true)['RESULT'];
                                    $order_service = '';
                                    $gia_cuoc = 1000000000;
                                    foreach($services as $ser){
                                        if($ser['GIA_CUOC'] < $gia_cuoc){
                                            $gia_cuoc = $ser['GIA_CUOC'];
                                            $order_service = $ser['MA_DV_CHINH'];
                                        }
                                    }
                                    
                                    $order_item["PRODUCT_NAME"] = $list_name;
                                    $order_item["PRODUCT_DESCRIPTION"] = $contract['sale_note'];
                                    $order_item["PRODUCT_QUANTITY"] = $contract['total_number_product'];
                                    $order_item["PRODUCT_PRICE"] = $contract['price_total'];
                                    $order_item["PRODUCT_WEIGHT"] = $total_weight;
                                    $order_item["PRODUCT_LENGTH"] = 0;
                                    $order_item["PRODUCT_WIDTH"] = 0;
                                    $order_item["PRODUCT_HEIGHT"] = 0;
                                    $order_item["ORDER_PAYMENT"] = 3;
                                    $order_item["ORDER_SERVICE"] = $order_service;
                                    $order_item["PRODUCT_TYPE"] = "HH";
                                    $order_item["ORDER_SERVICE_ADD"] = null;
                                    $order_item["ORDER_NOTE"] = $contract['ghtk_note'];
                                    $order_item["MONEY_COLLECTION"] = $contract['price_total'] - $contract['price_deposits'];
                                    $order_item["EXTRA_MONEY"] = 0;
                                    $order_item["CHECK_UNIQUE"] = true;
                                    $order_item["PRODUCT_DETAIL"] = $products;

                                    $listData_ghtk[$contract['code']] = $order_item;
                                }
                            }
                            # thực hiện đẩy đơn sang vtp
                            foreach ($listData_ghtk as $key => $value){
                                $result = $this->viettelpost('/order/createOrderNlp', $value, 'POST');
                                $res = json_decode($result, true);

                                if($res['status'] == 200 and $res['error'] == false){
                                    $contract_code_success[] = $key;

                                    $contract_item = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('code' => $key),  array('task' => 'by-code'));
                                    $arrParam['id']             = $contract_item['id'];
                                    $arrParam['ghtk_code']      = $res['data']['ORDER_NUMBER'];
                                    $arrParam['ghtk_result']    = $res['data'];
                                    $arrParam['unit_transport'] = 'viettel';
                                    $this->getServiceLocator()->get('Admin\Model\ContractTable')->updateItem(array('data' => $arrParam),  array('task' => 'update-ghtk'));
                                }
                                else{
                                    $contract_code_error[] = 'Đơn số : '. $key .' gặp lỗi do '.$res['message'];
                                }
                            }

                            if(!empty($contract_code_success)){
                                $this->flashMessenger()->addMessage('Các đơn đã đẩy thành công sang Viettel Post '.implode(', ', $contract_code_success) );
                            }
                            if(!empty($contract_code_error)){
                                $this->flashMessenger()->addMessage('Chưa đẩy thành công '.implode(', ', $contract_code_error) );
                            }

                            echo 'success';
                            return $this->response;
                        }
                    }
                }
            }
        }
        $viewModel =  new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Trừ số lượng sản phẩm bên kiotviet thủ công khi đơn lỗi không thể đồng bộ
    public function updateNumberProductKovAction() {
        $ids = $this->_params['data']['cid'];
        foreach($ids as $id){
            $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $id));
            if(in_array($contract['ghtk_status'], $this->_ghtk_status) && $contract['shipped'] == 0){
                $result = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/orders/' . $contract['id_kov']);
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
                }
                $result_kov = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/invoices', $invoi_data, 'POST');
                $result_kov = json_decode($result_kov, true);

                if(isset($result_kov['id'])){
                    $params['data']['id']       = $contract['id'];
                    $params['data']['shipped']  = 1;
                    $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($params, array('task' => 'update-shipped'));
                    $contract_code_success[] = $contract['code'];
                }
                else{
                    $contract_code_error[] = $contract['code'].'('.$result_kov['responseStatus']['message'].')';
                }
            }
            else{
                $contract_code_error[] = $contract['code'];
            }
        }

        if(!empty($contract_code_success)){
            $this->flashMessenger()->addMessage('Các đơn đã cập nhật số lượng thành công sang Kiotviet '.implode(', ', $contract_code_success) );
        }
        if(!empty($contract_code_error)){
            $this->flashMessenger()->addMessage('Chưa cập nhật thành công thành công '.implode(', ', $contract_code_error) );
        }

        $this->goRoute();
    }

    // Xác nhận đã nhận hoàn đơn hàng
    public function returnedAction() {
        $result = $this->getTable()->saveItem($this->_params, array('task' => 'returned'));
        $this->flashMessenger()->addMessage('Xác nhận hoàn '.$result.' đơn hàng thành công');
        $this->goRoute();
    }

    // Khóa đơn hàng
    public function lockAction() {
        $result = $this->getTable()->saveItem($this->_params, array('task' => 'lock'));
        $this->flashMessenger()->addMessage('Khóa '.$result.' đơn hàng thành công');
        $this->goRoute();
    }

    // Mở khóa đơn hàng
    public function unlockAction() {
        $result = $this->getTable()->saveItem($this->_params, array('task' => 'unlock'));
        $this->flashMessenger()->addMessage('Mở khóa '.$result.' đơn hàng thành công');
        $this->goRoute();
    }

    // Thêm giảm trừ doanh thu
    public function editReduceAction() {
        $myForm = new \Admin\Form\Contract\EditReduce($this->getServiceLocator(), $this->_params);

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
                $myForm->setInputFilter(new \Admin\Filter\Contract\EditReduce($this->_params));
                $myForm->setData($this->_params['data']);

                if($myForm->isValid()){
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['item'] = $contract;
                    $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params, array('task' => 'update-reduce'));
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
        $this->_viewModel['caption']    = 'Giảm trừ doanh thu';

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // cập nhật công nợ khách hàng
    public function editPricePaidAction() {
        $myForm = new \Admin\Form\Contract\EditPricePaid($this->getServiceLocator(), $this->_params);

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
                $myForm->setInputFilter(new \Admin\Filter\Contract\EditPricePaid($this->_params));
                $myForm->setData($this->_params['data']);

                if($myForm->isValid()){
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['item'] = $contract;
                    $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params, array('task' => 'update-price'));
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
        $this->_viewModel['caption']    = 'Đối soát thủ công';

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Thêm lịch sử chăm sóc đơn hàng
    public function addHistoryContractAction() {
        $myForm = new \Admin\Form\Contract\AddHistoryContract($this->getServiceLocator(), $this->_params);

        if(!empty($this->_params['data']['id'])) {
            $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->_params['data']['id']));
            if($contract['lock']){
                return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'lock', 'type' => 'modal'));
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }

        if($this->getRequest()->isPost()){
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\Contract\AddHistoryContract($this->_params));
                $myForm->setData($this->_params['data']);

                if($myForm->isValid()){
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['item'] = $contract;

                    $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params, array('task' => 'update-history-contract'));
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
        $history_contract = unserialize($contract['history_contract']);
        if(!empty($history_contract)){
            $history_contract = array_reverse($history_contract);
        }


        $this->_viewModel['myForm']           = $myForm;
        $this->_viewModel['history_contract'] = $history_contract;
        $this->_viewModel['user']             = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['caption']          = 'Lịch sử chăm sóc đơn hàng';

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Thêm giảm trừ doanh thu
    public function editShippingFeeAction() {
        $myForm = new \Admin\Form\Contract\ShippingFee($this->getServiceLocator(), $this->_params);

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
                $myForm->setInputFilter(new \Admin\Filter\Contract\ShippingFee($this->_params));
                $myForm->setData($this->_params['data']);

                if($myForm->isValid()){
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['item'] = $contract;
                    $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params, array('task' => 'update-shipping-fee'));
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
        $this->_viewModel['caption']    = 'Thêm tiền hỗ trợ ship';

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }
}


