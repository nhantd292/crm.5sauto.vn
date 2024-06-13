<?php

namespace Report\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class CheckController extends ActionController {
    
    public function init() {
        $this->setLayout('report');
        
        // Lấy dữ liệu post của form
        $this->_params['data'] = $this->getRequest()->getPost()->toArray();
        
        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__);
        $this->_params['ssFilter']['filter_date_begin']     = $ssFilter->filter_date_begin;
        $this->_params['ssFilter']['filter_date_end']       = $ssFilter->filter_date_end;
        $this->_params['ssFilter']['product_cat_id']        = $ssFilter->product_cat_id;

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

    // Báo cáo giục đơn HN
    public function internalAction() {
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
            }

            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin']     = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']       = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['shipper_id']     = $this->_params['data']['shipper_id'];
            $ssFilter->report['contract_type_bh']     = $this->_params['data']['contract_type_bh'];

            $this->_params['ssFilter'] = $ssFilter->report;

            // Xác định ngày tháng tìm kiếm
            $date           = new \ZendX\Functions\Date();
            $day_begin  = strtotime($date->formatToData($ssFilter->report['date_begin']));
            $day_end    = strtotime($date->formatToData($ssFilter->report['date_end']));
            $number_day = abs($day_end - $day_begin) / 86400;

            if(empty($this->_params['data']['shipper_id'])) {
                $shipers = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-positons-care')), array('key' => 'id', 'value' => 'name'));
            }
            else{
                $shipers = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(array('data' => array('ids' => $this->_params['data']['shipper_id'])), array('task' => 'list-positons-care')), array('key' => 'id', 'value' => 'name'));
            }

            $product_type = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-type')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'id'));
            $id_don_ha_noi  = $product_type[DON_HA_NOI];

            // Tạo mảng lưu báo cáo.
            $data_report = [];
            $data_report['total'] = null;
            $data_report['total_all'] = null;
            for ($i = 0; $i <= $number_day; $i++) {
                $day = date('Y-m-d', $day_begin + $i*86400);
                $data_report[$day] = null;
            }

            // Lấy dữ liệu doanh số.
            // Chỉ lấy danh sách đơn hà nội
            $where_contract = array(
                'filter_date_begin'         => $ssFilter->report['date_begin'],
                'filter_date_end'           => $ssFilter->report['date_end'],
                'sale_branch_id'            => $ssFilter->report['sale_branch_id'],
                'production_type_id'        => $id_don_ha_noi,
                'contract_type_bh'          => $ssFilter->report['contract_type_bh'],
            );
            $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report(array('ssFilter' => $where_contract), array('task' => 'join-contact'));

            foreach ($contracts as $key => $value){
                $day       = substr($value['date'], 0, 10);
                $shiper_id = $value['shipper_id'];
                if(array_key_exists($shiper_id, $shipers)) {
                    // Hàng gửi
                    if($value['production_department_type'] == STATUS_CONTRACT_PRODUCT_POST){
                        $data_report[$day][$shiper_id]['send_contract'] += 1;
                        $data_report['total'][$shiper_id]['send_contract'] += 1;
                        $data_report['total_all']['total']['send_contract'] += 1;

                        $data_report[$day][$shiper_id]['send_sales'] += $value['price_total'];
                        $data_report['total'][$shiper_id]['send_sales'] += $value['price_total'];
                        $data_report['total_all']['total']['send_sales'] += $value['price_total'];
                    }
                    // Giữ lại bưu điện
                    if($value['status_check_id'] == STATUS_CONTRACT_CHECK_KEEP){
                        $data_report[$day][$shiper_id]['keep_contract'] += 1;
                        $data_report['total'][$shiper_id]['keep_contract'] += 1;
                        $data_report['total_all']['total']['keep_contract'] += 1;

                        $data_report[$day][$shiper_id]['keep_sales'] += $value['price_total'];
                        $data_report['total'][$shiper_id]['keep_sales'] += $value['price_total'];
                        $data_report['total_all']['total']['keep_sales'] += $value['price_total'];
                    }
                    // Đang vận chuyển
                    if($value['status_check_id'] == STATUS_CONTRACT_CHECK_SENDING){
                        $data_report[$day][$shiper_id]['sending_contract'] += 1;
                        $data_report['total'][$shiper_id]['sending_contract'] += 1;
                        $data_report['total_all']['total']['sending_contract'] += 1;

                        $data_report[$day][$shiper_id]['sending_sales'] += $value['price_total'];
                        $data_report['total'][$shiper_id]['sending_sales'] += $value['price_total'];
                        $data_report['total_all']['total']['sending_sales'] += $value['price_total'];
                    }
                    // Đang phát
                    if($value['status_check_id'] == STATUS_CONTRACT_CHECK_POST){
                        $data_report[$day][$shiper_id]['post_contract'] += 1;
                        $data_report['total'][$shiper_id]['post_contract'] += 1;
                        $data_report['total_all']['total']['post_contract'] += 1;

                        $data_report[$day][$shiper_id]['post_sales'] += $value['price_total'];
                        $data_report['total'][$shiper_id]['post_sales'] += $value['price_total'];
                        $data_report['total_all']['total']['post_sales'] += $value['price_total'];
                    }
                    // Giảm trừ doanh thu
                    if(!empty($value['price_reduce_sale'])){
                        $data_report[$day][$shiper_id]['reduce_contract'] += 1;
                        $data_report['total'][$shiper_id]['reduce_contract'] += 1;
                        $data_report['total_all']['total']['reduce_contract'] += 1;

                        $data_report[$day][$shiper_id]['reduce_sales'] += $value['price_reduce_sale'];
                        $data_report['total'][$shiper_id]['reduce_sales'] += $value['price_reduce_sale'];
                        $data_report['total_all']['total']['reduce_sales'] += $value['price_reduce_sale'];
                    }
                    // Hàng hoàn
                    if($value['status_check_id'] == STATUS_CONTRACT_CHECK_RETURN){
                        $data_report[$day][$shiper_id]['return_contract'] += 1;
                        $data_report['total'][$shiper_id]['return_contract'] += 1;
                        $data_report['total_all']['total']['return_contract'] += 1;

                        $data_report[$day][$shiper_id]['return_sales'] += $value['price_total'];
                        $data_report['total'][$shiper_id]['return_sales'] += $value['price_total'];
                        $data_report['total_all']['total']['return_sales'] += $value['price_total'];
                    }
                    // Thành công
                    if($value['status_check_id'] == STATUS_CONTRACT_CHECK_SUCCESS){
                        $data_report[$day][$shiper_id]['success_contract'] += 1;
                        $data_report['total'][$shiper_id]['success_contract'] += 1;
                        $data_report['total_all']['total']['success_contract'] += 1;

                        $data_report[$day][$shiper_id]['success_sales'] += $value['price_total'];
                        $data_report['total'][$shiper_id]['success_sales'] += $value['price_total'];
                        $data_report['total_all']['total']['success_sales'] += $value['price_total'];
                    }
                    // Đã thu tiền
                    if($value['status_acounting_id'] == STATUS_CONTRACT_ACOUNTING_MONEY){
                        $data_report[$day][$shiper_id]['money_contract'] += 1;
                        $data_report['total'][$shiper_id]['money_contract'] += 1;
                        $data_report['total_all']['total']['money_contract'] += 1;

                        $data_report[$day][$shiper_id]['money_sales'] += $value['price_total'];
                        $data_report['total'][$shiper_id]['money_sales'] += $value['price_total'];
                        $data_report['total_all']['total']['money_sales'] += $value['price_total'];
                    }
                }
            }
            // Tham số bảng báo cáo
            $xhtmlItems = '';
            foreach ($data_report as $keys => $values){
                $rows_span = count($values) > 1 ? count($values) : '';

                $name_filter = $keys;
                if($keys == 'total'){
                    $name_filter = 'Tổng - nhân viên';
                }
                elseif ($keys == 'total_all'){
                    $name_filter = 'Tổng - tất cả';
                }

                $style_class = '';
                if($keys == 'total'){
                    $style_class = 'text-green';
                }
                elseif ($keys == 'total_all'){
                    $style_class = 'text-bold text-red';
                }

                if(!empty($values)){
                    $index = 1;
                    foreach ($values as $key => $value){
                        $percent_return   = ($value['send_sales'] > 0 ? round(($value['return_sales'] + $value['reduce_sales'] + $value['keep_sales']) / $value['send_sales'] * 100, 2) : 0);

                        $date_string ='';
                        if($rows_span > 1){
                            if($index == 1) {
                                $date_string = '<th rowspan="'.$rows_span.'" class="text-bold text-center text-middle">' . $name_filter . '</th>';
                            }
                        }
                        else{
                            $date_string = '<th class="text-bold text-center">' . $name_filter . '</th>';
                        }
                        if (empty($date_string)){
                            $class_name = 'left-2';
                        }


                        $index++;
                        $xhtmlItems .= '<tr class="'.$style_class.'">
                                            '.$date_string.'
                                            <th class="mask_currency '.$class_name.'">'.$shipers[$key].'</th> 
                                            <td class="mask_currency text-right">'.$value['send_contract'].'</td>
                                            <td class="mask_currency text-right">'.$value['send_sales'].'</td>
                                            <td class="mask_currency text-right">'.$value['keep_contract'].'</td>
                                            <td class="mask_currency text-right">'.$value['keep_sales'].'</td>
                                            <td class="mask_currency text-right">'.$value['sending_contract'].'</td>
                                            <td class="mask_currency text-right">'.$value['sending_sales'].'</td>
                                            <td class="mask_currency text-right">'.$value['post_contract'].'</td>
                                            <td class="mask_currency text-right">'.$value['post_sales'].'</td>
                                            <td class="mask_currency text-right">'.$value['reduce_contract'].'</td>
                                            <td class="mask_currency text-right">'.$value['reduce_sales'].'</td>
                                            <td class="mask_currency text-right">'.$value['return_contract'].'</td>
                                            <td class="mask_currency text-right">'.$value['return_sales'].'</td>
                                            <td class="mask_currency text-right">'.$value['success_contract'].'</td>
                                            <td class="mask_currency text-right">'.$value['success_sales'].'</td>
                                            <td class="mask_currency text-right">'.$value['money_contract'].'</td>
                                            <td class="mask_currency text-right">'.$value['money_sales'].'</td>
                                            <td class="mask_currency text-right">'.($value['success_contract'] - $value['money_contract']).'</td>
                                            <td class="mask_currency text-right">'.($value['success_sales'] - $value['money_sales']).'</td>
                                            <td class="mask_currency text-right">'.$percent_return.'%</td> 
                                        </tr>';
                    }
                }
                else{
                    $xhtmlItems .= '<tr class="'.$style_class.'">
                                    <th class="text-bold text-center">'.$name_filter.'</th> 
                                    <th></th><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                </tr>';
                }
            }

            $result['reportTable'] = '<thead>
                        				    <tr>
                            					<th rowspan="2" class="text-center fix-head">Ngày tháng</th>
                            					<th rowspan="2" class="text-center fix-head">Tên nhân viên</th>
                            					<th colspan="2" class="text-center">Gửi đi</th>
                            					<th colspan="2" class="text-center">Giữ lại bưu điện</th>
                            					<th colspan="2" class="text-center">Đang vận chuyển</th>
                            					<th colspan="2" class="text-center">Đang phát</th>
                            					<th colspan="2" class="text-center">Giảm trừ doanh thu</th>
                            					<th colspan="2" class="text-center">Hoàn</th>
                            					<th colspan="2" class="text-center">Thành công</th>
                            					<th colspan="2" class="text-center">Đã thu tiền</th>
                            					<th colspan="2" class="text-center">Công nợ</th>
                            					<th rowspan="2" class="text-center">Tỷ lệ % Hoàn</th>
                        					</tr>
                        				    <tr>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
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
            $day = date('w');
            $week_start = date('d/m/Y', strtotime('-'.$day.' days') + 86400);
            $week_end = date('d/m/Y', strtotime('+'.(6-$day).' days')+86400);

            $default_date_begin     = date('d/m/Y');
            $default_date_end       = date('d/m/Y');

            $ssFilter->report                   = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']     = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $week_start;
            $ssFilter->report['date_end']       = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $week_end;

            $this->_params['ssFilter']          = $ssFilter->report;

            // Set giá trị cho form
            $myForm	= new \Report\Form\Report($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);

            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['saleGroup']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['caption']        = 'Báo cáo giục đơn Hà Nội';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Báo cáo giục đơn HN mới
    public function internal2Action() {
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
                else{
                    $this->_params['data']['shipper_id_new'] = [$curent_user['id']];
                }
            }

            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin']     = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']       = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['shipper_id']     = $this->_params['data']['shipper_id'];
            $ssFilter->report['contract_type_bh']     = $this->_params['data']['contract_type_bh'];

            $this->_params['ssFilter'] = $ssFilter->report;

            // Xác định ngày tháng tìm kiếm
            $date           = new \ZendX\Functions\Date();
            $day_begin  = strtotime($date->formatToData($ssFilter->report['date_begin']));
            $day_end    = strtotime($date->formatToData($ssFilter->report['date_end']));
            $number_day = abs($day_end - $day_begin) / 86400;

            if(empty($this->_params['data']['shipper_id_new'])) {
                $shipers = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-positons-care')), array('key' => 'id', 'value' => 'name'));
            }
            else{
                $shipers = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(array('data' => array('ids' => $this->_params['data']['shipper_id_new'])), array('task' => 'list-positons-care')), array('key' => 'id', 'value' => 'name'));
            }

            $product_type = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-type')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'id'));
            $id_don_ha_noi  = $product_type[DON_HA_NOI];

            // Tạo mảng lưu báo cáo.
            $data_report = [];
            $data_report['total'] = null;
            $data_report['total_all'] = null;
            for ($i = 0; $i <= $number_day; $i++) {
                $day = date('Y-m-d', $day_begin + $i*86400);
                $data_report[$day] = null;
            }

            // Lấy dữ liệu doanh số.
            // Chỉ lấy danh sách đơn hà nội
            $where_contract = array(
                'date_type'         => 'production_date_send',
                'filter_date_begin'         => $ssFilter->report['date_begin'],
                'filter_date_end'           => $ssFilter->report['date_end'],
                'sale_branch_id'            => $ssFilter->report['sale_branch_id'],
                'production_type_id'        => $id_don_ha_noi,
                'contract_type_bh'          => $ssFilter->report['contract_type_bh'],
            );
            $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report(array('ssFilter' => $where_contract), array('task' => 'join-contact'));

            foreach ($contracts as $key => $value){
                $day       = substr($value['production_date_send'], 0, 10);
                $shiper_id = $value['shipper_id'];
                if(array_key_exists($shiper_id, $shipers)) {
                    // Hàng gửi
                    if($value['production_department_type'] == STATUS_CONTRACT_PRODUCT_POST){
                        $data_report[$day][$shiper_id]['send_contract'] += 1;
                        $data_report['total'][$shiper_id]['send_contract'] += 1;
                        $data_report['total_all']['total']['send_contract'] += 1;

                        $data_report[$day][$shiper_id]['send_sales'] += $value['price_total'];
                        $data_report['total'][$shiper_id]['send_sales'] += $value['price_total'];
                        $data_report['total_all']['total']['send_sales'] += $value['price_total'];
                    }
                    // Giữ lại bưu điện
                    if($value['status_check_id'] == STATUS_CONTRACT_CHECK_KEEP){
                        $data_report[$day][$shiper_id]['keep_contract'] += 1;
                        $data_report['total'][$shiper_id]['keep_contract'] += 1;
                        $data_report['total_all']['total']['keep_contract'] += 1;

                        $data_report[$day][$shiper_id]['keep_sales'] += $value['price_total'];
                        $data_report['total'][$shiper_id]['keep_sales'] += $value['price_total'];
                        $data_report['total_all']['total']['keep_sales'] += $value['price_total'];
                    }
                    // Đang vận chuyển
                    if($value['status_check_id'] == STATUS_CONTRACT_CHECK_SENDING){
                        $data_report[$day][$shiper_id]['sending_contract'] += 1;
                        $data_report['total'][$shiper_id]['sending_contract'] += 1;
                        $data_report['total_all']['total']['sending_contract'] += 1;

                        $data_report[$day][$shiper_id]['sending_sales'] += $value['price_total'];
                        $data_report['total'][$shiper_id]['sending_sales'] += $value['price_total'];
                        $data_report['total_all']['total']['sending_sales'] += $value['price_total'];
                    }
                    // Đang phát
                    if($value['status_check_id'] == STATUS_CONTRACT_CHECK_POST){
                        $data_report[$day][$shiper_id]['post_contract'] += 1;
                        $data_report['total'][$shiper_id]['post_contract'] += 1;
                        $data_report['total_all']['total']['post_contract'] += 1;

                        $data_report[$day][$shiper_id]['post_sales'] += $value['price_total'];
                        $data_report['total'][$shiper_id]['post_sales'] += $value['price_total'];
                        $data_report['total_all']['total']['post_sales'] += $value['price_total'];
                    }
                    // Giảm trừ doanh thu
                    if(!empty($value['price_reduce_sale'])){
                        $data_report[$day][$shiper_id]['reduce_contract'] += 1;
                        $data_report['total'][$shiper_id]['reduce_contract'] += 1;
                        $data_report['total_all']['total']['reduce_contract'] += 1;

                        $data_report[$day][$shiper_id]['reduce_sales'] += $value['price_reduce_sale'];
                        $data_report['total'][$shiper_id]['reduce_sales'] += $value['price_reduce_sale'];
                        $data_report['total_all']['total']['reduce_sales'] += $value['price_reduce_sale'];
                    }
                    // Hàng hoàn
                    if($value['status_check_id'] == STATUS_CONTRACT_CHECK_RETURN){
                        $data_report[$day][$shiper_id]['return_contract'] += 1;
                        $data_report['total'][$shiper_id]['return_contract'] += 1;
                        $data_report['total_all']['total']['return_contract'] += 1;

                        $data_report[$day][$shiper_id]['return_sales'] += $value['price_total'];
                        $data_report['total'][$shiper_id]['return_sales'] += $value['price_total'];
                        $data_report['total_all']['total']['return_sales'] += $value['price_total'];
                    }
                    // Thành công
                    if($value['status_check_id'] == STATUS_CONTRACT_CHECK_SUCCESS){
                        $data_report[$day][$shiper_id]['success_contract'] += 1;
                        $data_report['total'][$shiper_id]['success_contract'] += 1;
                        $data_report['total_all']['total']['success_contract'] += 1;

                        $data_report[$day][$shiper_id]['success_sales'] += $value['price_total'];
                        $data_report['total'][$shiper_id]['success_sales'] += $value['price_total'];
                        $data_report['total_all']['total']['success_sales'] += $value['price_total'];
                    }
                    // Đã thu tiền
                    if($value['status_acounting_id'] == STATUS_CONTRACT_ACOUNTING_MONEY){
                        $data_report[$day][$shiper_id]['money_contract'] += 1;
                        $data_report['total'][$shiper_id]['money_contract'] += 1;
                        $data_report['total_all']['total']['money_contract'] += 1;

//                        $data_report[$day][$shiper_id]['money_sales'] += $value['price_total'];
//                        $data_report['total'][$shiper_id]['money_sales'] += $value['price_total'];
//                        $data_report['total_all']['total']['money_sales'] += $value['price_total'];
                    }
                    $price_paid = $value['price_paid'] < $value['price_total'] ? $value['price_paid'] : $value['price_total'];
                    $data_report[$day][$shiper_id]['money_sales'] += $price_paid;
                    $data_report['total'][$shiper_id]['money_sales'] += $price_paid;
                    $data_report['total_all']['total']['money_sales'] += $price_paid;
                }
            }
            // Tham số bảng báo cáo
            $xhtmlItems = '';
            foreach ($data_report as $keys => $values){
                $rows_span = count($values) > 1 ? count($values) : '';

                $name_filter = $keys;
                if($keys == 'total'){
                    $name_filter = 'Tổng - nhân viên';
                }
                elseif ($keys == 'total_all'){
                    $name_filter = 'Tổng - tất cả';
                }

                $style_class = '';
                if($keys == 'total'){
                    $style_class = 'text-green';
                }
                elseif ($keys == 'total_all'){
                    $style_class = 'text-bold text-red';
                }

                if(!empty($values)){
                    $index = 1;
                    foreach ($values as $key => $value){
                        $percent_return   = ($value['send_sales'] > 0 ? round(($value['return_sales'] + $value['reduce_sales'] + $value['keep_sales']) / $value['send_sales'] * 100, 2) : 0);

                        $date_string ='';
                        if($rows_span > 1){
                            if($index == 1) {
                                $date_string = '<th rowspan="'.$rows_span.'" class="text-bold text-center text-middle">' . $name_filter . '</th>';
                            }
                        }
                        else{
                            $date_string = '<th class="text-bold text-center">' . $name_filter . '</th>';
                        }
                        if (empty($date_string)){
                            $class_name = 'left-2';
                        }


                        $index++;
                        $xhtmlItems .= '<tr class="'.$style_class.'">
                                            '.$date_string.'
                                            <th class="mask_currency '.$class_name.'">'.$shipers[$key].'</th> 
                                            <td class="mask_currency text-right">'.$value['send_contract'].'</td>
                                            <td class="mask_currency text-right">'.$value['send_sales'].'</td>
                                            <td class="mask_currency text-right">'.($value['send_contract']-$value['keep_contract']-$value['sending_contract']-$value['post_contract']-$value['return_contract']-$value['success_contract']).'</td>
                                            <td class="mask_currency text-right">'.($value['send_sales']-$value['keep_sales']-$value['sending_sales']-$value['post_sales']-$value['return_sales']-$value['success_sales']).'</td>
                                            <td class="mask_currency text-right">'.$value['keep_contract'].'</td>
                                            <td class="mask_currency text-right">'.$value['keep_sales'].'</td>
                                            <td class="mask_currency text-right">'.$value['sending_contract'].'</td>
                                            <td class="mask_currency text-right">'.$value['sending_sales'].'</td>
                                            <td class="mask_currency text-right">'.$value['post_contract'].'</td>
                                            <td class="mask_currency text-right">'.$value['post_sales'].'</td>
                                            <td class="mask_currency text-right">'.$value['reduce_contract'].'</td>
                                            <td class="mask_currency text-right">'.$value['reduce_sales'].'</td>
                                            <td class="mask_currency text-right">'.$value['return_contract'].'</td>
                                            <td class="mask_currency text-right">'.$value['return_sales'].'</td>
                                            <td class="mask_currency text-right">'.$value['success_contract'].'</td>
                                            <td class="mask_currency text-right">'.$value['success_sales'].'</td>
                                            <td class="mask_currency text-right">'.$value['money_contract'].'</td>
                                            <td class="mask_currency text-right">'.$value['money_sales'].'</td>
                                            <td class="mask_currency text-right">'.($value['success_contract'] - $value['money_contract']).'</td>
                                            <td class="mask_currency text-right">'.($value['success_sales'] - $value['money_sales']).'</td>
                                            <td class="mask_currency text-right">'.$percent_return.'%</td> 
                                        </tr>';
                    }
                }
                else{
                    $xhtmlItems .= '<tr class="'.$style_class.'">
                                    <th class="text-bold text-center">'.$name_filter.'</th> 
                                    <th></th><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                </tr>';
                }
            }

            $result['reportTable'] = '<thead>
                        				    <tr>
                            					<th rowspan="2" class="text-center fix-head">Ngày tháng</th>
                            					<th rowspan="2" class="text-center fix-head">Tên nhân viên</th>
                            					<th colspan="2" class="text-center">Gửi đi</th>
                            					<th colspan="2" class="text-center">Đang xử lý</th>
                            					<th colspan="2" class="text-center">Giữ lại bưu điện</th>
                            					<th colspan="2" class="text-center">Đang vận chuyển</th>
                            					<th colspan="2" class="text-center">Đang phát</th>
                            					<th colspan="2" class="text-center">Giảm trừ doanh thu</th>
                            					<th colspan="2" class="text-center">Hoàn</th>
                            					<th colspan="2" class="text-center">Thành công</th>
                            					<th colspan="2" class="text-center">Đã thu tiền</th>
                            					<th colspan="2" class="text-center">Công nợ</th>
                            					<th rowspan="2" class="text-center">Tỷ lệ % Hoàn</th>
                        					</tr>
                        				    <tr>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
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
            $day = date('w');
            $week_start = date('d/m/Y', strtotime('-'.$day.' days') + 86400);
            $week_end = date('d/m/Y', strtotime('+'.(6-$day).' days')+86400);

            $ssFilter->report                   = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']     = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $week_start;
            $ssFilter->report['date_end']       = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $week_end;

            $this->_params['ssFilter']          = $ssFilter->report;

            // Set giá trị cho form
            $myForm	= new \Report\Form\Report($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);

            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['saleGroup']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['caption']        = 'Báo cáo giục đơn Hà Nội';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Báo cáo giục đơn tỉnh
    public function externalAction() {
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
            }

            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin']     = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']       = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];

            $this->_params['ssFilter'] = $ssFilter->report;

            // Xác định ngày tháng tìm kiếm
            $date           = new \ZendX\Functions\Date();
            $day_begin  = strtotime($date->formatToData($ssFilter->report['date_begin']));
            $day_end    = strtotime($date->formatToData($ssFilter->report['date_end']));
            $number_day = abs($day_end - $day_begin) / 86400;

            $product_type = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-type')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'id'));
            $id_don_tinh  = $product_type[DON_TINH];

            // Tạo mảng lưu báo cáo.
            $data_report = [];
            for ($i = 0; $i <= $number_day; $i++) {
                $day = date('Y-m-d', $day_begin + $i*86400);

                $data_report[$day]['send_contract']    = 0;
                $data_report[$day]['send_sales']       = 0;
                $data_report[$day]['keep_contract']    = 0;
                $data_report[$day]['keep_sales']       = 0;
                $data_report[$day]['sending_contract'] = 0;
                $data_report[$day]['sending_sales']    = 0;
                $data_report[$day]['post_contract']    = 0;
                $data_report[$day]['post_sales']       = 0;
                $data_report[$day]['reduce_contract']  = 0;
                $data_report[$day]['reduce_sales']     = 0;
                $data_report[$day]['return_contract']  = 0;
                $data_report[$day]['return_sales']     = 0;
                $data_report[$day]['success_contract'] = 0;
                $data_report[$day]['success_sales']    = 0;
            }
            $data_report['total']['send_contract']    = 0;
            $data_report['total']['send_sales']       = 0;
            $data_report['total']['keep_contract']    = 0;
            $data_report['total']['keep_sales']       = 0;
            $data_report['total']['sending_contract'] = 0;
            $data_report['total']['sending_sales']    = 0;
            $data_report['total']['post_contract']    = 0;
            $data_report['total']['post_sales']       = 0;
            $data_report['total']['reduce_contract']  = 0;
            $data_report['total']['reduce_sales']     = 0;
            $data_report['total']['return_contract']  = 0;
            $data_report['total']['return_sales']     = 0;
            $data_report['total']['success_contract'] = 0;
            $data_report['total']['success_sales']    = 0;

            // Lấy dữ liệu doanh số mới, cũ, mua lại.
            $where_contract = array(
                'filter_date_begin' => $ssFilter->report['date_begin'],
                'filter_date_end'   => $ssFilter->report['date_end'],
                'sale_branch_id'    => $ssFilter->report['sale_branch_id'],
                'production_type_id'=> $id_don_tinh,
            );

            $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report(array('ssFilter' => $where_contract), array('task' => 'join-contact'));
            foreach ($contracts as $key => $value){
                $day = substr($value['date'],0 ,10);
                // Hàng gửi
                if($value['production_department_type'] == STATUS_CONTRACT_PRODUCT_POST){
                    $data_report[$day]['send_contract'] += 1;
                    $data_report['total']['send_contract'] += 1;

                    $data_report[$day]['send_sales']    += $value['price_total'];
                    $data_report['total']['send_sales']    += $value['price_total'];
                }
                // Giữ lại bưu điện
                if($value['status_check_id'] == STATUS_CONTRACT_CHECK_KEEP){
                    $data_report[$day]['keep_contract'] += 1;
                    $data_report['total']['keep_contract'] += 1;

                    $data_report[$day]['keep_sales']    += $value['price_total'];
                    $data_report['total']['keep_sales']    += $value['price_total'];
                }
                // Đang vận chuyển
                if($value['status_check_id'] == STATUS_CONTRACT_CHECK_SENDING){
                    $data_report[$day]['sending_contract'] += 1;
                    $data_report['total']['sending_contract'] += 1;

                    $data_report[$day]['sending_sales']    += $value['price_total'];
                    $data_report['total']['sending_sales']    += $value['price_total'];
                }
                // Đang phát
                if($value['status_check_id'] == STATUS_CONTRACT_CHECK_POST){
                    $data_report[$day]['post_contract'] += 1;
                    $data_report['total']['post_contract'] += 1;

                    $data_report[$day]['post_sales']    += $value['price_total'];
                    $data_report['total']['post_sales']    += $value['price_total'];
                }
                // Giảm trừ doanh thu
                if(!empty($value['price_reduce_sale'])){
                    $data_report[$day]['reduce_contract'] += 1;
                    $data_report['total']['reduce_contract'] += 1;

                    $data_report[$day]['reduce_sales']    += $value['price_reduce_sale'];
                    $data_report['total']['reduce_sales']    += $value['price_reduce_sale'];
                }
                // Hàng hoàn
                if($value['status_check_id'] == STATUS_CONTRACT_CHECK_RETURN){
                    $data_report[$day]['return_contract'] += 1;
                    $data_report['total']['return_contract'] += 1;

                    $data_report[$day]['return_sales']    += $value['price_total'];
                    $data_report['total']['return_sales']    += $value['price_total'];
                }
                // Thành công
                if($value['status_check_id'] == STATUS_CONTRACT_CHECK_SUCCESS){
                    $data_report[$day]['success_contract'] += 1;
                    $data_report['total']['success_contract'] += 1;

                    $data_report[$day]['success_sales']    += $value['price_total'];
                    $data_report['total']['success_sales']    += $value['price_total'];
                }
            }

            // Tham số bảng báo cáo
            $xhtmlItems = '';
            foreach ($data_report as $key => $value){
                $percent_return   = ($value['send_sales'] > 0 ? round(($value['return_sales'] + $value['reduce_sales'] + $value['keep_sales']) / $value['send_sales'] * 100, 2) : 0);

                $style_class = $key == 'total' ? 'text-bold text-red': '';
                $name_filter = $key == 'total' ? 'Tổng': $key;
                $xhtmlItems .= '<tr class="'.$style_class.'">
        		                <th class="text-bold text-center">'.$name_filter.'</th>
        						<td class="mask_currency text-right">'.$value['send_contract'].'</td>
        						<td class="mask_currency text-right">'.$value['send_sales'].'</td>
        						<td class="mask_currency text-right">'.$value['keep_contract'].'</td>
        						<td class="mask_currency text-right">'.$value['keep_sales'].'</td>
        						<td class="mask_currency text-right">'.$value['sending_contract'].'</td>
        						<td class="mask_currency text-right">'.$value['sending_sales'].'</td>
        						<td class="mask_currency text-right">'.$value['post_contract'].'</td>
        						<td class="mask_currency text-right">'.$value['post_sales'].'</td>
        						<td class="mask_currency text-right">'.$value['reduce_contract'].'</td>
        						<td class="mask_currency text-right">'.$value['reduce_sales'].'</td>
        						<td class="mask_currency text-right">'.$value['return_contract'].'</td>
        						<td class="mask_currency text-right">'.$value['return_sales'].'</td>
        						<td class="mask_currency text-right">'.$value['success_contract'].'</td>
        						<td class="mask_currency text-right">'.$value['success_sales'].'</td>
        						<td class="mask_currency text-right">'.$percent_return.'%</td> 
        					</tr>';
            }

            $result['reportTable'] = '<thead>
                        				    <tr>
                            					<th rowspan="2" class="text-center fix-head">Ngày tháng</th>
                            					<th colspan="2" class="text-center">Gửi đi</th>
                            					<th colspan="2" class="text-center">Giữ lại bưu điện</th>
                            					<th colspan="2" class="text-center">Đang vận chuyển</th>
                            					<th colspan="2" class="text-center">Đang phát</th>
                            					<th colspan="2" class="text-center">Giảm trừ doanh thu</th>
                            					<th colspan="2" class="text-center">Hoàn</th>
                            					<th colspan="2" class="text-center">Thành công</th>
                            					<th rowspan="2" class="text-center">Tỷ lệ % Hoàn</th>
                        					</tr>
                        				    <tr>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                        					</tr>
                        				</thead>
                        				<tbody>
                        				    '. $xhtmlItems .'
                        				</tbody>';

            echo json_encode($result);

            return $this->response;
        } else {
            // Khai báo giá trị ngày tháng
            $default_date_begin     = date('01/m/Y');
            $default_date_end       = date('t/m/Y');
            $default_sale_branch_id = $this->_userInfo->getUserInfo('sale_branch_id');

            $ssFilter->report                   = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']     = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $default_date_begin;
            $ssFilter->report['date_end']       = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $default_date_end;

            $this->_params['ssFilter']          = $ssFilter->report;

            // Set giá trị cho form
            $myForm	= new \Report\Form\Report($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);

            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['saleGroup']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['caption']        = 'Báo cáo giục đơn';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Báo cáo giục đơn Tỉnh mới
    public function external2Action() {
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
                else{
                    $this->_params['data']['shipper_id_new'] = [$curent_user['id']];
                }
            }

            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin']     = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']       = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['shipper_id_new']     = $this->_params['data']['shipper_id_new'];
            $ssFilter->report['contract_type_bh']   = $this->_params['data']['contract_type_bh'];

            $this->_params['ssFilter'] = $ssFilter->report;

            // Xác định ngày tháng tìm kiếm
            $date           = new \ZendX\Functions\Date();
            $day_begin  = strtotime($date->formatToData($ssFilter->report['date_begin']));
            $day_end    = strtotime($date->formatToData($ssFilter->report['date_end']));
            $number_day = abs($day_end - $day_begin) / 86400;

            if(empty($this->_params['data']['shipper_id_new'])) {
                $shipers = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-positons-care')), array('key' => 'id', 'value' => 'name'));
            }
            else{
                $shipers = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(array('data' => array('ids' => $this->_params['data']['shipper_id_new'])), array('task' => 'list-positons-care')), array('key' => 'id', 'value' => 'name'));
            }

            $product_type = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-type')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'id'));
            $id_don_ha_noi  = $product_type[DON_TINH];
            


            // Tạo mảng lưu báo cáo.
            $data_report = [];
            $data_report['total'] = null;
            $data_report['total_all'] = null;
            for ($i = 0; $i <= $number_day; $i++) {
                $day = date('Y-m-d', $day_begin + $i*86400);
                $data_report[$day] = null;
            }

            // Lấy dữ liệu doanh số.
            // Chỉ lấy danh sách đơn hà nội
            $where_contract = array(
                'filter_date_begin'         => $ssFilter->report['date_begin'],
                'filter_date_end'           => $ssFilter->report['date_end'],
                'sale_branch_id'            => $ssFilter->report['sale_branch_id'],
//                'production_type_id'        => $id_don_ha_noi,
//                'contract_type_bh'          => $ssFilter->report['contract_type_bh'],
            );
            $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report(array('ssFilter' => $where_contract), array('task' => 'join-contact'));

            foreach ($contracts as $key => $value){
                $day       = substr($value['production_date_send'], 0, 10);
                $shiper_id = $value['shipper_id'];
                if(array_key_exists($shiper_id, $shipers)) {
                    // Hàng gửi
                    if($value['production_department_type'] == STATUS_CONTRACT_PRODUCT_POST){
                        $data_report[$day][$shiper_id]['send_contract'] += 1;
                        $data_report['total'][$shiper_id]['send_contract'] += 1;
                        $data_report['total_all']['total']['send_contract'] += 1;

                        $data_report[$day][$shiper_id]['send_sales'] += $value['price_total'];
                        $data_report['total'][$shiper_id]['send_sales'] += $value['price_total'];
                        $data_report['total_all']['total']['send_sales'] += $value['price_total'];
                    }
                    // Giữ lại bưu điện
                    if($value['status_check_id'] == STATUS_CONTRACT_CHECK_KEEP){
                        $data_report[$day][$shiper_id]['keep_contract'] += 1;
                        $data_report['total'][$shiper_id]['keep_contract'] += 1;
                        $data_report['total_all']['total']['keep_contract'] += 1;

                        $data_report[$day][$shiper_id]['keep_sales'] += $value['price_total'];
                        $data_report['total'][$shiper_id]['keep_sales'] += $value['price_total'];
                        $data_report['total_all']['total']['keep_sales'] += $value['price_total'];
                    }
                    // Đang vận chuyển
                    if($value['status_check_id'] == STATUS_CONTRACT_CHECK_SENDING){
                        $data_report[$day][$shiper_id]['sending_contract'] += 1;
                        $data_report['total'][$shiper_id]['sending_contract'] += 1;
                        $data_report['total_all']['total']['sending_contract'] += 1;

                        $data_report[$day][$shiper_id]['sending_sales'] += $value['price_total'];
                        $data_report['total'][$shiper_id]['sending_sales'] += $value['price_total'];
                        $data_report['total_all']['total']['sending_sales'] += $value['price_total'];
                    }
                    // Đang phát
                    if($value['status_check_id'] == STATUS_CONTRACT_CHECK_POST){
                        $data_report[$day][$shiper_id]['post_contract'] += 1;
                        $data_report['total'][$shiper_id]['post_contract'] += 1;
                        $data_report['total_all']['total']['post_contract'] += 1;

                        $data_report[$day][$shiper_id]['post_sales'] += $value['price_total'];
                        $data_report['total'][$shiper_id]['post_sales'] += $value['price_total'];
                        $data_report['total_all']['total']['post_sales'] += $value['price_total'];
                    }
                    // Giảm trừ doanh thu
                    if(!empty($value['price_reduce_sale'])){
                        $data_report[$day][$shiper_id]['reduce_contract'] += 1;
                        $data_report['total'][$shiper_id]['reduce_contract'] += 1;
                        $data_report['total_all']['total']['reduce_contract'] += 1;

                        $data_report[$day][$shiper_id]['reduce_sales'] += $value['price_reduce_sale'];
                        $data_report['total'][$shiper_id]['reduce_sales'] += $value['price_reduce_sale'];
                        $data_report['total_all']['total']['reduce_sales'] += $value['price_reduce_sale'];
                    }
                    // Hàng hoàn
                    if($value['status_check_id'] == STATUS_CONTRACT_CHECK_RETURN){
                        $data_report[$day][$shiper_id]['return_contract'] += 1;
                        $data_report['total'][$shiper_id]['return_contract'] += 1;
                        $data_report['total_all']['total']['return_contract'] += 1;

                        $data_report[$day][$shiper_id]['return_sales'] += $value['price_total'];
                        $data_report['total'][$shiper_id]['return_sales'] += $value['price_total'];
                        $data_report['total_all']['total']['return_sales'] += $value['price_total'];
                    }
                    // Thành công
                    if($value['status_check_id'] == STATUS_CONTRACT_CHECK_SUCCESS){
                        $data_report[$day][$shiper_id]['success_contract'] += 1;
                        $data_report['total'][$shiper_id]['success_contract'] += 1;
                        $data_report['total_all']['total']['success_contract'] += 1;

                        $data_report[$day][$shiper_id]['success_sales'] += $value['price_total'];
                        $data_report['total'][$shiper_id]['success_sales'] += $value['price_total'];
                        $data_report['total_all']['total']['success_sales'] += $value['price_total'];
                    }
                    // Đã thu tiền
                    if($value['status_acounting_id'] == STATUS_CONTRACT_ACOUNTING_MONEY){
                        $data_report[$day][$shiper_id]['money_contract'] += 1;
                        $data_report['total'][$shiper_id]['money_contract'] += 1;
                        $data_report['total_all']['total']['money_contract'] += 1;

//                        $data_report[$day][$shiper_id]['money_sales'] += $value['price_total'];
//                        $data_report['total'][$shiper_id]['money_sales'] += $value['price_total'];
//                        $data_report['total_all']['total']['money_sales'] += $value['price_total'];
                    }

                    $price_paid = $value['price_paid'] < $value['price_total'] ? $value['price_paid'] : $value['price_total'];
                    $data_report[$day][$shiper_id]['money_sales'] += $price_paid;
                    $data_report['total'][$shiper_id]['money_sales'] += $price_paid;
                    $data_report['total_all']['total']['money_sales'] += $price_paid;


                }
            }
            // Tham số bảng báo cáo
            $xhtmlItems = '';
            foreach ($data_report as $keys => $values){
                $rows_span = count($values) > 1 ? count($values) : '';

                $name_filter = $keys;
                if($keys == 'total'){
                    $name_filter = 'Tổng - nhân viên';
                }
                elseif ($keys == 'total_all'){
                    $name_filter = 'Tổng - tất cả';
                }

                $style_class = '';
                if($keys == 'total'){
                    $style_class = 'text-green';
                }
                elseif ($keys == 'total_all'){
                    $style_class = 'text-bold text-red';
                }

                if(!empty($values)){
                    $index = 1;
                    foreach ($values as $key => $value){
                        $percent_return   = ($value['send_sales'] > 0 ? round(($value['return_sales'] + $value['reduce_sales'] + $value['keep_sales']) / $value['send_sales'] * 100, 2) : 0);

                        $date_string ='';
                        if($rows_span > 1){
                            if($index == 1) {
                                $date_string = '<th rowspan="'.$rows_span.'" class="text-bold text-center text-middle">' . $name_filter . '</th>';
                            }
                        }
                        else{
                            $date_string = '<th class="text-bold text-center">' . $name_filter . '</th>';
                        }
                        if (empty($date_string)){
                            $class_name = 'left-2';
                        }


                        $index++;
                        $xhtmlItems .= '<tr class="'.$style_class.'">
                                            '.$date_string.'
                                            <th class="mask_currency '.$class_name.'">'.$shipers[$key].'</th> 
                                            <td class="mask_currency text-right">'.$value['send_contract'].'</td>
                                            <td class="mask_currency text-right">'.$value['send_sales'].'</td>
                                            
                                            <td class="mask_currency text-right">'.($value['send_contract'] - $value['keep_contract'] - $value['sending_contract'] - $value['post_contract'] -$value['return_contract'] - $value['success_contract']).'</td>
                                            <td class="mask_currency text-right">'.($value['send_sales'] - $value['keep_sales'] - $value['sending_sals'] - $value['post_sales'] -$value['return_sales'] - $value['success_sales']).'</td>
                                            
                                            <td class="mask_currency text-right">'.$value['keep_contract'].'</td>
                                            <td class="mask_currency text-right">'.$value['keep_sales'].'</td>
                                            
                                            <td class="mask_currency text-right">'.$value['sending_contract'].'</td>
                                            <td class="mask_currency text-right">'.$value['sending_sales'].'</td>
                                            
                                            <td class="mask_currency text-right">'.$value['post_contract'].'</td>
                                            <td class="mask_currency text-right">'.$value['post_sales'].'</td>
                                            
                                            <td class="mask_currency text-right">'.$value['reduce_contract'].'</td>
                                            <td class="mask_currency text-right">'.$value['reduce_sales'].'</td>
                                            
                                            <td class="mask_currency text-right">'.$value['return_contract'].'</td>
                                            <td class="mask_currency text-right">'.$value['return_sales'].'</td>
                                            
                                            <td class="mask_currency text-right">'.$value['success_contract'].'</td>
                                            <td class="mask_currency text-right">'.$value['success_sales'].'</td>
                                            
                                            <td class="mask_currency text-right">'.$value['money_contract'].'</td>
                                            <td class="mask_currency text-right">'.$value['money_sales'].'</td>
                                            
                                            <td class="mask_currency text-right">'.($value['success_contract'] - $value['money_contract']).'</td>
                                            <td class="mask_currency text-right">'.($value['success_sales'] - $value['money_sales']).'</td>
                                            <td class="mask_currency text-right">'.$percent_return.'%</td> 
                                        </tr>';
                    }
                }
                else{
                    $xhtmlItems .= '<tr class="'.$style_class.'">
                                    <th class="text-bold text-center">'.$name_filter.'</th> 
                                    <th></th><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                </tr>';
                }
            }

            $result['reportTable'] = '<thead>
                        				    <tr>
                            					<th rowspan="2" class="text-center fix-head">Ngày tháng</th>
                            					<th rowspan="2" class="text-center fix-head">Tên nhân viên</th>
                            					<th colspan="2" class="text-center">Gửi đi</th>
                            					<th colspan="2" class="text-center">Đang xử lý</th>
                            					<th colspan="2" class="text-center">Giữ lại bưu điện</th>
                            					<th colspan="2" class="text-center">Đang vận chuyển</th>
                            					<th colspan="2" class="text-center">Đang phát</th>
                            					<th colspan="2" class="text-center">Giảm trừ doanh thu</th>
                            					<th colspan="2" class="text-center">Hoàn</th>
                            					<th colspan="2" class="text-center">Thành công</th>
                            					<th colspan="2" class="text-center">Đã thu tiền</th>
                            					<th colspan="2" class="text-center">Công nợ</th>
                            					<th rowspan="2" class="text-center">Tỷ lệ % Hoàn</th>
                        					</tr>
                        				    <tr>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
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
            $day = date('w');
            $week_start = date('d/m/Y', strtotime('-'.$day.' days') + 86400);
            $week_end = date('d/m/Y', strtotime('+'.(6-$day).' days')+86400);

            $ssFilter->report                   = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']     = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $week_start;
            $ssFilter->report['date_end']       = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $week_end;

            $this->_params['ssFilter']          = $ssFilter->report;

            // Set giá trị cho form
            $myForm	= new \Report\Form\Report($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);

            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['saleGroup']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['caption']        = 'Báo cáo giục đơn tỉnh';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function overviewAction() {
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
                elseif (in_array(GROUP_SALES_LEADER, $permission_ids) || in_array(GROUP_MKT_LEADER, $permission_ids) || in_array(CHECK_MANAGER_LEADER, $permission_ids)){
                    $this->_params['data']['sale_branch_id'] = $curent_user['sale_branch_id'];
                    $this->_params['data']['sale_group_id'] = $curent_user['sale_group_id'];
                    if(in_array(GROUP_MKT_LEADER, $permission_ids)){
                        $this->_params['data']['sale_group_id'] = $curent_user['branch_sale_group_id'];
                    }
                }
                else{
                    $this->_params['data']['delivery_id'] = $curent_user['id'];
                }
            }
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin']         = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']           = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id']     = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_group_id']      = $this->_params['data']['sale_group_id'];
            $ssFilter->report['delivery_id']        = $this->_params['data']['delivery_id'];
            $ssFilter->report['product_group_id']   = $this->_params['data']['product_group_id'];
            $ssFilter->report['production_type_id'] = $this->_params['data']['production_type_id'];

            $this->_params['data']['company_department_id'] = 'giuc-don';
            $sales = $this->getServiceLocator()->get('Admin\Model\UserTable')->report($this->_params, array('task' => 'list-all'));

            // Tạo mảng lưu báo cáo.
            $data_report = [];
            foreach ($sales as $key => $value) {
                $data_report[$value['id']]['name']                  = $value['name'];
                $data_report[$value['id']]['order_contract']        = 0; // số đơn xuất đi
                $data_report[$value['id']]['order_sale']            = 0; // doanh số xuất đi
                $data_report[$value['id']]['transport_contract']    = 0; // đang vận chuyển
                $data_report[$value['id']]['transport_sale']        = 0;
                $data_report[$value['id']]['refund_contract']       = 0; // giảm trừ doanh thu
                $data_report[$value['id']]['refund_sale']           = 0;
                $data_report[$value['id']]['return_contract']       = 0; // hoàn
                $data_report[$value['id']]['return_sale']           = 0;
                $data_report[$value['id']]['complete_contract']     = 0; // hoàn thành
                $data_report[$value['id']]['complete_sale']         = 0;
                $data_report[$value['id']]['check_contract']        = 0; // đã đối soát
                $data_report[$value['id']]['check_sale']            = 0;
                $data_report[$value['id']]['debt_contract']         = 0; // công nợ
                $data_report[$value['id']]['debt_sale']             = 0;
            }
            $data_report['total']['name']                  = "Tổng";
            $data_report['total']['order_contract']        = 0; // số đơn xuất đi
            $data_report['total']['order_sale']            = 0; // doanh số xuất đi
            $data_report['total']['transport_contract']    = 0; // đang vận chuyển
            $data_report['total']['transport_sale']        = 0;
            $data_report['total']['refund_contract']       = 0; // giảm trừ doanh thu
            $data_report['total']['refund_sale']           = 0;
            $data_report['total']['return_contract']       = 0; // hoàn
            $data_report['total']['return_sale']           = 0;
            $data_report['total']['complete_contract']     = 0; // Thành công
            $data_report['total']['complete_sale']         = 0;
            $data_report['total']['check_contract']        = 0; // đã đối soát
            $data_report['total']['check_sale']            = 0;
            $data_report['total']['debt_contract']         = 0; // công nợ
            $data_report['total']['debt_sale']             = 0;

            // Lấy dữ liệu doanh số.
            $where_contract = array(
                'filter_date_begin'         => $ssFilter->report['date_begin'],
                'filter_date_end'           => $ssFilter->report['date_end'],
                'filter_product_group_id'   => $ssFilter->report['product_group_id'],
                'production_type_id'        => $ssFilter->report['production_type_id'],
                'delivery_id'               => $ssFilter->report['delivery_id'],
                'date_type'                 => "shipped_date",
            );
            $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report(array('ssFilter' => $where_contract), array('task' => 'join-contact'));

            $dalayhang_status       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(array('alias' => 'da-lay-hang',  'code' => 'status-merge'), array('task' => 'by-custom-alias'));
            $hanghoan_status        = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(array('alias' => 'hang-hoan',  'code' => 'status-merge'), array('task' => 'by-custom-alias'));
            $danggiaohang_status    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(array('alias' => 'dang-giao-hang',  'code' => 'status-merge'), array('task' => 'by-custom-alias'));
            $thanhcong_status       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(array('alias' => 'thanh-cong',  'code' => 'status-merge'), array('task' => 'by-custom-alias'));

            $dalayhang_arr          = array_merge(explode(',', trim($dalayhang_status['content'])), explode(',', trim($dalayhang_status['note'])), explode(',', trim($dalayhang_status['description'])));
            $hanghoan_arr           = array_merge(explode(',', trim($hanghoan_status['content'])), explode(',', trim($hanghoan_status['note'])), explode(',', trim($hanghoan_status['description'])));
            $danggiaohang_arr       = array_merge(explode(',', trim($danggiaohang_status['content'])), explode(',', trim($danggiaohang_status['note'])), explode(',', trim($danggiaohang_status['description'])));
            $thanhcong_arr          = array_merge(explode(',', trim($thanhcong_status['content'])), explode(',', trim($thanhcong_status['note'])), explode(',', trim($thanhcong_status['description'])));

            foreach ($contracts as $key => $value){
                // Nếu người lên đơn nằm trong danh sách nhân viên sale.
                if (array_key_exists($value['delivery_id'], $data_report)) {
                    $data_report[$value['delivery_id']]['order_contract'] += 1;
                    $data_report['total']['order_contract'] += 1;

                    $data_report[$value['delivery_id']]['order_sale'] += $value['price_total'];
                    $data_report['total']['order_sale'] += $value['price_total'];

                    // Sales - Hủy sales
                    if ($value['status_id'] == HUY_SALES) {
                        $data_report[$value['delivery_id']]['cancel_contract'] += 1;
                        $data_report['total']['cancel_contract'] += 1;

                        $data_report[$value['delivery_id']]['cancel_sale'] += $value['price_total'] - $value['vat'];
                        $data_report['total']['cancel_sale'] += $value['price_total'] - $value['vat'];
                    }
                    // Giục đơn - đang giao hàng
                    if (in_array($value['ghtk_status'], $danggiaohang_arr)) {
                        $data_report[$value['delivery_id']]['transport_contract'] += 1;
                        $data_report['total']['transport_contract'] += 1;

                        $data_report[$value['delivery_id']]['transport_sale'] += $value['price_total'] - $value['vat'];
                        $data_report['total']['transport_sale'] += $value['price_total'] - $value['vat'];
                    }
                    // Giảm trừ doanh thu
                    if($value['price_reduce_sale'] > 0){
                        $data_report[$value['delivery_id']]['refund_contract'] += 1;
                        $data_report['total']['refund_contract'] += 1;
                        $data_report[$value['delivery_id']]['refund_sale'] += $value['price_reduce_sale'];
                        $data_report['total']['refund_sale'] += $value['price_reduce_sale'];
                    }
                    // Dục đơn - hoàn
                    if (in_array($value['ghtk_status'], $hanghoan_arr)) {
                        $data_report[$value['delivery_id']]['return_contract'] += 1;
                        $data_report['total']['return_contract'] += 1;
                        $data_report[$value['delivery_id']]['return_sale'] += $value['price_total'] - $value['vat'];
                        $data_report['total']['return_sale'] += $value['price_total'] - $value['vat'];
                    }
                    // Dục đơn - thành công
                    if (in_array($value['ghtk_status'], $thanhcong_arr)) {
                        $data_report[$value['delivery_id']]['complete_contract'] += 1;
                        $data_report['total']['complete_contract'] += 1;
                        $data_report[$value['delivery_id']]['complete_sale'] += $value['price_total'] - $value['price_reduce_sale'];
                        $data_report['total']['complete_sale'] += $value['price_total'] - $value['price_reduce_sale'];
                    }

                    // Dục đơn - đã đối soát
                    if ($value['status_acounting_id'] == 'da-doi-soat' && $value['returned'] == 0) {
                        $data_report[$value['delivery_id']]['check_contract'] += 1;
                        $data_report['total']['check_contract'] += 1;
                        $data_report[$value['delivery_id']]['check_sale'] += $value['price_paid'] + $value['price_deposits'];
                        $data_report['total']['check_sale'] += $value['price_paid'] + $value['price_deposits'];
                    }
//                    else{
//                        $data_report[$value['delivery_id']]['debt_contract'] += 1;
//                        $data_report['total']['debt_contract'] += 1;
//                        $data_report[$value['delivery_id']]['debt_sale'] += $value['price_total'] - $value['vat'];
//                        $data_report['total']['debt_sale'] += $value['price_total'] - $value['vat'];
//                    }
                }
            }

            // Tham số bảng báo cáo
            foreach ($data_report as $key => $value){
                $sale_tm = $value['order_sale'] - $value['cancel_sale'];
                $percent_return  = ($sale_tm > 0 ? round(($value['refund_sale'] + $value['return_sale']) / $sale_tm * 100, 2) : 0);
                $debt_contract = $value['order_contract'] - $value['cancel_contract'] - $value['refund_contract'] - $value['return_contract'] - $value['check_contract'];
                $debt_sale = $value['order_sale'] - $value['cancel_sale'] - $value['refund_sale'] - $value['return_sale'] - $value['check_sale'];;

                $data_report[$key]['percent_return']  = $percent_return;
                $data_report[$key]['debt_contract']  = $debt_contract;
                $data_report[$key]['debt_sale']  = $debt_sale;
            }
            // sắp xếp theo doanh tổng doanh thu
            $key_sort = [];
            $i = 0;
            foreach ($data_report as $key => $value) {
                if($key != 'total'){
                    $key_sort[$i]['id'] = $key;
                    $key_sort[$i]['order_contract'] = $data_report[$key]['order_contract'];
                    $i++;
                }
            }
            for($i = 0; $i < count($key_sort) - 1; $i++){
                for($j = $i+1; $j < count($key_sort); $j++){
                    if($key_sort[$i]['order_contract'] < $key_sort[$j]['order_contract']){
                        $tm           = $key_sort[$i];
                        $key_sort[$i] = $key_sort[$j];
                        $key_sort[$j] = $tm;
                    }
                }
            }

            $xhtmlItems = '';
            foreach ($key_sort as $key => $value){
                $xhtmlItems .= '<tr>
        		                <th class="text-bold">'.$data_report[$value['id']]['name'].'</th> <!--Tên nhân viên-->
        						<td class="mask_currency text-right">'.($data_report[$value['id']]['order_contract'] - $data_report[$value['id']]['cancel_contract']).'</td>
        						<td class="mask_currency text-right">'.($data_report[$value['id']]['order_sale'] - $data_report[$value['id']]['cancel_sale']).'</td> 
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['transport_contract'].'</td> 
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['transport_sale'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['refund_contract'].'</td> 
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['refund_sale'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['return_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['return_sale'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['complete_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['complete_sale'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['check_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['check_sale'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['percent_return'].'%</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['debt_sale'].'</td>';
                $xhtmlItems .=  '</tr>';
            }
            // Hiển thị dòng tổng tất cả.
            $xhtmlItems .= '<tr class="text-bold text-red">
        		                <th class="text-bold">'.$data_report['total']['name'].'</th> <!--Tên nhân viên-->
        						<td class="mask_currency text-right">'.($data_report['total']['order_contract'] - $data_report['total']['cancel_contract']).'</td> <!--Tổng doanh số-->
        						<td class="mask_currency text-right">'.($data_report['total']['order_sale'] - $data_report['total']['cancel_sale']).'</td> 
        						<td class="mask_currency text-right">'.$data_report['total']['transport_contract'].'</td> 
        						<td class="mask_currency text-right">'.$data_report['total']['transport_sale'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['refund_contract'].'</td> 
        						<td class="mask_currency text-right">'.$data_report['total']['refund_sale'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['return_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['return_sale'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['complete_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['complete_sale'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['check_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['check_sale'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['percent_return'].'%</td>
        						<td class="mask_currency text-right">'.$data_report['total']['debt_sale'].'</td>';
            $xhtmlItems .=  '</tr>';
            $result['reportTable'] = '<thead>
                        				    <tr>
                            					<th rowspan="2" class="text-center fix-head">Tên nhân viên</th>
                            					<th colspan="2" class="text-center">Tổng doanh số</th>
                            					<th colspan="2" class="text-center">Đang vận chuyển + đang giao hàng</th>
                            					<th colspan="2" class="text-center">Giảm trừ doanh thu</th>
                            					<th colspan="2" class="text-center">Hàng hoàn</th>
                            					<th colspan="2" class="text-center">Thành công</th>
                            					<th colspan="2" class="text-center">Đã đối soát</th>
                            					<th rowspan="2" class="text-center">Tỷ lệ Hoàn</th>
                            					<th rowspan="2" class="text-center">Công nợ</th>
                        					</tr>
                        				    <tr>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">doanh số</th>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">doanh số</th>
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
            $ssFilter->report['delivery_id']    = $ssFilter->report['delivery_id'] ? $ssFilter->report['delivery_id'] : '';
            $ssFilter->report['product_group_id'] = $ssFilter->report['product_group_id'] ? $ssFilter->report['product_group_id'] : '';
            $ssFilter->report['production_type_id'] = $ssFilter->report['production_type_id'] ? $ssFilter->report['production_type_id'] : '';

            // Set giá trị cho form
            $myForm	= new \Report\Form\Sales\Sales($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);

            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['caption']        = 'Báo cáo doanh thu sale';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }
}




















