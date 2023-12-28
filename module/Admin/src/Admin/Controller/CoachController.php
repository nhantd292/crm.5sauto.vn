<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class CoachController extends ActionController {
    
    public function init() {
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\CoachTable';
        $this->_options['formName'] = 'formAdminCoach';
        
        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']                  = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'name';
        $this->_params['ssFilter']['order']                     = !empty($ssFilter->order) ? $ssFilter->order : 'ASC';
        $this->_params['ssFilter']['filter_keyword']            = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['filter_status']             = $ssFilter->filter_status;
        $this->_params['ssFilter']['filter_company_branch']     = $ssFilter->filter_company_branch;
        
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
    
            $ssFilter->pagination_option            = intval($data['pagination_option']);
            $ssFilter->order_by                     = $data['order_by'];
            $ssFilter->order                        = $data['order'];
            $ssFilter->filter_keyword               = $data['filter_keyword'];
            $ssFilter->filter_status                = $data['filter_status'];
            $ssFilter->filter_company_branch        = $data['filter_company_branch'];
        }
    
        $this->goRoute();
    }
    
    public function indexAction() {
        $dateFormat = new \ZendX\Functions\Date();
        
        $myForm	= new \Admin\Form\Search\User($this->getServiceLocator(), $this->_params['ssFilter']);
        $myForm->setData($this->_params['ssFilter']);
        
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        
        $edu_class_coach = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->listItem(null, array('task' => 'public'));
        $edu_class = array();
        foreach ($edu_class_coach AS $key => $val) {
            if(!empty($val['coach_ids'])) {
                $coach_ids = explode(',', $val['coach_ids']);
                foreach ($coach_ids AS $coach_id) {
                    $edu_class[$coach_id][] = $val['time'] .' - '. $val['schedule'] .' - '. $val['name'] .' - Kết thúc: '. $dateFormat->formatToView($val['end_date']);
                }
            }
        }

        $this->_viewModel['myForm']	            = $myForm;
        $this->_viewModel['items']              = $items;
        $this->_viewModel['edu_class']          = $edu_class;
        $this->_viewModel['count']              = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['permission']         = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\PermissionTable')->listItem(null, array('task' => 'cache')), array('key' => 'code', 'value' => 'object'));
        $this->_viewModel['company_branch']     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'company-branch')), array('task' => 'cache'));
        $this->_viewModel['company_department'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'company-department')), array('task' => 'cache'));
        $this->_viewModel['company_position']   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'company-position')), array('task' => 'cache'));
        $this->_viewModel['caption']            = 'Trợ giảng - Danh sách';
        
        return new ViewModel($this->_viewModel);
    }
    
    public function formAction() {
        $myForm			= $this->getForm();
    
        $task = 'add-item';
        $caption = 'Trợ giảng - Thêm mới';
        if(!empty($this->params('id'))) {
            $this->_params['data']['id'] = $this->params('id');
            $item = $this->getTable()->getItem($this->_params['data']);
            if(!empty($item)) {
                $item['permission_ids'] = explode(',', $item['permission_ids']);
                
                $item_options = !empty($item['options']) ? unserialize($item['options']) : array();
                $item = array_merge($item, $item_options);
                
                if(!$this->getRequest()->isPost()){
                    $myForm->setData($item);
                }
                $task = 'edit-item';
                $caption = 'Trợ giảng - Sửa';
            }
        }
    
        if($this->getRequest()->isPost()){
            $myForm->setInputFilter(new \Admin\Filter\User(array('id' => $this->_params['data']['id'])));
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
        $this->_viewModel['caption']    = $caption;
        return new ViewModel($this->_viewModel);
    }
    
    public function exportAction() {
        $date               = new \ZendX\Functions\Date();
    
        $items              = $this->getTable()->listItem($this->_params, array('task' => 'list-item', 'paginator' => false));
        $user               = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $permission         = $this->getServiceLocator()->get('Admin\Model\PermissionTable')->listItem(null, array('task' => 'cache-code'));
        $company_branch     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'company-branch')), array('task' => 'cache'));
        $company_department = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'company-department')), array('task' => 'cache'));
        $company_position   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'company-position')), array('task' => 'cache'));
    
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
            array('field' => 'username', 'title' => 'Tên đăng nhập'),
            array('field' => 'name', 'title' => 'Họ và tên'),
            array('field' => 'email', 'title' => 'Email'),
            array('field' => 'phone', 'title' => 'Điện thoại'),
            array('field' => 'company_branch_id', 'title' => 'Cơ sở làm việc', 'type' => 'data_source', 'data_source' => $company_branch),
            array('field' => 'company_department_id', 'title' => 'Phòng ban', 'type' => 'data_source', 'data_source' => $company_department),
            array('field' => 'company_position_id', 'title' => 'Vị trí/Chức vụ', 'type' => 'data_source', 'data_source' => $company_position),
            array('field' => 'permission_ids', 'title' => 'Quyền truy cập', 'type' => 'array_source', 'data_source' => $permission),
            array('field' => 'login_ip', 'title' => 'IP đăng nhập'),
            array('field' => 'login_time', 'title' => 'Đăng nhập cuối', 'type' => 'date', 'format' => 'd/m/Y H:i'),
            array('field' => 'created_by', 'title' => 'Người tạo', 'type' => 'data_source', 'data_source' => $user),
            array('field' => 'created', 'title' => 'Ngày tạo', 'type' => 'date', 'format' => 'd/m/Y H:i'),
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
                        $data_source = $data['data_source'];
                        if(isset($data_source[$item[$data['field']]][$field])) {
                            $value = $data_source[$item[$data['field']]][$field];
                        } else {
                            $value = $data_source[$item[$data['field']]];
                        }
                        break;
                    case 'array_source':
                        $field = $data['data_source_field'] ? $data['data_source_field'] : 'name';
                        $result = explode(',', $item[$data['field']]);
                        $tmp = array();
                        if(!empty($result)) {
                            $data_source = $data['data_source'];
                            foreach ($result AS $id) {
                                if(isset($data_source[$id][$field])) {
                                    $tmp[] = $data_source[$id][$field];
                                } else {
                                    $tmp[] = $data_source[$id];
                                }
                            }
                        }
                        $value = '';
                        if(!empty($tmp)) {
                            $value = implode(', ', $tmp);
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
