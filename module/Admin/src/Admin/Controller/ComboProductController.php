<?php

namespace Admin\Controller;

use kcfinder\zipFolder;
use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class ComboProductController extends ActionController
{

    public function init()
    {
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\ComboProductTable';
        $this->_options['formName'] = 'formAdminComboProduct';

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
        }

        $this->goRoute(['action' => $action]);
    }

    public function indexAction()
    {
        $ssFilter = new Container(__CLASS__. 'sales');
        $ssFilter->currentPageNumber = $this->_paginator['currentPageNumber'];

        $myForm	= new \Admin\Form\Search\ComboProduct($this->getServiceLocator(), $this->_params);
        $myForm->setData($this->_params['ssFilter']);
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        $count = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));

        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $items;
        $this->_viewModel['count']                  = $count;
        $this->_viewModel['user']                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['product']                = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['caption']                = 'Danh sách combo sản phẩm';

        return new ViewModel($this->_viewModel);
    }

    // Thêm mới combo sản phẩm
    public function addAction() {
        $myForm = $this->getForm();
        if($this->getRequest()->isPost()){
            $myForm->setInputFilter(new \Admin\Filter\ComboProduct(array('data' => $this->_params['data'])));
            $myForm->setData($this->_params['data']);
            $controlAction = $this->_params['data']['control-action'];
            if($myForm->isValid()){
                $contract_product = $this->_params['data']['contract_product'];
                $check_emty_data = true;// kiểm tra thông tin sản phẩm của đơn hàng đã đầy đủ chưa
                for ($i = 0; $i < count($contract_product['product_id']) - 1; $i++ ){
                    if(
                        trim($contract_product['product_id'][$i]) == "" ||
                        trim($contract_product['carpet_color_id'][$i]) == "" ||
                        trim($contract_product['tangled_color_id'][$i]) == "" ||
                        trim($contract_product['flooring_id'][$i]) == "" ||
                        trim($contract_product['numbers'][$i]) == "" ||
                        trim($contract_product['price'][$i]) == ""
                    )$check_emty_data = false;
                }

                if($check_emty_data){
                    $result = $this->getTable()->saveItem($this->_params, array('task' => 'add-item'));
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
                    $this->_viewModel['check_product_id'] = 'Cần nhập đầy đủ thông tin của sản phẩm';
                    $this->_viewModel['productList'] = $this->_params['data']['contract_product'];
                }
            }
            else {
                $this->_viewModel['productList']  = $this->_params['data']['contract_product'];
            }
        }

        $this->_viewModel['product']        = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache-active'));
        $this->_viewModel['type_of_carpet'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'type-of-carpet')), array('task' => 'cache'));
        $this->_viewModel['carpet_color']   = $this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['tangled_color']  = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['row_seats']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'row-seats')), array('task' => 'cache'));
        $this->_viewModel['flooring']       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'flooring')), array('task' => 'cache'));
        $this->_viewModel['myForm']	        = $myForm;
        $this->_viewModel['caption']        = 'Combo sản phẩm - Thêm mới';
        return new ViewModel($this->_viewModel);
    }

    // Sửa Đơn hàng
    public function editAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $numberFormat = new \ZendX\Functions\Number();
        $myForm = $this->getForm();

        if(!empty($this->params('id'))) {
            $item = $this->getTable()->getItem(array('id' => $this->params('id')));
            $myForm->setData($item);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }

        if($this->getRequest()->isPost()){
            $myForm->setInputFilter(new \Admin\Filter\ComboProduct(array('data' => $this->_params['data'])));
            $myForm->setData($this->_params['data']);
            $controlAction = $this->_params['data']['control-action'];
            if($myForm->isValid()){
                $contract_product = $this->_params['data']['contract_product'];
                $check_emty_data = true;// kiểm tra thông tin sản phẩm của đơn hàng đã đầy đủ chưa
                for ($i = 0; $i < count($contract_product['product_id']) - 1; $i++ ){
                    if(
                        trim($contract_product['product_id'][$i]) == "" ||
                        trim($contract_product['carpet_color_id'][$i]) == "" ||
                        trim($contract_product['tangled_color_id'][$i]) == "" ||
                        trim($contract_product['flooring_id'][$i]) == "" ||
                        trim($contract_product['numbers'][$i]) == "" ||
                        trim($contract_product['price'][$i]) == ""
                    )$check_emty_data = false;
                }

                if($check_emty_data){
                    $result = $this->getTable()->saveItem($this->_params, array('task' => 'edit-item'));
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
                    $this->_viewModel['check_product_id'] = 'Cần nhập đầy đủ thông tin của sản phẩm';
                    $this->_viewModel['productList'] = $this->_params['data']['contract_product'];
                }
            }
            else {
                $this->_viewModel['productList']  = $this->_params['data']['contract_product'];
            }
        }

        $this->_viewModel['product_type']   = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache-active'));
        $this->_viewModel['type_of_carpet'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'type-of-carpet')), array('task' => 'cache'));
        $this->_viewModel['carpet_color']   = $this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['tangled_color']  = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['row_seats']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'row-seats')), array('task' => 'cache'));
        $this->_viewModel['flooring']       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'flooring')), array('task' => 'cache'));
        $this->_viewModel['item']	        = $item;
        $this->_viewModel['myForm']	        = $myForm;
        $this->_viewModel['caption']        = 'Combo sản phẩm - Cập nhật';
        return new ViewModel($this->_viewModel);
    }

    // Import sản phẩm combo
    public function importAction()
    {
        $dateFormat = new \ZendX\Functions\Date();
        $myForm = new \Admin\Form\ComboProduct\Import($this->getServiceLocator(), $this->_params);
        $myForm->setInputFilter(new \Admin\Filter\ComboProduct\Import($this->_params));

        $this->_viewModel['caption'] = 'Nhập combo sản phẩm';
        $this->_viewModel['myForm']  = $myForm;
        $viewModel                   = new ViewModel($this->_viewModel);

        if ($this->getRequest()->isPost()) {
                $myForm->setData($this->_params['data']);
                if ($myForm->isValid()) {
                    ini_set('memory_limit', '1024M');

                    if (!empty($this->_params['data']['file_import']['tmp_name'])) {
                        $upload      = new \ZendX\File\Upload();
                        $file_import = $upload->uploadFile('file_import', PATH_FILES . '/import/', array());
                    }
                    $viewModel->setVariable('file_import', $file_import);
                    $viewModel->setVariable('import', true);

                    require_once PATH_VENDOR . '/Excel/PHPExcel/IOFactory.php';
                    $objPHPExcel = \PHPExcel_IOFactory::load(PATH_FILES . '/import/' . $file_import);
                    $sheetData = $objPHPExcel->getActiveSheet(1)->toArray(null, true, true, true);

                    $options['product']        = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache-active')),array('key' => 'name', 'value' => 'object'));
                    $options['type_of_carpet'] = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'type-of-carpet')), array('task' => 'cache')),array('key' => 'name', 'value' => 'object'));
                    $options['carpet_color']   = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem(null, array('task' => 'cache')),array('key' => 'name', 'value' => 'object'));
                    $options['tangled_color']  = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem(null, array('task' => 'cache')),array('key' => 'name', 'value' => 'object'));
                    $options['flooring']       = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'flooring')), array('task' => 'cache')),array('key' => 'name', 'value' => 'object'));
                    $gid      = new \ZendX\Functions\Gid();
                    $arr_combo = [];

                    foreach($sheetData as $key=>$val){
                        $products_combo = [];
                        $products_list = [];
                        $combo_name = '';
                        if ($key==1) {
                            foreach($val as $k=>$v) $heading[] = $v;
                        } else {
                            foreach($val as $k=>$v) {
                                if ($k=='B')
                                    $combo_name = $v;
                                if ($k=='C'){
                                    $products_list[] = $v;

                                    $products_combo['product_id'] = $options['product'][$v?:'']['id']?:'';
                                    $products_combo['product_alias'] = $options['product'][$v?:'']['code']?:'';
                                    $products_combo['product_group_id'] = $options['product'][$v?:'']['product_group_id']?:'';
                                }
                                if ($k=='D'){
                                    $products_list[] = $v;

                                    $products_combo['carpet_color_id'] = $options['carpet_color'][$v?:'']['id']?:'';
                                }
                                if ($k=='E'){
                                    $products_list[] = $v;

                                    $products_combo['tangled_color_id'] = $options['tangled_color'][$v?:'']['id']?:'';
                                }
                                if ($k=='F'){
                                    $products_list[] = $v;

                                    $products_combo['flooring_id'] = $options['flooring'][$v?:'']['id']?:'';
                                }
                                if ($k=='G'){
                                    $products_list[] = $v;

                                    $products_combo['numbers'] = $v;
                                }
                                if ($k=='H'){
                                    $products_list[] = $v;

                                    $products_combo['price'] = $v * $products_combo['numbers'];
                                }


                                $products_combo['listed_price'] = $products_combo['numbers'] * $this->getServiceLocator()->get('Admin\Model\ProductListedTable')->getListedPrice(array(
                                    'product' => $products_combo['product_id'],
                                    'carpet_color' => $products_combo['carpet_color_id'],
                                    'tangled_color' => $products_combo['tangled_color_id'],
                                    'flooring' => $products_combo['flooring_id'],
                                    'type' => 'price',
                                    )
                                );
                                $products_combo['capital_default'] = $products_combo['numbers'] * $this->getServiceLocator()->get('Admin\Model\ProductListedTable')->getListedPrice(array(
                                    'product' => $products_combo['product_id'],
                                    'carpet_color' => $products_combo['carpet_color_id'],
                                    'tangled_color' => $products_combo['tangled_color_id'],
                                    'flooring' => $products_combo['flooring_id'],
                                    'type' => 'default',
                                    )
                                );
                                $products_combo['sale_price'] = $products_combo['listed_price'] - $products_combo['price'];
                                $products_combo['total'] = $products_combo['price'];
                                $products_combo['key_id'] = $gid->getId();
                            }
                            $arr_combo[$combo_name]['product'][] = $products_combo;
                            $arr_combo[$combo_name]['product_list'][] = $products_list;
                            $arr_combo[$combo_name]['price_total'] += $products_combo['price'];
                            $arr_combo[$combo_name]['status'] = "Hoàn thành";
                        }
                    }
                    foreach($arr_combo as $key => $value){
                        $check_exits = $this->getTable()->getItem(array('name' => $key), array('task' => 'by-name'));
                        if(empty($check_exits)){
                            $this->getTable()->saveItem(["data" => array('combo_name' => $key, 'detail' => $value)], array('task' => 'import-item'));
                        }
                        else{
                            $arr_combo[$key]['status'] = 'Tồn tại';
                        }

                    }

                    $viewModel->setVariable('combos', $arr_combo);
                }
            }

        return $viewModel;
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


