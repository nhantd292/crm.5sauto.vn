<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class CampaignDataController extends ActionController {
    
    public function init() {
        
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\CampaignDataTable';
        $this->_options['formName'] = 'formAdminCampaignData';
        
        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']              = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'created';
        $this->_params['ssFilter']['order']                 = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';
        $this->_params['ssFilter']['filter_status']         = $ssFilter->filter_status;
        $this->_params['ssFilter']['filter_keyword']        = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['filter_date_begin']     = $ssFilter->filter_date_begin;
        $this->_params['ssFilter']['filter_date_end']       = $ssFilter->filter_date_end;
        $this->_params['ssFilter']['filter_active']         = $ssFilter->filter_active;
        $this->_params['ssFilter']['filter_campaign']       = $ssFilter->filter_campaign;
        $this->_params['ssFilter']['filter_sale_branch']    = $ssFilter->filter_sale_branch;
        $this->_params['ssFilter']['filter_location_city']  = $ssFilter->filter_location_city;
        
        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage']               = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : 50;
        $this->_paginator['currentPageNumber']              = $this->params()->fromRoute('page', 1);
        $this->_params['paginator'] = $this->_paginator;
        
        // Lấy dữ liệu post của form
        $this->_params['data'] = array_merge( $this->getRequest()->getPost()->toArray(), $this->getRequest()->getFiles()->toArray() );
        
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
    }
    
    public function filterAction() {
    	$ssFilter	= new Container(__CLASS__);
    	$data = $this->_params['data'];
    	
        if($this->getRequest()->isPost()) {
            $ssFilter->pagination_option        = intval($data['pagination_option']);
    
            $ssFilter->order_by                 = $data['order_by'];
            $ssFilter->order                    = $data['order'];
    
            $ssFilter->filter_status            = $data['filter_status'];
            $ssFilter->filter_keyword           = $data['filter_keyword'];
            $ssFilter->filter_date_begin        = $data['filter_date_begin'];
            $ssFilter->filter_date_end          = $data['filter_date_end'];
            $ssFilter->filter_active            = $data['filter_active'];
            $ssFilter->filter_campaign          = $data['filter_campaign'];
            $ssFilter->filter_sale_branch       = $data['filter_sale_branch'];
            $ssFilter->filter_location_city     = $data['filter_location_city'];
        }
        
        if(!empty($this->_params['route']['id'])) {
        	$ssFilter->filter_campaign = $this->_params['route']['id'];
        }
    
        $this->goRoute();
    }
    
    public function indexAction() {
        $ssFilter	= new Container(__CLASS__);
        $aclInfo    = new \ZendX\System\UserInfo();
        $this->_params['permissionInfo'] = $aclInfo->getPermissionInfo();
        $this->_params['permissionListInfo'] = $aclInfo->getPermissionListInfo();
        
        // Lấy danh sách các form.
        $listForm = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\FormTable')->listItem($this->_params, array('task' => 'list-all')), array('key' => 'id', 'value' => 'object'));
        if(!empty($listForm)) {
            $this->_params['form_ids'] = array_keys($listForm);
        }
        
        $myForm	= new \Admin\Form\Search\FormData($this->getServiceLocator());
        $myForm->setData($this->_params['ssFilter']);
        
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['myForm']	            = $myForm;
        $this->_viewModel['items']              = $items;
        $this->_viewModel['listForm']           = $listForm;
        $this->_viewModel['count']              = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['user']               = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_group']         = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']        = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['edu_location']       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'edu-location')), array('task' => 'cache'));
        $this->_viewModel['form_data_result']   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'form-data-result')), array('task' => 'cache'));
        $this->_viewModel['edu_class']          = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->listItem(null, array('task' => 'cache-basic'));
        $this->_viewModel['product']            = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['location_city']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'location-city')), array('task' => 'cache'));
        $this->_viewModel['currentForm']        = $this->getServiceLocator()->get('Admin\Model\FormTable')->getItem(array('id' => $ssFilter->filter_form));
        $this->_viewModel['caption']            = 'Đăng ký - Danh sách';
        return new ViewModel($this->_viewModel);
    }
    
    public function managerAction() {
        $myForm = new \Admin\Form\FormData\Manager($this->getServiceLocator());
        
        if(!empty($this->_params['data']['id'])) {
            $item = $this->getServiceLocator()->get('Admin\Model\FormDataTable')->getItem(array('id' => $this->_params['data']['id']));
            $myForm->setData($item);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }
    
        if($this->getRequest()->isPost()){
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\FormData\Manager($this->_params));
                $myForm->setData($this->_params['data']);
    
                if($myForm->isValid()){
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
    
                    // Cập nhật lại thông tin quản lý khách hàng
                    $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($this->_params, array('task' => 'form_data-update'));
    
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
        $this->_viewModel['contact']    = $contact;
        $this->_viewModel['caption']    = 'Xác nhận quản lý khách hàng';
    
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
    
    public function eventAction() {
        $ssFilter	= new Container(__CLASS__);
        
        $myForm = new \Admin\Form\FormData\Event($this->getServiceLocator());
        $myForm->setData($this->_params['ssFilter']);
        
        if($this->getRequest()->isXmlHttpRequest()) {
            if($this->getRequest()->isPost()){
                $event_contact = $this->getServiceLocator()->get('Admin\Model\EventContactTable')->getItem(array('contact_id' => $this->_params['data']['contact_id'], 'event_id' => $this->_params['data']['event_id']), array('task' => 'by-contact'));
                $result = 'Update';
                if(empty($event_contact)){
                    $event_contact_id = $this->getServiceLocator()->get('Admin\Model\EventContactTable')->saveItem($this->_params, array('task' => 'add-contact'));
                    $result = 'Add';
                }
        
                echo $result;
                return $this->response;
            }
        } else {
            if($this->getRequest()->isPost()){
                $myForm->setInputFilter(new \Admin\Filter\FormData\Event($this->_params));
                $myForm->setData($this->_params['data']);
                
                $ssFilter->filter_date_begin    = $this->_params['data']['filter_date_begin'];
                $ssFilter->filter_date_end      = $this->_params['data']['filter_date_end'];
                $ssFilter->filter_form          = $this->_params['data']['filter_form'];
                $ssFilter->filter_event         = $this->_params['data']['filter_event'];
                
                if($myForm->isValid()){
                    $this->_params['ssFilter'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
    
                    $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item', 'paginator' => false));
                    $this->_viewModel['items'] = $items;
                }
            }
        }
    
        $this->_viewModel['myForm']     = $myForm;
        $this->_viewModel['caption']    = 'Thêm khách hàng vào sự kiện';
    
        $viewModel = new ViewModel($this->_viewModel);
    
        return $viewModel;
    }
     
    public function exportAction() {
        $date               = new \ZendX\Functions\Date();
        
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item', 'paginator' => false));
        
        $user               = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $company_branch     = $this->getServiceLocator()->get('Admin\Model\CompanyBranchTable')->listItem(null, array('task' => 'cache'));
        $company_group      = $this->getServiceLocator()->get('Admin\Model\CompanyGroupTable')->listItem(null, array('task' => 'cache'));
        $training_location  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'training-location')), array('task' => 'cache'));
        $training_class     = $this->getServiceLocator()->get('Admin\Model\TrainingClassTable')->listItem(null, array('task' => 'cache-basic'));
        $product            = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
        $location_city      = $this->getServiceLocator()->get('Admin\Model\LocationCityTable')->listItem(null, array('task' => 'cache'));
        $form               = $this->getServiceLocator()->get('Admin\Model\FormTable')->listItem(null, array('task' => 'cache'));
        
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
            array('field' => 'contact_name', 'title' => 'Tên'),
            array('field' => 'contact_phone', 'title' => 'Điện thoại', 'type' => 'phone'),
            array('field' => 'contact_email', 'title' => 'Email'),
            array('field' => 'contact_location_city_id', 'title' => 'Tỉnh thành', 'type' => 'data_source', 'data_source' => $location_city, 'data_source_field' => 'name'),
            array('field' => 'training_location_id', 'title' => 'Địa điểm đăng ký', 'type' => 'data_source', 'data_source' => $training_location),
            array('field' => 'company_branch_id', 'title' => 'Cơ sở đăng ký', 'type' => 'data_source', 'data_source' => $company_branch),
            array('field' => 'form_id', 'title' => 'Nguồn', 'type' => 'data_source', 'data_source' => $form),
            array('field' => 'contact_user_id', 'title' => 'Người quản lý', 'type' => 'data_source', 'data_source' => $user, 'data_source_field' => 'fullname'),
            array('field' => 'contact_test_online', 'title' => 'Test'),
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
                    case 'data_source_list':
                        $field = $data['data_source_field'] ? $data['data_source_field'] : 'name';
                        $list  = $item[$data['field']] ? unserialize($item[$data['field']]) : array();
                        $value = '';
                        if(!empty($list)) {
                            $list = array_keys($list);
                            asort($list);
                            $index = 0;
                            foreach ($list AS $key => $val) {
                                $index++;
                                if($index == 1) {
                                    $value .= $data['data_source'][$val][$field];
                                } else {
                                    $value .= ', '. $data['data_source'][$val][$field];
                                }
                            }
                        }
                        break;
                    case 'branch':
                        $training_location_id = $item['training_class_id'] ? $training_class[$item['training_class_id']]['training_location_id'] : '';
                        $value  = $training_location[$training_location_id]['document_id'] ? $company_branch[$training_location[$training_location_id]['document_id']]['name'] : '';
                        break;
                    case 'options':
                        $options = $item[$data['field']] ? unserialize($item[$data['field']]) : array();
                        $value   = $options[$data['data_source']][$data['data']];
                        break;
                    case 'options_list':
                        $options = $item[$data['field']] ? unserialize($item[$data['field']]) : array();
                        $value   = implode(',', $options);
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
    
    public function editResultAction() {
        if($this->getRequest()->isXmlHttpRequest()) {
            if($this->getRequest()->isPost()){
                $this->getTable()->saveItem($this->_params, array('task' => 'edit-result'));
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
    
        return $this->response;
    }
    
}

