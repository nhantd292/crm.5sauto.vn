<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class EventContactController extends ActionController {
    
    public function init() {
        
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\EventContactTable';
        $this->_options['formName'] = 'formAdminEventContact';
        
        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']              = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'created';
        $this->_params['ssFilter']['order']                 = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';
        $this->_params['ssFilter']['filter_status']         = $ssFilter->filter_status;
        $this->_params['ssFilter']['filter_keyword']        = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['event_id']           	= $ssFilter->event_id;
        
        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage'] = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : 20;
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
        }
    
        $this->goRoute();
    }
    
    public function indexAction() {
        $ssFilter = new Container(__CLASS__);
    	if(!empty($this->_params['route']['id']) && ($this->_params['route']['id'] != $ssFilter->event_id)) {
        	$ssFilter->event_id = $this->_params['route']['id'];
        }
        if(empty($ssFilter->event_id)) {
            $event_list = $this->getTable()->listItem($this->_params, array('task' => 'cache'));
            $event_list = current($event_list);
            $ssFilter->event_id = $event_list['id'];
        }
        $this->_params['ssFilter']['event_id'] = $ssFilter->event_id;
        
        $myForm	= new \Admin\Form\Search\EventContact($this->getServiceLocator(), $this->_params['ssFilter']);
        $myForm->setData($this->_params['ssFilter']);
        
        $event = $this->getServiceLocator()->get('Admin\Model\EventTable')->getItem(array('id' => $ssFilter->event_id), null);
        if(empty($event)) {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        
        $this->_viewModel['idEvent']	    = $ssFilter->event_id;
        $this->_viewModel['myForm']	        = $myForm;
        $this->_viewModel['items']          = $items;
        $this->_viewModel['event']          = $event;
        $this->_viewModel['item_all']       = $this->getTable()->listItem($this->_params, array('task' => 'list-item', 'paginator' => false));
        $this->_viewModel['count']          = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['user']           = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_branch']    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['sale_group']     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['caption']        = 'Danh sách Data - ' . $event['name'];
        return new ViewModel($this->_viewModel);
    }
    
    public function addAction() {
    	$ssFilter = new Container(__CLASS__);
    	
        $myForm			= $this->getForm();
        
        $event = $this->getServiceLocator()->get('Admin\Model\EventTable')->getItem(array('id' => $ssFilter->event_id), null);
        if(empty($event)) {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
        
        if($this->getRequest()->isPost()){
            $myForm->setInputFilter(new \Admin\Filter\EventContact(array('data' => $this->_params['data'], 'route' => $this->_params['route'], 'event' => $event)));
            $myForm->setData($this->_params['data']);
    
            $controlAction = $this->_params['data']['control-action'];
            if($myForm->isValid()){ 
                $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                $this->_params['event'] = $event;
                $result = $this->getTable()->saveItem($this->_params, array('task' => 'add-item'));
    
                $this->flashMessenger()->addMessage('Dữ liệu đã được cập nhật thành công');
    
                if($controlAction == 'save-new') {
                    $this->goRoute(array('action' => 'add', 'id' => $event['id']));
                } else {
                    $this->goRoute(array('id' => $event['id']));
                }
            }
        }
    
        $this->_viewModel['myForm']	    = $myForm;
        $this->_viewModel['event']	    = $event;
        $this->_viewModel['caption']    = 'Data - '. $event['name'] .' - Thêm mới';
        return new ViewModel($this->_viewModel);
    }
    
    public function activeAction() {
        if($this->getRequest()->isXmlHttpRequest()) {
            $this->_params['item'] = $this->getTable()->getItem(array('id' => $this->_params['data']['id']), null);
            $this->_params['event'] = $this->getServiceLocator()->get('Admin\Model\EventTable')->getItem(array('id' => $this->_params['data']['event_id']), null);
            $this->getTable()->changeStatus($this->_params, array('task' => 'update-status'));
        } else {
            $this->goRoute();
        }
    
        return $this->response;
    }
    
    public function deleteAction() {
        $ssFilter = new Container(__CLASS__);
        
        if($this->getRequest()->isPost()) {
            if(!empty($this->_params['data']['cid'])) {
                $result = $this->getTable()->deleteItem($this->_params, array('task' => 'delete-item'));
                $message = 'Xóa '. $result .' phần tử thành công';
                $this->flashMessenger()->addMessage($message);
            }
        }
    
        $this->goRoute(array('id' => $ssFilter->event_id));
    }
    
    public function editAjaxAction() {
        if($this->getRequest()->isXmlHttpRequest()) {
            if($this->getRequest()->isPost()){
                if(empty($this->_params['data']['id'])) {
                    echo 'Không tìm thấy id';
                } else {
                    $this->getTable()->saveItem($this->_params, array('task' => 'update-item'));
                }
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
    
        return $this->response;
    }
    
    public function exportAction() {
    	$ssFilter = new Container(__CLASS__);
    	
        $date   = new \ZendX\Functions\Date();
        $this->_params['ssFilter']['id'] = $ssFilter->event_id;
        
        $event              = $this->getServiceLocator()->get('Admin\Model\EventTable')->getItem(array('id' => $this->_params['route']['id']), null);
        $eventBranch        = $this->getServiceLocator()->get('Admin\Model\CompanyBranchTable')->getItem(array('id' => $event['company_branch_id']), null);
        
        $items              = $this->getTable()->listItem($this->_params, array('task' => 'list-export-people-event'));
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
            array('field' => 'event_id', 'title' => 'Sự kiện', 'type' => 'name'),
            array('field' => 'branch_id', 'title' => 'Cơ sở sự kiện', 'type' => 'branch'),
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
            array('field' => 'tester', 'title' => 'Tester'),
            array('field' => 'note', 'title' => 'Ghi chú'),
            array('field' => 'feedback', 'title' => 'Phản hồi'),
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
                    case 'name':
                        $value = $event['name'];
                        break;
                    case 'branch':
                        $value = $eventBranch['name'];
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
