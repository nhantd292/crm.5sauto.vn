<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class ContractDetailController extends ActionController {
    
    public function init() {
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\ContractDetailTable';
        $this->_options['formName'] = 'formAdminContractDetail';
        
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
        $this->_params['ssFilter']['filter_product'] 	    = $ssFilter->filter_product;
        $this->_params['ssFilter']['filter_bill_code']      = $ssFilter->filter_bill_code;
        $this->_params['ssFilter']['filter_status_type']    = $ssFilter->filter_status_type;
        $this->_params['ssFilter']['filter_status']         = $ssFilter->filter_status;
        $this->_params['ssFilter']['filter_coincider']      = $ssFilter->filter_coincider;
        $this->_params['ssFilter']['filter_send_ghtk']      = $ssFilter->filter_send_ghtk;
        $this->_params['ssFilter']['filter_category']       = $ssFilter->filter_category;
        $this->_params['ssFilter']['filter_product']        = $ssFilter->filter_product;

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
            $ssFilter->filter_date_begin        = $data['filter_date_begin'];
            $ssFilter->filter_date_end          = $data['filter_date_end'];
            $ssFilter->filter_date_type         = $data['filter_date_type'];
            $ssFilter->filter_product 	        = $data['filter_product'];
            $ssFilter->filter_status_type       = $data['filter_status_type'];
            $ssFilter->filter_status            = $data['filter_status'];
            $ssFilter->filter_user              = $data['filter_user'];
            $ssFilter->filter_action            = $data['filter_action'];
            $ssFilter->filter_coincider 	    = $data['filter_coincider'];
            $ssFilter->filter_send_ghtk 	    = $data['filter_send_ghtk'];
            $ssFilter->filter_category 	        = $data['filter_category'];
            $ssFilter->filter_product 	        = $data['filter_product'];

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
    
    // Danh sách
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
        $categories = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/categories?pageSize=100&hierachicalData=true');
        $categories = json_decode($categories, true)['data'];
        $categories = $this->getNameCat($this->addNew($categories), $result);
        $this->_params['categories'] = $categories;

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
                $products = $this->kiotviet_call(RETAILER, $this->kiotviet_token,
                    '/products?pageSize=100&currentItem=' . $currentItem);
                $products = json_decode($products, true);
                $product_data = array_merge($product_data, $products['data']);
            }
            $product_data = \ZendX\Functions\CreateArray::create($product_data, array('key' => 'id', 'value' => 'fullName'));
        }
        $this->_params['products'] = $product_data;

        $myForm	= new \Admin\Form\Search\ContractDetail($this, $this->_params);
        $myForm->setData($this->_params['ssFilter']);

        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['count']                  = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['user']                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_group']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));

        $this->_viewModel['status_check']           = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'ghtk-status')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['status_accounting']      = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-acounting')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['status_sales']           = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['caption']                = 'Sản phẩm đơn hàng';
        
        return new ViewModel($this->_viewModel);
    }
    
    // Danh sách
    public function productsAction() {
        $ssFilter = new Container(__CLASS__.'products');
        $param_search_product = 'pageSize=100&includeInventory=true';
        if($this->_params['ssFilter']['filter_product']){
            $param_search_product = $param_search_product.'&name='.$this->_params['ssFilter']['filter_product'];
        }
        $products = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/products?'.$param_search_product);
        $products = json_decode($products, true);
        if($products['total'] < $products['pageSize']){
            $product_data = $products['data'];
        }
        else{
            $total = $products['total'];
            $pageSize = $products['pageSize'];
            $pageTotal = (int)($total / $pageSize) + 1;
            $product_data = [];
            for ($index = 0; $index < $pageTotal; $index++) {
                $currentItem = $index * $pageSize;
                $products = $this->kiotviet_call(RETAILER, $this->kiotviet_token,
                    '/products?'.$param_search_product.'&currentItem=' . $currentItem);
                $products = json_decode($products, true);
                $product_data = array_merge($product_data, $products['data']);
            }
        }
        $result_data = [];
        foreach($product_data as $key => $value){
            $result_data[$value['id']]['code'] = $value['code'];
            $result_data[$value['id']]['name'] = $value['fullName'];
            $result_data[$value['id']]['onHand'] = $value['inventories'][0]['onHand'];
        }

        $products_order = $this->getTable()->listItem(['query' => 'SELECT product_id, SUM(numbers) as quantity FROM x_contract_detail GROUP BY product_id order by quantity DESC'], array('task' => 'list-query'));
        foreach($products_order as $key => $value){
            if(array_key_exists($value['product_id'], $result_data))
                $result_data[$value['product_id']]['order'] = $value['quantity'];
        }

        $products_ghtk = $this->getTable()->listItem(['query' => 'SELECT product_id, SUM(numbers) as quantity FROM x_contract_detail INNER JOIN x_contract ON x_contract_detail.contract_id = x_contract.id WHERE x_contract.shipped = 1 GROUP BY product_id order by quantity DESC'], array('task' => 'list-query'));
        foreach($products_ghtk as $key => $value){
            if(array_key_exists($value['product_id'], $result_data))
                $result_data[$value['product_id']]['ghtk'] = $value['quantity'];
        }

        $myForm	= new \Admin\Form\Search\ContractDetail($this, $this->_params);
        $myForm->setData($this->_params['ssFilter']);

        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $result_data;
        $this->_viewModel['caption']                = 'Báo cáo sản phẩm';
        
        return new ViewModel($this->_viewModel);
    }

}


