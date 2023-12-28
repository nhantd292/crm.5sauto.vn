<?php

namespace Admin\Controller;

use kcfinder\zipFolder;
use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class KovDiscountsController extends ActionController
{

    public function init()
    {
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\KovDiscountsTable';
        $this->_options['formName'] = 'formAdminKovDiscounts';

        // Thiết lập session filter
        $action = str_replace('-', '_', $this->_params['action']);
        $ssFilter                                    = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']       = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'year';
        $this->_params['ssFilter']['order']          = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';

        $this->_params['ssFilter']['filter_date_type']      = $ssFilter->filter_date_type;
        $this->_params['ssFilter']['filter_date_begin']     = $ssFilter->filter_date_begin;
        $this->_params['ssFilter']['filter_date_end']       = $ssFilter->filter_date_end;
        $this->_params['ssFilter']['filter_keyword']        = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['filter_status']         = $ssFilter->filter_status;
        $this->_params['ssFilter']['filter_discounts_type']   = $ssFilter->filter_discounts_type;
        $this->_params['ssFilter']['filter_discounts_option'] = $ssFilter->filter_discounts_option;

        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage']  = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : 50;
        $this->_paginator['currentPageNumber'] = $this->params()->fromRoute('page', 1);
        $this->_params['paginator']            = $this->_paginator;

        // Lấy dữ liệu post của form
        $this->_params['data'] = array_merge($this->getRequest()->getPost()->toArray(), $this->getRequest()->getFiles()->toArray());

        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
    }

    public function filterAction()
    {
        if ($this->getRequest()->isPost()) {
            $action = !empty($this->_params['data']['filter_action']) ? $this->_params['data']['filter_action'] : 'index';
            $ssFilter = new Container(__CLASS__);
            $data     = $this->_params['data'];

            $ssFilter->pagination_option = intval($data['pagination_option']);
            $ssFilter->order_by          = $data['order_by'];
            $ssFilter->order             = $data['order'];

            $ssFilter->filter_date_type   = $data['filter_date_type'];
            $ssFilter->filter_date_begin  = $data['filter_date_begin'];
            $ssFilter->filter_date_end    = $data['filter_date_end'];
            $ssFilter->filter_status      = $data['filter_status'];
            $ssFilter->filter_keyword     = $data['filter_keyword'];
            $ssFilter->filter_discounts_type   = $data['filter_discounts_type'];
            $ssFilter->filter_discounts_option = $data['filter_discounts_option'];
        }

        $this->goRoute(['action' => $action]);
    }

    public function indexAction()
    {
        $ssFilter = new Container(__CLASS__. 'sales');
        $ssFilter->currentPageNumber = $this->_paginator['currentPageNumber'];

        $myForm	= new \Admin\Form\Search\KovDiscounts($this->getServiceLocator(), $this->_params);
        $myForm->setData($this->_params['ssFilter']);
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        $count = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));

        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $items;
        $this->_viewModel['count']                  = $count;
        $this->_viewModel['user']                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['discounts_type']         = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'discounts-type')), array('task' => 'cache-alias'));
        $this->_viewModel['discounts_option']       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'discounts-option')), array('task' => 'cache-alias'));

        $this->_viewModel['products']                   = $this->getServiceLocator()->get('Admin\Model\KovProductsTable')->listItem(null, array('task' => 'cache'));

        $this->_viewModel['caption']                = 'Danh sách combo sản phẩm';

        return new ViewModel($this->_viewModel);
    }

    // Thêm khuyến mãi
    public function addAction() {
        $myForm = $this->getForm();
        if($this->getRequest()->isPost()){
            $myForm->setInputFilter(new \Admin\Filter\KovDiscounts(array('data' => $this->_params['data'])));
            $myForm->setData($this->_params['data']);
            $controlAction = $this->_params['data']['control-action'];
            if($myForm->isValid()){
                $detail_discounts = $this->_params['data']['detail_discounts'];
                $check_emty_data = true;
                if($this->_params['data']['discounts_type'] == 'hoa-don'){
                    if($this->_params['data']['discounts_option'] == 'giam-gia-hoa-don'){
                        for ($i = 0; $i <= count($detail_discounts['contract_total']) - 1; $i++ ){
                            if(trim($detail_discounts['contract_total'][$i]) == "" || trim($detail_discounts['discount_value'][$i]) == "" || trim($detail_discounts['unit_type'][$i]) == "") {
                                $check_emty_data = false;
                            }
                        }
                    }
                    if($this->_params['data']['discounts_option'] == 'tang-hang'){
                        for ($i = 0; $i <= count($detail_discounts['contract_total']) - 1; $i++ ){
                            if(trim($detail_discounts['contract_total'][$i]) == "" || trim($detail_discounts['number_donate'][$i]) == "" || empty($detail_discounts['product_donate'][$i])) {
                                $check_emty_data = false;
                            }
                        }
                    }
                    if($this->_params['data']['discounts_option'] == 'giam-gia-hang'){
                        for ($i = 0; $i <= count($detail_discounts['contract_total']) - 1; $i++ ){
                            if(trim($detail_discounts['contract_total'][$i]) == "" || trim($detail_discounts['discount_value'][$i]) == "" || trim($detail_discounts['unit_type'][$i]) == "" || trim($detail_discounts['number_donate'][$i]) == "" || empty($detail_discounts['product_donate'][$i])) {
                                $check_emty_data = false;
                            }
                        }
                    }
                }


                if($check_emty_data){
                    $this->getTable()->saveItem($this->_params, array('task' => 'add-item'));
                    $this->flashMessenger()->addMessage('Dữ liệu đã được cập nhật thành công');

                    if($controlAction == 'save-new') {
                        $this->goRoute(array('action' => 'add'));
                    } else if($controlAction == 'save') {
                        $this->goRoute();
                    } else {
                        $this->goRoute();
                    }
                }
                else{
                    $this->_viewModel['check_detail_discounts'] = 'Cần nhập đầy đủ thông tin chi tiết khuyến mãi';
                    $this->_viewModel['detail_discounts'] = $detail_discounts;
                }
            }
            else {
                $this->_viewModel['productList']  = $this->_params['data']['detail_discounts'];
            }
        }

        $this->_viewModel['myForm']	        = $myForm;
        $this->_viewModel['caption']        = 'Khuyến mãi - Thêm mới';
        return new ViewModel($this->_viewModel);
    }

    // Sửa khuyến mãi
    public function editAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $numberFormat = new \ZendX\Functions\Number();
//        $myForm = $this->getForm();
        if(!empty($this->params('id'))) {
            $item = $this->getTable()->getItem(array('id' => $this->params('id')));
            $item->date_begin = $dateFormat->formatToView($item->date_begin);
            $item->date_end   = $dateFormat->formatToView($item->date_end);
            $item->discounts_range_branchs_detail   = explode(',', $item->discounts_range_branchs_detail);
            $item->discounts_range_sales_detail   = explode(',', $item->discounts_range_sales_detail);
            $item->discounts_range_customers_detail   = explode(',', $item->discounts_range_customers_detail);

            $myForm = new \Admin\Form\KovDiscounts($this, array('discounts_type' => $item->discounts_type, 'discounts_option' => $item->discounts_option) );
            $myForm->setData($item);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }

        if($this->getRequest()->isPost()){
            $myForm->setInputFilter(new \Admin\Filter\KovDiscounts(array('data' => $this->_params['data'])));
            $myForm->setData($this->_params['data']);
            $controlAction = $this->_params['data']['control-action'];
            if($myForm->isValid()){
                $detail_discounts = $this->_params['data']['detail_discounts'];
                $check_emty_data = true;

                if($this->_params['data']['discounts_type'] == 'hoa-don'){
                    if($this->_params['data']['discounts_option'] == 'giam-gia-hoa-don'){
                        for ($i = 0; $i <= count($detail_discounts['contract_total']) - 1; $i++ ){
                            if(trim($detail_discounts['contract_total'][$i]) == "" || trim($detail_discounts['discount_value'][$i]) == "" || trim($detail_discounts['unit_type'][$i]) == "") {
                                $check_emty_data = false;
                            }
                        }
                    }
                    if($this->_params['data']['discounts_option'] == 'tang-hang'){
                        for ($i = 0; $i <= count($detail_discounts['contract_total']) - 1; $i++ ){
                            if(trim($detail_discounts['contract_total'][$i]) == "" || trim($detail_discounts['number_donate'][$i]) == "" || empty($detail_discounts['product_donate'][$i])) {
                                $check_emty_data = false;
                            }
                        }
                    }
                    if($this->_params['data']['discounts_option'] == 'giam-gia-hang'){
                        for ($i = 0; $i <= count($detail_discounts['contract_total']) - 1; $i++ ){
                            if(trim($detail_discounts['contract_total'][$i]) == "" || trim($detail_discounts['discount_value'][$i]) == "" || trim($detail_discounts['unit_type'][$i]) == "" || trim($detail_discounts['number_donate'][$i]) == "" || empty($detail_discounts['product_donate'][$i])) {
                                $check_emty_data = false;
                            }
                        }
                    }
                }

                if($check_emty_data){
                    $this->getTable()->saveItem($this->_params, array('task' => 'edit-item'));
                    $this->flashMessenger()->addMessage('Dữ liệu đã được cập nhật thành công');

                    if($controlAction == 'save-new') {
                        $this->goRoute(array('action' => 'add'));
                    } else if($controlAction == 'save') {
                        $this->goRoute();
                    } else {
                        $this->goRoute();
                    }
                }
                else{
                    $this->_viewModel['check_detail_discounts'] = 'Cần nhập đầy đủ thông tin chi tiết khuyến mãi';
                    $this->_viewModel['detail_discounts'] = $detail_discounts;
                }
            }
            else {
                $this->_viewModel['productList']  = $this->_params['data']['detail_discounts'];
            }
        }

        $this->_viewModel['item']	  = $item;
        $this->_viewModel['myForm']	  = $myForm;
        $this->_viewModel['caption']  = 'Khuyến mãi - Cập nhật';
        return new ViewModel($this->_viewModel);
    }

    public function statusAction() {
    if($this->getRequest()->isXmlHttpRequest()) {
        $this->getTable()->changeStatus($this->_params, array('task' => 'change-status'));
    } else {
        $this->goRoute();
    }

    return $this->response;
}
}


