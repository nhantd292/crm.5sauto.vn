<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use ZendX\System\UserInfo;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class UserController extends ActionController {
    
    public function init() {
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\UserTable';
        $this->_options['formName'] = 'formAdminUser';
        
        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']                  = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'name';
        $this->_params['ssFilter']['order']                     = !empty($ssFilter->order) ? $ssFilter->order : 'ASC';
        $this->_params['ssFilter']['filter_keyword']            = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['filter_status']             = $ssFilter->filter_status;
        $this->_params['ssFilter']['filter_permission']         = $ssFilter->filter_permission;
        $this->_params['ssFilter']['filter_company_branch']     = $ssFilter->filter_company_branch;
        $this->_params['ssFilter']['filter_company_department'] = $ssFilter->filter_company_department;
        $this->_params['ssFilter']['filter_company_position']   = $ssFilter->filter_company_position;
        $this->_params['ssFilter']['filter_sale_branch']        = $ssFilter->filter_sale_branch;
        $this->_params['ssFilter']['filter_sale_group']         = $ssFilter->filter_sale_group;
        $this->_params['ssFilter']['filter_kov_branch_id']      = $ssFilter->filter_kov_branch_id;

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
            $ssFilter->filter_permission            = $data['filter_permission'];
            $ssFilter->filter_company_branch        = $data['filter_company_branch'];
            $ssFilter->filter_company_department    = $data['filter_company_department'];
            $ssFilter->filter_company_position      = $data['filter_company_position'];
            $ssFilter->filter_sale_branch           = $data['filter_sale_branch'];
            $ssFilter->filter_sale_group            = $data['filter_sale_group'];
            $ssFilter->filter_kov_branch_id         = $data['filter_kov_branch_id'];
        }
    
        $this->goRoute();
    }
    
    public function indexAction() {
        $ssFilter = new Container(__CLASS__);
        // Phân quyền view
        $curent_user = $this->_userInfo->getUserInfo();
        $permission_ids = explode(',', $curent_user['permission_ids']);
        if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids) && !in_array(MANAGER, $permission_ids)){
            if(in_array(GDCN, $permission_ids) || in_array(SALEADMIN, $permission_ids)){
                $this->_params['ssFilter']['filter_sale_branch'] = $curent_user['sale_branch_id'];
                $ssFilter->filter_sale_branch = $curent_user['sale_branch_id'];
            }
            else{
                $this->_params['ssFilter']['filter_user'] = $curent_user['id'];
            }
        }
        $myForm	= new \Admin\Form\Search\User($this->getServiceLocator(), $this->_params['ssFilter']);
        $myForm->setData($this->_params['ssFilter']);
        
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));

        $this->_viewModel['myForm']	            = $myForm;
        $this->_viewModel['items']              = $items;
        $this->_viewModel['count']              = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['permission']         = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\PermissionTable')->listItem(null, array('task' => 'cache')), array('key' => 'code', 'value' => 'object'));
        $this->_viewModel['company_branch']     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'company-branch')), array('task' => 'cache'));
        $this->_viewModel['positions_care']     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'company-position-care')), array('task' => 'cache-alias'));
        $this->_viewModel['company_department'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'company-department')), array('task' => 'cache-alias'));
        $this->_viewModel['company_position']   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'company-position')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']        = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['sale_group']         = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['kov_branch']         = $this->getServiceLocator()->get('Admin\Model\kovBranchesTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['caption']            = 'Người dùng - Danh sách';
        
        return new ViewModel($this->_viewModel);
    }
    
    public function formAction() {
//        $myForm			= $this->getForm();
        $task = 'add-item';
        $caption = 'Người dùng - Thêm mới';
        if(!empty($this->params('id'))) {
            $myForm			= new \Admin\Form\User($this, 'edit');

            $this->_params['data']['id'] = $this->params('id');
            $item = $this->getTable()->getItem($this->_params['data']);
            if(!empty($item)) {
                $item['permission_ids'] = explode(',', $item['permission_ids']);
                $item['company_position_care_id'] = explode(',', $item['company_position_care_id']);
                $item['encode_phone'] = explode(',', $item['encode_phone']);
                $item['sale_group_hidden'] = $item['sale_group_ids'];
                
                $item_options = !empty($item['options']) ? unserialize($item['options']) : array();
                $item = array_merge($item, $item_options);
                
                if(!$this->getRequest()->isPost()){
                    $myForm->setData($item);
                }
                $task = 'edit-item';
                $caption = 'Người dùng - Sửa';
            }
        }
        else{
            $myForm			= new \Admin\Form\User($this, 'add');
        }
    
        if($this->getRequest()->isPost()){
            if(!empty($this->_params['data']['sale_group_ids'])) {
                $this->_params['data']['sale_group_hidden'] = implode(',', $this->_params['data']['sale_group_ids']);
            }
            $myForm->setInputFilter(new \Admin\Filter\User(array('id' => $this->_params['data']['id'])));
            $myForm->setData($this->_params['data']);
            
            $controlAction = $this->_params['data']['control-action'];
            $sale_group_ids = $this->_params['data']['sale_group_ids'];

            if($myForm->isValid()){
                $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                $this->_params['data']['sale_group_ids'] = $sale_group_ids;
                $this->_params['item'] = $item;
                $result = $this->getTable()->saveItem($this->_params, array('task' => $task));

                // Thêm nhân viên trong báo cáo chấm công.
                if($task == 'add-item'){
                    $checkInData = array(
                        'user_id' => $result,
                        'month'   => date(m),
                        'year'    => date(Y),
                    );
                    $this->getServiceLocator()->get('Admin\Model\CheckInTable')->saveItem(array('data' => $checkInData), array('task' => 'add-item'));
                }
    
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
    
    public function changePasswordAction() {
        $userInfo = new UserInfo();
        $userInfo = $userInfo->getUserInfo();
        
        if($this->getRequest()->isPost()){
            $post   = $this->_params['data'];
            $errors = array();
            $flag   = true;
            
            if(md5($post['password_current']) != $userInfo['password']) {
                $errors[] = '<b>Mật khẩu cũ:</b> không chính xác';
                $flag = false;
            }
            
            //$validator = new \Zend\Validator\Regex('/^(?=.*[a-zA-Z])(?=.*\d)[\S\W\D]{8,}$/');
            $validator = new \Zend\Validator\StringLength(array('min' => 6));
            if (!$validator->isValid($post['password_new'])) {
                $errors[] = '<b>Mật khẩu mới:</b> phải lớn hơn 6 ký tự';
                $flag = false;
            }
            
            if(md5($post['password_new']) != md5($post['password_confirm'])) {
                $errors[] = '<b>Xác nhận mật khẩu mới:</b> không đúng';
                $flag = false;
            }
            
            if($flag == true){
                $this->flashMessenger()->addMessage('Mật khẩu của bạn đã được đổi thành công');
                $result = $this->getTable()->saveItem($this->_params, array('task' => 'change-password'));
    
                // Cập nhật lại session user
                $userInfo = new UserInfo();
                $userInfo->setUserInfo('password', md5($post['password_new']));
                
                $this->goRoute(array('controller' => 'index', 'action' => 'index'));
            }
        }
    
        $this->_viewModel['errors']     = $errors;
        $this->_viewModel['caption']    = 'Đổi mật khẩu';
        return new ViewModel($this->_viewModel);
    }
    
    public function updatePasswordAction() {
        $this->setLayout('empty');
        
        $userInfo = new UserInfo();
        $userInfo = $userInfo->getUserInfo();
        
        $options = !empty($userInfo['options']) ? unserialize($userInfo['options']) : array();
        if($options['password_status'] != 1) {
            $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'index', 'action' => 'index'));
        }
        
        if($this->getRequest()->isPost()){
            $post   = $this->_params['data'];
            $errors = array();
            $flag   = true;
            
            //$validator = new \Zend\Validator\Regex('/^(?=.*[a-zA-Z])(?=.*\d)[\S\W\D]{8,}$/');
            $validator = new \Zend\Validator\StringLength(array('min' => 6));
            if (!$validator->isValid($post['password_new'])) {
                $errors[] = '<b>Mật khẩu mới:</b> phải lớn hơn 6 ký tự';
                $flag = false;
            }
            
            if(md5($post['password_new']) != md5($post['password_confirm'])) {
                $errors[] = '<b>Xác nhận mật khẩu mới:</b> không đúng';
                $flag = false;
            }
            
            if($flag == true){
                $this->flashMessenger()->addMessage('Mật khẩu của bạn đã được đổi thành công');
                $result = $this->getTable()->saveItem($this->_params, array('task' => 'update-password'));
    
                // Cập nhật lại session user
                $userInfo = new UserInfo();
                $options = !empty($userInfo->getUserInfo('options')) ? unserialize($userInfo->getUserInfo('options')) : array();
                $options['password_status'] = 0;
                $userInfo->setUserInfo('password', md5($post['password_new']));
                $userInfo->setUserInfo('options', serialize($options));
                
                $this->goRoute(array('controller' => 'index', 'action' => 'index'));
            }
        }
    
        $this->_viewModel['errors']     = $errors;
        $this->_viewModel['caption']    = 'Yêu cầu đổi mật khẩu mới';
        return new ViewModel($this->_viewModel);
    }
    
    public function loginAction() {
        $this->setLayout('login');
        if($this->identity()) {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'index', 'action' => 'index'));
        }
    
        $this->_options['formName'] = 'formAdminLogin';
        $myForm = $this->getForm();
    
        $authService = $this->getServiceLocator()->get('MyAuth');
    
        if($this->getRequest()->isPost()){
            $myForm->setData($this->_params['data']);
    
            if($myForm->isValid()){
                $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
    
                $paramLogin = array(
                    'username' => $this->_params['data']['username'],
                    'password' => $this->_params['data']['password'],
                );
                if($authService->login($paramLogin) == true) {
                    $user_id = $this->identity()->id;
                    $permission_ids = $this->identity()->permission_ids;
    
                    $userTable = $this->getServiceLocator()->get('Admin\Model\UserTable');
                    $permissionTable = $this->getServiceLocator()->get('Admin\Model\PermissionTable');
                    $permissionListTable = $this->getServiceLocator()->get('Admin\Model\PermissionListTable');
                    
                    $data['user'] = $userTable->getItem(array('id' => $user_id));
                    $data['permission'] = $permissionTable->listItem(array('code' => $permission_ids), array('task' => 'multi-code'));
                    
                    $data['permission_list']['role'] = 'role_'. $data['user']['id'];
                    $data['permission_list']['privileges'] = $permissionListTable->listItem($data['permission'], array('task' => 'list-privileges'));
    
                    $userInfo = new UserInfo();
                    $userInfo->storeInfo($data);
                    
                    // Cập nhật thông tin đăng nhập
                    $userTable->saveItem(array('data' => array('id' => $user_id)), array('task' => 'update-login'));
    
                    $linkRedirect = $this->url()->fromRoute('routeAdmin/default', array('controller' => 'index', 'action' => 'index'), array('force_canonical' => true));
                    $options = !empty($data['user']['options']) ? unserialize($data['user']['options']) : array();
                    if($options['password_status'] == 1) {
                        $linkRedirect = $this->url()->fromRoute('routeAdmin/default', array('controller' => 'user', 'action' => 'update-password'), array('force_canonical' => true));
                    }

                    $department = $userInfo->getUserInfo('company_department_id');
                    if($department == 'sales' || $department == 'care'){
                        $linkRedirect = $this->url()->fromRoute('routeAdmin/default', array('controller' => 'contract', 'action' => 'index'), array('force_canonical' => true));
                    }
                    elseif($department == 'marketing'){
                        $linkRedirect = $this->url()->fromRoute('routeAdmin/default', array('controller' => 'form-data', 'action' => 'index'), array('force_canonical' => true));
                    }
                    elseif($department == 'phong-ke-toan'){
                        $linkRedirect = $this->url()->fromRoute('routeAdmin/default', array('controller' => 'contract', 'action' => 'index-accounting'), array('force_canonical' => true));
                    }
                    elseif($department == 'phong-san-xuat'){
                        $linkRedirect = $this->url()->fromRoute('routeAdmin/default', array('controller' => 'contract', 'action' => 'index'), array('force_canonical' => true));
                    }
                    
                    $this->redirect()->toUrl($linkRedirect);
                }
            }
        }
    
        $this->_viewModel['settings']   = $this->_settings;
        $this->_viewModel['myForm']     = $myForm;
        $this->_viewModel['msgError']   = $authService->getError();
        return new ViewModel($this->_viewModel);
    }
    
    public function logoutAction() {
        $userInfo = new UserInfo();
        $user = $userInfo->getUserInfo();
        $this->getServiceLocator()->get('Admin\Model\CheckInTable')->saveItem(array("data" => $user), array('task' => 'time-check-out'));
        $userInfo->destroyInfo();

        $authService = $this->getServiceLocator()->get('MyAuth')->logout();
    
        $session = new Container();
        $session->getManager()->getStorage()->clear();
        
        /* $userInfo = new UserInfo();
        $userInfo->destroyInfo(); */
    
        return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'user', 'action' => 'login'));
    }
    
    public function exportAction() {
        $date               = new \ZendX\Functions\Date();
    
        $items              = $this->getTable()->listItem($this->_params, array('task' => 'list-item', 'paginator' => false));
        $user               = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $permission         = $this->getServiceLocator()->get('Admin\Model\PermissionTable')->listItem(null, array('task' => 'cache-code'));
        $company_branch     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'company-branch')), array('task' => 'cache'));
        $company_department = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'company-department')), array('task' => 'cache'));
        $company_position   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'company-position')), array('task' => 'cache'));
        $sale_branch        = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $sale_group         = \ZendX\Functions\CreateArray::createSelect($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name,content', 'symbol' => ' - '));
    
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
            array('field' => 'sale_branch_id', 'title' => 'Cơ sở kinh doanh', 'type' => 'data_source', 'data_source' => $sale_branch),
            array('field' => 'sale_group_id', 'title' => 'Đội nhóm', 'type' => 'data_source', 'data_source' => $sale_group),
            array('field' => 'sale_group_ids', 'title' => 'Đội nhóm quản lý', 'type' => 'array_source', 'data_source' => $sale_group),
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
