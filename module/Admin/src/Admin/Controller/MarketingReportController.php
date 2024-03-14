<?php

namespace Admin\Controller;

use kcfinder\zipFolder;
use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class   MarketingReportController extends ActionController
{

    public function init()
    {
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\MarketingReportTable';
        $this->_options['formName']  = 'formAdminMarketingReport';

        // Thiết lập session filter
        $ssFilter                                    = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']       = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'year';
        $this->_params['ssFilter']['order']          = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';
        $this->_params['ssFilter']['filter_status']  = $ssFilter->filter_status;
        $this->_params['ssFilter']['filter_keyword'] = $ssFilter->filter_keyword;

        $this->_params['ssFilter']['filter_date_begin'] = $ssFilter->filter_date_begin;
        $this->_params['ssFilter']['filter_date_end']   = $ssFilter->filter_date_end;
        $this->_params['ssFilter']['filter_sale_branch']= $ssFilter->filter_sale_branch;
        $this->_params['ssFilter']['filter_sale_group'] = $ssFilter->filter_sale_group;
        $this->_params['ssFilter']['filter_marketer_id']= $ssFilter->filter_marketer_id;
        $this->_params['ssFilter']['filter_product_group_id'] = $ssFilter->filter_product_group_id;

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
            $ssFilter->filter_date_begin = $data['filter_date_begin'];
            $ssFilter->filter_date_end   = $data['filter_date_end'];
            $ssFilter->filter_marketer_id= $data['filter_marketer_id'];
            $ssFilter->filter_product_group_id = $data['filter_product_group_id'];

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
        $this->_params['ssFilter']['filter_type'] = 'mkt_report_day_hour';
        // Mặc định lọc thời gian theo tuần.
        if(empty($this->_params['ssFilter']['filter_date_begin']))
            $this->_params['ssFilter']['filter_date_begin'] = $week_start;
        if(empty($this->_params['ssFilter']['filter_date_end']))
            $this->_params['ssFilter']['filter_date_end'] = $week_end;

        // phân quyền view
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
            }
        }

        // Tạo dữ liệu form search
        $myForm = new \Admin\Form\Search\MarketingReport($this->getServiceLocator(), $this->_params['ssFilter']);
        $myForm->setData($this->_params['ssFilter']);

        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item-type'));

        $this->_viewModel['myForm']       = $myForm;
        $this->_viewModel['items']        = $items;
        $this->_viewModel['count']        = $this->getTable()->countItem($this->_params, array('task' => 'list-item-type'));

        $this->_viewModel['user']         = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_group']   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['product_type'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-type')), array('task' => 'cache'));
        $this->_viewModel['products']     = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-group')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name'));

        $this->_viewModel['caption']      = 'Báo cáo chi phí chạy ADS';
        return new ViewModel($this->_viewModel);
    }

    public function formAction()
    {
        $myForm     = $this->getForm();
        $dateFormat = new \ZendX\Functions\Date();

        $task    = 'add-item';
        $caption = 'Báo cáo chi phí chạy ads - Thêm mới';
        $item    = array();
        if (!empty($this->params('id'))) {
            $this->_params['data']['id'] = $this->params('id');
            $item                        = $this->getTable()->getItem($this->_params['data']);
            if (!empty($item)) {
                $myForm->setInputFilter(new \Admin\Filter\MarketingReport());
                $item['date'] = $dateFormat->formatToView($item['date']);
                $myForm->bind($item);
                $task    = 'edit-item';
                $caption = 'Báo cáo chi phí chạy ads - Sửa';
            }
        } else {
            $data_default = array(
                'date' => date('d/m/Y'),
            );
            $myForm->setData($data_default);
        }

        if ($this->getRequest()->isPost()) {
            // Truyền các giá trị post vào filter để đặt điều kiện lại
            $myForm->setInputFilter(new \Admin\Filter\MarketingReport());

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

                    $report_item = $this->getTable()->getItem(array('date' => $dateFormat->formatToData($this->_params['data']['date']), 'marketer_id' => $this->_params['data']['marketer_id'], 'type' => 'mkt_report_day_hour'), array('task' => "marketer-date"));

                    if(empty($report_item)){
                        $result = $this->getTable()->saveItem($this->_params, array('task' => $task));
                        $this->flashMessenger()->addMessage('Dữ liệu đã được thêm mới thành công');
                    }
                    else{
                        $this->flashMessenger()->addMessage('Nhân viên đã có báo cáo không thể thêm mới');
                    }
                }
                elseif ($task == 'edit-item'){
                    $item = $this->getTable()->getItem($this->_params['data']);
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
        $report_time = array(
            '9h30'  => array(
                'sdt'    => 'SDT',
                'cp'     => 'Chi phí',
                //  'cp_sdt' => 'Chi phí/SDT'
            ),
            '11h00' => array(
                'sdt'    => 'SDT',
                'cp'     => 'Chi phí',
                //  'cp_sdt' => 'Chi phí/SDT'
            ),
            '15h00' => array(
                'sdt'    => 'SDT',
                'cp'     => 'Chi phí',
                //  'cp_sdt' => 'Chi phí/SDT'
            ),
            '17h30' => array(
                'sdt'    => 'SDT',
                'cp'     => 'Chi phí',
                //  'cp_sdt' => 'Chi phí/SDT'
            ),
            '22h00' => array(
                'sdt'    => 'SDT',
                'cp'     => 'Chi phí',
                //  'cp_sdt' => 'Chi phí/SDT'
            ),
            'total' => array(
                'sdt'    => 'SDT',
                'cp'     => 'Chi phí',
                //  'cp_sdt' => 'Chi phí/SDT'
            ),
        );

        $this->_viewModel['myForm'] = $myForm;
        $this->_viewModel['item']   = $item;
        $this->_viewModel['user'] = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-marketing'));
        $this->_viewModel['branch']          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['group']           = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group',
                                                                                                                                             'type' => 'marketing')), array('task' => 'cache'));
        $this->_viewModel['sale_group_type'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code'  => 'group-type',
                                                                                                                                             'alias' => 'marketing')), array('task' => 'cache'));

        $this->_viewModel['product_type'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-type')), array('task' => 'cache'));
        $this->_viewModel['report_time']  = $report_time;

        $this->_viewModel['caption'] = $caption;


        return new ViewModel($this->_viewModel);
    }

    public function addAllAction()
    {
        $dateFormat     = new \ZendX\Functions\Date();
        $myForm         = new \Admin\Form\MarketingReport\addAll($this);

        $caption = 'Tạo báo cáo marketing theo giờ';

        $data_default = array(
            'from_date' => date('01/m/Y'),
            'to_date'   => date('t/m/Y'),
        );
        $myForm->setData($data_default);

        if ($this->getRequest()->isPost()) {
            // Truyền các giá trị post vào filter để đặt điều kiện lại
            $myForm->setInputFilter(new \Admin\Filter\MarketingReport\addAll());
            $this->_params['data']['type']  = "mkt_report_day_hour";

            $myForm->setData($this->_params['data']);

            if ($myForm->isValid()) {
                $date_begin = date($dateFormat->formatToData($this->_params['data']['from_date']));
                $date_end   = date($dateFormat->formatToData($this->_params['data']['to_date']));
                $day_begin     = strtotime($date_begin);
                $day_end       = strtotime($date_end);
                $number_day    = abs($day_end - $day_begin) / 86400;

                $message = 'Tạo mới danh sách thành công';
                for ($i = 0; $i <= $number_day; $i++) {
                    $day   = date('Y-m-d', $day_begin + $i*86400);

                    $this->_params['data']['date']  = $day;
                    $this->_params['data']['month']  = date('m', strtotime($day));
                    $this->_params['data']['year']  = date('Y', strtotime($day));
                    if(count($this->_params['data']['user_marketing'])){
                        foreach ($this->_params['data']['user_marketing'] as $user) {
                            $this->_params['data']['marketer_id']  = $user;
                            foreach($this->_params['data']['product_ids'] as $product_group_id){
                                $this->_params['data']['product_group_id']  = $product_group_id;
                                $report_item = $this->getTable()->getItem(array('date' => $day, 'marketer_id' => $user, 'product_group_id' => $product_group_id, 'type' => 'mkt_report_day_hour'), array('task' => "marketer-date"));
                                if(empty($report_item)){
                                    $this->getTable()->saveItem($this->_params, array('task' => "add-all"));
                                }
                            }
                        }
                    }
                    else{
                        $message = 'Chưa chọn nhân viên cần thêm mới';
                    }
                }

                $this->flashMessenger()->addMessage(''.$message);
                $this->goRoute();
            }
        }

        $this->_viewModel['user_marketing'] = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-marketing'));
        $this->_viewModel['myForm']         = $myForm;
        $this->_viewModel['caption']        = $caption;
        return new ViewModel($this->_viewModel);
    }

    public function saveAjaxAction()
    {
        $arrParams['data']['id']                 = $this->_params['data']['id'];
        if(!empty($this->_params['data']['9h30_cp'])){
            $arrParams['data']['params']['9h30_cp']  = $this->_params['data']['9h30_cp'];
        }
        if(!empty($this->_params['data']['11h00_cp'])){
            $arrParams['data']['params']['11h00_cp'] = $this->_params['data']['11h00_cp'];
        }
        if(!empty($this->_params['data']['15h00_cp'])){
            $arrParams['data']['params']['15h00_cp'] = $this->_params['data']['15h00_cp'];
        }
        if(!empty($this->_params['data']['17h30_cp'])){
            $arrParams['data']['params']['17h30_cp'] = $this->_params['data']['17h30_cp'];
        }
        if(!empty($this->_params['data']['22h00_cp'])){
            $arrParams['data']['params']['22h00_cp'] = $this->_params['data']['22h00_cp'];
        }
        if(!empty($this->_params['data']['total_cp'])){
            $arrParams['data']['params']['total_cp'] = $this->_params['data']['total_cp'];
        }

        $arrParams['item'] = $this->getTable()->getItem(array('id' => $arrParams['data']['id']), null);
        $this->getTable()->saveItem($arrParams, array('task' => 'save-ajax'));

        // Cập nhật lại chi phí quảng cáo cho từng data
        if(!empty($arrParams['data']['params']['total_cp'])) {
            $arrParam['ssFilter']['filter_date_begin'] = $arrParams['item']['date'];
            $arrParam['ssFilter']['filter_date_end'] = $arrParams['item']['date'];
            $arrParam['ssFilter']['filter_type'] = 'mkt_report_day_hour';
            $items = $this->getTable()->listItem($arrParam, array('task' => 'list-item-type', 'paginator' => false));

            // $total_cp   = str_replace(',', '', $arrParams['data']['params']['total_cp']);
            $total_cp   = 0;
            foreach($items as $key => $value){
                if(!empty($value->params)){
                    $params = unserialize($value->params);
                    $cp = str_replace(',', '', $params['total_cp']);
                    $total_cp += $cp;
                }
            }

            $count_data = $this->getServiceLocator()->get('Admin\Model\ContractTable')->countItem(array('ssFilter' => array('filter_date_begin' => $arrParams['item']['date'], 'filter_date_end' => $arrParams['item']['date'])), array('task' => 'list-item'));
            $cost_ads = (int)($total_cp / $count_data);

            $params_update = array(
                'cost_ads' => $cost_ads,
                'date' => $arrParams['item']['date'],
            );
            $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($params_update, array('task' => 'update-cost-ads'));
        }
        return $this->response;
    }
}


