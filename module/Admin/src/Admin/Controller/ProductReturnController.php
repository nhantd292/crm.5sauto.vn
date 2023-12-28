<?php

namespace Admin\Controller;

use kcfinder\zipFolder;
use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class ProductReturnController extends ActionController{

    public function init()
    {
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\ProductReturnTable';
        $this->_options['formName'] = 'formAdminProductReturn';

        // Thiết lập session filter
        $action = str_replace('-', '_', $this->_params['action']);
        $ssFilter                                    = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']       = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'name';
        $this->_params['ssFilter']['order']          = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';

        $this->_params['ssFilter']['filter_keyword']        = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['filter_categoryId']     = $ssFilter->filter_categoryId;
        $this->_params['ssFilter']['filter_branches']       = $ssFilter->filter_branches ;
        $this->_params['ssFilter']['filter_name_year']       = $ssFilter->filter_name_year ;

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
            $action     = !empty($this->_params['data']['filter_action']) ? $this->_params['data']['filter_action'] : 'index';
            $ssFilter   = new Container(__CLASS__);
            $data       = $this->_params['data'];

            $ssFilter->pagination_option = intval($data['pagination_option']);
            $ssFilter->order_by          = $data['order_by'];
            $ssFilter->order             = $data['order'];

            $ssFilter->filter_categoryId    = $data['filter_categoryId'];
            $ssFilter->filter_keyword       = $data['filter_keyword'];
            $ssFilter->filter_branches      = $data['filter_branches'];
            $ssFilter->filter_name_year     = $data['filter_name_year'];
        }

        $this->goRoute(['action' => $action]);
    }

    public function indexAction(){
        $categories = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/categories?pageSize=100&hierachicalData=true');
        $categories = json_decode($categories, true)['data'];
        $categories = $this->getNameCat($this->addNew($categories), $result);

        $ssFilter = new Container(__CLASS__. 'sales');
        $ssFilter->currentPageNumber = $this->_paginator['currentPageNumber'];

        $myForm	= new \Admin\Form\Search\ProductReturn($this, $categories);
        $myForm->setData($this->_params['ssFilter']);
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        $count = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));

        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $items;
        $this->_viewModel['count']                  = $count;
        $this->_viewModel['categories']             = $categories;
        $this->_viewModel['kov_branch']         = $this->getServiceLocator()->get('Admin\Model\kovBranchesTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_branch']        = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));

        $this->_viewModel['caption']                = 'Danh sách có sẵn';

        return new ViewModel($this->_viewModel);
    }

    public function importAction()
    {
        $dateFormat = new \ZendX\Functions\Date();
        $myForm = new \Admin\Form\ProductReturn\Import($this->getServiceLocator(), $this->_params);
        $myForm->setInputFilter(new \Admin\Filter\ProductReturn\Import($this->_params));

        $this->_viewModel['caption'] = 'Nhập kho hàng có sẵn';
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

                $products = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\KovProductsTable')->listItem(null, array('task' => 'list-all')),array('key' => 'code', 'value' => 'id'));
                $sale_brachs   = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache')),array('key' => 'alias', 'value' => 'id'));
                $kov_brachs   = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\KovBranchesTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'list-all')),array('key' => 'branchName', 'value' => 'id'));

                $product_return = [];
                foreach($sheetData as $key=>$val){
                    $arrData = [];
                    if ($key==1) {
                        foreach($val as $k=>$v) $heading[] = $v;
                    }
                    else {
                        foreach($val as $k=>$v) {
                            if ($k=='B'){
                                $arrData['productId'] = $products[$v];
                            }
                            if ($k=='C') {
                                $arrData['branchId'] = $kov_brachs[$v];
                            }
                            if ($k=='D') {
                                $arrData['sale_branch_id'] = $sale_brachs[$v];
                            }
                            if ($k=='E'){
                                $arrData['contract_code'] = $v;
                                $arrData['contract_id'] = $this->getServiceLocator()->get('Admin\Model\contractTable')->getItem(array('code' => $v), array('task' => 'by-code-all'))['id'];
                            }
                            if ($k=='F') {
                                $arrData['name_year'] = $v;
                            }
                            if ($k=='G') {
                                $arrData['quantity'] = $v;
                            }
                        }
                        $product_return[] = $arrData;
                    }
                }

                foreach($product_return as $key => $value){
                    $check_empty = true;
                    $mes = '';
                    foreach($value as $k => $v_detail){
                        if(empty($v_detail)){
                            $check_empty = false;
                            if($k == 'productId') $mes .= 'Mã sản phẩm, ';
                            if($k == 'branchId') $mes .= 'Kho hàng, ';
                            if($k == 'sale_branch_id') $mes .= 'Chi nhánh CRM, ';
                            if($k == 'contract_id') $mes .= 'Mã đơn hàng, ';
                            if($k == 'name_year') $mes .= 'Tên xe năm sản xuất, ';
                            if($k == 'quantity') $mes .= 'Số lượng, ';
                        }
                    }
                    if($check_empty){
                        $check_exits = $this->getTable()->getItem($value);
                        if(empty($check_exits)){
                            $this->getTable()->saveItem(["data" => $value], array('task' => 'import-item'));
                            $product_return[$key]['status'] = 'Hoàn thành';
                        }
                        else{
                            $product_return[$key]['status'] = 'Tồn tại';
                        }
                    }
                    else{
                        $product_return[$key]['status'] = 'Sai dữ liệu: '.$mes;
                    }
                }
                $viewModel->setVariable('results', $product_return);
                $viewModel->setVariable('heading', $heading);
            }
        }
        return $viewModel;
    }

    public function editAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $myForm = $this->getForm();

        if(!empty($this->params('id'))) {
            $item = $this->getTable()->getItem(array('id' => $this->params('id')));
            $this->_params['item'] = $item;
            $myForm->setData($item);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }

        if($this->getRequest()->isPost()){
            $myForm->setInputFilter(new \Admin\Filter\ProductReturn(array('data' => $this->_params['data'])));
            $myForm->setData($this->_params['data']);
            $controlAction = $this->_params['data']['control-action'];
            if($myForm->isValid()){
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
            else {
                $this->_viewModel['productList']  = $this->_params['data']['contract_product'];
            }
        }

        $this->_viewModel['item']	        = $item;
        $this->_viewModel['myForm']	        = $myForm;
        $this->_viewModel['caption']        = 'cập nhật sản phẩm kho: '.$item->fullName;
        return new ViewModel($this->_viewModel);
    }
}


