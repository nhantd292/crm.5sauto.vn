<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class EduClassController extends ActionController {
    
    public function init() {
        
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\EduClassTable';
        $this->_options['formName'] = 'formAdminEduClass';
        
        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']             = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'public_date';
        $this->_params['ssFilter']['order']                = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';
        $this->_params['ssFilter']['filter_keyword']       = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['filter_date_begin']    = $ssFilter->filter_date_begin;
        $this->_params['ssFilter']['filter_date_end']      = $ssFilter->filter_date_end;
        $this->_params['ssFilter']['filter_date_type']     = $ssFilter->filter_date_type;
        $this->_params['ssFilter']['filter_status']        = $ssFilter->filter_status;
        $this->_params['ssFilter']['filter_product']       = $ssFilter->filter_product;
        $this->_params['ssFilter']['filter_location']      = $ssFilter->filter_location;
        $this->_params['ssFilter']['filter_room']          = $ssFilter->filter_room;
        $this->_params['ssFilter']['filter_time']          = $ssFilter->filter_time;
        $this->_params['ssFilter']['filter_teacher']       = $ssFilter->filter_teacher;
        $this->_params['ssFilter']['filter_coach']         = $ssFilter->filter_coach;
        
        if($this->_userInfo->getUserInfo('permission_ids') == 'teacher') {
            $this->_params['ssFilter']['filter_teacher'] = $this->_userInfo->getUserInfo('id');
        }
        
        if($this->_userInfo->getUserInfo('permission_ids') == 'coach') {
            $this->_params['ssFilter']['filter_coach'] = $this->_userInfo->getUserInfo('id');
        }
        
        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage'] = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : $this->_paginator['itemCountPerPage'];
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
            $ssFilter->filter_keyword       = $data['filter_keyword'];
            $ssFilter->filter_date_begin    = $data['filter_date_begin'];
            $ssFilter->filter_date_end      = $data['filter_date_end'];
            $ssFilter->filter_date_type     = $data['filter_date_type'];
            $ssFilter->filter_status        = $data['filter_status'];
            $ssFilter->filter_product       = $data['filter_product'];
            $ssFilter->filter_location      = $data['filter_location'];
            $ssFilter->filter_room          = $data['filter_room'];
            $ssFilter->filter_time          = $data['filter_time'];
            $ssFilter->filter_teacher       = $data['filter_teacher'];
            $ssFilter->filter_coach         = $data['filter_coach'];
        }
        
        $this->goRoute();
    }
    
    public function indexAction() {
        $myForm	= new \Admin\Form\Search\EduClass($this->getServiceLocator(), $this->_params['ssFilter']);
        $myForm->setData($this->_params['ssFilter']);
        
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        
        $this->_viewModel['myForm']	        = $myForm;
        $this->_viewModel['items']          = $items;
        $this->_viewModel['count']          = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['user']           = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['product']        = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['location']       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( 'where' => array( 'code' => 'edu-location' )), array('task' => 'cache'));
        $this->_viewModel['room']           = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( 'where' => array( 'code' => 'edu-room' )), array('task' => 'cache'));
        $this->_viewModel['public_status']  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( 'where' => array( 'code' => 'edu-status' ) ), array('task' => 'cache-alias'));
        $this->_viewModel['userInfo']       = $this->_userInfo->getUserInfo();
        $this->_viewModel['caption']        = 'Lớp học - Danh sách';
        
        if(!empty($this->_params['ssFilter']['filter_teacher'])) {
            $this->_viewModel['caption'] .= ' - Giảng viên: '. $this->_viewModel['user'][$this->_params['ssFilter']['filter_teacher']]['name'];
        }
        
        if(!empty($this->_params['ssFilter']['filter_coach'])) {
            $this->_viewModel['caption'] .= ' - Trợ giảng: '. $this->_viewModel['user'][$this->_params['ssFilter']['filter_coach']]['name'];
        }
        
        return new ViewModel($this->_viewModel);
    }
    
    public function formAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $myForm = $this->getForm();
        
        $task = 'add-item';
        $caption = 'Lớp học - Thêm mới';
        $item = array();
        if(!empty($this->params('id'))) {
            $this->_params['data']['id'] = $this->params('id');
            $item = $this->getTable()->getItem($this->_params['data']);
            if(!empty($item)) {
                $item['public_date']    = $dateFormat->formatToView($item['public_date']);
                $item['end_date']       = $dateFormat->formatToView($item['end_date']);
                $item['schedule']       = $item['schedule'] ? preg_split("/[,+ ]+/", $item['schedule']) : '';
                $item['teacher_ids']    = $item['teacher_ids'] ? explode(',', $item['teacher_ids']) : null;
                $item['coach_ids']      = $item['coach_ids'] ? explode(',', $item['coach_ids']) : null;
                
                $item_options = !empty($item['options']) ? unserialize($item['options']) : array();
                $item = array_merge($item, $item_options);
                
                if(!$this->getRequest()->isPost()){
                    $myForm->setData($item);
                }
                
                if($this->params('code') == 'copy') {
                    $task = 'add-item';
                    $caption = 'Lớp học - Copy';
                } else {
                    $task = 'edit-item';
                    $caption = 'Lớp học - Sửa';
                }
            }
        }
    
        if($this->getRequest()->isPost()){
            $myForm->setInputFilter(new \Admin\Filter\EduClass(array('id' => $this->_params['data']['id'])));
            $myForm->setData($this->_params['data']);
            
            $controlAction = $this->_params['data']['control-action'];
            if($myForm->isValid()){
                $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                $this->_params['item'] = $item;
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
    
    // Xem chi tiết lớp học
    public function detailAction() {
        $item = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->getItem(array('id' => $this->_params['route']['id']), null);
         
        $this->_viewModel['caption']        = 'Chi tiết lớp học ' . $item['name'];
        $this->_viewModel['edu_class_id']   = $this->_params['route']['id'];
        return new ViewModel($this->_viewModel);
    }
    
    // Thông tin cơ bản
    public function infoGeneralAction() {
        $this->_viewModel['item']           = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->getItem(array('id' => $this->_params['route']['id']), null);
        $this->_viewModel['product']        = $this->getServiceLocator()->get('Admin\Model\ProductTable')->getItem(array('id' => $this->_viewModel['item']['product_id']), null);
        $this->_viewModel['location']       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(array('id' => $this->_viewModel['item']['location_id']), null);
        $this->_viewModel['room']           = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(array('id' => $this->_viewModel['item']['room_id']), null);
        $this->_viewModel['user']           = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['public_status']  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( 'where' => array( 'code' => 'edu-status' ) ), array('task' => 'cache-alias'));
        
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
    
    // Danh sách học viên
    public function studentAction() {
        $item  = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->getItem(array('id' => $this->_params['route']['id']), null);
        $items  = $this->getServiceLocator()->get('Admin\Model\ContractTable')->listItem(array('edu_class_id' => $item['id']), array('task' => 'list-contract-class'));
        
        if($item['student_total'] != $items->count()) {
            $this->getServiceLocator()->get('Admin\Model\EduClassTable')->saveItem(array('data' => array('id' => $item['id'], 'student_total' => $items->count())), array('task' => 'update-student'));
        }
        
        $this->_viewModel['item']           = $item;
        $this->_viewModel['items']          = $items;
        $this->_viewModel['user']           = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_group']     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['edu_class']      = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sex']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sex')), array('task' => 'cache-alias'));
        
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
        return $viewModel;
    }
    
    // Điểm danh
    public function musterAction() {
        if($this->getRequest()->isPost()){
            $item = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->getItem(array('id' => $this->_params['data']['id']), null);
            $this->_params['item'] = $item;
            if(!empty($this->_params['data']['muster'])) {
                $result = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->saveItem($this->_params, array('task' => 'update-muster'));
            }
            if(!empty($this->_params['data']['exercise'])) {
                $result = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->saveItem($this->_params, array('task' => 'update-exercise'));
            }
    
            return $this->response;
        } else {
            $item  = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->getItem(array('id' => $this->_params['route']['id']), null);
            $items  = $this->getServiceLocator()->get('Admin\Model\ContractTable')->listItem(array('edu_class_id' => $item['id']), array('task' => 'list-contract-class'));
            
            $this->_viewModel['item']           = $item;
            $this->_viewModel['items']          = $items;
            $this->_viewModel['user']           = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
            $this->_viewModel['sale_group']     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['sale_branch']    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
            $this->_viewModel['edu_class']      = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->listItem(null, array('task' => 'cache'));
            $this->_viewModel['sex']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sex')), array('task' => 'cache-alias'));
            
            $viewModel = new ViewModel($this->_viewModel);
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
    
    // Lộ trình học
    public function routeAction() {
        if($this->getRequest()->isPost()){
            $item = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->getItem(array('id' => $this->_params['data']['id']), null);
            $this->_params['item'] = $item;
            if(!empty($this->_params['data']['route'])) {
                $result = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->saveItem($this->_params, array('task' => 'update-route'));
            }
    
            return $this->response;
        } else {
            $item  = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->getItem(array('id' => $this->_params['route']['id']), null);
            $items  = $this->getServiceLocator()->get('Admin\Model\ContractTable')->listItem(array('edu_class_id' => $item['id']), array('task' => 'list-contract-class'));
    
            $this->_viewModel['item']           = $item;
            $this->_viewModel['items']          = $items;
            $this->_viewModel['user']           = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
            $this->_viewModel['sale_group']     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['sale_branch']    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
            $this->_viewModel['sex']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sex')), array('task' => 'cache-alias'));
            $this->_viewModel['edu_class']      = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->listItem(null, array('task' => 'cache'));
    
            $viewModel = new ViewModel($this->_viewModel);
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
    
    // Update ghi chú
    public function noteAction() {
        $myForm = new \Admin\Form\EduClass\Note($this->getServiceLocator());
    
        if(!empty($this->_params['data']['id'])) {
            $item = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->getItem(array('id' => $this->_params['data']['edu_class_id']), null);
            $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->_params['data']['id']));
            $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']));
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }
        
        if($this->getRequest()->isPost()){
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\EduClass\Note($this->_params));
                $myForm->setData($this->_params['data']);
                
                if($myForm->isValid()){
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['contract'] = $contract;
                    $this->_params['item'] = $item;
                    
                    $this->getServiceLocator()->get('Admin\Model\EduClassTable')->saveItem($this->_params, array('task' => 'update-note'));
    
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
        $this->_viewModel['caption']    = 'Ghi chú học viên';
    
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
    
    // Điểm học phần
    public function testAction() {
        if($this->getRequest()->isPost()){
            $item = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->getItem(array('id' => $this->_params['data']['id']), null);
            $this->_params['item'] = $item;
            $result = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->saveItem($this->_params, array('task' => 'update-point'));
    
            return $this->response;
        } else {
            $item  = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->getItem(array('id' => $this->_params['route']['id']), null);
            $items  = $this->getServiceLocator()->get('Admin\Model\ContractTable')->listItem(array('edu_class_id' => $item['id']), array('task' => 'list-contract-class'));
            
            $this->_viewModel['item']           = $item;
            $this->_viewModel['items']          = $items;
            $this->_viewModel['user']           = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
            $this->_viewModel['sale_group']     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['sale_branch']    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
            $this->_viewModel['product']        = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
            $this->_viewModel['edu_class']      = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->listItem(null, array('task' => 'cache'));
            $this->_viewModel['sex']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sex')), array('task' => 'cache-alias'));
            $this->_viewModel['product_type']   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-type')), array('task' => 'cache-alias'));
            $this->_viewModel['edu_point']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array( 'code' => 'edu-point')), array('task' => 'cache'));
            $this->_viewModel['edu_point_type'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array( 'code' => 'edu-point-type')), array('task' => 'cache'));
            
            $viewModel = new ViewModel($this->_viewModel);
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
    
    public function exportAction() {
        $date               = new \ZendX\Functions\Date();
    
        $items              = $this->getTable()->listItem($this->_params, array('task' => 'list-item', 'paginator' => false));
        $user               = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $sale_group         = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $sale_branch        = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $product            = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
        $location           = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'edu-location')), array('task' => 'cache'));
        $room               = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'edu-room')), array('task' => 'cache'));
    
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
            array('field' => 'name', 'title' => 'Mã lớp'),
            array('field' => 'public_date', 'title' => 'Khai giảng', 'type' => 'date'),
            array('field' => 'product_id', 'title' => 'Khóa học', 'type' => 'data_source', 'data_source' => $product),
            array('field' => 'location_id', 'title' => 'Địa điểm', 'type' => 'data_source', 'data_source' => $location),
            array('field' => 'room_id', 'title' => 'Phòng học', 'type' => 'data_source', 'data_source' => $room),
            array('field' => 'teacher_id', 'title' => 'Giảng viên', 'type' => 'data_source', 'data_source' => $user),
            array('field' => 'student_max', 'title' => 'Học viên tối đa'),
            array('field' => 'student_total', 'title' => 'Học viên hiện tại'),
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
                        $value = $date->formatToView($item[$data['field']], $formatDate);
                        break;
                    case 'data_source':
                        $field = $data['data_source_field'] ? $data['data_source_field'] : 'name';
                        $value = $data['data_source'][$item[$data['field']]][$field];
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
