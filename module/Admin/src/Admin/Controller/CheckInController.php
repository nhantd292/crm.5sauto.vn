<?php

    namespace Admin\Controller;

    use kcfinder\zipFolder;
    use ZendX\Controller\ActionController;
    use Zend\View\Model\ViewModel;
    use Zend\Session\Container;
    use Zend\Form\FormInterface;

    class   CheckInController extends ActionController
    {

        public function init()
        {
            // Thiết lập options
            $this->_options['tableName'] = 'Admin\Model\CheckInTable';
            $this->_options['formName']  = 'formAdminCheckIn';

            // Thiết lập session filter
            $ssFilter                                    = new Container(__CLASS__);
            $this->_params['ssFilter']['order_by']       = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'year';
            $this->_params['ssFilter']['order']          = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';
            $this->_params['ssFilter']['filter_status']  = $ssFilter->filter_status;
            $this->_params['ssFilter']['filter_keyword'] = $ssFilter->filter_keyword;

            $this->_params['ssFilter']['filter_year'] = $ssFilter->filter_year;
            $this->_params['ssFilter']['filter_month']   = $ssFilter->filter_month;

            $this->_params['ssFilter']['filter_sale_branch']   = $ssFilter->filter_sale_branch;
            $this->_params['ssFilter']['filter_sale_group']   = $ssFilter->filter_sale_group;

            // Thiết lập lại thông số phân trang
            $this->_paginator['itemCountPerPage']  = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : 50;
            $this->_paginator['currentPageNumber'] = $this->params()->fromRoute('page', 1);
            $this->_params['paginator']            = $this->_paginator;

            // Lấy dữ liệu post của form
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();

            // Truyển dữ dữ liệu ra ngoài view
            $this->_viewModel['params'] = $this->_params;
        }

        public function filterAction()
        {
            if ($this->getRequest()->isPost()) {
                $ssFilter = new Container(__CLASS__);
                $data     = $this->_params['data'];

                $ssFilter->pagination_option = intval($data['pagination_option']);
                $ssFilter->order_by          = $data['order_by'];
                $ssFilter->order             = $data['order'];
                $ssFilter->filter_status     = $data['filter_status'];
                $ssFilter->filter_keyword    = $data['filter_keyword'];

                $ssFilter->filter_year    = $data['filter_year'];
                $ssFilter->filter_month   = $data['filter_month'];

                $ssFilter->filter_sale_branch = $data['filter_sale_branch'];
                $ssFilter->filter_sale_group  = $data['filter_sale_group'];
            }

            $this->goRoute();
        }

        public function indexAction()
        {
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

            $myForm = new \Admin\Form\Search\CheckIn($this->getServiceLocator(), $this->_params['ssFilter']);
            // Sét giá trị điều kiện tìm kiếm mặc định.
            if(empty($this->_params['ssFilter']['filter_year']))
                $this->_params['ssFilter']['filter_year'] = date('Y');
            if(empty($this->_params['ssFilter']['filter_month']))
                $this->_params['ssFilter']['filter_month'] = date('m');
            $myForm->setData($this->_params['ssFilter']);

            // Đếm số ngày trong tháng hiện tại.

            $date_begin     =  date($this->_params['ssFilter']['filter_year'] . '-'. $this->_params['ssFilter']['filter_month'] .'-01' );
            $date_end       =  date("Y-m-t", strtotime($date_begin));


            // $number_day = cal_days_in_month(CAL_GREGORIAN, $this->_params['ssFilter']['filter_month'], $this->_params['ssFilter']['filter_year']);
            $number_day = explode('-', $date_end)[2];
            $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));

            $department = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'company-department')), array('task' => 'cache'));
            $time_checkin_department = [];
            foreach ($department as $key => $value){
                str_replace(" ","",$value['content']);

                $content = preg_replace('/\s+/', '', $value['content']);
                $content = explode('-',$content);
                $in      = $content[0];
                $out     = $content[1];

                $time_checkin_department[$value['alias']]['in'] = $in;
                $time_checkin_department[$value['alias']]['out'] = $out;
            }

            $this->_viewModel['myForm']       = $myForm;
            $this->_viewModel['items']        = $items;
            $this->_viewModel['number_day']   = $number_day;
            $this->_viewModel['count']        = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
            $this->_viewModel['sale_group']   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['sale_branch']  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
            $this->_viewModel['time_checkin_department'] = $time_checkin_department;
            $this->_viewModel['caption']      = 'Bảng chấm công - Danh sách';
            return new ViewModel($this->_viewModel);
        }

        public function formAction()
        {
            $myForm     = $this->getForm();
            $dateFormat = new \ZendX\Functions\Date();

            $task    = 'add-item';
            $caption = 'Chi tiết chấm công - Thêm mới';
            $item    = array();
            if (!empty($this->params('id'))) {
                $this->_params['data']['id'] = $this->params('id');
                $item                        = $this->getTable()->getItem($this->_params['data']);

                $date_begin     =  date($item['year'] . '-'. $item['month'] .'-01' );
                $date_end       =  date("Y-m-t", strtotime($date_begin));
                $number_day = explode('-', $date_end)[2];
                // $number_day                  = cal_days_in_month(CAL_GREGORIAN, $item['month'], $item['year']);

                if (!empty($item)) {
                    $myForm->setInputFilter(new \Admin\Filter\CheckIn());
                    $item['date'] = $dateFormat->formatToView($item['date']);
                    $myForm->bind($item);
                    $task    = 'edit-item';
                    $caption = 'Chi tiết chấm công - Cập nhật';
                }
            }

            if ($this->getRequest()->isPost()) {
                // Truyền các giá trị post vào filter để đặt điều kiện lại
                $myForm->setInputFilter(new \Admin\Filter\CheckIn());

                $myForm->setData($this->_params['data']);
                $controlAction = $this->_params['data']['control-action'];
                $params        = $this->_params['data']['params'];

                if ($myForm->isValid()) {
                    if($task == 'add-item'){
                        $list_date = explode('/', $this->_params['data']['date']);
                        $this->_params['data']['day'] = $list_date['0'];
                        $this->_params['data']['month'] = $list_date['1'];
                        $this->_params['data']['year'] = $list_date['2'];
                        $this->_params['data']['type'] = "mkt_report_day_hour";
                        $this->_params['data']['params'] = $params;

                        $report_item = $this->getTable()->getItem(array('date' => $dateFormat->formatToData($this->_params['data']['date']), 'marketer_id' => $this->_params['data']['marketer_id'], 'type' => 'mkt_target'), array('task' => "marketer-date"));

                        if(empty($report_item)){
                            $result = $this->getTable()->saveItem($this->_params, array('task' => $task));
                            $this->flashMessenger()->addMessage('Dữ liệu đã được thêm mới thành công');
                        }
                        else{
                            $this->flashMessenger()->addMessage('Nhân viên đã có báo cáo không thể thêm mới');
                        }
                    }
                    elseif ($task == 'edit-item'){
                        $item                            = $this->getTable()->getItem($this->_params['data']);
                        $this->_params['item']           = $item;
                        $this->_params['data']['params'] = $params;
                        $result                          = $this->getTable()->saveItem($this->_params, array('task' => $task));
                        $this->flashMessenger()->addMessage('Dữ liệu đã được cập nhật thành công');
                    }

                    if ($controlAction == 'save-new') {
                        $this->goRoute(array('action' => 'form'));
                    } else if ($controlAction == 'save') {
                        $this->goRoute(array('action' => 'form','id'     => $result));
                    } else {
                        $this->goRoute();
                    }
                }
            }

            $this->_viewModel['myForm'] = $myForm;
            $this->_viewModel['item']   = $item;
            $this->_viewModel['number_day'] = $number_day;
            $this->_viewModel['user'] = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-marketing'));
            $this->_viewModel['caption'] = $caption;

            return new ViewModel($this->_viewModel);
        }

        public function addAllAction()
        {
            $myForm     = new \Admin\Form\CheckIn\addAll($this);
            $caption    = 'Thêm bảng chấm công cho nhân viên';

            $data_default = array(
                'month' => date('m'),
                'year'  => date('Y'),
            );
            $myForm->setData($data_default);

            if ($this->getRequest()->isPost()) {
                // Truyền các giá trị post vào filter để đặt điều kiện lại
                $myForm->setInputFilter(new \Admin\Filter\CheckIn\addAll());

                $myForm->setData($this->_params['data']);
                $controlAction = $this->_params['data']['control-action'];

                if ($myForm->isValid()) {
                    $user_list = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-all'));
                    $month = $this->_params['data']['month'];
                    $year  = $this->_params['data']['year'];

                    foreach ($user_list as $u) {
                        $this->_params['data']['user_id']  = $u['id'];
                        $item = $this->getTable()->getItem(array('user_id' => $u['id'], 'month' => $month, 'year' => $year), array('task' => "user-exist"));
                        if(empty($item)){
                            $this->getTable()->saveItem($this->_params, array('task' => "add-all"));
                        }
                    }
                    $this->flashMessenger()->addMessage('Thêm mới danh sách thành công');
                    $this->goRoute();
                }
            }

            $this->_viewModel['myForm'] = $myForm;
            $this->_viewModel['caption'] = $caption;
            return new ViewModel($this->_viewModel);
        }
    }


