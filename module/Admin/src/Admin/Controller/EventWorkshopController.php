<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class EventWorkshopController extends ActionController {
    
    public function init() {
        
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\EventTable';
        $this->_options['formName'] = 'formAdminEventWorkshop';
        
        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']          = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'created';
        $this->_params['ssFilter']['order']             = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';
        $this->_params['ssFilter']['filter_status']     = $ssFilter->filter_status;
        $this->_params['ssFilter']['filter_keyword']    = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['filter_date_begin'] = $ssFilter->filter_date_begin;
        $this->_params['ssFilter']['filter_date_end']   = $ssFilter->filter_date_end;
        
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
    
            $ssFilter->pagination_option    = intval($data['pagination_option']);
    
            $ssFilter->order_by             = $data['order_by'];
            $ssFilter->order                = $data['order'];
    
            $ssFilter->filter_status        = $data['filter_status'];
            $ssFilter->filter_keyword       = $data['filter_keyword'];
            $ssFilter->filter_date_begin    = $data['filter_date_begin'];
            $ssFilter->filter_date_end      = $data['filter_date_end'];
        }
    
        $this->goRoute();
    }
    
    public function indexAction() {
        $myForm	= new \Admin\Form\Search\EventWorkshop($this->getServiceLocator());
        $myForm->setData($this->_params['ssFilter']);
        
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item', 'type' => 'workshop'));
        
        $this->_viewModel['myForm']	    	= $myForm;
        $this->_viewModel['items']      	= $items;
        $this->_viewModel['count']      	= $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['user']       	= $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['company_branch']	= $this->getServiceLocator()->get('Admin\Model\CompanyBranchTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['userInfo']		= $this->_userInfo->getUserInfo();
        $this->_viewModel['caption']    	= 'Hội thảo - Danh sách';
        return new ViewModel($this->_viewModel);
    }
    
    public function formAction() {
        $myForm			= $this->getForm();
        
        $task = 'add-item';
        $caption = 'Hội thảo - Thêm mới';
        $item = array();
        if(!empty($this->params('id'))) {
            $this->_params['data']['id']	= $this->params('id');
            $item = $this->getTable()->getItem($this->_params['data']);
            $item['time']					= unserialize($item['time']);
            if(!empty($item)) {
                $myForm->setInputFilter(new \Admin\Filter\EventWorkshop(array('id' => $this->_params['data']['id'])));
                $myForm->bind($item);
                $task = 'edit-item';
                $caption = 'Hội thảo - Sửa';
            }
        }
    
        if($this->getRequest()->isPost()){
            $myForm->setData($this->_params['data']);
    
            $controlAction = $this->_params['data']['control-action'];
            
            if($myForm->isValid()){
                $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                $result = $this->getTable()->saveItem($this->_params, array('task' => $task));
    
                $this->flashMessenger()->addMessage('Dữ liệu đã được cập nhật thành công');
    
                if($controlAction == 'save-new') {
                    $this->goRoute(array('action' => 'form'));
                } else if($controlAction == 'save') {
                    $this->goRoute(array('action' => 'form', 'id' => $result));
                } else {
                    $this->goRoute();
                }
            }
        }
    
        $this->_viewModel['myForm']	    = $myForm;
        $this->_viewModel['item']       = $item;
        $this->_viewModel['caption']    = $caption;
        return new ViewModel($this->_viewModel);
    }
    
    public function exportAction() {
        $date   = new \ZendX\Functions\Date();
    
        $events             = $this->getTable()->listItem($this->_params, array('task' => 'list-all', 'type' => 'workshop'));
    
        $items              = $this->getServiceLocator()->get('Admin\Model\EventContactTable')->listItem(array('event_ids' => array_keys($events)), array('task' => 'list-export-people-event'));
        $user               = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $company_group      = $this->getServiceLocator()->get('Admin\Model\CompanyGroupTable')->listItem(null, array('task' => 'cache'));
        $company_branch     = $this->getServiceLocator()->get('Admin\Model\CompanyBranchTable')->listItem(null, array('task' => 'cache'));
        $source_group       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('table' => 'document', 'where' => array('code' => 'source-group')), array('task' => 'cache'));
        $source_channel     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('table' => 'document', 'where' => array('code' => 'source-channel')), array('task' => 'cache'));
        $sex                = array('male' => array('name' => 'Nam'), 'female' => array('name' => 'Nữ'));
        $subject            = array('1' => array('name' => 'Sinh viên'), '2' => array('name' => 'Người đi làm'), '3' => array('name' => 'Chưa đi làm'), '4' => array('name' => 'Học sinh'), '5' => array('name' => 'Trẻ em'));
        $school             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('table' => 'document', 'where' => array('code' => 'school')), array('task' => 'cache'));
        $major              = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('table' => 'document', 'where' => array('code' => 'major')), array('task' => 'cache'));
        $location_city      = $this->getServiceLocator()->get('Admin\Model\LocationCityTable')->listItem(null, array('task' => 'cache'));
        $location_district  = $this->getServiceLocator()->get('Admin\Model\LocationDistrictTable')->listItem(null, array('task' => 'cache'));
    
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
            array('field' => 'event_id', 'title' => 'Sự kiện', 'type' => 'data_source', 'data_source' => $events),
            array('field' => 'event_id', 'title' => 'Cơ sở sự kiện', 'type' => 'branch'),
            array('field' => 'created', 'title' => 'Ngày thêm', 'type' => 'date'),
            array('field' => 'contact_phone', 'title' => 'Điện thoại'),
            array('field' => 'contact_name', 'title' => 'Tên khách hàng'),
            array('field' => 'contact_user_id', 'title' => 'Người quản lý', 'type' => 'data_source', 'data_source' => $user, 'data_source_field' => 'fullname'),
            array('field' => 'contact_company_group_id', 'title' => 'Đội nhóm', 'type' => 'data_source', 'data_source' => $company_group),
            array('field' => 'contact_company_branch_id', 'title' => 'Cơ sở', 'type' => 'data_source', 'data_source' => $company_branch),
            array('field' => 'contact_source_group_id', 'title' => 'Nhóm nguồn', 'type' => 'data_source', 'data_source' => $source_group),
            array('field' => 'contact_source_channel_id', 'title' => 'Kênh nguồn', 'type' => 'data_source', 'data_source' => $source_channel),
            array('field' => 'contact_sex', 'title' => 'Giới tính', 'type' => 'data_source', 'data_source' => $sex),
            array('field' => 'contact_subject', 'title' => 'Đối tượng', 'type' => 'data_source', 'data_source' => $subject),
            array('field' => 'contact_student_school_id', 'title' => 'Trường học', 'type' => 'data_source', 'data_source' => $school),
            array('field' => 'contact_student_major_id', 'title' => 'Ngành học', 'type' => 'data_source', 'data_source' => $major),
            array('field' => 'contact_location_city_id', 'title' => 'Tỉnh thành', 'type' => 'data_source', 'data_source' => $location_city),
            array('field' => 'contact_location_district_id', 'title' => 'Quận huyện', 'type' => 'data_source', 'data_source' => $location_district),
            array('field' => 'call', 'title' => 'Gọi điện'),
            array('field' => 'listen', 'title' => 'Nghe máy'),
            array('field' => 'no_listen', 'title' => 'Không nghe'),
            array('field' => 'busy', 'title' => 'Bận'),
            array('field' => 'wrong_number', 'title' => 'Sai số'),
            array('field' => 'sms', 'title' => 'Tin nhắn'),
            array('field' => 'mail', 'title' => 'Mal'),
            array('field' => 'agree', 'title' => 'Đồng ý'),
            array('field' => 'confirm', 'title' => 'Xác thực'),
            array('field' => 'ticket', 'title' => 'Nhận vé'),
            array('field' => 'join', 'title' => 'Tham gia'),
            array('field' => 'contact_contract', 'title' => 'đơn hàng'),
            array('field' => 'contact_test_online', 'title' => 'Test đầu vào'),
        );
    
        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();
    
        // Set document properties
        $objPHPExcel->getProperties()->setCreator($this->_userInfo->getUserInfo('fullname'))
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
                        $value = $date->fomartToView($item[$data['field']], $formatDate);
                        break;
                    case 'data_source':
                        $field = $data['data_source_field'] ? $data['data_source_field'] : 'name';
                        $value = $data['data_source'][$item[$data['field']]][$field];
                        break;
                    case 'branch':
                        $value = $events[$item[$data['field']]]['company_branch_name'];
                        break;
                    default:
                        $value = $item[$data['field']];
                }
    
                $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[$startColumn] . $startRow, $value);
                $startColumn++;
            }
            $startRow++;
        }
    
        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Export.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
    
        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0
    
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    
        return $this->response;
    }
}
