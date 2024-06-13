<?php

namespace Report\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class MarketingController extends ActionController {
    
    public function init() {
        $this->setLayout('report');
        
        // Lấy dữ liệu post của form
        $this->_params['data'] = $this->getRequest()->getPost()->toArray();
        
        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__);
        $this->_params['ssFilter']['filter_date_begin']     = $ssFilter->filter_date_begin;
        $this->_params['ssFilter']['filter_date_end']       = $ssFilter->filter_date_end;
        $this->_params['ssFilter']['product_cat_id']        = $ssFilter->product_cat_id;
        $this->_params['ssFilter']['product_group_id']      = $ssFilter->product_group_id;

        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
    }
    
    public function indexAction() {
        if(empty($this->_params['route']['id'])) {
            $this->_params['route']['id'] = 'revenue-branch';
        }
        
        $this->_viewModel['params'] = $this->_params;
        return new ViewModel($this->_viewModel);
    }

    // Báo cáo marketing 1
    public function overviewAction() {
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));
        if($this->getRequest()->isPost()) {
            // Lấy giá trị post từ filter
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();

            // Quyền user
            $curent_user = $this->_userInfo->getUserInfo();
            $permission_ids = explode(',', $curent_user['permission_ids']);
            if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids) && !in_array(MANAGER, $permission_ids)){
                if(in_array(GDCN, $permission_ids)){
                    $this->_params['data']['sale_branch_id'] = $curent_user['sale_branch_id'];
                }
                elseif (in_array(GROUP_MKT_LEADER, $permission_ids)){
                    $this->_params['data']['sale_branch_id'] = $curent_user['sale_branch_id'];
                    $this->_params['data']['sale_group_id'] = $curent_user['sale_group_id'];
                }
                else{
                    $this->_params['data']['marketer_id'] = $curent_user['id'];
                }
            }
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin']     = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']       = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_group_id']  = $this->_params['data']['sale_group_id'];
            $ssFilter->report['marketer_id']    = $this->_params['data']['marketer_id'];
            $ssFilter->report['product_cat_id'] = $this->_params['data']['product_cat_id'];

            $this->_params['ssFilter']          = $ssFilter->report;

            $marketers = $this->getServiceLocator()->get('Admin\Model\UserTable')->report($this->_params, array('task' => 'list-marketing'));
            // Tạo mảng lưu báo cáo.
            $data_report = [];
            foreach ($marketers as $key => $value) {
                $data_report[$value['id']]['name']           = $value['name'];
                $data_report[$value['id']]['target_phone']   = 0;
                $data_report[$value['id']]['target_sales']   = 0;
                $data_report[$value['id']]['new_phone']      = 0;
                $data_report[$value['id']]['new_contract']   = 0;
                $data_report[$value['id']]['new_sales']      = 0;
                $data_report[$value['id']]['old_contract']   = 0;
                $data_report[$value['id']]['old_sales']      = 0;
                $data_report[$value['id']]['cost_ads']       = 0;
                $data_report[$value['id']]['cost_capital']   = 0;
                $data_report[$value['id']]['cod_total']   = 0;
            }
            $data_report['total']['name']           = "Tổng";
            $data_report['total']['target_phone']   = 0;
            $data_report['total']['target_sales']   = 0;
            $data_report['total']['new_phone']      = 0;
            $data_report['total']['new_contract']   = 0;
            $data_report['total']['new_sales']      = 0;
            $data_report['total']['old_contract']   = 0;
            $data_report['total']['old_sales']      = 0;
            $data_report['total']['cost_ads']       = 0;
            $data_report['total']['cost_capital']   = 0;
            $data_report['total']['cod_total']   = 0;

            // Lấy dữ liệu mục tiêu.
            $where_target = array(
                'filter_type'       => 'mkt_target',
                'filter_date_begin' => $ssFilter->report['date_begin'],
                'filter_date_end'   => $ssFilter->report['date_end'],
            );
            $marketing_target = $this->getServiceLocator()->get('Admin\Model\MarketingReportTable')->report(array('ssFilter' => $where_target), array('task' => 'list-item-type'));
            foreach ($marketing_target as $key => $value){
                if (array_key_exists($value['marketer_id'], $data_report)) {
                    if (!empty($value['params'])) {
                        $params                                             = unserialize($value['params']);
                        $data_report[$value['marketer_id']]['target_phone'] += str_replace(",", "", $params['phone']);
                        $data_report['total']['target_phone'] += str_replace(",", "", $params['phone']);

                        $data_report[$value['marketer_id']]['target_sales'] += str_replace(",", "", $params['sales']);
                        $data_report['total']['target_sales'] += str_replace(",", "", $params['sales']);
                    }
                }
            }
            // Lấy dữ liệu doanh số mới, cũ.
            $where_contract = array(
                'filter_date_begin'     => $ssFilter->report['date_begin'],
                'filter_date_end'       => $ssFilter->report['date_end'],
                'filter_status_type'    => 'production_department_type',
                'filter_status'         => 'success',
            );

            $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report(array('ssFilter' => $where_contract), array('task' => 'join-contact'));

            foreach ($contracts as $key => $value){
                if(!empty($value['marketer_id']) && array_key_exists($value['marketer_id'], $data_report)){
                    if($ssFilter->report['product_cat_id']){
                        if (!empty($value['options'])) {
                            $options = unserialize($value['options']);
                            if (count($options['product'])) {
                                foreach ($options['product'] as $k => $v) {
                                    if($v['product_id'] == $ssFilter->report['product_cat_id']) {
                                        if(!empty($v['sales_new'])){
                                            $data_report[$value['marketer_id']]['new_contract'] += 1;
                                            $data_report['total']['new_contract']               += 1;

                                            $data_report[$value['marketer_id']]['new_sales'] += $v['sales_new'];
                                            $data_report['total']['new_sales']               += $v['sales_new'];
                                        }
                                        if(!empty($v['sales_old'])){
                                            $data_report[$value['marketer_id']]['old_contract'] += 1;
                                            $data_report['total']['old_contract']               += 1;

                                            $data_report[$value['marketer_id']]['old_sales'] += $v['sales_old'];
                                            $data_report['total']['old_sales']               += $v['sales_old'];
                                        }
                                        //$data_report[$value['marketer_id']]['cost_capital'] += $v['total_production'];
                                        //$data_report['total']['cost_capital'] += $v['total_production'];
                                        $data_report[$value['marketer_id']]['cost_capital'] += $v['capital_default'];
                                        $data_report['total']['cost_capital'] += $v['capital_default'];
                                    }
                                }
                            }
                        }
                    }
                    else {
                        if (!empty($value['sales_new'])) {
                            $data_report[$value['marketer_id']]['new_contract'] += 1;
                            $data_report['total']['new_contract']               += 1;

                            $data_report[$value['marketer_id']]['new_sales'] += $value['sales_new'];
                            $data_report['total']['new_sales']               += $value['sales_new'];
                        }
                        if (!empty($value['sales_old'])) {
                            $data_report[$value['marketer_id']]['old_contract'] += 1;
                            $data_report['total']['old_contract']               += 1;

                            $data_report[$value['marketer_id']]['old_sales'] += $value['sales_old'];
                            $data_report['total']['old_sales']               += $value['sales_old'];
                        }

                        // Tính giá vốn
                        if (!empty($value['options'])) {
                            $options = unserialize($value['options']);
                            if (count($options['product'])) {
                                foreach ($options['product'] as $k => $v) {
//                                    $data_report[$value['marketer_id']]['cost_capital'] += $v['total_production'];
//                                    $data_report['total']['cost_capital'] += $v['total_production'];
                                    $data_report[$value['marketer_id']]['cost_capital'] += $v['capital_default'];
                                    $data_report['total']['cost_capital'] += $v['capital_default'];
                                }
                            }
                        }
                    }
                    if ($data_report[$value['marketer_id']]['old_sales']||$data_report[$value['marketer_id']]['new_sales']) {
                        $data_report[$value['marketer_id']]['cod_total'] += $value['price_transport'];
                        $data_report['total']['cod_total']               += $value['price_transport'];
                    }
                }
            }

            // Lấy số điện thoại của mkt_x đã được chia.
            $contacts = $this->getServiceLocator()->get('Admin\Model\ContactTable')->report($this->_params, array('task' => 'join-user'))->toArray();
            foreach ($contacts as $key => $value){
                if(!empty($value['marketer_id'])  && array_key_exists($value['marketer_id'], $data_report)){
                    $data_report[$value['marketer_id']]['new_phone'] += 1;
                    $data_report['total']['new_phone'] += 1;
                }
            }

            // Lấy dữ liệu chi phí quảng cáo.
            $where_report = array(
                'filter_type'       => 'mkt_report_day_hour',
                'filter_date_begin' => $ssFilter->report['date_begin'],
                'filter_date_end'   => $ssFilter->report['date_end'],
            );
            $marketing_report = $this->getServiceLocator()->get('Admin\Model\MarketingReportTable')->report(array('ssFilter' => $where_report), array('task' => 'list-item-type'));
            foreach ($marketing_report as $key => $value){
                if(!empty($value['params'])  && array_key_exists($value['marketer_id'], $data_report)){
                    $params = unserialize($value['params']);
                    $data_report[$value['marketer_id']]['cost_ads'] +=  str_replace(",","",$params['total_cp']);
                    $data_report['total']['cost_ads'] +=  str_replace(",","",$params['total_cp']);
                }
            }

            // Người có quyền hiển thị chi phí giá vốn, doanh thu thực tế
            $show_cost_capital = false;
            $curent_user = $this->_userInfo->getUserInfo();
            $permission_ids = explode(',', $curent_user['permission_ids']);
            if(in_array(SYSTEM, $permission_ids) || in_array(ADMIN, $permission_ids) || in_array(GDCN, $permission_ids)){
                $show_cost_capital = true;
            }

            // Tham số bảng báo cáo
            $xhtmlItems = '';
            foreach ($data_report as $key => $value){
                $total_contract = ($value['new_contract'] + $value['old_contract']);
                $total_sales = ($value['old_sales'] + $value['new_sales']);

                $target_percent   = ($value['target_sales'] > 0 ? round($total_sales / $value['target_sales'] * 100, 2) : 0);
                $contract_percent = ($value['new_phone'] > 0 ? round($total_contract / $value['new_phone'] * 100, 2) : 0);
                $cost_phone       = ($value['new_phone'] > 0 ? ($value['cost_ads'] / $value['new_phone']) : 0);
                $cost_sales       = ($total_sales > 0 ? round($value['cost_ads'] / $total_sales * 100, 2) : 0);
                $cost_contract    = ($total_contract > 0 ? ($value['cost_ads'] / $total_contract) : 0);
                $sales_reality    = ($total_sales - $value['cost_ads'] - $value['cost_capital'] - $value['cod_total']);

                $data_report[$key]['total_sales']      = $total_sales;
                $data_report[$key]['target_percent']   = $target_percent;
                $data_report[$key]['contract_percent'] = $contract_percent;
                $data_report[$key]['cost_phone']       = $cost_phone;
                $data_report[$key]['cost_sales']       = $cost_sales;
                $data_report[$key]['cost_contract']    = $cost_contract;
                $data_report[$key]['sales_reality']    = $sales_reality;
            }

            // sắp xếp theo doanh tổng doanh thu
            $key_sort = [];
            $i = 0;
            foreach ($data_report as $key => $value) {
                if($key != 'total'){
                    $key_sort[$i]['id'] = $key;
                    $key_sort[$i]['total_sales'] = $data_report[$key]['total_sales'];
                    $i++;
                }
            }
            for($i = 0; $i < count($key_sort) - 1; $i++){
                for($j = $i+1; $j < count($key_sort); $j++){
                    if($key_sort[$i]['total_sales'] < $key_sort[$j]['total_sales']){
                        $tm              = $key_sort[$i];
                        $key_sort[$i]    = $key_sort[$j];
                        $key_sort[$j]    = $tm;
                    }
                }
            }

            foreach ($key_sort as $key => $value){
                $xhtmlItems .= '<tr data-key="'.$value['id'].'">
                                    <th class="text-bold">'.$data_report[$value['id']]['name'].'</th>
                                    <td class="mask_currency text-center">'.$data_report[$value['id']]['target_phone'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['target_sales'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['target_percent'].'%</td> 
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['new_phone'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['new_contract'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['new_sales'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['old_contract'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['old_sales'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['total_sales'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['contract_percent'].'%</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['cost_phone'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['cost_sales'].'%</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['cost_contract'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['cost_ads'].'</td>';
                                    if($show_cost_capital){
                                        $xhtmlItems .= '<td class="mask_currency text-right">'.$data_report[$value['id']]['cost_capital'].'</td>
                                                        <td class="mask_currency text-right">'.$data_report[$value['id']]['cod_total'].'</td>
                                                        <td class="mask_currency text-right">'.$data_report[$value['id']]['sales_reality'].'</td>';
                                    }

                $xhtmlItems .=  '</tr>';
            }
            // Hiển thị dòng tổng.
            $xhtmlItems .= '<tr class="text-bold text-red">
                                    <th class="text-bold">'.$data_report['total']['name'].'</th>
                                    <td class="mask_currency text-center">'.$data_report['total']['target_phone'].'</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['target_sales'].'</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['target_percent'].'%</td> 
                                    <td class="mask_currency text-right">'.$data_report['total']['new_phone'].'</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['new_contract'].'</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['new_sales'].'</td>
                                    <td class="mask_currency text-right" data-field="old_contract">'.$data_report['total']['old_contract'].'</td>
                                    <td class="mask_currency text-right" data-field="old_sales">'.$data_report['total']['old_sales'].'</td>
                                    <td class="mask_currency text-right" data-field="total_sales">'.$data_report['total']['total_sales'].'</td>
                                    <td class="mask_currency text-right" data-field="contract_percent">'.$data_report['total']['contract_percent'].'%</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['cost_phone'].'</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['cost_sales'].'%</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['cost_contract'].'</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['cost_ads'].'</td>';
                                    if($show_cost_capital){
                                        $xhtmlItems .= '<td class="mask_currency text-right">'.$data_report['total']['cost_capital'].'</td>
                                                        <td class="mask_currency text-right">'.$data_report['total']['cod_total'].'</td>
                                                        <td class="mask_currency text-right">'.$data_report['total']['sales_reality'].'</td>';
                                    }
            $xhtmlItems .=  '</tr>';

            $cost_capital = '';
            if($show_cost_capital){
                $cost_capital .= '<th rowspan="2">Giá vốn mặc định </th>
                            	  <th rowspan="2">COD</th>
                            	  <th rowspan="2">Điểm hòa vốn </th>';
            }
            $result['reportTable'] = '<thead data-count_contracts="'.count($contracts).'">
                        				    <tr>
                            					<th class="fix-head" rowspan="2" class="text-center">Nhân viên</th>
                            					<th colspan="3" class="text-center">Mục tiêu tổng</th>
                            					<th colspan="3" class="text-center">Doanh số mới</th>
                            					<th colspan="2" class="text-center">Doanh số cũ</th>
                            					<th rowspan="2" class="text-center">Tổng doanh số</th>
                            					<th rowspan="2" class="text-center">% Tỉ lệ chốt</th>
                            					<th rowspan="2" class="text-center">% Chi phí QC</br>/ SĐT</th>
                            					<th rowspan="2" class="text-center">% Chi Phí QC</br>/ Doanh Số</th>
                            					<th rowspan="2" class="text-center">Chi Phí QC</br>/ Đơn Hàng</th>
                            					<th rowspan="2" class="text-center">Chi Phí QC</th>
                            					'.$cost_capital.'
                        					</tr>
                        				    <tr>
                            					<th style="min-width: 80px;" class="text-center">SĐT</th>
                            					<th style="min-width: 80px;" class="text-center">Doanh Số</th>
                            					<th style="min-width: 80px;" class="text-center">% Mục Tiêu</th>
                            					<th style="min-width: 80px;" class="text-center">Tổng SĐT</th>
                            					<th style="min-width: 80px;" class="text-center">Số Đơn</th>
                            					<th style="min-width: 80px;" class="text-center">Doanh Số</th>
                            					<th style="min-width: 80px;" class="text-center">Số Đơn</th>
                            					<th style="min-width: 80px;" class="text-center">Doanh Số</th>
                        					</tr>
                        				</thead>
                        				<tbody>
                        				    '. $xhtmlItems .'
                        				</tbody>';

            echo json_encode($result);

            return $this->response;
        }
        else {
            // Khai báo giá trị ngày tháng
            $default_date_begin     = date('01/m/Y');
            $default_date_end       = date('t/m/Y');

            $ssFilter->report                   = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']     = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $default_date_begin;
            $ssFilter->report['date_end']       = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $default_date_end;
            $ssFilter->report['sale_branch_id'] = $ssFilter->report['sale_branch_id'] ? $ssFilter->report['sale_branch_id'] : $this->_userInfo->getUserInfo('sale_branch_id');
            $ssFilter->report['sale_group_id']  = $ssFilter->report['sale_group_id'] ? $ssFilter->report['sale_group_id'] : $this->_userInfo->getUserInfo('sale_group_id');
            $ssFilter->report['marketer_id']    = $ssFilter->report['marketer_id'] ? $ssFilter->report['marketer_id'] : '';
            $ssFilter->report['product_cat_id'] = $ssFilter->report['product_cat_id'] ? $ssFilter->report['product_cat_id'] : '';

            $this->_params['ssFilter']          = $ssFilter->report;

            // Set giá trị cho form
            $myForm	= new \Report\Form\Marketing\Overview($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);

            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['saleGroup']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['caption']        = 'Báo cáo marketing tổng quan';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Báo cáo marketing thành công
    public function overview12Action() {
        $date_format     = new \ZendX\Functions\Date();
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));
        if($this->getRequest()->isPost()) {
            // Lấy giá trị post từ filter
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();

            // Quyền user
            $curent_user = $this->_userInfo->getUserInfo();
            $permission_ids = explode(',', $curent_user['permission_ids']);
            if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids) && !in_array(MANAGER, $permission_ids)){
                if(in_array(GDCN, $permission_ids)){
                    $this->_params['data']['sale_branch_id'] = $curent_user['sale_branch_id'];
                }
                elseif (in_array(GROUP_MKT_LEADER, $permission_ids)){
                    $this->_params['data']['sale_branch_id'] = $curent_user['sale_branch_id'];
                    $this->_params['data']['sale_group_id'] = $curent_user['sale_group_id'];
                }
                else{
                    $this->_params['data']['marketer_id'] = $curent_user['id'];
                }
            }
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin']         = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']           = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id']     = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_group_id']      = $this->_params['data']['sale_group_id'];
            $ssFilter->report['marketer_id']        = $this->_params['data']['marketer_id'];
            $ssFilter->report['product_group_id']   = $this->_params['data']['product_group_id'];

            $this->_params['ssFilter']          = $ssFilter->report;

            $marketers = $this->getServiceLocator()->get('Admin\Model\UserTable')->report($this->_params, array('task' => 'list-marketing'));
            // Tạo mảng lưu báo cáo.
            $data_report = [];
            foreach ($marketers as $key => $value) {
                $data_report[$value['id']]['name']              = $value['name'];
                $data_report[$value['id']]['new_phone']         = 0;
                $data_report[$value['id']]['new_contract']      = 0;
                $data_report[$value['id']]['giam-tru-doanh-thu']= 0;
                $data_report[$value['id']]['hang-hoan']         = 0;
                $data_report[$value['id']]['new_sales']         = 0;
                $data_report[$value['id']]['old_contract']      = 0;
                $data_report[$value['id']]['old_sales']         = 0;
                $data_report[$value['id']]['cost_ads']          = 0;
                $data_report[$value['id']]['cost_capital']      = 0;
                $data_report[$value['id']]['cod_total']         = 0;
            }
            $data_report['total']['name']               = "Tổng";
            $data_report['total']['new_phone']          = 0;
            $data_report['total']['new_contract']       = 0;
            $data_report['total']['giam-tru-doanh-thu'] = 0;
            $data_report['total']['hang-hoan']          = 0;
            $data_report['total']['new_sales']          = 0;
            $data_report['total']['old_contract']       = 0;
            $data_report['total']['old_sales']          = 0;
            $data_report['total']['cost_ads']           = 0;
            $data_report['total']['cost_capital']       = 0;
            $data_report['total']['cod_total']          = 0;

            $data_report[$value['user_id']]['giam-tru-doanh-thu'] += $value['price_reduce_sale'];
            $data_report['total']['giam-tru-doanh-thu'] += $value['price_reduce_sale'];

            // Lấy dữ liệu doanh số mới, cũ.
            $where_contract = array(
                'filter_date_begin'         => $ssFilter->report['date_begin'],
                'filter_date_end'           => $ssFilter->report['date_end'],
                'filter_product_group_id'   => $ssFilter->report['product_group_id'],
                'date_type'                 => 'shipped_date',
                'filter_status'             => 'success',
            );

            $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report(array('ssFilter' => $where_contract), array('task' => 'join-contact-producted'));

            $thanhcong_status       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(array('alias' => 'thanh-cong',  'code' => 'status-merge'), array('task' => 'by-custom-alias'));
            $thanhcong_arr          = array_merge(explode(',', trim($thanhcong_status['content'])), explode(',', trim($thanhcong_status['note'])), explode(',', trim($thanhcong_status['description'])));

            $hanghoan_status        = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(array('alias' => 'hang-hoan',  'code' => 'status-merge'), array('task' => 'by-custom-alias'));
            $hanghoan_arr           = array_merge(explode(',', trim($hanghoan_status['content'])), explode(',', trim($hanghoan_status['note'])), explode(',', trim($hanghoan_status['description'])));

            foreach ($contracts as $key => $value){
                if(!empty($value['marketer_id']) && array_key_exists($value['marketer_id'], $data_report) && $value['status_id'] != HUY_SALES){


                    if($date_format->diff($value['contact_contract_first_date'], $value['created'], 'hour') < 48 && !empty($value['marketer_id'])) {
                        if (in_array($value['ghtk_status'], $thanhcong_arr)) {
                            $data_report[$value['marketer_id']]['new_contract'] += 1;
                            $data_report['total']['new_contract'] += 1;
                            $data_report[$value['marketer_id']]['new_sales'] += $value['price_total'] - $value['price_reduce_sale'];
                            $data_report['total']['new_sales'] += $value['price_total'] - $value['price_reduce_sale'];
                        }
                        // Tính giá vốn
                        if (!empty($value['options'])) {
                            $options = unserialize($value['options']);
                            if (count($options['product'])) {
                                foreach ($options['product'] as $k => $v) {
                                    $data_report[$value['marketer_id']]['cost_capital'] += ($v['cost'] + $v['cost_new']) * $v['numbers'];
                                    $data_report['total']['cost_capital'] += ($v['cost'] + $v['cost_new']) * $v['numbers'];
                                }
                            }
                        }

                        $data_report[$value['marketer_id']]['cod_total'] += $value['price_transport'] + $value['ship_ext'];
                        $data_report['total']['cod_total'] += $value['price_transport'] + $value['ship_ext'];
                    }

                    // Giảm trừ doanh thu
                    $data_report[$value['marketer_id']]['giam-tru-doanh-thu'] += $value['price_reduce_sale'];
                    $data_report['total']['giam-tru-doanh-thu'] += $value['price_reduce_sale'];
                    // Dục đơn - hoàn
                    if (in_array($value['ghtk_status'], $hanghoan_arr)) {
                        $data_report[$value['marketer_id']]['hang-hoan'] += $value['price_total'] - $value['vat'];
                        $data_report['total']['hang-hoan'] += $value['price_total'] - $value['vat'];
                    }
                }
            }

            $this->_params['data']['huy_contact'] = 1;
            $contacts = $this->getServiceLocator()->get('Admin\Model\FormDataTable')->report($this->_params, array('task' => 'list-item-shared'));
            foreach ($contacts as $key => $value){
                if(!empty($value['marketer_id'])  && array_key_exists($value['marketer_id'], $data_report)){
                    $data_report[$value['marketer_id']]['new_phone'] += 1;
                    $data_report['total']['new_phone'] += 1;
                }
            }

//            $product_group_condition = !empty($ssFilter->report['product_group_id']) ? " and product_group_id = '".$ssFilter->report['product_group_id']."' " : '';
//            $marketer_id_condition = !empty($ssFilter->report['marketer_id']) ? " and marketer_id = '".$ssFilter->report['marketer_id']."' " : '';
//            $sql_select = "SELECT marketer_id, sum(cost_ads) as cost_ads FROM ".TABLE_CONTACT." WHERE date >= '".$date_format->formatToData($ssFilter->report['date_begin'], 'Y-m-d')." 00:00:00'
//            and date <= '".$date_format->formatToData($ssFilter->report['date_end'], 'Y-m-d')." 23:59:59' ".$product_group_condition . $marketer_id_condition . " GROUP BY marketer_id;";
//            $contact_cost_ads = $this->getServiceLocator()->get('Admin\Model\ContactTable')->report(array('sql' => $sql_select), array('task' => 'query'));
//            foreach($contact_cost_ads as $key => $value){
//                if (array_key_exists($value['marketer_id'], $data_report)) {
//                    $data_report[$value['marketer_id']]['cost_ads'] += $value['cost_ads'];
//                    $data_report['total']['cost_ads'] += $value['cost_ads'];
//                }
//            }
            $product_group_condition = !empty($ssFilter->report['product_group_id']) ? " and product_group_id = '".$ssFilter->report['product_group_id']."' " : '';
            $marketer_id_condition = !empty($ssFilter->report['marketer_id']) ? " and marketer_id = '".$ssFilter->report['marketer_id']."' " : '';
            $sql_select = "SELECT marketer_id, sum(price) as cost_ads FROM ".TABLE_MARKETING_ADS." WHERE from_date >= '".$date_format->formatToData($ssFilter->report['date_begin'], 'Y-m-d')." 00:00:00'
            and to_date <= '".$date_format->formatToData($ssFilter->report['date_end'], 'Y-m-d')." 23:59:59' ".$product_group_condition . $marketer_id_condition . " GROUP BY marketer_id;";
            $contact_cost_ads = $this->getServiceLocator()->get('Admin\Model\ContactTable')->report(array('sql' => $sql_select), array('task' => 'query'));
            foreach($contact_cost_ads as $key => $value){
                if (array_key_exists($value['marketer_id'], $data_report)) {
                    $data_report[$value['marketer_id']]['cost_ads'] += $value['cost_ads'];
                    $data_report['total']['cost_ads'] += $value['cost_ads'];
                }
            }






            // Người có quyền hiển thị chi phí giá vốn, doanh thu thực tế
            $show_cost_capital = false;
            $curent_user = $this->_userInfo->getUserInfo();
            $permission_ids = explode(',', $curent_user['permission_ids']);
            if(in_array(SYSTEM, $permission_ids) || in_array(ADMIN, $permission_ids) || in_array(GDCN, $permission_ids)){
                $show_cost_capital = true;
            }

            // Tham số bảng báo cáo
            $xhtmlItems = '';
            foreach ($data_report as $key => $value){
                $total_contract = ($value['new_contract'] + $value['old_contract']);
                $total_sales = ($value['old_sales'] + $value['new_sales']);

                $target_percent   = ($value['target_sales'] > 0 ? round($total_sales / $value['target_sales'] * 100, 2) : 0);
                $contract_percent = ($value['new_phone'] > 0 ? round($total_contract / $value['new_phone'] * 100, 2) : 0);
                $cost_phone       = ($value['new_phone'] > 0 ? ($value['cost_ads'] / $value['new_phone']) : 0);
                $cost_sales       = ($total_sales > 0 ? round($value['cost_ads'] / $total_sales * 100, 2) : 0);
                $cost_contract    = ($total_contract > 0 ? ($value['cost_ads'] / $total_contract) : 0);
                $sales_reality    = ($total_sales - $value['cost_ads'] - $value['cost_capital'] - $value['cod_total']);

                $data_report[$key]['total_sales']      = $total_sales;
                $data_report[$key]['target_percent']   = $target_percent;
                $data_report[$key]['contract_percent'] = $contract_percent;
                $data_report[$key]['cost_phone']       = $cost_phone;
                $data_report[$key]['cost_sales']       = $cost_sales;
                $data_report[$key]['cost_contract']    = $cost_contract;
                $data_report[$key]['sales_reality']    = $sales_reality;
            }

            // sắp xếp theo doanh tổng doanh thu
            $key_sort = [];
            $i = 0;
            foreach ($data_report as $key => $value) {
                if($key != 'total'){
                    $key_sort[$i]['id'] = $key;
                    $key_sort[$i]['total_sales'] = $data_report[$key]['total_sales'];
                    $i++;
                }
            }
            for($i = 0; $i < count($key_sort) - 1; $i++){
                for($j = $i+1; $j < count($key_sort); $j++){
                    if($key_sort[$i]['total_sales'] < $key_sort[$j]['total_sales']){
                        $tm              = $key_sort[$i];
                        $key_sort[$i]    = $key_sort[$j];
                        $key_sort[$j]    = $tm;
                    }
                }
            }

            foreach ($key_sort as $key => $value){
                $xhtmlItems .= '<tr data-key="'.$value['id'].'">
                                    <th class="text-bold">'.$data_report[$value['id']]['name'].'</th>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['new_phone'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['new_contract'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['new_sales'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['giam-tru-doanh-thu'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['hang-hoan'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['contract_percent'].'%</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['cost_phone'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['cost_sales'].'%</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['cost_contract'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['cost_ads'].'</td>';
                                    if($show_cost_capital){
                                        $xhtmlItems .= '<td class="mask_currency text-right">'.$data_report[$value['id']]['cost_capital'].'</td>
                                                        <td class="mask_currency text-right">'.$data_report[$value['id']]['cod_total'].'</td>
                                                        <td class="mask_currency text-right">'.$data_report[$value['id']]['sales_reality'].'</td>';
                                    }

                $xhtmlItems .=  '</tr>';
            }
            // Hiển thị dòng tổng.
            $xhtmlItems .= '<tr class="text-bold text-red">
                                    <th class="text-bold">'.$data_report['total']['name'].'</th>
                                    <td class="mask_currency text-right">'.$data_report['total']['new_phone'].'</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['new_contract'].'</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['new_sales'].'</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['hang-hoan'].'</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['hang-hoan'].'</td>
                                    <td class="mask_currency text-right" data-field="contract_percent">'.$data_report['total']['contract_percent'].'%</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['cost_phone'].'</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['cost_sales'].'%</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['cost_contract'].'</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['cost_ads'].'</td>';
                                    if($show_cost_capital){
                                        $xhtmlItems .= '<td class="mask_currency text-right">'.$data_report['total']['cost_capital'].'</td>
                                                        <td class="mask_currency text-right">'.$data_report['total']['cod_total'].'</td>
                                                        <td class="mask_currency text-right">'.$data_report['total']['sales_reality'].'</td>';
                                    }
            $xhtmlItems .=  '</tr>';

            $cost_capital = '';
            if($show_cost_capital){
                $cost_capital .= '<th rowspan="2">Giá vốn mặc định </th>
                            	  <th rowspan="2">COD</th>
                            	  <th rowspan="2">Điểm hòa vốn </th>';
            }
            $result['reportTable'] = '<thead data-count_contracts="'.count($contracts).'">
                        				    <tr>
                            					<th class="fix-head" rowspan="2" class="text-center">Nhân viên</th>
                            					<th colspan="5" class="text-center">Doanh số</th>
                            					<th rowspan="2" class="text-center">% Tỉ lệ chốt</th>
                            					<th rowspan="2" class="text-center">% Chi phí QC</br>/ SĐT</th>
                            					<th rowspan="2" class="text-center">% Chi Phí QC</br>/ Doanh Số</th>
                            					<th rowspan="2" class="text-center">Chi Phí QC</br>/ Đơn Hàng</th>
                            					<th rowspan="2" class="text-center">Chi Phí QC</th>
                            					'.$cost_capital.'
                        					</tr>
                        				    <tr>
                            					<th style="min-width: 80px;" class="text-center">Tổng SĐT</th>
                            					<th style="min-width: 80px;" class="text-center">Số Đơn</th>
                            					<th style="min-width: 80px;" class="text-center">Doanh Số</th>
                            					<th style="min-width: 80px;" class="text-center">Giảm trừ doanh thu</th>
                            					<th style="min-width: 80px;" class="text-center">Hàng hoàn</th>
                        					</tr>
                        				</thead>
                        				<tbody>
                        				    '. $xhtmlItems .'
                        				</tbody>';

            echo json_encode($result);

            return $this->response;
        }
        else {
            // Khai báo giá trị ngày tháng
            $default_date_begin     = date('01/m/Y');
            $default_date_end       = date('t/m/Y');

            $ssFilter->report                   = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']     = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $default_date_begin;
            $ssFilter->report['date_end']       = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $default_date_end;
            $ssFilter->report['sale_branch_id'] = $ssFilter->report['sale_branch_id'] ? $ssFilter->report['sale_branch_id'] : $this->_userInfo->getUserInfo('sale_branch_id');
            $ssFilter->report['sale_group_id']  = $ssFilter->report['sale_group_id'] ? $ssFilter->report['sale_group_id'] : $this->_userInfo->getUserInfo('sale_group_id');
            $ssFilter->report['marketer_id']    = $ssFilter->report['marketer_id'] ? $ssFilter->report['marketer_id'] : '';
            $ssFilter->report['product_group_id'] = $ssFilter->report['product_group_id'] ? $ssFilter->report['product_group_id'] : '';

            $this->_params['ssFilter']          = $ssFilter->report;

            // Set giá trị cho form
            $myForm	= new \Report\Form\Marketing\Overview($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);

            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['saleGroup']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['caption']        = 'Báo cáo marketing tổng quan';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Báo cáo marketing xuất hàng
    public function overview13Action() {
        $date_format     = new \ZendX\Functions\Date();
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));
        if($this->getRequest()->isPost()) {
            // Lấy giá trị post từ filter
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();

            // Quyền user
            $curent_user = $this->_userInfo->getUserInfo();
            $permission_ids = explode(',', $curent_user['permission_ids']);
            if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids) && !in_array(MANAGER, $permission_ids)){
                if(in_array(GDCN, $permission_ids)){
                    $this->_params['data']['sale_branch_id'] = $curent_user['sale_branch_id'];
                }
                elseif (in_array(GROUP_MKT_LEADER, $permission_ids)){
                    $this->_params['data']['sale_branch_id'] = $curent_user['sale_branch_id'];
                    $this->_params['data']['sale_group_id'] = $curent_user['sale_group_id'];
                }
                else{
                    $this->_params['data']['marketer_id'] = $curent_user['id'];
                }
            }
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin']         = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']           = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id']     = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_group_id']      = $this->_params['data']['sale_group_id'];
            $ssFilter->report['marketer_id']        = $this->_params['data']['marketer_id'];
            $ssFilter->report['product_group_id']   = $this->_params['data']['product_group_id'];

            $this->_params['ssFilter']          = $ssFilter->report;

            $marketers = $this->getServiceLocator()->get('Admin\Model\UserTable')->report($this->_params, array('task' => 'list-marketing'));
            // Tạo mảng lưu báo cáo.
            $data_report = [];
            foreach ($marketers as $key => $value) {
                $data_report[$value['id']]['name']                  = $value['name'];
                $data_report[$value['id']]['new_phone']             = 0;
                $data_report[$value['id']]['new_contract']          = 0;
                $data_report[$value['id']]['new_sales']             = 0;
                $data_report[$value['id']]['giam-tru-doanh-thu']    = 0;
                $data_report[$value['id']]['hang-hoan']             = 0;
                $data_report[$value['id']]['old_contract']          = 0;
                $data_report[$value['id']]['old_sales']             = 0;
                $data_report[$value['id']]['cost_ads']              = 0;
                $data_report[$value['id']]['cost_capital']          = 0;
                $data_report[$value['id']]['cod_total']             = 0;
            }
            $data_report['total']['name']               = "Tổng";
            $data_report['total']['new_phone']          = 0;
            $data_report['total']['new_contract']       = 0;
            $data_report['total']['new_sales']          = 0;
            $data_report['total']['giam-tru-doanh-thu'] = 0;
            $data_report['total']['hang-hoan']          = 0;
            $data_report['total']['old_contract']       = 0;
            $data_report['total']['old_sales']          = 0;
            $data_report['total']['cost_ads']           = 0;
            $data_report['total']['cost_capital']       = 0;
            $data_report['total']['cod_total']          = 0;

            // Lấy dữ liệu doanh số mới, cũ.
            $where_contract = array(
                'filter_date_begin'     => $ssFilter->report['date_begin'],
                'filter_date_end'       => $ssFilter->report['date_end'],
                'filter_product_group_id'       => $ssFilter->report['product_group_id'],
                'date_type'             => 'shipped_date',
                'filter_status'         => 'success',
            );

            $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report(array('ssFilter' => $where_contract), array('task' => 'join-contact-producted'));

            $hanghoan_status        = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(array('alias' => 'hang-hoan',  'code' => 'status-merge'), array('task' => 'by-custom-alias'));
            $hanghoan_arr           = array_merge(explode(',', trim($hanghoan_status['content'])), explode(',', trim($hanghoan_status['note'])), explode(',', trim($hanghoan_status['description'])));

            foreach ($contracts as $key => $value){
                if(!empty($value['marketer_id']) && array_key_exists($value['marketer_id'], $data_report) && $value['status_id'] != HUY_SALES){
                    if($date_format->diff($value['contact_contract_first_date'], $value['created'], 'hour') < 48 && !empty($value['marketer_id'])){
                        $data_report[$value['marketer_id']]['new_contract'] += 1;
                        $data_report['total']['new_contract']               += 1;

                        $data_report[$value['marketer_id']]['new_sales'] += $value['price_total'];
                        $data_report['total']['new_sales']               += $value['price_total'];

                        // Tính giá vốn
                        if (!empty($value['options'])) {
                            $options = unserialize($value['options']);
                            if (count($options['product'])) {
                                foreach ($options['product'] as $k => $v) {
                                    $data_report[$value['marketer_id']]['cost_capital'] += ($v['cost'] + $v['cost_new']) * $v['numbers'];
                                    $data_report['total']['cost_capital'] += ($v['cost'] + $v['cost_new']) * $v['numbers'];
                                }
                            }
                        }

                        $data_report[$value['marketer_id']]['cod_total'] += $value['price_transport'] + $value['ship_ext'];
                        $data_report['total']['cod_total']               += $value['price_transport'] + $value['ship_ext'];
                    }

                    // Giảm trừ doanh thu
                    $data_report[$value['marketer_id']]['giam-tru-doanh-thu'] += $value['price_reduce_sale'];
                    $data_report['total']['giam-tru-doanh-thu'] += $value['price_reduce_sale'];
                    // Dục đơn - hoàn
                    if (in_array($value['ghtk_status'], $hanghoan_arr)) {
                        $data_report[$value['marketer_id']]['hang-hoan'] += $value['price_total'] - $value['vat'];
                        $data_report['total']['hang-hoan'] += $value['price_total'] - $value['vat'];
                    }
                }
            }

            $this->_params['data']['huy_contact'] = 1;
            $contacts = $this->getServiceLocator()->get('Admin\Model\FormDataTable')->report($this->_params, array('task' => 'list-item-shared'));
            foreach ($contacts as $key => $value){
                if(!empty($value['marketer_id'])  && array_key_exists($value['marketer_id'], $data_report)){
                    $data_report[$value['marketer_id']]['new_phone'] += 1;
                    $data_report['total']['new_phone'] += 1;
                }
            }

//            $product_group_condition = !empty($ssFilter->report['product_group_id']) ? " and product_group_id = '".$ssFilter->report['product_group_id']."' " : '';
//            $marketer_id_condition = !empty($ssFilter->report['marketer_id']) ? " and marketer_id = '".$ssFilter->report['marketer_id']."' " : '';
//            $sql_select = "SELECT marketer_id, sum(cost_ads) as cost_ads FROM ".TABLE_CONTACT." WHERE date >= '".$date_format->formatToData($ssFilter->report['date_begin'], 'Y-m-d')." 00:00:00'
//            and date <= '".$date_format->formatToData($ssFilter->report['date_end'], 'Y-m-d')." 23:59:59' ".$product_group_condition . $marketer_id_condition . " GROUP BY marketer_id;";
//            $contact_cost_ads = $this->getServiceLocator()->get('Admin\Model\ContactTable')->report(array('sql' => $sql_select), array('task' => 'query'));
//            foreach($contact_cost_ads as $key => $value){
//                if (array_key_exists($value['marketer_id'], $data_report)) {
//                    $data_report[$value['marketer_id']]['cost_ads'] += $value['cost_ads'];
//                    $data_report['total']['cost_ads'] += $value['cost_ads'];
//                }
//            }

            $product_group_condition = !empty($ssFilter->report['product_group_id']) ? " and product_group_id = '".$ssFilter->report['product_group_id']."' " : '';
            $marketer_id_condition = !empty($ssFilter->report['marketer_id']) ? " and marketer_id = '".$ssFilter->report['marketer_id']."' " : '';
            $sql_select = "SELECT marketer_id, sum(price) as cost_ads FROM ".TABLE_MARKETING_ADS." WHERE from_date >= '".$date_format->formatToData($ssFilter->report['date_begin'], 'Y-m-d')." 00:00:00'
            and to_date <= '".$date_format->formatToData($ssFilter->report['date_end'], 'Y-m-d')." 23:59:59' ".$product_group_condition . $marketer_id_condition . " GROUP BY marketer_id;";
            $contact_cost_ads = $this->getServiceLocator()->get('Admin\Model\ContactTable')->report(array('sql' => $sql_select), array('task' => 'query'));
            foreach($contact_cost_ads as $key => $value){
                if (array_key_exists($value['marketer_id'], $data_report)) {
                    $data_report[$value['marketer_id']]['cost_ads'] += $value['cost_ads'];
                    $data_report['total']['cost_ads'] += $value['cost_ads'];
                }
            }

            // Người có quyền hiển thị chi phí giá vốn, doanh thu thực tế
            $show_cost_capital = false;
            $curent_user = $this->_userInfo->getUserInfo();
            $permission_ids = explode(',', $curent_user['permission_ids']);
            if(in_array(SYSTEM, $permission_ids) || in_array(ADMIN, $permission_ids) || in_array(GDCN, $permission_ids)){
                $show_cost_capital = true;
            }

            // Tham số bảng báo cáo
            $xhtmlItems = '';
            foreach ($data_report as $key => $value){
                $total_contract = ($value['new_contract'] + $value['old_contract']);
                $total_sales = ($value['old_sales'] + $value['new_sales']);

                $target_percent   = ($value['target_sales'] > 0 ? round($total_sales / $value['target_sales'] * 100, 2) : 0);
                $contract_percent = ($value['new_phone'] > 0 ? round($total_contract / $value['new_phone'] * 100, 2) : 0);
                $cost_phone       = ($value['new_phone'] > 0 ? ($value['cost_ads'] / $value['new_phone']) : 0);
                $cost_sales       = ($total_sales > 0 ? round($value['cost_ads'] / $total_sales * 100, 2) : 0);
                $cost_contract    = ($total_contract > 0 ? ($value['cost_ads'] / $total_contract) : 0);
                $sales_reality    = ($total_sales - $value['cost_ads'] - $value['cost_capital'] - $value['cod_total']);

                $data_report[$key]['total_sales']      = $total_sales;
                $data_report[$key]['target_percent']   = $target_percent;
                $data_report[$key]['contract_percent'] = $contract_percent;
                $data_report[$key]['cost_phone']       = $cost_phone;
                $data_report[$key]['cost_sales']       = $cost_sales;
                $data_report[$key]['cost_contract']    = $cost_contract;
                $data_report[$key]['sales_reality']    = $sales_reality;
            }

            // sắp xếp theo doanh tổng doanh thu
            $key_sort = [];
            $i = 0;
            foreach ($data_report as $key => $value) {
                if($key != 'total'){
                    $key_sort[$i]['id'] = $key;
                    $key_sort[$i]['total_sales'] = $data_report[$key]['total_sales'];
                    $i++;
                }
            }
            for($i = 0; $i < count($key_sort) - 1; $i++){
                for($j = $i+1; $j < count($key_sort); $j++){
                    if($key_sort[$i]['total_sales'] < $key_sort[$j]['total_sales']){
                        $tm              = $key_sort[$i];
                        $key_sort[$i]    = $key_sort[$j];
                        $key_sort[$j]    = $tm;
                    }
                }
            }

            foreach ($key_sort as $key => $value){
                $xhtmlItems .= '<tr data-key="'.$value['id'].'">
                                    <th class="text-bold">'.$data_report[$value['id']]['name'].'</th>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['new_phone'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['new_contract'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['new_sales'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['giam-tru-doanh-thu'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['hang-hoan'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['contract_percent'].'%</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['cost_phone'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['cost_sales'].'%</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['cost_contract'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['cost_ads'].'</td>';
                                    if($show_cost_capital){
                                        $xhtmlItems .= '<td class="mask_currency text-right">'.$data_report[$value['id']]['cost_capital'].'</td>
                                                        <td class="mask_currency text-right">'.$data_report[$value['id']]['cod_total'].'</td>
                                                        <td class="mask_currency text-right">'.$data_report[$value['id']]['sales_reality'].'</td>';
                                    }

                $xhtmlItems .=  '</tr>';
            }
            // Hiển thị dòng tổng.
            $xhtmlItems .= '<tr class="text-bold text-red">
                                    <th class="text-bold">'.$data_report['total']['name'].'</th>
                                    <td class="mask_currency text-right">'.$data_report['total']['new_phone'].'</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['new_contract'].'</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['new_sales'].'</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['giam-tru-doanh-thu'].'</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['hang-hoan'].'</td>
                                    <td class="mask_currency text-right" data-field="contract_percent">'.$data_report['total']['contract_percent'].'%</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['cost_phone'].'</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['cost_sales'].'%</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['cost_contract'].'</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['cost_ads'].'</td>';
                                    if($show_cost_capital){
                                        $xhtmlItems .= '<td class="mask_currency text-right">'.$data_report['total']['cost_capital'].'</td>
                                                        <td class="mask_currency text-right">'.$data_report['total']['cod_total'].'</td>
                                                        <td class="mask_currency text-right">'.$data_report['total']['sales_reality'].'</td>';
                                    }
            $xhtmlItems .=  '</tr>';

            $cost_capital = '';
            if($show_cost_capital){
                $cost_capital .= '<th rowspan="2">Giá vốn mặc định </th>
                            	  <th rowspan="2">COD</th>
                            	  <th rowspan="2">Điểm hòa vốn </th>';
            }
            $result['reportTable'] = '<thead data-count_contracts="'.count($contracts).'">
                        				    <tr>
                            					<th class="fix-head" rowspan="2" class="text-center">Nhân viên</th>
                            					<th colspan="5" class="text-center">Doanh số</th>
                            					<th rowspan="2" class="text-center">% Tỉ lệ chốt</th>
                            					<th rowspan="2" class="text-center">% Chi phí QC</br>/ SĐT</th>
                            					<th rowspan="2" class="text-center">% Chi Phí QC</br>/ Doanh Số</th>
                            					<th rowspan="2" class="text-center">Chi Phí QC</br>/ Đơn Hàng</th>
                            					<th rowspan="2" class="text-center">Chi Phí QC</th>
                            					'.$cost_capital.'
                        					</tr>
                        				    <tr>
                            					<th style="min-width: 80px;" class="text-center">Tổng SĐT</th>
                            					<th style="min-width: 80px;" class="text-center">Số Đơn</th>
                            					<th style="min-width: 80px;" class="text-center">Doanh Số</th>
                            					<th style="min-width: 80px;" class="text-center">Giảm trừ doanh thu</th>
                            					<th style="min-width: 80px;" class="text-center">Hàng hoàn</th>
                        					</tr>
                        				</thead>
                        				<tbody>
                        				    '. $xhtmlItems .'
                        				</tbody>';

            echo json_encode($result);

            return $this->response;
        }
        else {
            // Khai báo giá trị ngày tháng
            $default_date_begin     = date('01/m/Y');
            $default_date_end       = date('t/m/Y');

            $ssFilter->report                   = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']     = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $default_date_begin;
            $ssFilter->report['date_end']       = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $default_date_end;
            $ssFilter->report['sale_branch_id'] = $ssFilter->report['sale_branch_id'] ? $ssFilter->report['sale_branch_id'] : $this->_userInfo->getUserInfo('sale_branch_id');
            $ssFilter->report['sale_group_id']  = $ssFilter->report['sale_group_id'] ? $ssFilter->report['sale_group_id'] : $this->_userInfo->getUserInfo('sale_group_id');
            $ssFilter->report['marketer_id']    = $ssFilter->report['marketer_id'] ? $ssFilter->report['marketer_id'] : '';
            $ssFilter->report['product_group_id'] = $ssFilter->report['product_group_id'] ? $ssFilter->report['product_group_id'] : '';

            $this->_params['ssFilter']          = $ssFilter->report;

            // Set giá trị cho form
            $myForm	= new \Report\Form\Marketing\Overview($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);

            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['saleGroup']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['caption']        = 'Báo cáo marketing tổng quan';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Báo cáo marketing 2
    public function overview2Action() {
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));
        $date     = new \ZendX\Functions\Date();
        $number   = new \ZendX\Functions\Number();

        if($this->getRequest()->isPost()) {
            // Lấy giá trị post từ filter
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();

            // Quyền user
            $curent_user = $this->_userInfo->getUserInfo();
            $permission_ids = explode(',', $curent_user['permission_ids']);
            if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids) && !in_array(MANAGER, $permission_ids)){
                if(in_array(GDCN, $permission_ids)){
                    $this->_params['data']['sale_branch_id'] = $curent_user['sale_branch_id'];
                }
                elseif (in_array(GROUP_MKT_LEADER, $permission_ids)){
                    $this->_params['data']['sale_branch_id'] = $curent_user['sale_branch_id'];
                    $this->_params['data']['sale_group_id'] = $curent_user['sale_group_id'];
                }
                else{
                    $this->_params['data']['marketer_id'] = $curent_user['id'];
                }
            }
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin']     = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']       = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_group_id']  = $this->_params['data']['sale_group_id'];
            $ssFilter->report['marketer_id']    = $this->_params['data']['marketer_id'];
            $ssFilter->report['product_cat_id'] = $this->_params['data']['product_cat_id'];

            $marketers = $this->getServiceLocator()->get('Admin\Model\UserTable')->report($this->_params, array('task' => 'list-marketing'));
            // Tạo mảng lưu báo cáo.
            $data_report = [];
            foreach ($marketers as $key => $value) {
                $data_report[$value['id']]['name']           = $value['name'];
                $data_report[$value['id']]['target_phone']   = 0;
                $data_report[$value['id']]['target_sales']   = 0;
                $data_report[$value['id']]['new_phone']      = 0;
                $data_report[$value['id']]['new_contract']   = 0;
                $data_report[$value['id']]['new_sales']      = 0;
                $data_report[$value['id']]['old_contract']   = 0;
                $data_report[$value['id']]['old_sales']      = 0;
                $data_report[$value['id']]['care_contract']  = 0;
                $data_report[$value['id']]['care_sales']     = 0;
                $data_report[$value['id']]['cross_contract'] = 0;
                $data_report[$value['id']]['cross_sales']    = 0;
                $data_report[$value['id']]['cost_ads']       = 0;
                $data_report[$value['id']]['cost_capital']   = 0;
                $data_report[$value['id']]['cod_total']   = 0;
            }
            $data_report['total']['name']           = "Tổng";
            $data_report['total']['target_phone']   = 0;
            $data_report['total']['target_sales']   = 0;
            $data_report['total']['new_phone']      = 0;
            $data_report['total']['new_contract']   = 0;
            $data_report['total']['new_sales']      = 0;
            $data_report['total']['old_contract']   = 0;
            $data_report['total']['old_sales']      = 0;
            $data_report['total']['care_contract']  = 0;
            $data_report['total']['care_sales']     = 0;
            $data_report['total']['cross_contract'] = 0;
            $data_report['total']['cross_sales']    = 0;
            $data_report['total']['cost_ads']       = 0;
            $data_report['total']['cost_capital']   = 0;
            $data_report['total']['cod_total']   = 0;

            // Lấy dữ liệu mục tiêu.
            $where_target = array(
                'filter_type'       => 'mkt_target',
                'filter_date_begin' => $ssFilter->report['date_begin'],
                'filter_date_end'   => $ssFilter->report['date_end'],
            );
            $marketing_target = $this->getServiceLocator()->get('Admin\Model\MarketingReportTable')->report(array('ssFilter' => $where_target), array('task' => 'list-item-type'));
            foreach ($marketing_target as $key => $value){
                if (array_key_exists($value['marketer_id'], $data_report)) {
                    if (!empty($value['params'])) {
                        $params                                             = unserialize($value['params']);
                        $data_report[$value['marketer_id']]['target_phone'] += str_replace(",", "", $params['phone']);
                        $data_report['total']['target_phone'] += str_replace(",", "", $params['phone']);

                        $data_report[$value['marketer_id']]['target_sales'] += str_replace(",", "", $params['sales']);
                        $data_report['total']['target_sales'] += str_replace(",", "", $params['sales']);
                    }
                }
            }
            // Lấy dữ liệu doanh số mới, cũ.
            $where_contract = array(
                'filter_date_begin'     => $ssFilter->report['date_begin'],
                'filter_date_end'       => $ssFilter->report['date_end'],
                'filter_status_type'    => 'production_department_type',
                'filter_status'         => 'success',
            );
            $keys = [
                'sales_new' => [
                    'count' => 'new_contract',
                    'sum' => 'new_sales',
                ],
                'sales_old' => [
                    'count' => 'old_contract',
                    'sum' => 'old_sales',
                ],
                'sales_care' => [
                    'count' => 'care_contract',
                    'sum' => 'care_sales',
                ],
                'sales_cross' => [
                    'count' => 'cross_contract',
                    'sum' => 'cross_sales',
                ],
            ];
            $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report(array('ssFilter' => $where_contract), array('task' => 'join-contact'));
            foreach ($contracts as $key => $value){
                if(!empty($value['marketer_id']) && array_key_exists($value['marketer_id'], $data_report)){
                    $data_report[$value['marketer_id']]['cod_total'] += $value['price_transport'];
                    $data_report['total']['cod_total']               += $value['price_transport'];
                    if($ssFilter->report['product_cat_id']){
                        if (!empty($value['options'])) {
                            $options = unserialize($value['options']);
                            if (count($options['product'])) {
                                foreach ($options['product'] as $k => $v) {
                                    if($v['product_id'] == $ssFilter->report['product_cat_id']) {
                                        foreach($keys as $key=>$key_val) {                                            
                                            if(!empty($v[$key])){
                                                $data_report[$value['marketer_id']][$key_val['count']] += 1;
                                                $data_report['total'][$key_val['count']]               += 1;

                                                $data_report[$value['marketer_id']][$key_val['sum']] += $v[$key];
                                                $data_report['total'][$key_val['sum']]               += $v[$key];
                                            }
                                        }
                                        // if(!empty($v['sales_new'])){
                                        //     $data_report[$value['marketer_id']]['new_contract'] += 1;
                                        //     $data_report['total']['new_contract']               += 1;

                                        //     $data_report[$value['marketer_id']]['new_sales'] += $v['sales_new'];
                                        //     $data_report['total']['new_sales']               += $v['sales_new'];
                                        // }
                                        // if(!empty($v['sales_old'])){
                                        //     $data_report[$value['marketer_id']]['old_contract'] += 1;
                                        //     $data_report['total']['old_contract']               += 1;

                                        //     $data_report[$value['marketer_id']]['old_sales'] += $v['sales_old'];
                                        //     $data_report['total']['old_sales']               += $v['sales_old'];
                                        // }
                                        // if(!empty($v['sales_care'])){
                                        //     $data_report[$value['marketer_id']]['care_contract'] += 1;
                                        //     $data_report['total']['care_contract']               += 1;

                                        //     $data_report[$value['marketer_id']]['care_sales'] += $v['sales_care'];
                                        //     $data_report['total']['care_sales']               += $v['sales_care'];
                                        // }
                                        // if(!empty($v['sales_cross'])){
                                        //     $data_report[$value['marketer_id']]['cross_contract'] += 1;
                                        //     $data_report['total']['cross_contract']               += 1;

                                        //     $data_report[$value['marketer_id']]['cross_sales'] += $v['sales_cross'];
                                        //     $data_report['total']['cross_sales']               += $v['sales_cross'];
                                        // }

//                                        $data_report[$value['marketer_id']]['cost_capital'] += $v['total_production'];
//                                        $data_report['total']['cost_capital'] += $v['total_production'];
                                        $data_report[$value['marketer_id']]['cost_capital'] += $v['capital_default'];
                                        $data_report['total']['cost_capital'] += $v['capital_default'];
                                    }
                                }
                            }
                        }
                    }
                    else {
                        foreach($keys as $key=>$key_val) { 
                            if (!empty($value[$key])) {
                                $data_report[$value['marketer_id']][$key_val['count']] += 1;
                                $data_report['total'][$key_val['count']]               += 1;
    
                                $data_report[$value['marketer_id']][$key_val['sum']] += $value[$key];
                                $data_report['total'][$key_val['sum']]               += $value[$key];
                            }
                        }
                        // if (!empty($value['sales_new'])) {
                        //     $data_report[$value['marketer_id']]['new_contract'] += 1;
                        //     $data_report['total']['new_contract']               += 1;

                        //     $data_report[$value['marketer_id']]['new_sales'] += $value['sales_new'];
                        //     $data_report['total']['new_sales']               += $value['sales_new'];
                        // }
                        // if (!empty($value['sales_old'])) {
                        //     $data_report[$value['marketer_id']]['old_contract'] += 1;
                        //     $data_report['total']['old_contract']               += 1;

                        //     $data_report[$value['marketer_id']]['old_sales'] += $value['sales_old'];
                        //     $data_report['total']['old_sales']               += $value['sales_old'];
                        // }
                        // if (!empty($value['sales_care'])) {
                        //     $data_report[$value['marketer_id']]['care_contract'] += 1;
                        //     $data_report['total']['care_contract']               += 1;

                        //     $data_report[$value['marketer_id']]['care_sales'] += $value['sales_care'];
                        //     $data_report['total']['care_sales']               += $value['sales_care'];
                        // }
                        // if (!empty($value['sales_cross'])) {
                        //     $data_report[$value['marketer_id']]['cross_contract'] += 1;
                        //     $data_report['total']['cross_contract']               += 1;

                        //     $data_report[$value['marketer_id']]['cross_sales'] += $value['sales_cross'];
                        //     $data_report['total']['cross_sales']               += $value['sales_cross'];
                        // }

                        // Tính giá vốn
                        if (!empty($value['options'])) {
                            $options = unserialize($value['options']);
                            if (count($options['product'])) {
                                foreach ($options['product'] as $k => $v) {
//                                    $data_report[$value['marketer_id']]['cost_capital'] += $v['total_production'];
//                                    $data_report['total']['cost_capital'] += $v['total_production'];
                                    $data_report[$value['marketer_id']]['cost_capital'] += $v['capital_default'];
                                    $data_report['total']['cost_capital'] += $v['capital_default'];
                                }
                            }
                        }
                    }
                }
            }
            // Lấy số điện thoại của mkt_x đã được chia.
            $contacts = $this->getServiceLocator()->get('Admin\Model\ContactTable')->report($this->_params, array('task' => 'date'))->toArray();
            foreach ($contacts as $key => $value){
                if(!empty($value['marketer_id'])  && array_key_exists($value['marketer_id'], $data_report)){
                    $data_report[$value['marketer_id']]['new_phone'] += 1;
                    $data_report['total']['new_phone'] += 1;
                }
            }
            // Lấy dữ liệu chi phí quảng cáo.
            $where_report = array(
                'filter_type'       => 'mkt_report_day_hour',
                'filter_date_begin' => $ssFilter->report['date_begin'],
                'filter_date_end'   => $ssFilter->report['date_end'],
            );
            $marketing_report = $this->getServiceLocator()->get('Admin\Model\MarketingReportTable')->report(array('ssFilter' => $where_report), array('task' => 'list-item-type'));
            foreach ($marketing_report as $key => $value){
                if(!empty($value['params'])  && array_key_exists($value['marketer_id'], $data_report)){
                    $params = unserialize($value['params']);
                    $data_report[$value['marketer_id']]['cost_ads'] +=  str_replace(",","",$params['total_cp']);
                    $data_report['total']['cost_ads'] +=  str_replace(",","",$params['total_cp']);
                }
            }

            $show_cost_capital = false;  // Người có quyền hiển thị chi phí giá vốn, doanh thu thực tế
            $show_sales_capital = false; // có được hiển thị doanh số chăm sóc doanh số bán chéo không
            $curent_user = $this->_userInfo->getUserInfo();
            $permission_ids = explode(',', $curent_user['permission_ids']);
            if(in_array(SYSTEM, $permission_ids) || in_array(ADMIN, $permission_ids) || in_array(GDCN, $permission_ids)){
                $show_cost_capital = true;
            }
            if(in_array(SYSTEM, $permission_ids) || in_array(ADMIN, $permission_ids) || in_array(GDCN, $permission_ids)){
                $show_sales_capital = true;
            }

            // Tham số bảng báo cáo
            $xhtmlItems = '';
            foreach ($data_report as $key => $value){
                $total_sales = ($value['old_sales'] + $value['new_sales'] + $value['care_sales'] +$value['cross_sales']);
                $target_percent   = ($value['target_sales'] > 0 ? round($total_sales / $value['target_sales'] * 100, 2) : 0);
                $contract_percent = ($value['new_phone'] > 0 ? round(($value['new_contract'] + $value['old_contract']) / $value['new_phone'] * 100, 2) : 0);
                $cost_phone       = ($value['new_phone'] > 0 ? ($value['cost_ads'] / $value['new_phone']) : 0);
                $cost_sales       = ($total_sales > 0 ? round($value['cost_ads'] / $total_sales * 100, 2) : 0);
                $cost_contract    = (($value['new_contract'] + $value['old_contract']) > 0 ? ($value['cost_ads'] / ($value['new_contract'] + $value['old_contract'])) : 0);
                $sales_reality    = ($total_sales - $value['cost_ads'] - $value['cost_capital'] - $value['cod_total']);

                $data_report[$key]['total_sales']      = $total_sales;
                $data_report[$key]['target_percent']   = $target_percent;
                $data_report[$key]['contract_percent'] = $contract_percent;
                $data_report[$key]['cost_phone']       = $cost_phone;
                $data_report[$key]['cost_sales']       = $cost_sales;
                $data_report[$key]['cost_contract']    = $cost_contract;
                $data_report[$key]['sales_reality']    = $sales_reality;
            }

            // sắp xếp theo doanh tổng doanh thu
            $key_sort = [];
            $i = 0;
            foreach ($data_report as $key => $value) {
                if($key != 'total'){
                    $key_sort[$i]['id'] = $key;
                    $key_sort[$i]['total_sales'] = $data_report[$key]['total_sales'];
                    $i++;
                }
            }
            for($i = 0; $i < count($key_sort) - 1; $i++){
                for($j = $i+1; $j < count($key_sort); $j++){
                    if($key_sort[$i]['total_sales'] < $key_sort[$j]['total_sales']){
                        $tm              = $key_sort[$i];
                        $key_sort[$i]    = $key_sort[$j];
                        $key_sort[$j]    = $tm;
                    }
                }
            }

            $xhtmlItems = '';
            foreach ($key_sort as $key => $value){
                $xhtmlItems .= '<tr data-key="'.$value['id'].'">
        		                <th class="text-bold">'.$data_report[$value['id']]['name'].'</th>
        						<td class="mask_currency text-center">'.$data_report[$value['id']]['target_phone'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['target_sales'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['target_percent'].'%</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['new_phone'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['new_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['new_sales'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['old_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['old_sales'].'</td>';

        						if($show_sales_capital){
                                    $xhtmlItems .= '<td class="mask_currency text-right">'.$data_report[$value['id']]['care_sales'].'</td>
        						                    <td class="mask_currency text-right">'.$data_report[$value['id']]['cross_sales'].'</td>';
        						}

                $xhtmlItems .= '<td class="mask_currency text-right">'.$data_report[$value['id']]['total_sales'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['contract_percent'].'%</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['cost_phone'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['cost_sales'].'%</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['cost_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['cost_ads'].'</td>';

        						if($show_cost_capital){
                                    $xhtmlItems .= '<td class="mask_currency text-right">'.$data_report[$value['id']]['cost_capital'].'</td>
        						                    <td class="mask_currency text-right">'.$data_report[$value['id']]['cod_total'].'</td>
        						                    <td class="mask_currency text-right">'.$data_report[$value['id']]['sales_reality'].'</td>';
                                }

                $xhtmlItems .=  '</tr>';
            }
            // Hiển thị dòng tổng
            $xhtmlItems .= '<tr class="text-bold text-red">
        		                <th class="text-bold">'.$data_report['total']['name'].'</th>
        						<td class="mask_currency text-center">'.$data_report['total']['target_phone'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['target_sales'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['target_percent'].'%</td>
        						<td class="mask_currency text-right">'.$data_report['total']['new_phone'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['new_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['new_sales'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['old_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['old_sales'].'</td>';

                                if($show_sales_capital){
                                    $xhtmlItems .= '<td class="mask_currency text-right">'.$data_report['total']['care_sales'].'</td>
                                                    <td class="mask_currency text-right">'.$data_report['total']['cross_sales'].'</td>';
                                }

            $xhtmlItems .= '<td class="mask_currency text-right">'.$data_report['total']['total_sales'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['contract_percent'].'%</td>
        						<td class="mask_currency text-right">'.$data_report['total']['cost_phone'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['cost_sales'].'%</td>
        						<td class="mask_currency text-right">'.$data_report['total']['cost_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['cost_ads'].'</td>';

                            if($show_cost_capital){
                                $xhtmlItems .= '<td class="mask_currency text-right">'.$data_report['total']['cost_capital'].'</td>
                                                <td class="mask_currency text-right">'.$data_report['total']['cod_total'].'</td>
                                                <td class="mask_currency text-right">'.$data_report['total']['sales_reality'].'</td>';
                            }

            $xhtmlItems .=  '</tr>';


            $cost_capital = '';
            $sale_capital = '';
            if($show_cost_capital){
                $cost_capital .= '<th rowspan="2">Giá vốn mặc định </th>
                            	  <th rowspan="2">COD </th>
                            	  <th rowspan="2">Điểm hòa vốn </th>';
            }
            if($show_sales_capital){
                $sale_capital .= '<th rowspan="2" class="text-center">Doanh số<br>chăm sóc</th>
                            	  <th rowspan="2" class="text-center">Doanh số<br>bán chéo</th>';
            }
            $result['reportTable'] = '<thead>
                        				    <tr>
                            					<th rowspan="2" class="text-center fix-head">Nhân viên</th>
                            					<th colspan="3" class="text-center">Mục tiêu tổng</th>
                            					<th colspan="3" class="text-center">Doanh số mới</th>
                            					<th colspan="2" class="text-center">Doanh số cũ</th>
                            					'.$sale_capital.'
                            					<th rowspan="2" class="text-center">Tổng doanh số</th>
                            					<th rowspan="2" class="text-center">% Tỉ lệ chốt</th>
                            					<th rowspan="2" class="text-center">% Chi phí QC</br>/ SĐT</th>
                            					<th rowspan="2" class="text-center">% Chi Phí QC</br>/ Doanh Số</th>
                            					<th rowspan="2" class="text-center">Chi Phí QC</br>/ Đơn Hàng</th>
                            					<th rowspan="2" class="text-center">Chi Phí QC</th>
                            					'.$cost_capital.'
                        					</tr>
                        				    <tr>
                            					<th class="text-center">SĐT</th>
                            					<th class="text-center">Doanh Số</th>
                            					<th class="text-center">% Mục Tiêu</th>
                            					<th class="text-center">Tổng SĐT</th>
                            					<th class="text-center">Số Đơn</th>
                            					<th class="text-center">Doanh Số</th>
                            					<th class="text-center">Số Đơn</th>
                            					<th class="text-center">Doanh Số</th>
                        					</tr>
                        				</thead>
                        				<tbody>
                        				    '. $xhtmlItems .'
                        				</tbody>';

            echo json_encode($result);

            return $this->response;
        }
        else {
            // Khai báo giá trị ngày tháng
            $default_date_begin     = date('01/m/Y');
            $default_date_end       = date('t/m/Y');

            $ssFilter->report                   = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']     = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $default_date_begin;
            $ssFilter->report['date_end']       = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $default_date_end;
            $ssFilter->report['sale_branch_id'] = $ssFilter->report['sale_branch_id'] ? $ssFilter->report['sale_branch_id'] : $this->_userInfo->getUserInfo('sale_branch_id');
            $ssFilter->report['sale_group_id']  = $ssFilter->report['sale_group_id'] ? $ssFilter->report['sale_group_id'] : $this->_userInfo->getUserInfo('sale_group_id');
            $ssFilter->report['marketer_id']    = $ssFilter->report['marketer_id'] ? $ssFilter->report['marketer_id'] : '';
            $ssFilter->report['product_cat_id'] = $ssFilter->report['product_cat_id'] ? $ssFilter->report['product_cat_id'] : '';

            $this->_params['ssFilter']          = $ssFilter->report;

            // Set giá trị cho form
            $myForm	= new \Report\Form\Marketing\Overview($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);

            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['saleGroup']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['caption']        = 'Báo cáo marketing tổng quan';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function overview22Action() {
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));
        $date     = new \ZendX\Functions\Date();
        $number   = new \ZendX\Functions\Number();

        if($this->getRequest()->isPost()) {
            // Lấy giá trị post từ filter
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();

            // Quyền user
            $curent_user = $this->_userInfo->getUserInfo();
            $permission_ids = explode(',', $curent_user['permission_ids']);
            if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids) && !in_array(MANAGER, $permission_ids)){
                if(in_array(GDCN, $permission_ids)){
                    $this->_params['data']['sale_branch_id'] = $curent_user['sale_branch_id'];
                }
                elseif (in_array(GROUP_MKT_LEADER, $permission_ids)){
                    $this->_params['data']['sale_branch_id'] = $curent_user['sale_branch_id'];
                    $this->_params['data']['sale_group_id'] = $curent_user['sale_group_id'];
                }
                else{
                    $this->_params['data']['marketer_id'] = $curent_user['id'];
                }
            }
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin']     = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']       = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_group_id']  = $this->_params['data']['sale_group_id'];
            $ssFilter->report['marketer_id']    = $this->_params['data']['marketer_id'];
            $ssFilter->report['product_cat_id'] = $this->_params['data']['product_cat_id'];

            $marketers = $this->getServiceLocator()->get('Admin\Model\UserTable')->report($this->_params, array('task' => 'list-marketing'));
            // Tạo mảng lưu báo cáo.
            $data_report = [];
            foreach ($marketers as $key => $value) {
                $data_report[$value['id']]['name']           = $value['name'];
                $data_report[$value['id']]['target_phone']   = 0;
                $data_report[$value['id']]['target_sales']   = 0;
                $data_report[$value['id']]['new_phone']      = 0;
                $data_report[$value['id']]['new_contract']   = 0;
                $data_report[$value['id']]['new_sales']      = 0;
                $data_report[$value['id']]['old_contract']   = 0;
                $data_report[$value['id']]['old_sales']      = 0;
                $data_report[$value['id']]['care_contract']  = 0;
                $data_report[$value['id']]['care_sales']     = 0;
                $data_report[$value['id']]['cross_contract'] = 0;
                $data_report[$value['id']]['cross_sales']    = 0;
                $data_report[$value['id']]['cost_ads']       = 0;
                $data_report[$value['id']]['cost_capital']   = 0;
                $data_report[$value['id']]['cod_total']   = 0;
            }
            $data_report['total']['name']           = "Tổng";
            $data_report['total']['target_phone']   = 0;
            $data_report['total']['target_sales']   = 0;
            $data_report['total']['new_phone']      = 0;
            $data_report['total']['new_contract']   = 0;
            $data_report['total']['new_sales']      = 0;
            $data_report['total']['old_contract']   = 0;
            $data_report['total']['old_sales']      = 0;
            $data_report['total']['care_contract']  = 0;
            $data_report['total']['care_sales']     = 0;
            $data_report['total']['cross_contract'] = 0;
            $data_report['total']['cross_sales']    = 0;
            $data_report['total']['cost_ads']       = 0;
            $data_report['total']['cost_capital']   = 0;
            $data_report['total']['cod_total']   = 0;

            // Lấy dữ liệu mục tiêu.
            $where_target = array(
                'filter_type'       => 'mkt_target',
                'filter_date_begin' => $ssFilter->report['date_begin'],
                'filter_date_end'   => $ssFilter->report['date_end'],
            );
            $marketing_target = $this->getServiceLocator()->get('Admin\Model\MarketingReportTable')->report(array('ssFilter' => $where_target), array('task' => 'list-item-type'));
            foreach ($marketing_target as $key => $value){
                if (array_key_exists($value['marketer_id'], $data_report)) {
                    if (!empty($value['params'])) {
                        $params                                             = unserialize($value['params']);
                        $data_report[$value['marketer_id']]['target_phone'] += str_replace(",", "", $params['phone']);
                        $data_report['total']['target_phone'] += str_replace(",", "", $params['phone']);

                        $data_report[$value['marketer_id']]['target_sales'] += str_replace(",", "", $params['sales']);
                        $data_report['total']['target_sales'] += str_replace(",", "", $params['sales']);
                    }
                }
            }
            // Lấy dữ liệu doanh số mới, cũ.
            $where_contract = array(
                'filter_date_begin'     => $ssFilter->report['date_begin'],
                'filter_date_end'       => $ssFilter->report['date_end'],
                'filter_status_type'    => 'production_department_type',
                'filter_status'         => 'success',
            );
            $keys = [
                'mkt_sales_new' => [
                    'count' => 'new_contract',
                    'sum' => 'new_sales',
                ],
//                'sales_old' => [
//                    'count' => 'old_contract',
//                    'sum' => 'old_sales',
//                ],
                'mkt_sales_care' => [
                    'count' => 'care_contract',
                    'sum' => 'care_sales',
                ],
//                'sales_cross' => [
//                    'count' => 'cross_contract',
//                    'sum' => 'cross_sales',
//                ],
            ];
            $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report(array('ssFilter' => $where_contract), array('task' => 'join-contact-producted'));
            foreach ($contracts as $key => $value){
                if(!empty($value['marketer_id']) && array_key_exists($value['marketer_id'], $data_report)){
                    $data_report[$value['marketer_id']]['cod_total'] += $value['price_transport'];
                    $data_report['total']['cod_total']               += $value['price_transport'];
                    foreach($keys as $key=>$key_val) {
                        if (!empty($value[$key])) {
                            $data_report[$value['marketer_id']][$key_val['count']] += 1;
                            $data_report['total'][$key_val['count']]               += 1;

                            $data_report[$value['marketer_id']][$key_val['sum']] += $value[$key];
                            $data_report['total'][$key_val['sum']]               += $value[$key];
                        }
                    }

                    // Tính giá vốn
                    if (!empty($value['options'])) {
                        $options = unserialize($value['options']);
                        if (count($options['product'])) {
                            foreach ($options['product'] as $k => $v) {
                                $data_report[$value['marketer_id']]['cost_capital'] += $v['capital_default'];
                                $data_report['total']['cost_capital'] += $v['capital_default'];
                            }
                        }
                    }
                }
            }


            // Lấy số điện thoại của mkt_x đã được chia.
            $contacts = $this->getServiceLocator()->get('Admin\Model\ContactTable')->report($this->_params, array('task' => 'date'))->toArray();
            foreach ($contacts as $key => $value){
                if(!empty($value['marketer_id'])  && array_key_exists($value['marketer_id'], $data_report)){
                    $data_report[$value['marketer_id']]['new_phone'] += 1;
                    $data_report['total']['new_phone'] += 1;
                }
            }
            // Lấy dữ liệu chi phí quảng cáo.
            $where_report = array(
                'filter_type'       => 'mkt_report_day_hour',
                'filter_date_begin' => $ssFilter->report['date_begin'],
                'filter_date_end'   => $ssFilter->report['date_end'],
            );
            $marketing_report = $this->getServiceLocator()->get('Admin\Model\MarketingReportTable')->report(array('ssFilter' => $where_report), array('task' => 'list-item-type'));
            foreach ($marketing_report as $key => $value){
                if(!empty($value['params'])  && array_key_exists($value['marketer_id'], $data_report)){
                    $params = unserialize($value['params']);
                    $data_report[$value['marketer_id']]['cost_ads'] +=  str_replace(",","",$params['total_cp']);
                    $data_report['total']['cost_ads'] +=  str_replace(",","",$params['total_cp']);
                }
            }

            $show_cost_capital = false;  // Người có quyền hiển thị chi phí giá vốn, doanh thu thực tế
            $show_sales_capital = false; // có được hiển thị doanh số chăm sóc doanh số bán chéo không
            $curent_user = $this->_userInfo->getUserInfo();
            $permission_ids = explode(',', $curent_user['permission_ids']);
            if(in_array(SYSTEM, $permission_ids) || in_array(ADMIN, $permission_ids) || in_array(GDCN, $permission_ids)){
                $show_cost_capital = true;
            }
            if(in_array(SYSTEM, $permission_ids) || in_array(ADMIN, $permission_ids) || in_array(GDCN, $permission_ids)){
                $show_sales_capital = true;
            }

            // Tham số bảng báo cáo
            $xhtmlItems = '';
            foreach ($data_report as $key => $value){
                $total_sales = ($value['old_sales'] + $value['new_sales'] + $value['care_sales'] +$value['cross_sales']);
                $target_percent   = ($value['target_sales'] > 0 ? round($total_sales / $value['target_sales'] * 100, 2) : 0);
                $contract_percent = ($value['new_phone'] > 0 ? round(($value['new_contract'] + $value['old_contract']) / $value['new_phone'] * 100, 2) : 0);
                $cost_phone       = ($value['new_phone'] > 0 ? ($value['cost_ads'] / $value['new_phone']) : 0);
                $cost_sales       = ($total_sales > 0 ? round($value['cost_ads'] / $total_sales * 100, 2) : 0);
                $cost_contract    = (($value['new_contract'] + $value['old_contract']) > 0 ? ($value['cost_ads'] / ($value['new_contract'] + $value['old_contract'])) : 0);
                $sales_reality    = ($total_sales - $value['cost_ads'] - $value['cost_capital'] - $value['cod_total']);

                $data_report[$key]['total_sales']      = $total_sales;
                $data_report[$key]['target_percent']   = $target_percent;
                $data_report[$key]['contract_percent'] = $contract_percent;
                $data_report[$key]['cost_phone']       = $cost_phone;
                $data_report[$key]['cost_sales']       = $cost_sales;
                $data_report[$key]['cost_contract']    = $cost_contract;
                $data_report[$key]['sales_reality']    = $sales_reality;
            }

            // sắp xếp theo doanh tổng doanh thu
            $key_sort = [];
            $i = 0;
            foreach ($data_report as $key => $value) {
                if($key != 'total'){
                    $key_sort[$i]['id'] = $key;
                    $key_sort[$i]['total_sales'] = $data_report[$key]['total_sales'];
                    $i++;
                }
            }
            for($i = 0; $i < count($key_sort) - 1; $i++){
                for($j = $i+1; $j < count($key_sort); $j++){
                    if($key_sort[$i]['total_sales'] < $key_sort[$j]['total_sales']){
                        $tm              = $key_sort[$i];
                        $key_sort[$i]    = $key_sort[$j];
                        $key_sort[$j]    = $tm;
                    }
                }
            }

            $xhtmlItems = '';
            foreach ($key_sort as $key => $value){
                $xhtmlItems .= '<tr data-key="'.$value['id'].'">
        		                <th class="text-bold">'.$data_report[$value['id']]['name'].'</th>
        						<td class="mask_currency text-center">'.$data_report[$value['id']]['target_phone'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['target_sales'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['target_percent'].'%</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['new_phone'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['new_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['new_sales'].'</td>';

        						if($show_sales_capital){
                                    $xhtmlItems .= '<td class="mask_currency text-right">'.$data_report[$value['id']]['care_contract'].'</td>
                                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['care_sales'].'</td>';
        						}

                $xhtmlItems .= '<td class="mask_currency text-right">'.$data_report[$value['id']]['total_sales'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['contract_percent'].'%</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['cost_phone'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['cost_sales'].'%</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['cost_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['cost_ads'].'</td>';

        						if($show_cost_capital){
                                    $xhtmlItems .= '<td class="mask_currency text-right">'.$data_report[$value['id']]['cost_capital'].'</td>
        						                    <td class="mask_currency text-right">'.$data_report[$value['id']]['cod_total'].'</td>
        						                    <td class="mask_currency text-right">'.$data_report[$value['id']]['sales_reality'].'</td>';
                                }

                $xhtmlItems .=  '</tr>';
            }
            // Hiển thị dòng tổng
            $xhtmlItems .= '<tr class="text-bold text-red">
        		                <th class="text-bold">'.$data_report['total']['name'].'</th>
        						<td class="mask_currency text-center">'.$data_report['total']['target_phone'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['target_sales'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['target_percent'].'%</td>
        						<td class="mask_currency text-right">'.$data_report['total']['new_phone'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['new_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['new_sales'].'</td>';

                                if($show_sales_capital){
                                    $xhtmlItems .= '<td class="mask_currency text-right">'.$data_report['total']['care_contract'].'</td>
                                                    <td class="mask_currency text-right">'.$data_report['total']['care_sales'].'</td>';
                                }

            $xhtmlItems .= '<td class="mask_currency text-right">'.$data_report['total']['total_sales'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['contract_percent'].'%</td>
        						<td class="mask_currency text-right">'.$data_report['total']['cost_phone'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['cost_sales'].'%</td>
        						<td class="mask_currency text-right">'.$data_report['total']['cost_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['cost_ads'].'</td>';

                            if($show_cost_capital){
                                $xhtmlItems .= '<td class="mask_currency text-right">'.$data_report['total']['cost_capital'].'</td>
                                                <td class="mask_currency text-right">'.$data_report['total']['cod_total'].'</td>
                                                <td class="mask_currency text-right">'.$data_report['total']['sales_reality'].'</td>';
                            }

            $xhtmlItems .=  '</tr>';


            $cost_capital = '';
            $sale_capital = '';
            if($show_cost_capital){
                $cost_capital .= '<th rowspan="2">Giá vốn mặc định </th>
                            	  <th rowspan="2">COD </th>
                            	  <th rowspan="2">Điểm hòa vốn </th>';
            }
            if($show_sales_capital){
                $sale_capital .= '<th colspan="2" class="text-center">Doanh số<br>chăm sóc</th>';
            }
            $result['reportTable'] = '<thead>
                        				    <tr>
                            					<th rowspan="2" class="text-center fix-head">Nhân viên</th>
                            					<th colspan="3" class="text-center">Mục tiêu tổng</th>
                            					<th colspan="3" class="text-center">Doanh số mới</th>
                            					'.$sale_capital.'
                            					<th rowspan="2" class="text-center">Tổng doanh số</th>
                            					<th rowspan="2" class="text-center">% Tỉ lệ chốt</th>
                            					<th rowspan="2" class="text-center">% Chi phí QC</br>/ SĐT</th>
                            					<th rowspan="2" class="text-center">% Chi Phí QC</br>/ Doanh Số</th>
                            					<th rowspan="2" class="text-center">Chi Phí QC</br>/ Đơn Hàng</th>
                            					<th rowspan="2" class="text-center">Chi Phí QC</th>
                            					'.$cost_capital.'
                        					</tr>
                        				    <tr>
                            					<th class="text-center">SĐT</th>
                            					<th class="text-center">Doanh Số</th>
                            					<th class="text-center">% Mục Tiêu</th>
                            					<th class="text-center">Tổng SĐT</th>
                            					<th class="text-center">Số Đơn</th>
                            					<th class="text-center">Doanh Số</th>
                            					<th class="text-center">Số Đơn</th>
                            					<th class="text-center">Doanh Số</th>
                        					</tr>
                        				</thead>
                        				<tbody>
                        				    '. $xhtmlItems .'
                        				</tbody>';

            echo json_encode($result);

            return $this->response;
        }
        else {
            // Khai báo giá trị ngày tháng
            $default_date_begin     = date('01/m/Y');
            $default_date_end       = date('t/m/Y');

            $ssFilter->report                   = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']     = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $default_date_begin;
            $ssFilter->report['date_end']       = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $default_date_end;
            $ssFilter->report['sale_branch_id'] = $ssFilter->report['sale_branch_id'] ? $ssFilter->report['sale_branch_id'] : $this->_userInfo->getUserInfo('sale_branch_id');
            $ssFilter->report['sale_group_id']  = $ssFilter->report['sale_group_id'] ? $ssFilter->report['sale_group_id'] : $this->_userInfo->getUserInfo('sale_group_id');
            $ssFilter->report['marketer_id']    = $ssFilter->report['marketer_id'] ? $ssFilter->report['marketer_id'] : '';
            $ssFilter->report['product_cat_id'] = $ssFilter->report['product_cat_id'] ? $ssFilter->report['product_cat_id'] : '';

            $this->_params['ssFilter']          = $ssFilter->report;

            // Set giá trị cho form
            $myForm	= new \Report\Form\Marketing\Overview($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);

            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['saleGroup']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['caption']        = 'Báo cáo marketing tổng quan';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Báo cáo kênh nguồn mkt
    public function sourcesAction() {
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));
        $date     = new \ZendX\Functions\Date();
        $number   = new \ZendX\Functions\Number();

        if($this->getRequest()->isPost()) {
            // Lấy giá trị post từ filter
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();

            // Quyền user
            $curent_user = $this->_userInfo->getUserInfo();
            $permission_ids = explode(',', $curent_user['permission_ids']);
            if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids) && !in_array(MANAGER, $permission_ids)){
                if(in_array(GDCN, $permission_ids)){
                    $this->_params['data']['sale_branch_id'] = $curent_user['sale_branch_id'];
                }
                elseif (in_array(GROUP_MKT_LEADER, $permission_ids)){
                    $this->_params['data']['sale_branch_id'] = $curent_user['sale_branch_id'];
                    $this->_params['data']['sale_group_id'] = $curent_user['sale_group_id'];
                }
                else{
                    $this->_params['data']['marketer_id'] = $curent_user['id'];
                }
            }

            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin']     = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']       = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_group_id'] = $this->_params['data']['sale_group_id'];
            $ssFilter->report['marketer_id']    = $this->_params['data']['marketer_id'];

            $this->_params['ssFilter'] = $ssFilter->report;

            // Xác định ngày tháng tìm kiếm
            $date       = new \ZendX\Functions\Date();
            $day_begin  = strtotime($date->formatToData($ssFilter->report['date_begin']));
            $day_end    = strtotime($date->formatToData($ssFilter->report['date_end']));
            $number_day = abs($day_end - $day_begin) / 86400;

            $sources = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'marketing-channel')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name'));

            // Tạo mảng lưu báo cáo.
            $data_report = [];
            foreach ($sources as $key => $value) {
                $data_report[$key]['name']   = $value;
                $data_report[$key]['number'] = 0;
            }

            // Lấy danh sách data
            $data_contact = $this->getServiceLocator()->get('Admin\Model\FormDataTable')->report($this->_params, array('task' => 'list-item'));
            $total_data = 0;
            foreach ($data_contact as $key => $value){
                $total_data += 1;
                $data_report[$value['marketing_channel_id']]['number'] += 1;
            }

            // Tham số bảng báo cáo
            $xhtmlItems = '';
            $data_chart = [];

            $index = 0;
            foreach ($data_report as $key => $value){
                $xhtmlItems .= '<td class="mask_currency text-center">'.$value['number'].'</td>';

                $data_chart[$index]['name']   = $value['name'];
                $data_chart[$index]['y']      = $total_data > 0 ? round($value['number'] / $total_data * 100, 2) : 0;
                $index++;
            }unset($index);

            $thHtml = '';
            foreach ($data_report as $key => $value){
                $thHtml .= '<th class="text-center">'.$value['name'].'</th>';
            }

            $result['reportTable'] =    '<thead>
                                            <tr>
                                                '. $thHtml .'
                                            </tr>
                        				</thead>
                        				<tbody>
                        				    <tr>
                        				        '. $xhtmlItems .'
                        				    </tr>
                        				</tbody>';

            $result['reportChart'] = array(
                'series' => array(
                    0 => array(
                        'data' => $data_chart,
                        'name' => 'Chiếm',
                    ),
                )
            );

            echo json_encode($result);
            return $this->response;
        } else {
            // Khai báo giá trị ngày tháng
            $default_date_begin     = date('01/m/Y');
            $default_date_end       = date('t/m/Y');

            $ssFilter->report                   = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']     = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $default_date_begin;
            $ssFilter->report['date_end']       = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $default_date_end;
            $ssFilter->report['sale_branch_id'] = $ssFilter->report['sale_branch_id'] ? $ssFilter->report['sale_branch_id'] : $this->_userInfo->getUserInfo('sale_branch_id');
            $ssFilter->report['sale_group_id']  = $ssFilter->report['sale_group_id'] ? $ssFilter->report['sale_group_id'] : $this->_userInfo->getUserInfo('sale_group_id');
            $ssFilter->report['marketer_id']    = $ssFilter->report['marketer_id'] ? $ssFilter->report['marketer_id'] : '';

            $this->_params['ssFilter']          = $ssFilter->report;

            // Set giá trị cho form
            $myForm	= new \Report\Form\Marketing\Overview($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);

            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['saleGroup']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['caption']        = 'Báo cáo data marketing theo kênh - nguồn';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }
}




















