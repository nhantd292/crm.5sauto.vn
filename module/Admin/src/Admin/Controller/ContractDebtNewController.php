<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class ContractDebtNewController extends ActionController {
    
    public function init() {
        
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\ContractTable';
        $this->_options['formName'] = 'formAdminContract';
        
        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']          = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'date';
        $this->_params['ssFilter']['order']             = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';
        $this->_params['ssFilter']['filter_keyword']    = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['filter_user']       = $this->_userInfo->getUserInfo('id');
        $this->_params['ssFilter']['filter_debt']       = 'debt_new';
        
        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage'] = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : $this->_paginator['itemCountPerPage'];
        $this->_paginator['currentPageNumber'] = $this->params()->fromRoute('page', 1);
        $this->_params['paginator'] = $this->_paginator;
        
        // Lấy dữ liệu post của form
        $this->_params['data'] = $this->getRequest()->getPost()->toArray();
        
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
    }
    
    // Tìm kiếm
    public function filterAction() {
    
        if($this->getRequest()->isPost()) {
            $ssFilter	= new Container(__CLASS__);
            $data = $this->_params['data'];
    
            $ssFilter->pagination_option        = intval($data['pagination_option']);
            $ssFilter->order_by                 = $data['order_by'];
            $ssFilter->order                    = $data['order'];
            $ssFilter->filter_keyword           = $data['filter_keyword'];
            
            $ssFilter->filter_user              = $data['filter_user'];
        }
    
        $this->goRoute();
    }
    
    // Danh sách
    public function indexAction() {
        $myForm	= new \Admin\Form\Search\Contract($this->getServiceLocator(), $this->_params);
        $myForm->setData($this->_params['ssFilter']);
        
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        
        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $items;
        $this->_viewModel['count']                  = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['user']                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_group']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['contract_type']          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contract-type')), array('task' => 'cache'));
        $this->_viewModel['contract_use_status']    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contract-use-status')), array('task' => 'cache'));
        $this->_viewModel['product']                = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['edu_class']              = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['caption']                = 'đơn hàng công nợ mới - Danh sách';
        
        return new ViewModel($this->_viewModel);
    }
    
    public function exportAction() {
        $dateFormat             = new \ZendX\Functions\Date();
        
        $items                  = $this->getTable()->listItem($this->_params, array('task' => 'list-item', 'paginator' => false));
        $user                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $sale_group             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $sale_branch            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $contract_type          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contract-type')), array('task' => 'cache'));
        $contract_use_status    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contract-use-status')), array('task' => 'cache'));
        $sale_contact_type      = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contact-type')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $sale_history_action    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-action')), array('task' => 'cache'));
        $sale_history_result    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-result')), array('task' => 'cache'));
        $sale_source_group      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-source-group')), array('task' => 'cache'));
        $sale_source_access     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-source-access')), array('task' => 'cache'));
        $location_city          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'location-city')), array('task' => 'cache'));
        $location_district      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'location-district')), array('task' => 'cache'));
        $product                = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
        $edu_class              = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->listItem(null, array('task' => 'cache'));
        
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
            array('field' => 'date', 'title' => 'Ngày', 'type' => 'date', 'format' => 'd/m/Y'),
            array('field' => 'date', 'title' => 'Tháng', 'type' => 'date', 'format' => 'm'),
            array('field' => 'price', 'title' => 'Đơn giá'),
            array('field' => 'price_promotion', 'title' => 'Ưu đãi'),
            array('field' => 'price_total', 'title' => 'Thành tiền'),
            array('field' => 'price_paid', 'title' => 'Đã đóng'),
            array('field' => 'price_accrued', 'title' => 'Đã chi'),
            array('field' => 'price_owed', 'title' => 'Công nợ'),
            array('field' => 'price_surcharge', 'title' => 'Phụ phí'),
            array('field' => 'promotion_content', 'title' => 'Lý do ưu đãi', 'type' => 'data_serialize', 'data_serialize_field' => 'options'),
            array('field' => 'product_id', 'title' => 'Sản phẩm', 'type' => 'data_source', 'data_source' => $product),
            array('field' => 'edu_class_id', 'title' => 'Lớp học', 'type' => 'data_source', 'data_source' => $edu_class),
            array('field' => 'contact_phone', 'title' => 'Điện thoại', 'type' => 'phone'),
            array('field' => 'contact_name', 'title' => 'Họ tên'),
            array('field' => 'contact_email', 'title' => 'Email'),
            array('field' => 'contact_birthday_year', 'title' => 'Năm sinh'),
            array('field' => 'contact_location_city_id', 'title' => 'Tỉnh thành', 'type' => 'data_source', 'data_source' => $location_city),
            array('field' => 'contact_location_district_id', 'title' => 'Quận/huyện', 'type' => 'data_source', 'data_source' => $location_district),
            array('field' => 'address', 'title' => 'Địa chỉ', 'type' => 'data_serialize', 'data_serialize_field' => 'contact_options'),
            array('field' => 'facebook', 'title' => 'Facebook', 'type' => 'data_serialize', 'data_serialize_field' => 'contact_options'),
            array('field' => 'user_id', 'title' => 'Người quản lý', 'type' => 'data_source', 'data_source' => $user, 'data_source_field' => 'name'),
            array('field' => 'sale_group_id', 'title' => 'Đội nhóm', 'type' => 'data_source', 'data_source' => $sale_group),
            array('field' => 'sale_branch_id', 'title' => 'Cơ sở', 'type' => 'data_source', 'data_source' => $sale_branch),
            array('field' => 'contact_type', 'title' => 'Phân loại khách hàng', 'type' => 'data_serialize', 'data_serialize_field' => 'options', 'data_source' => $sale_contact_type, 'data_source_field' => 'name'),
            array('field' => 'contact_source_group_id', 'title' => 'Nguồn khách hàng', 'type' => 'data_serialize', 'data_serialize_field' => 'options', 'data_source' => $sale_source_group, 'data_source_field' => 'name'),
            array('field' => 'contact_history_created', 'title' => 'Ngày chăm sóc cuối', 'type' => 'data_serialize', 'data_serialize_field' => 'options', 'data_date_format' => 'd/m/Y'),
            array('field' => 'contact_store', 'title' => 'Ngày kho', 'type' => 'data_serialize', 'data_serialize_field' => 'options', 'data_date_format' => 'd/m/Y'),
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


