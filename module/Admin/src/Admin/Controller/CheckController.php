<?php
/*
* Controller giục đơn
*/
namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class CheckController extends ActionController {

    public function init() {
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\CheckTable';
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

    // Danh sách đơn Tỉnh
    public function indexAction() {
        $ssFilter       = new Container(__CLASS__.'index');
        $curent_user = $this->_userInfo->getUserInfo();
        $permission_ids = explode(',', $curent_user['permission_ids']);
        if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids) && !in_array(MANAGER, $permission_ids) && !in_array(CHECK_MANAGER, $permission_ids)){
            if(in_array(GDCN, $permission_ids)){
                $this->_params['ssFilter']['filter_sale_branch'] = $curent_user['sale_branch_id'];
                $ssFilter->filter_sale_branch = $curent_user['sale_branch_id'];
            }
            else{
                $this->_params['ssFilter']['filter_shipper_id'] = $curent_user['id'];
                $ssFilter->filter_shipper_id = $curent_user['id'];
            }
        }

        $myForm	= new \Admin\Form\Search\Check($this->getServiceLocator(), $this->_params['ssFilter']);
        $myForm->setData($this->_params['ssFilter']);

        // id của loại đơn tỉnh
        $productionType = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-type')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $id_product_type = $productionType[DON_TINH]['id'];
        $this->_params['ssFilter']['filter_product_type_id'] = $id_product_type;

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

        $this->_viewModel['caption']                = 'Đơn hàng - Danh sách';

        return new ViewModel($this->_viewModel);
    }

    // Danh sách đơn Hà Nội
    public function internalAction() {
        $ssFilter       = new Container(__CLASS__.'internal');
        $curent_user = $this->_userInfo->getUserInfo();
        $permission_ids = explode(',', $curent_user['permission_ids']);
        if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids) && !in_array(MANAGER, $permission_ids) && !in_array(CHECK_MANAGER, $permission_ids)){
            if(in_array(GDCN, $permission_ids)){
                $this->_params['ssFilter']['filter_sale_branch'] = $curent_user['sale_branch_id'];
                $ssFilter->filter_sale_branch = $curent_user['sale_branch_id'];
            }
            else{
                $this->_params['ssFilter']['filter_shipper_id'] = $curent_user['id'];
                $ssFilter->filter_shipper_id = $curent_user['id'];
            }
        }

        $myForm	= new \Admin\Form\Search\Check($this->getServiceLocator(), $this->_params['ssFilter']);
        $myForm->setData($this->_params['ssFilter']);
        // id của loại đơn Hà Nội
        $productionType = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-type')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $id_product_type = $productionType[DON_HA_NOI]['id'];
        $this->_params['ssFilter']['filter_product_type_id'] = $id_product_type;

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

        $this->_viewModel['caption']                = 'Đơn hàng - Danh sách';

        return new ViewModel($this->_viewModel);
    }

    public function importStatusAction()
    {
        $myForm = new \Admin\Form\Check\Import($this->getServiceLocator(), $this->_params);
        $myForm->setInputFilter(new \Admin\Filter\Check\Import($this->_params));
        $this->_viewModel['caption'] = 'Nhập trạng thái từ giao vận';
        $this->_viewModel['myForm']  = $myForm;
        $viewModel                   = new ViewModel($this->_viewModel);

        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($this->getRequest()->isPost()) {
                $itemByBillCode   = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('bill_code' => $this->_params['data']['bill_code']), array('task' => 'by-bill-code'));
                $statusName       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(array('alias' => $this->_params['data']['status_check_id'],  'code' => 'status-check'), array('task' => 'by-custom-alias'));
                if (empty($itemByBillCode)) {
                    echo 'Mã vận đơn không tồn tại';
                } else {
                    if($itemByBillCode['lock']){
                        echo 'Đơn hàng đã khóa';
                    }
                    else {
                        $this->_params['data']['id'] = $itemByBillCode['id'];
                        if ($statusName) {
                            $this->_params['data']['status_check_id'] = $statusName['alias'];
                        }
                        $this->_params['data']['price_transport'] = $this->_params['data']['price_transport'];
                        $check = $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params,
                            array('task' => 'update-status'));
                        echo 'Hoàn thành';
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
                    $historyId = $this->getServiceLocator()->get('Admin\Model\HistoryImportTable')->saveItem($this->_params, array('task' => 'add-item'));
                    $viewModel->setVariable('historyId', $historyId);
                }
            }
        }

        return $viewModel;
    }

    public function importStatusInternalAction()
    {
        $myForm = new \Admin\Form\Check\ImportInternal($this->getServiceLocator(), $this->_params);
        $myForm->setInputFilter(new \Admin\Filter\Check\ImportInternal($this->_params));
        $this->_viewModel['caption'] = 'Nhập trạng thái từ giao vận';
        $this->_viewModel['myForm']  = $myForm;
        $viewModel                   = new ViewModel($this->_viewModel);

        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($this->getRequest()->isPost()) {
                $itemByBillCode   = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('code' => $this->_params['data']['bill_code']), array('task' => 'by-code'));
                $statusName       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(array('alias' => $this->_params['data']['status_check_id'],  'code' => 'status-check'), array('task' => 'by-custom-alias'));
                if (empty($itemByBillCode)) {
                    echo 'Mã đơn hàng không tồn tại';
                } else {
                    if($itemByBillCode['lock']){
                        echo 'Đơn hàng đã khóa';
                    }
                    else {
                        $this->_params['data']['id'] = $itemByBillCode['id'];
                        if ($statusName) {
                            $this->_params['data']['status_check_id'] = $statusName['alias'];
                        }
                        $this->_params['data']['price_transport'] = $this->_params['data']['price_transport'];
                        $check = $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params,
                            array('task' => 'update-status'));
                        echo 'Hoàn thành';
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
                    $historyId = $this->getServiceLocator()->get('Admin\Model\HistoryImportTable')->saveItem($this->_params, array('task' => 'add-item'));
                    $viewModel->setVariable('historyId', $historyId);
                }
            }
        }

        return $viewModel;
    }

    // Sửa trạng thái
    public function editStatusAction() {
        $myForm = new \Admin\Form\Check\EditStatus($this->getServiceLocator(), $this->_params);

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
                $myForm->setInputFilter(new \Admin\Filter\Check\EditStatus($this->_params));
                $myForm->setData($this->_params['data']);

                if($myForm->isValid()){
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);

                    $result = $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params, array('task' => 'update-status'));

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
        $this->_viewModel['caption']    = 'Sửa trạng thái';

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Cập nhật mã vận đơn
    public function importAction()
    {
        $myForm = new \Admin\Form\Contract\Import($this->getServiceLocator(), $this->_params);
        $myForm->setInputFilter(new \Admin\Filter\Contract\Import($this->_params));
        $this->_viewModel['caption'] = 'Cập nhật mã vận đơn';
        $this->_viewModel['myForm']  = $myForm;
        $viewModel                   = new ViewModel($this->_viewModel);

        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($this->getRequest()->isPost()) {
                //Check liên hệ
                $itemByCode                  = $this->getTable()->getItem(array('code' => $this->_params['data']['code']), array('task' => 'by-code'));
                $itemByBillCode              = $this->getTable()->getItem(array('bill_code' => $this->_params['data']['bill_code']), array('task' => 'by-bill-code'));
                $this->_params['data']['id'] = $itemByCode['id'];
                $user_transport =  \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-positons-care')), array('key' => 'name', 'value' => 'id'));

                if (empty($itemByCode)) {
                    echo 'Mã đơn hàng không tồn tại';
                } else {
                    if($itemByCode['lock']){
                        echo 'Đơn hàng đã khóa';
                    }
                    else{
                        if (!empty($itemByBillCode) AND $itemByBillCode['code'] != $this->_params['data']['code']) {
                            echo 'Mã vận đơn đã tồn tại';
                        } else {
                            if(!empty($this->_params['data']['shipper_id'])){
                                if(array_key_exists($this->_params['data']['shipper_id'], $user_transport)){
                                    $this->_params['data']['shipper_id'] = $user_transport[$this->_params['data']['shipper_id']];
                                    $this->getTable()->saveItem($this->_params, array('task' => 'import-update'));
                                    echo 'Hoàn thành';
                                }
                                else{
                                    echo 'Nhân viên giao hàng không tồn tại';
                                }
                            }
                            else{
                                $this->getTable()->saveItem($this->_params, array('task' => 'import-update'));
                                echo 'Hoàn thành';
                            }
                        }
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

    // Thêm lịch sử chăm sóc đơn hàng
    public function addHistoryContractAction() {
        $myForm = new \Admin\Form\Check\AddHistoryContract($this->getServiceLocator(), $this->_params);

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
                $myForm->setInputFilter(new \Admin\Filter\Check\AddHistoryContract($this->_params));
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

    // Hoàn đơn hàng
    public function refundContractAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $myForm = new \Admin\Form\Check\RefundContract($this->getServiceLocator(), $this->_params);

        if(!empty($this->_params['data']['id'])) {
            $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->_params['data']['id']));
            $contract_options = !empty($contract['options']) ? unserialize($contract['options']) : array();
            $contract = array_merge($contract, $contract_options);
            $contract['date'] = $dateFormat->formatToView($contract['date']);
            $contract['production_date'] = $dateFormat->formatToView($contract['production_date']);
            $contract['history_content'] = nl2br($contract_options['refund_note']);

            $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']));

            $myForm->setData($contract);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }

        if($this->getRequest()->isPost()){
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\Check\RefundContract($this->_params));
                $myForm->setData($this->_params['data']);

                if($myForm->isValid()){
                    $contract_product = $this->_params['data']['contract_product']?:[];
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['data']['contract_product'] = $contract_product;
                    $this->_params['item'] = $contract;

                    $result = $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params, array('task' => 'update-refund-contract'));
                    if($result == 'er1'){
                        $this->flashMessenger()->addMessage('Tách đơn thất bại! Không được trả toàn bộ sản phẩm');
                    }
                    else if($result == 'er2'){
                        $this->flashMessenger()->addMessage('Tách đơn thất bại! Không có sản phẩm trả lại');
                    }
                    else{
                        $this->flashMessenger()->addMessage('Cập nhật thông tin hoàn đơn thành công. Đơn mới đã được tạo');
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

        $this->_viewModel['caption']          = 'Hoàn đơn';

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }
    // Tách đơn hàng cần hoàn
    public function refundSplitContractAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $myForm = new \Admin\Form\Check\RefundContract($this->getServiceLocator(), $this->_params);

        if(!empty($this->_params['data']['id'])) {
            $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->_params['data']['id']));
            $contract_options = !empty($contract['options']) ? unserialize($contract['options']) : array();
            $contract = array_merge($contract, $contract_options);
            $contract['date'] = $dateFormat->formatToView($contract['date']);
            $contract['production_date'] = $dateFormat->formatToView($contract['production_date']);
            $contract['history_content'] = nl2br($contract_options['refund_note']);

            $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']));

            $myForm->setData($contract);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }

        if($this->getRequest()->isPost()){
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\Check\RefundContract($this->_params));
                $myForm->setData($this->_params['data']);

                if($myForm->isValid()){
                    $contract_product = $this->_params['data']['contract_product']?:[];
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['contract'] = $contract;
                    $this->_params['contact'] = $contact;

                    $contract_id = $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params, array('task' => 'approve-refund-contract'));
                    $contract_new = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(['id'=>$contract_id]);
                    $this->flashMessenger()->addMessage('Hoàn đơn thành công. Đơn mới đã được tạo.');
                    echo 'success';
                    return $this->response;
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

        $this->_viewModel['caption']          = 'Hoàn đơn';

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
        $viewModel->setTemplate('admin/check/refund-contract.phtml');

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
}