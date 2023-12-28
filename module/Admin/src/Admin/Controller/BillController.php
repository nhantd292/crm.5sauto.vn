<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class BillController extends ActionController {
    
    public function init() {
        
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\BillTable';
        $this->_options['formName'] = 'formAdminBill';
        
        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']                  = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'date';
        $this->_params['ssFilter']['order']                     = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';
        $this->_params['ssFilter']['filter_keyword']            = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['filter_date_begin']         = $ssFilter->filter_date_begin;
        $this->_params['ssFilter']['filter_date_end']           = $ssFilter->filter_date_end;
        $this->_params['ssFilter']['filter_date_type']          = $ssFilter->filter_date_type;
        $this->_params['ssFilter']['filter_sale_branch']        = $ssFilter->filter_sale_branch;
        $this->_params['ssFilter']['filter_sale_group']         = $ssFilter->filter_sale_group;
        $this->_params['ssFilter']['filter_user']               = $ssFilter->filter_user;
        $this->_params['ssFilter']['filter_type']               = $ssFilter->filter_type;
        $this->_params['ssFilter']['filter_bill_type']          = $ssFilter->filter_bill_type;
        
        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage'] = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : 50;
        $this->_paginator['currentPageNumber'] = $this->params()->fromRoute('page', 1);
        $this->_params['paginator'] = $this->_paginator;
        
        // Lấy dữ liệu post của form
        $this->_params['data'] = $this->getRequest()->getPost()->toArray();
        
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
    }
    
    public function filterAction() {
    
        if($this->getRequest()->isPost()) {
            $ssFilter	= new Container(__CLASS__);
            $data = $this->_params['data'];
    
            $ssFilter->pagination_option        = intval($data['pagination_option']);
            $ssFilter->order_by                 = $data['order_by'];
            $ssFilter->order                    = $data['order'];
            $ssFilter->filter_keyword           = $data['filter_keyword'];
            $ssFilter->filter_date_begin        = $data['filter_date_begin'];
            $ssFilter->filter_date_end          = $data['filter_date_end'];
            $ssFilter->filter_date_type         = $data['filter_date_type'];
            $ssFilter->filter_type              = $data['filter_type'];
            $ssFilter->filter_bill_type         = $data['filter_bill_type'];
            
            $ssFilter->filter_user              = $data['filter_user'];
            
            if(!empty($data['filter_sale_group'])) {
                if($ssFilter->filter_sale_group != $data['filter_sale_group']) {
                    $ssFilter->filter_user = null;
                    $ssFilter->filter_sale_group = $data['filter_sale_group'];
                }
            } else {
                $ssFilter->filter_user = null;
                $ssFilter->filter_sale_group = $data['filter_sale_group'];
            }
            
            if(!empty($data['filter_sale_branch'])) {
                if($ssFilter->filter_sale_branch != $data['filter_sale_branch']) {
                    $ssFilter->filter_sale_group = null;
                    $ssFilter->filter_user = null;
                    $ssFilter->filter_sale_branch = $data['filter_sale_branch'];
                }
            } else {
                $ssFilter->filter_sale_group = null;
                $ssFilter->filter_user = null;
                $ssFilter->filter_sale_branch = $data['filter_sale_branch'];
            }
        }
    
        $this->goRoute();
    }
    
    public function indexAction() {
        $myForm	= new \Admin\Form\Search\Bill($this->getServiceLocator(), $this->_params['ssFilter']);
        $myForm->setData($this->_params['ssFilter']);
        
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        
        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $items;
        $this->_viewModel['type']                   = array('paid' => 'Thu', 'accrued' => 'Chi', 'surcharge' => 'Phụ phí');
        $this->_viewModel['count']                  = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['user']                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_group']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['bill_type']              = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-bill-type')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['product']                = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['caption']                = 'Hóa đơn - Danh sách';
        return new ViewModel($this->_viewModel);
    }
    
    public function updateAction() {
        $user           = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
            
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-branch'));
        echo '<pre>';
        print_r($items);
        echo '</pre>';
        foreach ($items AS $item) {
            $branch_id = !empty($user[$item['created_by']]['company_branch_id']) ? $user[$item['created_by']]['company_branch_id'] : $item['company_branch_id'];
            if(!empty($branch_id)) {
                $data = array(
                    'id' => $item['id'],
                    'branch_id' => $branch_id,
                );
                $update = $this->getTable()->saveItem(array('data' => $data), array('task' => 'update_branch'));
            }
        }

        return $this->response;
    }
    
    public function printAction() {
        $item       = $this->getServiceLocator()->get('Admin\Model\BillTable')->getItem(array('id' => $this->_params['route']['id']));
        $contract   = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $item['contract_id']));
        $contact    = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']));
        
        if(empty($item)) {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }
        
        $this->_viewModel['item']           = $item;
        $this->_viewModel['contact']        = $contact;
        $this->_viewModel['contract']       = $contract;
        $this->_viewModel['company_branch'] = $this->getServiceLocator()->get('Admin\Model\CompanyBranchTable')->listItem(null, array('task' => 'cache'));
        
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
        
        return $viewModel;
    }
    
    public function exportAction() {
        $dateFormat         = new \ZendX\Functions\Date();
    
        $items              = $this->getTable()->listItem($this->_params, array('task' => 'list-item', 'paginator' => false));
        $user               = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $sale_group         = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $sale_branch        = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $bill_type          = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-bill-type')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $product            = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
        $edu_class          = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->listItem(null, array('task' => 'cache'));
    
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
            array('field' => 'date', 'title' => 'Ngày chứng từ', 'type' => 'date', 'format' => 'd/m/Y'),
            array('field' => 'contract_date', 'title' => 'Ngày đơn hàng', 'type' => 'date', 'format' => 'd/m/Y'),
            array('field' => 'type', 'title' => 'Phân loại'),
            array('field' => 'index', 'title' => 'Số chứng từ'),
            array('field' => 'paid_price', 'title' => 'Thu'),
            array('field' => 'accrued_price', 'title' => 'Chi'),
            array('field' => 'contract_price_owed', 'title' => 'Còn nợ'),
            array('field' => 'bill_type_id', 'title' => 'Hình thức', 'type' => 'data_source', 'data_source' => $bill_type),
            array('field' => 'contact_phone', 'title' => 'Điện thoại'),
            array('field' => 'contact_name', 'title' => 'Tên khách hàng'),
            array('field' => 'contract_product_id', 'title' => 'Sản phẩm', 'type' => 'data_source', 'data_source' => $product),
            array('field' => 'contract_edu_class_id', 'title' => 'Lớp học', 'type' => 'data_source', 'data_source' => $edu_class),
            array('field' => 'created_by', 'title' => 'Người tạo', 'type' => 'data_source', 'data_source' => $user),
            array('field' => 'user_id', 'title' => 'Người quản lý', 'type' => 'data_source', 'data_source' => $user),
            array('field' => 'sale_group_id', 'title' => 'Đội nhóm', 'type' => 'data_source', 'data_source' => $sale_group),
            array('field' => 'sale_branch_id', 'title' => 'Cơ sở', 'type' => 'data_source', 'data_source' => $sale_branch),
            array('field' => 'note', 'title' => 'Ghi chú', 'type' => 'data_serialize', 'data_serialize_field' => 'options'),
        );
    
        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();
    
        // Set document properties
        $objPHPExcel->getProperties()->setCreator($this->_userInfo->getUserInfo('name'))
                                     ->setLastModifiedBy($this->_userInfo->getUserInfo('username'))
                                     ->setTitle("Export");
    
        // Dữ liệu tiêu đề cột
        $startColumn = $config['startColumn'];
        foreach ($arrData AS $key => $data) {
            $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[$startColumn] . $config['headRow'], $data['title']);
            $objPHPExcel->getActiveSheet()->getStyle($arrColumn[$startColumn] . $config['headRow'])->getFont()->setBold(true);
            $startColumn++;
        }
    
        // Dữ liệu data
        $startRow = $config['startRow'];
        foreach ($items AS $item) {
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


