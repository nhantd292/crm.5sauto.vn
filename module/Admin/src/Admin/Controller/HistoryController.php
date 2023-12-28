<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class HistoryController extends ActionController {
    
    public function init() {
        
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\HistoryTable';
        $this->_options['formName'] = 'formAdminHistory';
        
        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']              = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'created';
        $this->_params['ssFilter']['order']                 = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';
        $this->_params['ssFilter']['filter_keyword']        = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['filter_date_begin']     = $ssFilter->filter_date_begin;
        $this->_params['ssFilter']['filter_date_end']       = $ssFilter->filter_date_end;
        $this->_params['ssFilter']['filter_date_type']      = $ssFilter->filter_date_type;
        $this->_params['ssFilter']['filter_action']         = $ssFilter->filter_action;
        $this->_params['ssFilter']['filter_result']         = $ssFilter->filter_result;
        $this->_params['ssFilter']['filter_sale_branch']    = $ssFilter->filter_sale_branch ? $ssFilter->filter_sale_branch : $this->_userInfo->getUserInfo()->sale_branch_id;
        
        $sale_group_ids = $this->_userInfo->getUserInfo('sale_group_ids') ? explode(',', $this->_userInfo->getUserInfo('sale_group_ids')) : array();
        if(!empty($ssFilter->filter_sale_group)) {
            $this->_params['ssFilter']['filter_sale_group'] = $ssFilter->filter_sale_group;
        } else {
            if(!empty($this->_userInfo->getUserInfo('sale_group_id') && count($sale_group_ids) <= 1)) {
                $this->_params['ssFilter']['filter_sale_group'] = $this->_userInfo->getUserInfo('sale_group_id');
            }
        }
        
        if(!empty($ssFilter->filter_user)) {
            $this->_params['ssFilter']['filter_user'] = $ssFilter->filter_user;
        } else {
            if(!empty($this->_userInfo->getUserInfo('sale_group_id')) && empty($this->_userInfo->getUserInfo('sale_group_ids'))) {
                $this->_params['ssFilter']['filter_user'] = $this->_userInfo->getUserInfo('id');
            }
        }
        
        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage'] = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : $this->_paginator['itemCountPerPage'];
        $this->_paginator['currentPageNumber'] = $this->params()->fromRoute('page', 1);
        $this->_params['paginator'] = $this->_paginator;
        
        // Lấy dữ liệu post của form
        $this->_params['data'] = $this->getRequest()->getPost()->toArray();
        
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
        $this->_viewModel['curent_user']  = $this->_userInfo->getUserInfo();
    }
    
    public function filterAction() {
    
        if($this->getRequest()->isPost()) {
            $ssFilter	= new Container(__CLASS__);
            $data = $this->_params['data'];
    
            $ssFilter->pagination_option    = intval($data['pagination_option']);
            $ssFilter->order_by             = $data['order_by'];
            $ssFilter->order                = $data['order'];
            $ssFilter->filter_keyword       = $data['filter_keyword'];
            $ssFilter->filter_date_begin    = $data['filter_date_begin'];
            $ssFilter->filter_date_end      = $data['filter_date_end'];
            $ssFilter->filter_date_type     = $data['filter_date_type'];
            $ssFilter->filter_action        = $data['filter_action'];
            $ssFilter->filter_result        = $data['filter_result'];
            
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
        $ssFilter = new Container(__CLASS__.'index');

        $curent_user = $this->_userInfo->getUserInfo();
        $permission_ids = explode(',', $curent_user['permission_ids']);
        if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids) && !in_array(MANAGER, $permission_ids)){
            if(in_array(GDCN, $permission_ids)){
                $this->_params['ssFilter']['filter_sale_branch'] = $curent_user['sale_branch_id'];
                $ssFilter->filter_sale_branch = $curent_user['sale_branch_id'];
            }
        }
        $myForm	= new \Admin\Form\Search\History($this->getServiceLocator(), $this->_params['ssFilter']);
        $myForm->setData($this->_params['ssFilter']);
        
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        
        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $items;
        $this->_viewModel['count']                  = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['user']                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_group']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['sale_contact_type']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contact-type')), array('task' => 'cache'));
        $this->_viewModel['sale_history_action']    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-action')), array('task' => 'cache'));
        $this->_viewModel['sale_history_result']    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-result')), array('task' => 'cache'));
        $this->_viewModel['sale_history_type']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array("where" => array("code" => "sale-history-type" )), array('task' => 'cache'));

        $this->_viewModel['caption']                = 'Lịch sử chăm sóc - Danh sách';
        return new ViewModel($this->_viewModel);
    }
    
    public function exportAction() {
        $dateFormat             = new \ZendX\Functions\Date();
    
        $items                  = $this->getTable()->listItem($this->_params, array('task' => 'list-item', 'paginator' => false));
    
        $user                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $sale_group             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $sale_branch            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $sale_contact_type      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contact-type')), array('task' => 'cache'));
        $sale_history_action    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-action')), array('task' => 'cache'));
        $sale_history_result    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-result')), array('task' => 'cache'));
        $location_city          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'location-city')), array('task' => 'cache'));
        $location_district      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'location-district')), array('task' => 'cache'));
    
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
            array('field' => 'created', 'title' => 'Ngày', 'type' => 'date', 'format' => 'd/m/Y'),
            array('field' => 'created', 'title' => 'Tháng', 'type' => 'date', 'format' => 'm'),
            array('field' => 'contact_phone', 'title' => 'Điện thoại', 'type' => 'phone'),
            array('field' => 'contact_name', 'title' => 'Họ tên'),
            array('field' => 'contact_email', 'title' => 'Email'),
            array('field' => 'contact_birthday_year', 'title' => 'Năm sinh'),
            array('field' => 'contact_location_city_id', 'title' => 'Tỉnh thành', 'type' => 'data_source', 'data_source' => $location_city),
            array('field' => 'contact_location_district_id', 'title' => 'Quận/huyện', 'type' => 'data_source', 'data_source' => $location_district),
            array('field' => 'user_id', 'title' => 'Người quản lý', 'type' => 'data_source', 'data_source' => $user, 'data_source_field' => 'name'),
            array('field' => 'sale_group_id', 'title' => 'Đội nhóm', 'type' => 'data_source', 'data_source' => $sale_group),
            array('field' => 'sale_branch_id', 'title' => 'Cơ sở', 'type' => 'data_source', 'data_source' => $sale_branch),
            array('field' => 'created_by', 'title' => 'Người tạo', 'type' => 'data_source', 'data_source' => $user, 'data_source_field' => 'name'),
            array('field' => 'action_id', 'title' => 'Hành động', 'type' => 'data_source', 'data_source' => $sale_history_action),
            array('field' => 'result_id', 'title' => 'Kết quả', 'type' => 'data_source', 'data_source' => $sale_history_result),
            array('field' => 'content', 'title' => 'Nội dung/Ghi chú'),
            array('field' => 'return', 'title' => 'Ngày chăm sóc lại', 'type' => 'date', 'format' => 'd/m/Y'),
            array('field' => 'return_content', 'title' => 'Nội dung chăm sóc lại'),
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
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
    
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    
        return $this->response;
    }

//    public function deleteAction() {
//        if($this->getRequest()->isPost()) {
//            if(!empty($this->_params['data']['cid'])) {
//                $history_type = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "sale-history-type" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'alias'));
//                foreach ($this->_params['data']['cid'] as $id){
//                    $history = $result = $this->getTable()->getItem(array('id' => $id), null);
//                    if($history_type[$history['type_id']] == DA_CHOT){
//                        $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem(array('id' => $history['contact_id'],), array('task' => 'update-latched'));
//                    }
//                }
//                $result = $this->getTable()->deleteItem($this->_params, array('task' => 'delete-item'));
//
//                $message = 'Xóa '. $result .' phần tử thành công';
//                $this->flashMessenger()->addMessage($message);
//            }
//        }
//
//        $this->goRoute(array('action' => 'index'));
//    }
}
