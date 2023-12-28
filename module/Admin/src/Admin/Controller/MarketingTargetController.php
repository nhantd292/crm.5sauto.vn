<?php

namespace Admin\Controller;

use kcfinder\zipFolder;
use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class   MarketingTargetController extends ActionController
{

    public function init()
    {
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\MarketingReportTable';
        $this->_options['formName']  = 'formAdminMarketingTarget';

        // Thiết lập session filter
        $ssFilter                                    = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']       = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'year';
        $this->_params['ssFilter']['order']          = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';
        $this->_params['ssFilter']['filter_status']  = $ssFilter->filter_status;
        $this->_params['ssFilter']['filter_keyword'] = $ssFilter->filter_keyword;

        $this->_params['ssFilter']['filter_date_begin'] = $ssFilter->filter_date_begin;
        $this->_params['ssFilter']['filter_date_end']   = $ssFilter->filter_date_end;
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

            $ssFilter->filter_date_begin  = $data['filter_date_begin'];
            $ssFilter->filter_date_end    = $data['filter_date_end'];

            $ssFilter->filter_sale_group = $data['filter_sale_group'];
            if(!empty($data['filter_sale_branch'])) {
                if($ssFilter->filter_sale_branch != $data['filter_sale_branch']) {
                    $ssFilter->filter_sale_group = null;
                    $ssFilter->filter_sale_branch = $data['filter_sale_branch'];
                }
            } else {
                $ssFilter->filter_sale_group = null;
                $ssFilter->filter_sale_branch = $data['filter_sale_branch'];
            }
        }

        $this->goRoute();
    }

    public function indexAction()
    {
        $ssFilter = new Container(__CLASS__);
        $day = date('w');
        $week_start = date('d/m/Y', strtotime('-'.$day.' days') + 86400);
        $week_end = date('d/m/Y', strtotime('+'.(6-$day).' days')+86400);

        if(empty($this->_params['ssFilter']['filter_date_begin']))
            $this->_params['ssFilter']['filter_date_begin'] = $week_start;
        if(empty($this->_params['ssFilter']['filter_date_end']))
            $this->_params['ssFilter']['filter_date_end'] = $week_end;

        $this->_params['ssFilter']['filter_type'] = 'mkt_target';

        $total_day = true;
        $curent_user = $this->_userInfo->getUserInfo();
        $permission_ids = explode(',', $curent_user['permission_ids']);
        if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids) && !in_array(MANAGER, $permission_ids)){
            if(in_array(GDCN, $permission_ids)){
                $this->_params['ssFilter']['filter_sale_branch'] = $curent_user['sale_branch_id'];
                $ssFilter->filter_sale_branch = $curent_user['sale_branch_id'];
            }
            elseif (in_array(GROUP_MKT_LEADER, $permission_ids)){
                $this->_params['ssFilter']['filter_sale_branch'] = $curent_user['sale_branch_id'];
                $this->_params['ssFilter']['filter_sale_group'] = $curent_user['sale_group_id'];
                $ssFilter->filter_sale_branch = $curent_user['sale_branch_id'];
                $ssFilter->filter_sale_group = $curent_user['filter_sale_group'];
            }
            else{
                $this->_params['ssFilter']['filter_marketer_id'] = $curent_user['id'];
                $total_day = false;
            }
        }

        $myForm = new \Admin\Form\Search\MarketingTarget($this->getServiceLocator(), $this->_params['ssFilter']);
        $myForm->setData($this->_params['ssFilter']);

        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item-type', 'paginator' => false));

        $this->_viewModel['myForm']       = $myForm;
        $this->_viewModel['total_day']        = $total_day;
        $this->_viewModel['items']        = $items;
        $this->_viewModel['count']        = $this->getTable()->countItem($this->_params, array('task' => 'list-item-type'));

        $this->_viewModel['user']         = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_group']   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['product_type'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-type')), array('task' => 'cache'));
        $this->_viewModel['caption']      = 'Chỉ tiêu Marketing';
        return new ViewModel($this->_viewModel);
    }

    public function formAction()
    {
        $myForm     = $this->getForm();
        $dateFormat = new \ZendX\Functions\Date();

        $task    = 'add-item';
        $caption = 'Chỉ tiêu Marketing - Thêm mới';
        $item    = array();
        if (!empty($this->params('id'))) {
            $this->_params['data']['id'] = $this->params('id');
            $item                        = $this->getTable()->getItem($this->_params['data']);

            if (!empty($item)) {
                $myForm->setInputFilter(new \Admin\Filter\MarketingTarget());
                $item['date'] = $dateFormat->formatToView($item['date']);
                $myForm->bind($item);
                $task    = 'edit-item';
                $caption = 'Chỉ tiêu Marketing - Cập nhật';
            }
        } else {
            $data_default = array(
                'date' => date('d/m/Y'),
            );
            $myForm->setData($data_default);
        }

        if ($this->getRequest()->isPost()) {
            // Truyền các giá trị post vào filter để đặt điều kiện lại
            $myForm->setInputFilter(new \Admin\Filter\MarketingTarget());

            $this->_params['data']['type']  = "mkt_report_day_hour";
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
                    $this->goRoute(array('action' => 'form',
                                         'id'     => $result));
                } else {
                    $this->goRoute();
                }
            }
        }

        $this->_viewModel['myForm'] = $myForm;
        $this->_viewModel['item']   = $item;
        $this->_viewModel['user'] = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-marketing'));
        $this->_viewModel['branch'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['group'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group', 'type' => 'marketing')), array('task' => 'cache'));
        $this->_viewModel['sale_group_type'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'group-type', 'alias' => 'marketing')), array('task' => 'cache'));
        $this->_viewModel['product_type'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-type')), array('task' => 'cache'));
        $this->_viewModel['caption'] = $caption;

        return new ViewModel($this->_viewModel);
    }

    public function addAllAction()
    {
        $myForm     = new \Admin\Form\MarketingTarget\addAll($this);

        $caption = 'Thêm chi tiêu nhân viên marketing theo tháng';

        $data_default = array(
            'month' => date('m'),
            'year'  => date('Y'),
        );
        $myForm->setData($data_default);

        if ($this->getRequest()->isPost()) {
            // Truyền các giá trị post vào filter để đặt điều kiện lại
            $myForm->setInputFilter(new \Admin\Filter\MarketingTarget\addAll());
            $this->_params['data']['type']  = "mkt_target";

            $myForm->setData($this->_params['data']);
            $controlAction = $this->_params['data']['control-action'];

            if ($myForm->isValid()) {
                $user_marketing = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-marketing'));

                $date_begin = date($this->_params['data']['year'] . '-'. $this->_params['data']['month'] .'-01');
                $date_end   = date("Y-m-t", strtotime($date_begin));
                $day_begin     = strtotime($date_begin);
                $day_end       = strtotime($date_end);
                $number_day    = abs($day_end - $day_begin) / 86400;

                for ($i = 0; $i <= $number_day; $i++) {
                    $day   = date('Y-m-d', $day_begin + $i*86400);
                    $this->_params['data']['date']  = $day;
                    foreach ($user_marketing as $u) {
                        $this->_params['data']['marketer_id']  = $u['id'];
                        $report_item = $this->getTable()->getItem(array('date' => $day, 'marketer_id' => $u['id'], 'type' => 'mkt_target'), array('task' => "marketer-date"));
                        if(empty($report_item)){
                            $this->getTable()->saveItem($this->_params, array('task' => "add-all"));
                        }
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

    public function saveAjaxAction()
    {
        $arrParams['data']['id']                 = $this->_params['data']['id'];
        if(!empty($this->_params['data']['request'])){
            $arrParams['data']['params']['request']  = $this->_params['data']['request'];
        }
        if(!empty($this->_params['data']['phone'])){
            $arrParams['data']['params']['phone']    = $this->_params['data']['phone'];
        }
        if(!empty($this->_params['data']['sales'])){
            $arrParams['data']['params']['sales']    = $this->_params['data']['sales'];
        }

        $arrParams['item'] = $this->getTable()->getItem(array('id' => $arrParams['data']['id']), null);

        $this->getTable()->saveItem($arrParams, array('task' => 'save-ajax'));
        return $this->response;
    }
}


