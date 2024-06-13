<?php

namespace Report\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class SaleController extends ActionController {

    public function init() {
        $this->setLayout('report');

        // Lấy dữ liệu post của form
        $this->_params['data'] = $this->getRequest()->getPost()->toArray();

        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__);
        $this->_params['ssFilter']['filter_date_begin']     = $ssFilter->filter_date_begin;
        $this->_params['ssFilter']['filter_date_end']       = $ssFilter->filter_date_end;

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

    // Báo cáo Chia số
    public function shareAction() {
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));

        if($this->getRequest()->isPost()) {
            // Lấy giá trị post từ filter
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();

            // Quyền user
            $curent_user = $this->_userInfo->getUserInfo();
            $permission_ids = explode(',', $curent_user['permission_ids']);
            if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids) && !in_array(MANAGER, $permission_ids)){
                if(in_array(GDCN, $permission_ids) || in_array(SALEADMIN, $permission_ids) || in_array('share_data', $permission_ids)){
                    $this->_params['data']['sale_branch_id'] = $curent_user['sale_branch_id'];
                }
                elseif (in_array(GROUP_SALES_LEADER, $permission_ids) || in_array(GROUP_MKT_LEADER, $permission_ids)){
                    $this->_params['data']['sale_branch_id'] = $curent_user['sale_branch_id'];
                    $this->_params['data']['sale_group_id'] = $curent_user['sale_group_id'];
                    if(in_array(GROUP_MKT_LEADER, $permission_ids)){
                        $this->_params['data']['sale_group_id'] = $curent_user['branch_sale_group_id'];
                    }
                }
                else{
                    $this->_params['data']['sale_branch_id'] = $curent_user['sale_branch_id'];
//                    $this->_params['data']['sale_id'] = $curent_user['id'];
                }
            }
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin']     = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']       = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_group_id']  = $this->_params['data']['sale_group_id'];
            $ssFilter->report['sale_id']        = $this->_params['data']['sale_id'];

            $sales = $this->getServiceLocator()->get('Admin\Model\UserTable')->report($this->_params, array('task' => 'list-sale'));

            // Tạo mảng lưu báo cáo.
            $data_report = [];
            foreach ($sales as $key => $value) {
                $data_report[$value['id']]['name']           = $value['name'];
                $data_report[$value['id']]['phone']          = 0;
                $data_report[$value['id']]['cancel']         = 0;
                $data_report[$value['id']]['called']         = 0;
                $data_report[$value['id']]['latched']        = 0; // Đã chốt
                $data_report[$value['id']]['sales_expected'] = 0; // Doanh số tạm tính
                $data_report[$value['id']]['sales_order']    = 0; // Doanh số lên đơn
                $data_report[$value['id']]['not_call']       = 0;
            }
            $data_report['total']['name']           = "Tổng";
            $data_report['total']['phone']          = 0; // SĐT nhận
            $data_report['total']['cancel']         = 0; // Hủy
            $data_report['total']['called']         = 0; // Đã tư vấn
            $data_report['total']['latched']        = 0; // Đã chốt
            $data_report['total']['sales_expected'] = 0; // Doanh số tạm tính
            $data_report['total']['sales_order']    = 0; // Doanh số lên đơn
            $data_report['total']['not_call']       = 0; // Chưa tương tác

            // Lấy số điện thoại của sale_x đã được nhận.
            $contacts = $this->getServiceLocator()->get('Admin\Model\ContactTable')->report($this->_params, array('task' => 'date'))->toArray();
            $contacts_2 = $this->getServiceLocator()->get('Admin\Model\ContactTable')->report($this->_params, array('task' => 'join-contract'))->toArray();
            $sale_history_type = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-type')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'id'));

            foreach ($contacts_2 as $key => $value) {
                // Nếu người quản lý contact nằm trong danh sách nhân viên sale
                if (array_key_exists($value['user_id'], $data_report)) {
                    if ($value['contract_status_id'] != HUY_SALES) {
                        $data_report[$value['user_id']]['sales_order'] += $value['contract_price_total'];
                        $data_report['total']['sales_order'] += $value['contract_price_total'];
                    }
                }
            }

            foreach ($contacts as $key => $value){
                // Nếu người quản lý contact nằm trong danh sách nhân viên sale
                if (array_key_exists($value['user_id'], $data_report)){
                    if(!empty($value['marketer_id'])){
                        $data_report[$value['user_id']]['phone'] += 1;
                        $data_report['total']['phone'] += 1;
                    }

                    // Đếm số contact chưa được chăm sóc (số contact chưa phát sinh lịch sử chăm sóc chưa có ngày 'history_created')
                    if(empty($value['history_created'])){
                        $data_report[$value['user_id']]['not_call'] += 1;
                        $data_report['total']['not_call'] += 1;
                    }
                    else{
                        // Đếm số contact hủy.
                        $options = !empty($value['options']) ? unserialize($value['options']) : null;
                        if(!empty($options)){
                            $id_status = $sale_history_type[STATUS_CONTACT_CANCEL]; // id trạng thái hủy. ['huy']
                            //if($options['history_type_id'] == $id_status && $options['history_created_by'] == $value['user_id']){
                            if($options['history_type_id'] == $id_status){
                                $data_report[$value['user_id']]['cancel'] += 1;
                                $data_report['total']['cancel'] += 1;
                            }
                            else{
                                if(!empty($value['marketer_id'])) {
                                    $data_report[$value['user_id']]['called'] += 1;
                                    $data_report['total']['called'] += 1;
                                }
                            }
                        }
                    }

                    // Đếm số contact đã được chốt
                    if($value['latched'] > 0){
                        $data_report[$value['user_id']]['latched'] += 1;
                        $data_report['total']['latched'] += 1;
                    }
                    // Tính doanh số tạm tính của contact
                    $data_report[$value['user_id']]['sales_expected'] += $value['sales_expected'];
                    $data_report['total']['sales_expected'] += $value['sales_expected'];
                }
            }

            // Tham số bảng báo cáo
            foreach ($data_report as $key => $value){
                $percent = ($value['phone'] > 0 ? round($value['latched'] / $value['phone'] * 100, 2) : 0);
                $data_report[$key]['percent'] = $percent;
            }

            // sắp xếp theo doanh tổng doanh thu
            $key_sort = [];
            $i = 0;
            foreach ($data_report as $key => $value) {
                if($key != 'total'){
                    $key_sort[$i]['id'] = $key;
                    $key_sort[$i]['sales_expected'] = $data_report[$key]['sales_expected'];
                    $i++;
                }
            }
            for($i = 0; $i < count($key_sort) - 1; $i++){
                for($j = $i+1; $j < count($key_sort); $j++){
                    if($key_sort[$i]['sales_expected'] < $key_sort[$j]['sales_expected']){
                        $tm              = $key_sort[$i];
                        $key_sort[$i]    = $key_sort[$j];
                        $key_sort[$j]    = $tm;
                    }
                }
            }

            // Tham số bảng báo cáo
            $xhtmlItems = '';
            foreach ($key_sort as $key => $value){
                $xhtmlItems .= '<tr>
                                    <td class="text-bold">'.$data_report[$value['id']]['name'].'</td>
                                    <td class="mask_currency text-center">'.$data_report[$value['id']]['phone'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['cancel'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['called'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['latched'].'</td> 
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['sales_expected'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['sales_order'].'</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['percent'].'%</td>
                                    <td class="mask_currency text-right">'.$data_report[$value['id']]['not_call'].'</td>
                                </tr>';
            }
            // Hiển thị dòng tổng
            $xhtmlItems .= '<tr class="text-bold text-red">
                                    <td class="text-bold">'.$data_report['total']['name'].'</td>
                                    <td class="mask_currency text-center">'.$data_report['total']['phone'].'</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['cancel'].'</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['called'].'</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['latched'].'</td> 
                                    <td class="mask_currency text-right">'.$data_report['total']['sales_expected'].'</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['sales_order'].'</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['percent'].'%</td>
                                    <td class="mask_currency text-right">'.$data_report['total']['not_call'].'</td>
                                </tr>';


//            // Tham số bảng báo cáo
//            $xhtmlItems = '';
//            foreach ($data_report as $key => $value){
//                $percent   = ($value['phone'] > 0 ? round($value['latched'] / $value['phone'] * 100, 2) : 0);
//                $style_class = $key == 'total' ? 'text-bold text-red': '';
//                $xhtmlItems .= '<tr class="'.$style_class.'">
//                                    <td class="text-bold">'.$value['name'].'</td>
//                                    <td class="mask_currency text-center">'.$value['phone'].'</td>
//                                    <td class="mask_currency text-right">'.$value['cancel'].'</td>
//                                    <td class="mask_currency text-right">'.$value['latched'].'</td>
//                                    <td class="mask_currency text-right">'.$value['sales_expected'].'</td>
//                                    <td class="mask_currency text-right">'.$percent.'%</td>
//                                    <td class="mask_currency text-right">'.$value['not_call'].'</td>
//                                </tr>';
//            }


            $result['reportTable'] = '<thead>
                        				    <tr>
                            					<th width="180" rowspan="2" class="text-center">Tên NV Sale</th>
                            					<th width="150" rowspan="2" class="text-center">SĐT nhận</th>
                            					<th width="150" rowspan="2" class="text-center">SĐT hủy</th>
                            					<th width="150" rowspan="2" class="text-center">SĐT tư vấn</th>
                            					<th width="150" colspan="3" class="text-center">Tổng chốt</th>
                            					<th width="150" rowspan="2" class="text-center">% Tỉ lệ chốt</th>
                            					<th width="150" rowspan="2" class="text-center">Số chưa tương tác</th>
                        					</tr>
                        				    <tr>
                            					<th width="150" class="text-center">Số đơn</th>
                            					<th width="150" class="text-center">Doanh số tạm tính</th>
                            					<th width="150" class="text-center">Doanh số lên đơn</th>
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
            $ssFilter->report['sale_id']        = $ssFilter->report['sale_id'] ? $ssFilter->report['sale_id'] : '';

            // Set giá trị cho form
            $myForm	= new \Report\Form\Sales\Sales($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);

            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['saleGroup']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['caption']        = 'Báo cáo chia số';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Báo cáo sale 1: Báo cáo doanh thu sales
    public function sale1Action() {
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
                elseif (in_array(GROUP_SALES_LEADER, $permission_ids) || in_array(GROUP_MKT_LEADER, $permission_ids)){
                    $this->_params['data']['sale_branch_id'] = $curent_user['sale_branch_id'];
                    $this->_params['data']['sale_group_id'] = $curent_user['sale_group_id'];
                    if(in_array(GROUP_MKT_LEADER, $permission_ids)){
                        $this->_params['data']['sale_group_id'] = $curent_user['branch_sale_group_id'];
                    }
                }
                else{
                    $this->_params['data']['sale_id'] = $curent_user['id'];
                }
            }
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin']     = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']       = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_group_id']  = $this->_params['data']['sale_group_id'];
            $ssFilter->report['sale_id']        = $this->_params['data']['sale_id'];
            $ssFilter->report['product_cat_id'] = $this->_params['data']['product_cat_id'];

            $sales = $this->getServiceLocator()->get('Admin\Model\UserTable')->report($this->_params, array('task' => 'list-sale'));

            // Tạo mảng lưu báo cáo.
            $data_report = [];
            foreach ($sales as $key => $value) {
                $data_report[$value['id']]['name']           = $value['name'];
                $data_report[$value['id']]['sales_total']    = 0; // Tổng doanh số
                $data_report[$value['id']]['sales_sending']  = 0; // Doanh số Đang vận chuyển
                $data_report[$value['id']]['sales_success_new']  = 0; // Doanh thu - Thành công-mới
                $data_report[$value['id']]['sales_shipping_fee']  = 0; // Hỗ trợ ship
                $data_report[$value['id']]['sales_success']  = 0; // Doanh thu - Thành công-tổng
                $data_report[$value['id']]['deposit']        = 0; // Thanh toán trước
                $data_report[$value['id']]['sales_return']   = 0; // Hàng hoàn
                $data_report[$value['id']]['sales_refund']   = 0; // giảm trừ doanh thu
                $data_report[$value['id']]['cost_ads']       = 0; // Chi phí MKT
                $data_report[$value['id']]['cod_total']   = 0; // COD
                $data_report[$value['id']]['cost_capital']   = 0; // Giá vốn
            }
            $data_report['total']['name']           = "Tổng";
            $data_report['total']['sales_total']    = 0; // Tổng doanh số
            $data_report['total']['sales_sending']  = 0; // Doanh số Đang vận chuyển
            $data_report['total']['sales_success_new']  = 0; // Doanh thu - Thành công-mới
            $data_report['total']['sales_shipping_fee']  = 0; // tiền hỗ trợ shipp
            $data_report['total']['sales_success']  = 0; // Doanh thu - Thành công-tổng
            $data_report['total']['deposit']        = 0; // Thanh toán trước
            $data_report['total']['sales_return']   = 0; // Hàng hoàn
            $data_report['total']['sales_refund']   = 0; // giảm trừ doanh thu
            $data_report['total']['cost_ads']       = 0; // Chi phí MKT
            $data_report['total']['cod_total']      = 0; // COD
            $data_report['total']['cost_capital']   = 0; // Giá vốn

            // Lấy dữ liệu doanh số.
            $where_contract = array(
                'filter_date_begin'         => $ssFilter->report['date_begin'],
                'filter_date_end'           => $ssFilter->report['date_end'],
                'date_type'                 => "shipped_date",
                'filter_sales_status_id'    => "true",
            );
            $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report(array('ssFilter' => $where_contract), array('task' => 'join-contact'));
            foreach ($contracts as $key => $value){
                // Nếu người lên đơn nằm trong danh sách nhân viên sale.
                if (array_key_exists($value['user_id'], $data_report)) {
                    $data_report[$value['user_id']]['cod_total'] += $value['price_transport'];
                    $data_report['total']['cod_total'] += $value['price_transport'];

                    $data_report[$value['user_id']]['cost_ads'] += $value['cost_ads'];
                    $data_report['total']['cost_ads'] += $value['cost_ads'];

                    if ($ssFilter->report['product_cat_id']) {
                        if (!empty($value['options'])) {
                            $options = unserialize($value['options']);
                            if (count($options['product'])) {
                                foreach ($options['product'] as $k => $v) {
                                    if ($v['product_id'] == $ssFilter->report['product_cat_id']) {
                                        $data_report[$value['user_id']]['sales_total'] += $v['total'];
                                        $data_report['total']['sales_total'] += $v['total'];

                                        // Sales - Hủy sales
                                        if ($value['status_id'] == HUY_SALES) {
                                            $data_report[$value['user_id']]['sales_cancel_sale'] += $v['total'];
                                            $data_report['total']['sales_cancel_sale'] += $v['total'];
                                        }
                                        // Dục đơn - Đang vận chuyển
                                        if (in_array($value['ghtk_status'], [3,4,9,10,123,127,128,45,49,410])) {
                                            $data_report[$value['user_id']]['sales_sending'] += $v['total'];
                                            $data_report['total']['sales_sending'] += $v['total'];
                                        }
                                        // Dục đơn - hoàn
                                        if ($value['returned'] == 1 || in_array($value['ghtk_status'], [11,20,21])) {
                                            $data_report[$value['user_id']]['sales_return'] += $v['total'];
                                            $data_report['total']['sales_return'] += $v['total'];
                                        }
                                        // Dục đơn - Thành công
                                        if ($value['status_acounting_id'] == 'da-doi-soat') {
                                            $data_report[$value['user_id']]['sales_success_new'] += $v['total'];
                                            $data_report['total']['sales_success_new'] += $v['total'];

                                            $data_report[$value['user_id']]['sales_success'] += $v['total'];
                                            $data_report['total']['sales_success'] += $v['total'];
                                        }

                                        // Thanh toán trước
                                        $data_report[$value['user_id']]['deposit'] += $v['price_deposits'];
                                        $data_report['total']['deposit'] += $v['price_deposits'];
                                        // Giảm trừ doanh thu
                                        $data_report[$value['user_id']]['sales_refund'] += $v['price_reduce_sale'];
                                        $data_report['total']['sales_refund'] += $v['price_reduce_sale'];
                                        // Giá vốn
                                        $data_report[$value['user_id']]['cost_capital'] += $v['capital_default'];
                                        $data_report['total']['cost_capital'] += $v['capital_default'];
                                    }
                                }
                            }
                        }
                    }
                    else {
                        $data_report[$value['user_id']]['sales_total'] += $value['price_total'] - $value['vat'];
                        $data_report['total']['sales_total'] += $value['price_total'] - $value['vat'];

                        // Sales - Hủy sales
                        if ($value['status_id'] == HUY_SALES) {
                            $data_report[$value['user_id']]['sales_cancel_sale'] += $value['price_total'] - $value['vat'];
                            $data_report['total']['sales_cancel_sale'] += $value['price_total'] - $value['vat'];
                        }
                        // Giục đơn - Dang vận chuyển
                        if (in_array($value['ghtk_status'], [3,4,9,10,123,127,128,45,49,410])) {
                            $data_report[$value['user_id']]['sales_sending'] += $value['price_total'] - $value['vat'];
                            $data_report['total']['sales_sending'] += $value['price_total'] - $value['vat'];
                        }
                        // Dục đơn - hoàn
                        if ($value['returned'] == 1 || in_array($value['ghtk_status'], [11,20,21])) {
                            $data_report[$value['user_id']]['sales_return'] += $value['price_total'] - $value['vat'];
                            $data_report['total']['sales_return'] += $value['price_total'] - $value['vat'];
                        }
                        // Dục đơn - Thành công
                        if ($value['status_acounting_id'] == 'da-doi-soat' && $value['returned'] == 0) {
                            $data_report[$value['user_id']]['sales_success_new'] += $value['price_total'];
                            $data_report['total']['sales_success_new'] += $value['price_total'];

                            $data_report[$value['user_id']]['sales_success'] += $value['price_total'] - $value['vat'];
                            $data_report['total']['sales_success'] += $value['price_total'] - $value['vat'];
                        }

                        // Thanh toán trước
                        $data_report[$value['user_id']]['deposit'] += $value['price_deposits'];
                        $data_report['total']['deposit'] += $value['price_deposits'];
                        // Giảm trừ doanh thu
                        $data_report[$value['user_id']]['sales_refund'] += $value['price_reduce_sale'];
                        $data_report['total']['sales_refund'] += $value['price_reduce_sale'];

                        $data_report[$value['user_id']]['sales_shipping_fee'] += $value['shipping_fee'];
                        $data_report['total']['sales_shipping_fee'] += $value['shipping_fee'];

                        // Tính chi phí giá vốn và giảm giá
                        if (!empty($value['options'])) {
                            $options = unserialize($value['options']);
                            if (count($options['product'])) {
                                foreach ($options['product'] as $k => $v) {
                                    $data_report[$value['user_id']]['cost_capital'] += $v['cost'] * $v['numbers'];
                                    $data_report['total']['cost_capital'] += $v['cost'] * $v['numbers'];
                                }
                            }
                        }
                    }
                }
            }

            // Tính chi phí quảng cáo cho sales
//            $contracts_cost_ads = $this->getServiceLocator()->get('Admin\Model\FormDataTable')->report($this->_params, array('task' => 'list-item-shared'));
//            foreach ($contracts_cost_ads as $key => $value){
//                if(array_key_exists($value['sales_id'], $data_report)){
//                    $data_report[$value['sales_id']]['cost_ads'] +=  $value['cost_ads'];
//                    $data_report['total']['cost_ads'] +=  $value['cost_ads'];
//                }
//            }

            // Người có quyền hiển thị chi phí giá vốn, doanh thu thực tế
            $show_cost_capital = false;
            $curent_user = $this->_userInfo->getUserInfo();
            $permission_ids = explode(',', $curent_user['permission_ids']);
            if(in_array(SYSTEM, $permission_ids) || in_array(ADMIN, $permission_ids) || in_array(GDCN, $permission_ids) || in_array(MANAGER, $permission_ids)){
                $show_cost_capital = true;
            }

            // Tham số bảng báo cáo
            foreach ($data_report as $key => $value){
                $percent_target  = ($value['target'] > 0 ? round($value['sales_total'] / $value['target'] * 100, 2) : 0);
                $percent_return  = (($value['sales_total'] - $value['sales_cancel_sale']) > 0 ? round(($value['sales_return'] + $value['sales_refund']) / ($value['sales_total'] - $value['sales_cancel_sale']) * 100, 2) : 0);
                $revenue         = ($value['sales_success_new'] + $value['sales_success_add']) - $value['cost_capital'] - $value['cost_ads'] - $value['cod_total'];
                $tc              = $value['sales_success'] - $value['sales_refund'];
                $percent_cost_tc = ($tc > 0 ? round($value['cost_ads'] / $tc * 100, 2) : 0);

                $data_report[$key]['percent_target']  = $percent_target;
                $data_report[$key]['percent_return']  = $percent_return;
                $data_report[$key]['revenue']         = $revenue;
                $data_report[$key]['percent_cost_tc'] = $percent_cost_tc;
            }
            // sắp xếp theo doanh tổng doanh thu
            $key_sort = [];
            $i = 0;
            foreach ($data_report as $key => $value) {
                if($key != 'total'){
                    $key_sort[$i]['id'] = $key;
                    $key_sort[$i]['sales_success'] = $data_report[$key]['sales_success'];
                    $i++;
                }
            }
            for($i = 0; $i < count($key_sort) - 1; $i++){
                for($j = $i+1; $j < count($key_sort); $j++){
                    if($key_sort[$i]['sales_success'] < $key_sort[$j]['sales_success']){
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
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['sales_total'].'</td> <!--Tổng doanh số-->
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['sales_sending'].'</td> <!--Đang vận chuyển-->
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['sales_return'].'</td> <!--Hàng hoàn-->
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['sales_refund'].'</td> <!--Giảm trừ doanh thu-->
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['sales_shipping_fee'].'</td> <!--Hỗ trợ ship-->
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['sales_success_new'].'</td> <!--Mới + mua thêm-->
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['percent_return'].'%</td> <!--% Hoàn-->
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['cod_total'].'</td><!--COD-->
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['cost_ads'].'</td><!--Chi phí MKT-->
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['percent_cost_tc'].'%</td><!--% CPQC/Doanh Thu-->';
                if($show_cost_capital){
                    $xhtmlItems .= '<td class="mask_currency text-right">'.$data_report[$value['id']]['cost_capital'].'</td><!--Giá vốn-->
        						                    <td class="mask_currency text-right">'.$data_report[$value['id']]['revenue'].'</td><!--Điểm hòa vốn-->';
                }
                $xhtmlItems .=  '</tr>';
            }
            // Hiển thị dòng tổng tất cả.
            $xhtmlItems .= '<tr class="text-bold text-red">
        		                <th class="text-bold">'.$data_report['total']['name'].'</th> <!--Tên nhân viên-->
        						<td class="mask_currency text-right">'.$data_report['total']['sales_total'].'</td> <!--Tổng doanh số-->
        						<td class="mask_currency text-right">'.$data_report['total']['sales_sending'].'</td> <!--Đang vận chuyển-->
        						<td class="mask_currency text-right">'.$data_report['total']['sales_return'].'</td> <!--Hàng hoàn-->
        						<td class="mask_currency text-right">'.$data_report['total']['sales_refund'].'</td> <!--Giảm trừ doanh thu-->
        						<td class="mask_currency text-right">'.$data_report['total']['sales_shipping_fee'].'</td> <!--Hỗ trợ shipp-->
        						<td class="mask_currency text-right">'.$data_report['total']['sales_success_new'].'</td> <!--Mới + mua thêm-->
        						<td class="mask_currency text-right">'.$data_report['total']['percent_return'].'%</td> <!--% Hoàn-->
        						<td class="mask_currency text-right">'.$data_report['total']['cod_total'].'</td> <!--% COD -->
        						<td class="mask_currency text-right">'.$data_report['total']['cost_ads'].'</td><!--Chi phí MKT-->
        						<td class="mask_currency text-right">'.$data_report['total']['percent_cost_tc'].'%</td><!--% CPQC/Doanh Thu-->';
            if($show_cost_capital){
                $xhtmlItems .= '<td class="mask_currency text-right">'.$data_report['total']['cost_capital'].'</td><!--Giá vốn-->
                                                    <td class="mask_currency text-right">'.$data_report['total']['revenue'].'</td><!--Điểm hòa vốn-->';
            }
            $xhtmlItems .=  '</tr>';

            $cost_capital = '';
            if($show_cost_capital){
                $cost_capital .= '<th rowspan="2">Giá Vốn mặc định</th>
                            	  <th rowspan="2">Điểm hòa vốn </th>';
            }
            $result['reportTable'] = '<thead>
                        				    <tr>
                            					<th class="text-center  fix-head">Tên nhân viên</th>
                            					<th class="text-center">Tổng doanh số</th>
                            					<th class="text-center">Đang vận chuyển</th>
                            					<th class="text-center">Hàng hoàn</th>
                            					<th class="text-center">Giảm trừ doanh thu</th>
                            					<th class="text-center">Khách hỗ trợ ship</th>
                            					<th class="text-center">Tổng DS thành công</th>
                            					<th class="text-center">% Hoàn</th>
                            					<th class="text-center">COD</th>
                            					<th class="text-center">Chi phí MKT</th>
                            					<th class="text-center">% CPQC/Doanh Thu</th>
                            					'.$cost_capital.'
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
            $ssFilter->report['sale_id']        = $ssFilter->report['sale_id'] ? $ssFilter->report['sale_id'] : '';
            $ssFilter->report['product_cat_id'] = $ssFilter->report['product_cat_id'] ? $ssFilter->report['product_cat_id'] : '';

            // Lấy danh sách sản phẩm đưa vào bộ lọc
            $products = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/products?pageSize=100');
            $products = json_decode($products, true);
            if($products['total'] < $products['pageSize']){
                $product_data = \ZendX\Functions\CreateArray::create($products['data'], array('key' => 'id', 'value' => 'fullName'));
                // $category_data = \ZendX\Functions\CreateArray::create($products['data'], array('key' => 'categoryId', 'value' => 'categoryName'));
            }
            else{
                $total = $products['total'];
                $pageSize = $products['pageSize'];
                $pageTotal = (int)($total / $pageSize) + 1;
                $product_data = [];
                for ($index = 0; $index < $pageTotal; $index++) {
                    $currentItem = $index * $pageSize;
                    $products = $this->kiotviet_call(RETAILER, $this->kiotviet_token,
                        '/products?pageSize=100&currentItem=' . $currentItem);
                    $products = json_decode($products, true);
                    $product_data = array_merge($product_data, $products['data']);
                }
                $product_data = \ZendX\Functions\CreateArray::create($product_data, array('key' => 'id', 'value' => 'fullName'));
                // $category_data = \ZendX\Functions\CreateArray::create($product_data, array('key' => 'categoryId', 'value' => 'categoryName'));
            }
            $ssFilter->report['products'] = $product_data;

            // Set giá trị cho form
            $myForm	= new \Report\Form\Sales\Sales($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);

            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['saleGroup']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['caption']        = 'Báo cáo doanh thu sale';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Báo cáo sale 2: Báo cáo sale chi tiết
    public function sale2Action() {
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
                elseif (in_array(GROUP_SALES_LEADER, $permission_ids) || in_array(GROUP_MKT_LEADER, $permission_ids)){
                    $this->_params['data']['sale_branch_id'] = $curent_user['sale_branch_id'];
                    $this->_params['data']['sale_group_id'] = $curent_user['sale_group_id'];
                    if(in_array(GROUP_MKT_LEADER, $permission_ids)){
                        $this->_params['data']['sale_group_id'] = $curent_user['branch_sale_group_id'];
                    }
                }
                else{
                    $this->_params['data']['sale_id'] = $curent_user['id'];
                }
            }
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin']     = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']       = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_group_id']  = $this->_params['data']['sale_group_id'];
            $ssFilter->report['sale_id']        = $this->_params['data']['sale_id'];
            $ssFilter->report['product_cat_id'] = $this->_params['data']['product_cat_id'];

            $sales = $this->getServiceLocator()->get('Admin\Model\UserTable')->report($this->_params, array('task' => 'list-sale'));

            // Tạo mảng lưu báo cáo.
            $data_report = [];
            foreach ($sales as $key => $value) {
                $data_report[$value['id']]['name']     = $value['name'];
                $data_report[$value['id']]['phone']    = 0; // Nhận
                $data_report[$value['id']]['cancel']   = 0; // Hủy
                $data_report[$value['id']]['unheard']  = 0; // Không nghe
                $data_report[$value['id']]['advisory'] = 0; // Tư vấn
                $data_report[$value['id']]['think']    = 0; // Suy nghĩ

                $data_report[$value['id']]['new_contract'] = 0; // Số đơn
                $data_report[$value['id']]['new_sales']    = 0; // Doanh số

                $data_report[$value['id']]['old_phone']    = 0; //
                $data_report[$value['id']]['old_contract'] = 0; //
                $data_report[$value['id']]['old_sales']    = 0; //
                $data_report[$value['id']]['old_tlc']      = 0; //

                $data_report[$value['id']]['care_contract'] = 0; //
                $data_report[$value['id']]['care_sales']    = 0; //

                $data_report[$value['id']]['cross_contract'] = 0; //
                $data_report[$value['id']]['cross_sales']    = 0; //

                $data_report[$value['id']]['agency_contract'] = 0; //
                $data_report[$value['id']]['agency_sales']    = 0; //
            }
            $data_report['total']['name']     = "Tổng";
            $data_report['total']['phone']    = 0; // Nhận
            $data_report['total']['cancel']   = 0; // Hủy
            $data_report['total']['unheard']  = 0; // Không nghe
            $data_report['total']['advisory'] = 0; // Tư vấn
            $data_report['total']['think']    = 0; // Suy nghĩ

            $data_report['total']['new_contract'] = 0; // Số đơn
            $data_report['total']['new_sales']    = 0; // Doanh số

            $data_report['total']['old_phone']    = 0; //
            $data_report['total']['old_contract'] = 0; //
            $data_report['total']['old_sales']    = 0; //
            $data_report['total']['old_tlc']      = 0; //

            $data_report['total']['care_contract'] = 0; //
            $data_report['total']['care_sales']    = 0; //

            $data_report['total']['cross_contract'] = 0; //
            $data_report['total']['cross_sales']    = 0; //

            $data_report['total']['agency_contract'] = 0; //
            $data_report['total']['agency_sales']    = 0; //

            // Lấy số điện thoại của sale_x đã được nhận.
            $contacts = $this->getServiceLocator()->get('Admin\Model\ContactTable')->report($this->_params, array('task' => 'date'))->toArray();
            foreach ($contacts as $key => $value){
                // Nếu contact được phụ người trong danh sách nhân viên sale quản lý
                if (array_key_exists($value['user_id'], $data_report)){
                    $data_report[$value['user_id']]['phone'] += 1;
                    $data_report['total']['phone'] += 1;
                    // Đếm số contact chưa được chăm sóc (số contact chưa phát sinh lịch sử chăm sóc chưa có ngày 'history_created')
                    if(empty($value['history_created'])){
                        $data_report[$value['user_id']]['not_call'] += 1;
                        $data_report['total']['not_call'] += 1;
                    }

                    // Đếm số contact hủy.
                    $options = !empty($value['options']) ? unserialize($value['options']) : null;
                    if(!empty($options)){
                        $sale_history_type = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-type')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'id'));

                        // Số hủy
                        $id_status_cancel = $sale_history_type[STATUS_CONTACT_CANCEL];
                        if($options['history_type_id'] == $id_status_cancel && $options['history_created_by'] == $value['user_id']){
                            $data_report[$value['user_id']]['cancel'] += 1;
                            $data_report['total']['cancel'] += 1;
                        }

                        // Số không nghe
                        $id_status_unheard = $sale_history_type[STATUS_CONTACT_UNHEARD];
                        if($options['history_type_id'] == $id_status_unheard && $options['history_created_by'] == $value['user_id']){
                            $data_report[$value['user_id']]['unheard'] += 1;
                            $data_report['total']['unheard'] += 1;
                        }

                        // Số đã tư vấn
                        $id_status_advisory = $sale_history_type[STATUS_CONTACT_ADVISORY];
                        if($options['history_type_id'] == $id_status_advisory && $options['history_created_by'] == $value['user_id']){
                            $data_report[$value['user_id']]['advisory'] += 1;
                            $data_report['total']['advisory'] += 1;
                        }

                        // Số suy nghĩ
                        $id_status_think = $sale_history_type[STATUS_CONTACT_THINK];
                        if($options['history_type_id'] == $id_status_think && $options['history_created_by'] == $value['user_id']){
                            $data_report[$value['user_id']]['think'] += 1;
                            $data_report['total']['think'] += 1;
                        }
                    }
                }
            }

            // Lấy số điện thoại của sale_x đã được nhận.
            $contacts2 = $this->getServiceLocator()->get('Admin\Model\ContactTable')->report($this->_params, array('task' => 'history-date'))->toArray();
            foreach ($contacts2 as $key => $value){
                // Nếu contact được phụ người trong danh sách nhân viên sale quản lý
                if (array_key_exists($value['user_id'], $data_report)){
                    // Đếm số contact hủy.
                    $options = !empty($value['options']) ? unserialize($value['options']) : null;
                    if(!empty($options)){
                        // Số gọi lại
                        $history_created = substr($value['history_created'], 0, 10);
                        $created = substr($value['created'], 0, 10);
                        if($history_created != $created && $value['history_number'] > 1){
                            $data_report[$value['user_id']]['old_phone'] += 1;
                            $data_report['total']['old_phone'] += 1;
                        }
                    }
                }
            }

            // Lấy dữ liệu doanh số mới, cũ, chăm sóc, bán chéo.
            $where_contract = array(
                'filter_date_begin'         => $ssFilter->report['date_begin'],
                'filter_date_end'           => $ssFilter->report['date_end'],
                'date_type'           => "production_date",
                'filter_sales_status_id'    => 'yes',
                'filter_check_status_id'    => 'yes',
            );
            if(!empty($ssFilter->report['sale_id'])){
                $where_contract['sale_id'] = $ssFilter->report['sale_id'];
            }
            $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report(array('ssFilter' => $where_contract), array('task' => 'join-contact'));

            foreach ($contracts as $key => $value){
                if(!empty($value['user_id']) && array_key_exists($value['user_id'], $data_report)){
                    if($ssFilter->report['product_cat_id']){
                        if (!empty($value['options'])) {
                            $options = unserialize($value['options']);
                            if (count($options['product'])) {
                                foreach ($options['product'] as $k => $v) {
                                    if($v['product_id'] == $ssFilter->report['product_cat_id']) {
                                        if(!empty($v['sales_new']) && $value['contact_type'] != CONTACT_TYPE_MULTIL){
                                            $data_report[$value['user_id']]['new_contract'] += 1;
                                            $data_report['total']['new_contract'] += 1;

                                            $data_report[$value['user_id']]['new_sales']    += $v['sales_new'];
                                            $data_report['total']['new_sales']    += $v['sales_new'];
                                        }

                                        if(!empty($v['sales_old']) && $value['contact_type'] != CONTACT_TYPE_MULTIL){
                                            $data_report[$value['user_id']]['old_contract'] += 1;
                                            $data_report['total']['old_contract'] += 1;

                                            $data_report[$value['user_id']]['old_sales']    += $v['sales_old'];
                                            $data_report['total']['old_sales']    += $v['sales_old'];
                                        }

                                        if(!empty($v['sales_care']) && $value['contact_type'] != CONTACT_TYPE_MULTIL){
                                            $data_report[$value['user_id']]['care_contract'] += 1;
                                            $data_report['total']['care_contract'] += 1;

                                            $data_report[$value['user_id']]['care_sales']    += $v['sales_care'];
                                            $data_report['total']['care_sales']    += $v['sales_care'];
                                        }

//                                        if(!empty($v['sales_cross']) && $value['contact_type'] != CONTACT_TYPE_MULTIL){
//                                            $data_report[$value['user_id']]['cross_contract'] += 1;
//                                            $data_report['total']['cross_contract'] += 1;
//
//                                            $data_report[$value['user_id']]['cross_sales']    += $v['sales_cross'];
//                                            $data_report['total']['cross_sales']    += $v['sales_ross'];
//                                        }

                                        if($value['contact_type'] == CONTACT_TYPE_MULTIL){
                                            $data_report[$value['user_id']]['agency_contract'] += 1;
                                            $data_report['total']['agency_contract'] += 1;

                                            $data_report[$value['user_id']]['agency_sales']    += $v['total'];
                                            $data_report['total']['agency_sales']    += $v['total'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                    else {
                        if(!empty($value['sales_new']) && $value['contact_type'] != CONTACT_TYPE_MULTIL) {
                            $data_report[$value['user_id']]['new_contract'] += 1;
                            $data_report['total']['new_contract'] += 1;

                            $data_report[$value['user_id']]['new_sales']    += $value['sales_new'];
                            $data_report['total']['new_sales']    += $value['sales_new'];
                        }

                        if(!empty($value['sales_old']) && $value['contact_type'] != CONTACT_TYPE_MULTIL) {
                            $data_report[$value['user_id']]['old_contract'] += 1;
                            $data_report['total']['old_contract'] += 1;

                            $data_report[$value['user_id']]['old_sales']    += $value['sales_old'];
                            $data_report['total']['old_sales']    += $value['sales_old'];
                        }

                        if(!empty($value['sales_care']) && $value['contact_type'] != CONTACT_TYPE_MULTIL) {
                            $data_report[$value['user_id']]['care_contract'] += 1;
                            $data_report['total']['care_contract'] += 1;

                            $data_report[$value['user_id']]['care_sales']    += $value['sales_care'];
                            $data_report['total']['care_sales']    += $value['sales_care'];
                        }

//                        if(!empty($value['sales_cross']) && $value['contact_type'] != CONTACT_TYPE_MULTIL) {
//                            $data_report[$value['user_id']]['cross_contract'] += 1;
//                            $data_report['total']['cross_contract'] += 1;
//
//                            $data_report[$value['user_id']]['cross_sales']    += $value['sales_cross'];
//                            $data_report['total']['cross_sales']    += $value['sales_cross'];
//                        }

                        if($value['contact_type'] == CONTACT_TYPE_MULTIL){
                            $data_report[$value['user_id']]['agency_contract'] += 1;
                            $data_report['total']['agency_contract'] += 1;

                            $data_report[$value['user_id']]['agency_sales']    += $value['price_total'];
                            $data_report['total']['agency_sales']    += $value['price_total'];
                        }
                    }
                }
            }

            // Tham số bảng báo cáo
            foreach ($data_report as $key => $value){
                $new_tlc   = ($value['phone'] > 0 ? round($value['new_contract'] / $value['phone'] * 100, 2) : 0);
                $old_tlc   = ($value['old_phone'] > 0 ? round($value['old_contract'] / $value['old_phone'] * 100, 2) : 0);
                $total_contract = ($value['new_contract'] + $value['old_contract'] + $value['care_contract'] + $value['agency_contract'] + $value['cross_contract']);
                $total_sales = ($value['new_sales'] + $value['old_sales'] + $value['care_sales'] + $value['agency_sales'] + $value['cross_sales']);
                $tlc_sdttv = (($value['advisory'] + $value['think']) > 0 ? round(($value['new_contract'] + $value['old_contract']) / ($value['advisory'] + $value['think']) * 100, 2) : 0);
                $tlc_tt = ($value['phone'] > 0 ? round($total_contract / $value['phone'] * 100, 2) : 0);
                $value_contract = $total_contract > 0 ? (int)($total_sales / $total_contract) : 0;

                $data_report[$key]['new_tlc']        = $new_tlc;
                $data_report[$key]['old_tlc']        = $old_tlc;
                $data_report[$key]['total_contract'] = $total_contract;
                $data_report[$key]['total_sales']    = $total_sales;
                $data_report[$key]['tlc_sdttv']      = $tlc_sdttv;
                $data_report[$key]['tlc_tt']         = $tlc_tt;
                $data_report[$key]['value_contract'] = $value_contract;
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
                        $tm           = $key_sort[$i];
                        $key_sort[$i] = $key_sort[$j];
                        $key_sort[$j] = $tm;
                    }
                }
            }

            $xhtmlItems = '';
            foreach ($key_sort as $key => $value){
                $xhtmlItems .= '<tr>
        		                <th class="text-bold">'.$data_report[$value['id']]['name'].'</th>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['phone'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['cancel'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['unheard'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['advisory'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['think'].'</td>
        						
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['new_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['new_sales'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['new_tlc'].'%</td>
        						
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['old_phone'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['old_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['old_sales'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['old_tlc'].'%</td>
        						
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['care_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['care_sales'].'</td>
        						
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['agency_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['agency_sales'].'</td>
        						
        						<!--<td class="mask_currency text-right">'.$data_report[$value['id']]['cross_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['cross_sales'].'</td>-->
        						
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['total_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['total_sales'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['tlc_sdttv'].'%</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['tlc_tt'].'%</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['value_contract'].'</td>
        					</tr>';
            }
            // Hiển thị dòng tổng.
            $xhtmlItems .= '<tr class="text-bold text-red">
        		                <th class="text-bold">'.$data_report['total']['name'].'</th>
        						<td class="mask_currency text-right">'.$data_report['total']['phone'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['cancel'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['unheard'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['advisory'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['think'].'</td>
        						
        						<td class="mask_currency text-right">'.$data_report['total']['new_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['new_sales'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['new_tlc'].'%</td>
        						
        						<td class="mask_currency text-right">'.$data_report['total']['old_phone'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['old_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['old_sales'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['old_tlc'].'%</td>
        						
        						<td class="mask_currency text-right">'.$data_report['total']['care_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['care_sales'].'</td>
        						
        						<td class="mask_currency text-right">'.$data_report['total']['agency_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['agency_sales'].'</td>
        						
        						<!--<td class="mask_currency text-right">'.$data_report['total']['cross_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['cross_sales'].'</td>-->
        						
        						<td class="mask_currency text-right">'.$data_report['total']['total_contract'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['total_sales'].'</td>
        						<td class="mask_currency text-right">'.$data_report['total']['tlc_sdttv'].'%</td>
        						<td class="mask_currency text-right">'.$data_report['total']['tlc_tt'].'%</td>
        						<td class="mask_currency text-right">'.$data_report['total']['value_contract'].'</td>
        					</tr>';

            $result['reportTable'] = '<thead>
                        				    <tr>
                            					<th rowspan="2" class="text-center fix-head">Tên nhân viên</th>
                            					<th rowspan="2" class="text-center">SĐT nhận</th>
                            					<th rowspan="2" class="text-center">Số hủy</th>
                            					<th rowspan="2" class="text-center">Không nghe máy</th>
                            					<th rowspan="2" class="text-center">Đã tư vấn</th>
                            					<th rowspan="2" class="text-center">Số suy nghĩ</th>
                            					<th colspan="3" class="text-center">Chốt mới</th>
                            					<th colspan="4" class="text-center">Chốt cũ</th>
                            					<th colspan="2" class="text-center">Doanh số chăm sóc</th>
                            					<th colspan="2" class="text-center">Đại lý</th>
                            					<!--<th colspan="2" class="text-center">Doanh số bán chéo</th>-->
                            					<th colspan="2" class="text-center">Tổng</th>
                            					<th rowspan="2" class="text-center">TLC/SĐTTV</th>
                            					<th rowspan="2" class="text-center">TLC/TT</th>
                            					<th rowspan="2" class="text-center">Giá Trị / Đơn Hàng </th>
                        					</tr>
                        				    <tr>
                            					<th class="text-center">Số đơn</th>
                            					<th class="text-center">Doanh số</th>
                            					<th class="text-center">% Tỉ lệ chốt</th>
                            					
                            					<th class="text-center">SĐT Gọi Lại</th>
                            					<th class="text-center">Số Đơn </th>
                            					<th class="text-center">Doanh  Số </th>
                            					<th class="text-center">% Tỉ Lệ Chốt </th>
                            					
                            					<th class="text-center">Số Đơn </th>
                            					<th class="text-center">Doanh  Số </th>
                            					
                            					<th class="text-center">Số Đơn </th>
                            					<th class="text-center">Doanh  Số </th>
                            					
                            					<!--<th class="text-center">Số Đơn </th>
                            					<th class="text-center">Doanh  Số </th>-->
                            					
                            					<th class="text-center">Số Đơn </th>
                            					<th class="text-center">Doanh  Số </th>
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
            $ssFilter->report['sale_id']        = $ssFilter->report['sale_id'] ? $ssFilter->report['sale_id'] : '';
            $ssFilter->report['product_cat_id'] = $ssFilter->report['product_cat_id'] ? $ssFilter->report['product_cat_id'] : '';

            // Set giá trị cho form
            $myForm	= new \Report\Form\Sales\Sales($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);

            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['saleGroup']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['caption']        = 'Báo cáo sale chi tiết';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Báo cáo doanh thu sale cửa hàng.
    public function saleStoreAction() {
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
                elseif (in_array(GROUP_SALES_LEADER, $permission_ids) || in_array(GROUP_MKT_LEADER, $permission_ids)){
                    $this->_params['data']['sale_branch_id'] = $curent_user['sale_branch_id'];
                    $this->_params['data']['sale_group_id'] = $curent_user['sale_group_id'];
                    if(in_array(GROUP_MKT_LEADER, $permission_ids)){
                        $this->_params['data']['sale_group_id'] = $curent_user['branch_sale_group_id'];
                    }
                }
                else{
                    $this->_params['data']['sale_id'] = $curent_user['id'];
                }
            }
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin']     = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']       = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_group_id']  = $this->_params['data']['sale_group_id'];
            $ssFilter->report['sale_id']        = $this->_params['data']['sale_id'];
            $ssFilter->report['product_cat_id'] = $this->_params['data']['product_cat_id'];
            $this->_params['data']['sale-store-status'] = 'sales-store';

            $sales = $this->getServiceLocator()->get('Admin\Model\UserTable')->report($this->_params, array('task' => 'list-sale'));

            // Tạo mảng lưu báo cáo.
            $data_report = [];
            foreach ($sales as $key => $value) {
                $data_report[$value['id']]['name']           = $value['name'];
                $data_report[$value['id']]['target']         = 0; // Mục tiêu
                $data_report[$value['id']]['discount']       = 0; // Giảm giá
                $data_report[$value['id']]['sales_total']    = 0; // Tổng doanh số
                $data_report[$value['id']]['sales_cancel']   = 0; // Doanh số Hủy không sản xuất
                $data_report[$value['id']]['sales_not_send'] = 0; // Doanh số Hủy không gửi
                $data_report[$value['id']]['sales_sending']  = 0; // Doanh số Đang vận chuyển
                $data_report[$value['id']]['sales_success_new']  = 0; // Doanh thu - Thành công-mới
                $data_report[$value['id']]['sales_success_add']  = 0; // Doanh thu - Thành công-mua thêm
                $data_report[$value['id']]['sales_success']  = 0; // Doanh thu - Thành công
                $data_report[$value['id']]['sales_money']    = 0; // Đã nhận tiền
                $data_report[$value['id']]['deposit']        = 0; // Thanh toán trước
                $data_report[$value['id']]['sales_return']   = 0; // Hàng hoàn
                $data_report[$value['id']]['check_keep']     = 0; // giữ lại bưu điện
                $data_report[$value['id']]['sales_refund']   = 0; // giảm trừ doanh thu
                $data_report[$value['id']]['cost_ads']       = 0; // Chi phí MKT
                $data_report[$value['id']]['cod_total']   = 0; // COD
                $data_report[$value['id']]['cost_capital']   = 0; // Giá vốn
            }
            $data_report['total']['name']           = "Tổng";
            $data_report['total']['target']         = 0; // Mục tiêu
            $data_report['total']['discount']       = 0; // Giảm giá
            $data_report['total']['sales_total']    = 0; // Tổng doanh số
            $data_report['total']['sales_cancel']   = 0; // Doanh số Hủy không sản xuất
            $data_report['total']['sales_not_send'] = 0; // Doanh số Hủy không gửi
            $data_report['total']['sales_sending']  = 0; // Doanh số Đang vận chuyển
            $data_report['total']['sales_success_new']  = 0; // Doanh thu - Thành công-mới
            $data_report['total']['sales_success_add']  = 0; // Doanh thu - Thành công-mua thêm
            $data_report['total']['sales_success']  = 0; // Doanh thu - Thành công
            $data_report['total']['sales_money']    = 0; // Đã nhận tiền
            $data_report['total']['deposit']        = 0; // Thanh toán trước
            $data_report['total']['sales_return']   = 0; // Hàng hoàn
            $data_report['total']['check_keep']     = 0; // Giữ lại bưu điện
            $data_report['total']['sales_refund']   = 0; // giảm trừ doanh thu
            $data_report['total']['cost_ads']       = 0; // Chi phí MKT
            $data_report['total']['cod_total']      = 0; // COD
            $data_report['total']['cost_capital']   = 0; // Giá vốn

            // Lấy dữ mục tiêu sales.
            $where_report = array(
                'filter_type'       => 'sales_target',
                'filter_date_begin' => $ssFilter->report['date_begin'],
                'filter_date_end'   => $ssFilter->report['date_end'],
            );
            $sales_target = $this->getServiceLocator()->get('Admin\Model\TargetTable')->report(array('ssFilter' => $where_report), array('task' => 'list-item-type'));
            foreach ($sales_target as $key => $value){
                if(!empty($value['params']) && array_key_exists($value['user_id'], $data_report)){
                    $params = unserialize($value['params']);
                    $data_report[$value['user_id']]['target'] +=  str_replace(",","",$params['sales']);
                    $data_report['total']['target'] +=  str_replace(",","",$params['sales']);
                }
            }

            // Lấy dữ liệu doanh số.
            $where_contract = array(
                'filter_date_begin'         => $ssFilter->report['date_begin'],
                'filter_date_end'           => $ssFilter->report['date_end'],
                'status_store'              => 1,
//                'filter_sales_status_id'    => 'yes',
            );
            $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report(array('ssFilter' => $where_contract), array('task' => 'join-contact'));
            foreach ($contracts as $key => $value){
                // Nếu người lên đơn nằm trong danh sách nhân viên sale.
                if (array_key_exists($value['created_by'], $data_report)){
                    $data_report[$value['created_by']]['cod_total'] += $value['price_transport'];
                    $data_report['total']['cod_total'] += $value['price_transport'];
                    if($ssFilter->report['product_cat_id']){
                        if (!empty($value['options'])) {
                            $options = unserialize($value['options']);
                            if (count($options['product'])) {
                                foreach ($options['product'] as $k => $v) {
                                    if($v['product_id'] == $ssFilter->report['product_cat_id']) {
                                        $data_report[$value['created_by']]['sales_total']    += $v['total'];
                                        $data_report['total']['sales_total']    += $v['total'];

                                        // Sales - Hủy sales
                                        if ($value['status_id'] == HUY_SALES) {
                                            $data_report[$value['created_by']]['sales_cancel_sale'] += $v['total'];
                                            $data_report['total']['sales_cancel_sale'] += $v['total'];
                                        }
                                        // Sản xuất - Hủy sản xuất
                                        if ($value['production_department_type'] == STATUS_CONTRACT_PRODUCT_CANCEL) {
                                            $data_report[$value['created_by']]['sales_cancel'] += $v['total'];
                                            $data_report['total']['sales_cancel'] += $v['total'];
                                        }
                                        // SẢN XUẤT _ Hủy không gửi
                                        if ($value['production_department_type'] == STATUS_CONTRACT_PRODUCT_NOT_SEND) {
                                            $data_report[$value['created_by']]['sales_not_send'] += $v['total'];
                                            $data_report['total']['sales_not_send'] += $v['total'];
                                        }
                                        // Dục đơn - Đang vận chuyển
                                        if ($value['status_check_id'] == STATUS_CONTRACT_CHECK_SENDING || $value['status_check_id'] == STATUS_CONTRACT_CHECK_POST) {
                                            $data_report[$value['created_by']]['sales_sending'] += $v['total'];
                                            $data_report['total']['sales_sending'] += $v['total'];
                                        }
                                        // Dục đơn - hoàn
                                        if ($value['status_check_id'] == STATUS_CONTRACT_CHECK_RETURN) {
                                            $data_report[$value['created_by']]['sales_return'] += $v['total'];
                                            $data_report['total']['sales_return'] += $v['total'];
                                        }
                                        // Dục đơn - hoàn
                                        if ($value['status_check_id'] == STATUS_CONTRACT_CHECK_KEEP) {
                                            $data_report[$value['created_by']]['check_keep'] += $v['total'];
                                            $data_report['total']['check_keep'] += $v['total'];
                                        }
                                        // Dục đơn - Thành công
                                        if ($value['status_check_id'] == STATUS_CONTRACT_CHECK_SUCCESS) {
                                            if(empty($value['marketer_id'])){
                                                $data_report[$value['created_by']]['sales_success_add'] += $v['total'];
                                                $data_report['total']['sales_success_add'] += $v['total'];
                                            }
                                            else {
                                                if (!empty($value['contact_contract_time_success'])) {
                                                    $day_begin = strtotime($value['contact_contract_time_success']);
                                                    $day = date('Y-m-d H:i:s',$day_begin + $value['time_sales'] * 3600);
                                                    if ($day > $value['created']) {
                                                        $data_report[$value['created_by']]['sales_success_new'] += $v['total'];
                                                        $data_report['total']['sales_success_new'] += $v['total'];
                                                    } else {
                                                        $data_report[$value['created_by']]['sales_success_add'] += $v['total'];
                                                        $data_report['total']['sales_success_add'] += $v['total'];
                                                    }
                                                }
                                            }

                                            $data_report[$value['created_by']]['sales_success'] += $v['total'];
                                            $data_report['total']['sales_success'] += $v['total'];
                                        }
                                        // Kế toán - Đã nhận tiền
                                        if ($value['status_acounting_id'] == STATUS_CONTRACT_ACOUNTING_MONEY) {
                                            $data_report[$value['created_by']]['sales_money'] += $v['total'];
                                            $data_report['total']['sales_money'] += $v['total'];
                                        }

                                        // Thanh toán trước
                                        $data_report[$value['created_by']]['deposit'] += $v['price_deposits'];
                                        $data_report['total']['deposit'] += $v['price_deposits'];
                                        // Giảm trừ doanh thu
                                        $data_report[$value['created_by']]['sales_refund'] += $v['price_reduce_sale'];
                                        $data_report['total']['sales_refund'] += $v['price_reduce_sale'];
                                        // Giá vốn
//                                        $data_report[$value['created_by']]['cost_capital'] += $v['total_production'];
//                                        $data_report['total']['cost_capital'] += $v['total_production'];
                                        $data_report[$value['created_by']]['cost_capital'] += $v['capital_default'];
                                        $data_report['total']['cost_capital'] += $v['capital_default'];
                                        // Giảm giá
                                        $data_report[$value['created_by']]['discount']     += $v['sale_price'];
                                        $data_report['total']['discount']     += $v['sale_price'];
                                    }
                                }
                            }
                        }
                    }
                    else {
                        $data_report[$value['created_by']]['sales_total'] += $value['price_total'];
                        $data_report['total']['sales_total'] += $value['price_total'];

                        // Sales - Hủy sales
                        if ($value['status_id'] == HUY_SALES) {
                            $data_report[$value['created_by']]['sales_cancel_sale'] += $value['price_total'];
                            $data_report['total']['sales_cancel_sale'] += $value['price_total'];
                        }
                        // Sản xuất - Hủy sản xuất
                        if ($value['production_department_type'] == STATUS_CONTRACT_PRODUCT_CANCEL) {
                            $data_report[$value['created_by']]['sales_cancel'] += $value['price_total'];
                            $data_report['total']['sales_cancel'] += $value['price_total'];
                        }
                        // SẢN XUẤT - Hủy không gửi
                        if ($value['production_department_type'] == STATUS_CONTRACT_PRODUCT_NOT_SEND) {
                            $data_report[$value['created_by']]['sales_not_send'] += $value['price_total'];
                            $data_report['total']['sales_not_send'] += $value['price_total'];
                        }
                        // Giục đơn - Dang vận chuyển
                        if ($value['status_check_id'] == STATUS_CONTRACT_CHECK_SENDING || $value['status_check_id'] == STATUS_CONTRACT_CHECK_POST) {
                            $data_report[$value['created_by']]['sales_sending'] += $value['price_total'];
                            $data_report['total']['sales_sending'] += $value['price_total'];
                        }
                        // Dục đơn - hoàn
                        if ($value['status_check_id'] == STATUS_CONTRACT_CHECK_RETURN) {
                            $data_report[$value['created_by']]['sales_return'] += $value['price_total'];
                            $data_report['total']['sales_return'] += $value['price_total'];
                        }
                        // Dục đơn - hoàn
                        if ($value['status_check_id'] == STATUS_CONTRACT_CHECK_KEEP) {
                            $data_report[$value['created_by']]['check_keep'] += $value['price_total'];
                            $data_report['total']['check_keep'] += $value['price_total'];
                        }
                        // Dục đơn - Thành công
                        if ($value['status_check_id'] == STATUS_CONTRACT_CHECK_SUCCESS) {
                            if(empty($value['marketer_id'])){
                                $data_report[$value['created_by']]['sales_success_add'] += $value['price_total'];
                                $data_report['total']['sales_success_add'] += $value['price_total'];
                            }
                            else {
                                if (!empty($value['contact_contract_time_success'])) {
                                    $day_begin = strtotime($value['contact_contract_time_success']);
                                    $day = date('Y-m-d H:i:s',$day_begin + $value['time_sales'] * 3600);
                                    if ($day > $value['created']) {
                                        $data_report[$value['created_by']]['sales_success_new'] += $value['price_total'];
                                        $data_report['total']['sales_success_new'] += $value['price_total'];
                                    } else {
                                        $data_report[$value['created_by']]['sales_success_add'] += $value['price_total'];
                                        $data_report['total']['sales_success_add'] += $value['price_total'];
                                    }
                                }
                            }

                            $data_report[$value['created_by']]['sales_success'] += $value['price_total'];
                            $data_report['total']['sales_success'] += $value['price_total'];
                        }
                        // Kế toán - Đã nhận tiền
                        if ($value['status_acounting_id'] == STATUS_CONTRACT_ACOUNTING_MONEY) {
                            $data_report[$value['created_by']]['sales_money'] += $value['price_total'];
                            $data_report['total']['sales_money'] += $value['price_total'];
                        }

                        // Thanh toán trước
                        $data_report[$value['created_by']]['deposit'] += $value['price_deposits'];
                        $data_report['total']['deposit'] += $value['price_deposits'];
                        // Giảm trừ doanh thu
                        $data_report[$value['created_by']]['sales_refund'] += $value['price_reduce_sale'];
                        $data_report['total']['sales_refund'] += $value['price_reduce_sale'];

                        // Tính chi phí giá vốn và giảm giá
                        if (!empty($value['options'])) {
                            $options = unserialize($value['options']);
                            if (count($options['product'])) {
                                foreach ($options['product'] as $k => $v) {
//                                    $data_report[$value['created_by']]['cost_capital'] += $v['total_production'];
//                                    $data_report['total']['cost_capital'] += $v['total_production'];
                                    $data_report[$value['created_by']]['cost_capital'] += $v['capital_default'];
                                    $data_report['total']['cost_capital'] += $v['capital_default'];

                                    $data_report[$value['created_by']]['discount']     += $v['sale_price'];
                                    $data_report['total']['discount']     += $v['sale_price'];
                                }
                            }
                        }
                    }
                }
            }

            // Tính chi phí quảng cáo cho sales
            $contracts_cost_ads = $this->getServiceLocator()->get('Admin\Model\FormDataTable')->report($this->_params, array('task' => 'list-item-shared'));
            foreach ($contracts_cost_ads as $key => $value){
                if(array_key_exists($value['sales_id'], $data_report)){
                    $data_report[$value['sales_id']]['cost_ads'] +=  $value['cost_ads'];
                    $data_report['total']['cost_ads'] +=  $value['cost_ads'];
                }
            }

            // Người có quyền hiển thị chi phí giá vốn, doanh thu thực tế
            $show_cost_capital = false;
            $curent_user = $this->_userInfo->getUserInfo();
            $permission_ids = explode(',', $curent_user['permission_ids']);
            if(in_array(SYSTEM, $permission_ids) || in_array(ADMIN, $permission_ids) || in_array(GDCN, $permission_ids) || in_array(MANAGER, $permission_ids)){
                $show_cost_capital = true;
            }

            // Tham số bảng báo cáo
            foreach ($data_report as $key => $value){
                $percent_target  = ($value['target'] > 0 ? round($value['sales_total'] / $value['target'] * 100, 2) : 0);
                $percent_return  = (($value['sales_total'] - $value['sales_cancel_sale'] - $value['sales_cancel']) > 0 ? round(($value['sales_return'] + $value['sales_refund'] + $value['sales_not_send']) / ($value['sales_total'] - $value['sales_cancel_sale'] - $value['sales_cancel']) * 100, 2) : 0);
                $revenue         = $value['sales_money'] - $value['cost_capital'] - $value['cost_ads'] - $value['cod_total'];
                $tc              = $value['sales_success'] - $value['sales_refund'];
                $percent_cost_tc = ($tc > 0 ? round($value['cost_ads'] / $tc * 100, 2) : 0);

                $data_report[$key]['percent_target']  = $percent_target;
                $data_report[$key]['percent_return']  = $percent_return;
                $data_report[$key]['revenue']         = $revenue;
                $data_report[$key]['percent_cost_tc'] = $percent_cost_tc;
            }
            // sắp xếp theo doanh tổng doanh thu
            $key_sort = [];
            $i = 0;
            foreach ($data_report as $key => $value) {
                if($key != 'total'){
                    $key_sort[$i]['id'] = $key;
                    $key_sort[$i]['sales_success'] = $data_report[$key]['sales_success'];
                    $i++;
                }
            }
            for($i = 0; $i < count($key_sort) - 1; $i++){
                for($j = $i+1; $j < count($key_sort); $j++){
                    if($key_sort[$i]['sales_success'] < $key_sort[$j]['sales_success']){
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
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['target'].'</td> <!--Mục tiêu-->
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['percent_target'].'%</td> <!--% Mục tiêu-->
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['discount'].'</td> <!--Giảm giá-->
        						<td class="mask_currency text-right">'.($data_report[$value['id']]['sales_total'] - $data_report[$value['id']]['sales_cancel_sale']).'</td> <!--Tổng doanh số-->
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['sales_cancel_sale'].'</td> <!--Hủy sales-->
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['sales_cancel'].'</td> <!--Hủy không sản xuất-->
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['sales_not_send'].'</td> <!--Hủy không gửi-->
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['sales_sending'].'</td> <!--Đang vận chuyển-->
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['deposit'].'</td> <!--Thanh toán trước-->
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['sales_return'].'</td> <!--Hàng hoàn-->
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['check_keep'].'</td> <!--Giữ lại bưu điện-->
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['sales_refund'].'</td> <!--Giảm trừ doanh thu-->
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['sales_success_new'].'</td> <!--Thành công mới-->
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['sales_success_add'].'</td> <!--Thành công mua thêm-->
        						<td class="mask_currency text-right">'.($data_report[$value['id']]['sales_success_new'] + $data_report[$value['id']]['sales_success_add']).'</td> <!--Mới + mua thêm-->
        						<td class="mask_currency text-right">'.($data_report[$value['id']]['sales_success'] - $data_report[$value['id']]['sales_refund']).'</td> <!--Thành công-->
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['percent_return'].'%</td> <!--% Hoàn-->
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['cod_total'].'</td><!--COD-->
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['cost_ads'].'</td><!--Chi phí MKT-->
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['percent_cost_tc'].'%</td><!--% CPQC/Doanh Thu-->';
                if($show_cost_capital){
                    $xhtmlItems .= '<td class="mask_currency text-right">'.$data_report[$value['id']]['cost_capital'].'</td><!--Giá vốn-->
        						                    <td class="mask_currency text-right">'.$data_report[$value['id']]['revenue'].'</td><!--Điểm hòa vốn-->';
                }
                $xhtmlItems .=  '</tr>';
            }
            // Hiển thị dòng tổng tất cả.
            $xhtmlItems .= '<tr class="text-bold text-red">
        		                <th class="text-bold">'.$data_report['total']['name'].'</th> <!--Tên nhân viên-->
        						<td class="mask_currency text-right">'.$data_report['total']['target'].'</td> <!--Mục tiêu-->
        						<td class="mask_currency text-right">'.$data_report['total']['percent_target'].'%</td> <!--% Mục tiêu-->
        						<td class="mask_currency text-right">'.$data_report['total']['discount'].'</td> <!--Giảm giá-->
        						<td class="mask_currency text-right">'.($data_report['total']['sales_total'] - $data_report['total']['sales_cancel_sale']).'</td> <!--Tổng doanh số-->
        						<td class="mask_currency text-right">'.$data_report['total']['sales_cancel_sale'].'</td> <!--Hủy sale-->
        						<td class="mask_currency text-right">'.$data_report['total']['sales_cancel'].'</td> <!--Hủy không sản xuất-->
        						<td class="mask_currency text-right">'.$data_report['total']['sales_not_send'].'</td> <!--Hủy không gửi-->
        						<td class="mask_currency text-right">'.$data_report['total']['sales_sending'].'</td> <!--Đang vận chuyển-->
        						<td class="mask_currency text-right">'.$data_report['total']['deposit'].'</td> <!--Thanh toán trước-->
        						<td class="mask_currency text-right">'.$data_report['total']['sales_return'].'</td> <!--Hàng hoàn-->
        						<td class="mask_currency text-right">'.$data_report['total']['check_keep'].'</td> <!--Giữ lại bưu điện-->
        						<td class="mask_currency text-right">'.$data_report['total']['sales_refund'].'</td> <!--Giảm trừ doanh thu-->
        						<td class="mask_currency text-right">'.$data_report['total']['sales_success_new'].'</td> <!--Thành công mới-->
        						<td class="mask_currency text-right">'.$data_report['total']['sales_success_add'].'</td> <!--Thành công mua thêm-->
        						<td class="mask_currency text-right">'.($data_report['total']['sales_success_new'] + $data_report[$value['id']]['sales_success_add']).'</td> <!--Mới + mua thêm-->
        						<td class="mask_currency text-right">'.($data_report['total']['sales_success'] - $data_report['total']['sales_refund']).'</td> <!--Thành công-->
        						<td class="mask_currency text-right">'.$data_report['total']['percent_return'].'%</td> <!--% Hoàn-->
        						<td class="mask_currency text-right">'.$data_report['total']['cod_total'].'</td> <!--% COD -->
        						<td class="mask_currency text-right">'.$data_report['total']['cost_ads'].'</td><!--Chi phí MKT-->
        						<td class="mask_currency text-right">'.$data_report['total']['percent_cost_tc'].'%</td><!--% CPQC/Doanh Thu-->';
            if($show_cost_capital){
                $xhtmlItems .= '<td class="mask_currency text-right">'.$data_report['total']['cost_capital'].'</td><!--Giá vốn-->
                                                    <td class="mask_currency text-right">'.$data_report['total']['revenue'].'</td><!--Điểm hòa vốn-->';
            }
            $xhtmlItems .=  '</tr>';

            $cost_capital = '';
            if($show_cost_capital){
                $cost_capital .= '<th rowspan="2">Giá Vốn mặc định</th>
                            	  <th rowspan="2">Điểm hòa vốn </th>';
            }
            $result['reportTable'] = '<thead>
                        				    <tr>
                            					<th class="text-center  fix-head">Tên nhân viên</th>
                            					<th class="text-center">Mục tiêu</th>
                            					<th class="text-center">% Mục tiêu</th>
                            					<th class="text-center">Giảm giá</th>
                            					<th class="text-center">Tổng doanh số</th>
                            					<th class="text-center">Hủy sales</th>
                            					<th class="text-center">Hủy không sản xuất</th>
                            					<th class="text-center">Hủy đã sản xuất</th> <!-- Hủy không gửi -->
                            					<th class="text-center">Đang vận chuyển</th>
                            					<th class="text-center">Thanh toán trước</th>
                            					<th class="text-center">Hàng hoàn</th>
                            					<th class="text-center">Giữ lại bưu điện</th>
                            					<th class="text-center">Giảm trừ doanh thu</th>
                            					<th class="text-center">DS mới thành công</th>
                            					<th class="text-center">DS chăm sóc thành công</th>
                            					<th class="text-center">Tổng DS thành công</th>
                            					<th class="text-center">Thành công</th> <!-- Doanh thu -->
                            					<th class="text-center">% Hoàn</th>
                            					<th class="text-center">COD</th>
                            					<th class="text-center">Chi phí MKT</th>
                            					<th class="text-center">% CPQC/Doanh Thu</th>
                            					'.$cost_capital.'
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
            $ssFilter->report['sale_id']        = $ssFilter->report['sale_id'] ? $ssFilter->report['sale_id'] : '';
            $ssFilter->report['product_cat_id'] = $ssFilter->report['product_cat_id'] ? $ssFilter->report['product_cat_id'] : '';

            // Set giá trị cho form
            $myForm	= new \Report\Form\Sales\Sales($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);

            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['saleGroup']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['caption']        = 'Báo cáo doanh thu sale';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Báo cáo sale 3: Báo cáo doanh thu sales
    public function sale3Action() {
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
                elseif (in_array(GROUP_SALES_LEADER, $permission_ids) || in_array(GROUP_MKT_LEADER, $permission_ids)){
                    $this->_params['data']['sale_branch_id'] = $curent_user['sale_branch_id'];
                    $this->_params['data']['sale_group_id'] = $curent_user['sale_group_id'];
                    if(in_array(GROUP_MKT_LEADER, $permission_ids)){
                        $this->_params['data']['sale_group_id'] = $curent_user['branch_sale_group_id'];
                    }
                }
                else{
                    $this->_params['data']['sale_id'] = $curent_user['id'];
                }
            }
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin']     = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']       = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_group_id']  = $this->_params['data']['sale_group_id'];
            $ssFilter->report['sale_id']        = $this->_params['data']['sale_id'];
            $ssFilter->report['product_group_id'] = $this->_params['data']['product_group_id'];

            $sales = $this->getServiceLocator()->get('Admin\Model\UserTable')->report($this->_params, array('task' => 'list-sale'));

            // Tạo mảng lưu báo cáo.
            $data_report = [];
            foreach ($sales as $key => $value) {
                $data_report[$value['id']]['name']              = $value['name'];
                $data_report[$value['id']]['sales_total']       = 0; // Tổng doanh số
                $data_report[$value['id']]['da-lay-hang']       = 0; // Doanh số Đang vận chuyển
                $data_report[$value['id']]['dang-giao-hang']    = 0; // Doanh thu - Thành công-mới
                $data_report[$value['id']]['giam-tru-doanh-thu']= 0; // giảm trừ doanh thu
                $data_report[$value['id']]['hang-hoan']         = 0; // Hàng hoàn
                $data_report[$value['id']]['sale_new']          = 0; // Doanh số mới thành công
                $data_report[$value['id']]['sales_care']        = 0; // doanh số chăm sóc thành công
                $data_report[$value['id']]['cod_total']         = 0; // COD
                $data_report[$value['id']]['cost_ads']          = 0; // Chi phí MKT
                $data_report[$value['id']]['cost_capital']      = 0; // Giá vốn
            }
            $data_report['total']['name']               = "Tổng";
            $data_report['total']['sales_total']        = 0; // Tổng doanh số
            $data_report['total']["da-lay-hang"]        = 0; // Doanh số Đã lấy hàng
            $data_report['total']['dang-giao-hang']     = 0; // Doanh số đang giao hàng
            $data_report['total']['giam-tru-doanh-thu'] = 0; // tiền hỗ trợ shipp
            $data_report['total']['hang-hoan']          = 0; // Hàng hoàn
            $data_report['total']['sales_new']          = 0; // Doanh số mới thành công
            $data_report['total']['sales_care']         = 0; // doanh số chăm sóc thành công
            $data_report['total']['cod_total']      = 0; // COD
            $data_report['total']['cost_ads']       = 0; // Chi phí MKT
            $data_report['total']['cost_capital']   = 0; // Giá vốn

            // Lấy dữ liệu doanh số.
            $where_contract = array(
                'filter_date_begin'         => $ssFilter->report['date_begin'],
                'filter_date_end'           => $ssFilter->report['date_end'],
                'filter_product_group_id'   => $ssFilter->report['product_group_id'],
                'date_type'                 => "shipped_date",
//                'filter_sales_status_id'    => "true",
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
                if (array_key_exists($value['user_id'], $data_report)) {
                    $data_report[$value['user_id']]['cod_total'] += $value['price_transport'] + $value['ship_ext'];
                    $data_report['total']['cod_total'] += $value['price_transport'] + $value['ship_ext'];

//                    $data_report[$value['user_id']]['cost_ads'] += $value['contact_cost_ads'];
//                    $data_report['total']['cost_ads'] += $value['contact_cost_ads'];

                    $data_report[$value['user_id']]['sales_total'] += $value['price_total'];
                    $data_report['total']['sales_total'] += $value['price_total'];

                    // Sales - Hủy sales
                    if ($value['status_id'] == HUY_SALES) {
                        $data_report[$value['user_id']]['sales_cancel_sale'] += $value['price_total'] - $value['vat'];
                        $data_report['total']['sales_cancel_sale'] += $value['price_total'] - $value['vat'];
                    }
                    // Giục đơn - đã lấy hàng
                    if (in_array($value['ghtk_status'], $dalayhang_arr)) {
                        $data_report[$value['user_id']]['da-lay-hang'] += $value['price_total'] - $value['vat'];
                        $data_report['total']['da-lay-hang'] += $value['price_total'] - $value['vat'];
                    }
                    // Giục đơn - đang giao hàng
                    if (in_array($value['ghtk_status'], $danggiaohang_arr)) {
                        $data_report[$value['user_id']]['dang-giao-hang'] += $value['price_total'] - $value['vat'];
                        $data_report['total']['dang-giao-hang'] += $value['price_total'] - $value['vat'];
                    }
                    // Giảm trừ doanh thu
                    $data_report[$value['user_id']]['giam-tru-doanh-thu'] += $value['price_reduce_sale'];
                    $data_report['total']['giam-tru-doanh-thu'] += $value['price_reduce_sale'];
                    // Dục đơn - hoàn
                    if (in_array($value['ghtk_status'], $hanghoan_arr)) {
                        $data_report[$value['user_id']]['hang-hoan'] += $value['price_total'] - $value['vat'];
                        $data_report['total']['hang-hoan'] += $value['price_total'] - $value['vat'];
                    }

                    # nhưng đơn lên trong vòng 144 giờ từ khi lên đơn đầu tiên thì tính doanh số mới sau thì tính doanh số chăm sóc
                    $price_paid = $value['price_paid'] < $value['price_total'] ? $value['price_paid'] : $value['price_total'];
                    if (in_array($value['ghtk_status'], $thanhcong_arr)) {
//                        if ($date_format->diff($value['contact_contract_first_date'], $value['created'], 'hour') < 144 && !empty($value['marketer_id'])) {
//                            $data_report[$value['user_id']]['sales_new'] += $value['price_total'] - $value['price_reduce_sale'];
//                            $data_report['total']['sales_new'] += $value['price_total'] - $value['price_reduce_sale'];
//                        } else {
//                            $data_report[$value['user_id']]['sales_care'] += $value['price_total'] - $value['price_reduce_sale'];
//                            $data_report['total']['sales_care'] += $value['price_total'] - $value['price_reduce_sale'];
//                        }

                        # nhưng đơn lên trong vòng 48h giờ từ khi đơn đầu tiên thành công thì tính doanh số mới sau thì tính doanh số chăm sóc
                        if (empty($value['marketer_id'])) {
                            $data_report[$value['user_id']]['sales_care'] += $value['price_total'] - $value['price_reduce_sale'];;
                            $data_report['total']['sales_care'] += $value['price_total'] - $value['price_reduce_sale'];;
                        } else {
                            $day_begin = strtotime($value['contact_contract_time_success']);
                            $day = date('Y-m-d H:i:s', $day_begin + 48*3600);
                            if ($day > $value['created']) {
                                $data_report[$value['user_id']]['sales_new'] += $value['price_total'] - $value['price_reduce_sale'];;
                                $data_report['total']['sales_new'] += $value['price_total'] - $value['price_reduce_sale'];;
                            } else {
                                $data_report[$value['user_id']]['sales_care'] += $value['price_total'] - $value['price_reduce_sale'];;
                                $data_report['total']['sales_care'] += $value['price_total'] - $value['price_reduce_sale'];;
                            }
                        }
                    }

                    // Thanh toán trước
                    $data_report[$value['user_id']]['deposit'] += $value['price_deposits'];
                    $data_report['total']['deposit'] += $value['price_deposits'];

                    $data_report[$value['user_id']]['sales_shipping_fee'] += $value['shipping_fee'];
                    $data_report['total']['sales_shipping_fee'] += $value['shipping_fee'];

                    // Tính chi phí giá vốn và giảm giá
                    if (!empty($value['options'])) {
                        $options = unserialize($value['options']);
                        if (count($options['product'])) {
                            foreach ($options['product'] as $k => $v) {
                                $data_report[$value['user_id']]['cost_capital'] += $v['cost_new'] * $v['numbers'];
                                $data_report['total']['cost_capital'] += $v['cost_new'] * $v['numbers'];
                            }
                        }
                    }
                }
            }
            $product_group_condition = !empty($ssFilter->report['product_group_id']) ? " and product_group_id = '".$ssFilter->report['product_group_id']."' " : '';
            $sale_id_condition = !empty($ssFilter->report['sale_id']) ? " and user_id = '".$ssFilter->report['sale_id']."' " : '';

            # lấy chi phí quảng cáo cho khi liên hệ chia cho sale
            $sql_select = "SELECT user_id, sum(cost_ads) as cost_ads FROM ".TABLE_CONTACT." WHERE date >= '".$date_format->formatToData($ssFilter->report['date_begin'], 'Y-m-d')." 00:00:00'
            and date <= '".$date_format->formatToData($ssFilter->report['date_end'], 'Y-m-d')." 23:59:59' ".$product_group_condition . $sale_id_condition . " GROUP BY user_id;";
            $contact_cost_ads = $this->getServiceLocator()->get('Admin\Model\ContactTable')->report(array('sql' => $sql_select), array('task' => 'query'));
            foreach($contact_cost_ads as $key => $value){
                if (array_key_exists($value['user_id'], $data_report)) {
                    $data_report[$value['user_id']]['cost_ads'] += $value['cost_ads'];
                    $data_report['total']['cost_ads'] += $value['cost_ads'];
                }
            }

            // Người có quyền hiển thị chi phí giá vốn, doanh thu thực tế
            $show_cost_capital = false;
            $curent_user = $this->_userInfo->getUserInfo();
            $permission_ids = explode(',', $curent_user['permission_ids']);
            if(in_array(SYSTEM, $permission_ids) || in_array(ADMIN, $permission_ids) || in_array(GDCN, $permission_ids) || in_array(MANAGER, $permission_ids)){
                $show_cost_capital = true;
            }

            // Tham số bảng báo cáo
            foreach ($data_report as $key => $value){
                $sale_tm = $value['sales_total'] - $value['sales_cancel_sale'];
                $sales_new_care  = $value['sales_new'] + $value['sales_care'];
                $percent_target  = ($value['target'] > 0 ? round($value['sales_total'] / $value['target'] * 100, 2) : 0);
                $percent_return  = ($sale_tm > 0 ? round(($value['giam-tru-doanh-thu'] + $value['hang-hoan']) / $sale_tm * 100, 2) : 0);
                $revenue         = $sales_new_care - $value['cod_total'] - $value['cost_ads'] - $value['cost_capital'];
                $percent_cost_tc = ($value['sales_new'] > 0 ? round($value['cost_ads'] / $value['sales_new'] * 100, 2) : 0);

                $data_report[$key]['percent_target']  = $percent_target;
                $data_report[$key]['percent_return']  = $percent_return;
                $data_report[$key]['revenue']         = $revenue;
                $data_report[$key]['percent_cost_tc'] = $percent_cost_tc;
                $data_report[$key]['sales_new_care']  = $sales_new_care;
            }
            // sắp xếp theo doanh tổng doanh thu
            $key_sort = [];
            $i = 0;
            foreach ($data_report as $key => $value) {
                if($key != 'total'){
                    $key_sort[$i]['id'] = $key;
                    $key_sort[$i]['sales_success'] = $data_report[$key]['sales_success'];
                    $i++;
                }
            }
            for($i = 0; $i < count($key_sort) - 1; $i++){
                for($j = $i+1; $j < count($key_sort); $j++){
                    if($key_sort[$i]['sales_success'] < $key_sort[$j]['sales_success']){
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
        						<td class="mask_currency text-right">'.($data_report[$value['id']]['sales_total'] - $data_report[$value['id']]['sales_cancel_sale']).'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['da-lay-hang'].'</td> 
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['dang-giao-hang'].'</td> 
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['giam-tru-doanh-thu'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['hang-hoan'].'</td> 
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['sales_new'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['sales_care'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['sales_new_care'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['percent_return'].'%</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['cod_total'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['cost_ads'].'</td>
        						<td class="mask_currency text-right">'.$data_report[$value['id']]['percent_cost_tc'].'%</td>';
                if($show_cost_capital){
                    $xhtmlItems .= '<td class="mask_currency text-right">'.$data_report[$value['id']]['cost_capital'].'</td><!--Giá vốn-->
        						                    <td class="mask_currency text-right">'.$data_report[$value['id']]['revenue'].'</td><!--Điểm hòa vốn-->';
                }
                $xhtmlItems .=  '</tr>';
            }
            // Hiển thị dòng tổng tất cả.
            $xhtmlItems .= '<tr class="text-bold text-red">
        		                <th class="text-bold">'.$data_report['total']['name'].'</th> <!--Tên nhân viên-->
        						<td class="mask_currency text-right">'.($data_report['total']['sales_total'] - $data_report['total']['sales_cancel_sale']).'</td> <!--Tổng doanh số-->
        						<td class="mask_currency text-right">'.$data_report['total']['da-lay-hang'].'</td> 
        						<td class="mask_currency text-right">'.$data_report['total']['dang-giao-hang'].'</td> 
        						<td class="mask_currency text-right">'.$data_report['total']['giam-tru-doanh-thu'].'</td> 
        						<td class="mask_currency text-right">'.$data_report['total']['hang-hoan'].'</td> 
        						<td class="mask_currency text-right">'.$data_report['total']['sales_new'].'</td> <!--Mới -->
        						<td class="mask_currency text-right">'.$data_report['total']['sales_care'].'</td> <!--% chăm sóc-->
        						<td class="mask_currency text-right">'.$data_report['total']['sales_new_care'].'</td> <!--% tổng ds-->
        						<td class="mask_currency text-right">'.$data_report['total']['percent_return'].'%</td> <!--% haonf -->
        						<td class="mask_currency text-right">'.$data_report['total']['cod_total'].'</td> <!--% COD -->
        						<td class="mask_currency text-right">'.$data_report['total']['cost_ads'].'</td><!--Chi phí MKT-->
        						<td class="mask_currency text-right">'.$data_report['total']['percent_cost_tc'].'%</td><!--% CPQC/Doanh Thu-->';
            if($show_cost_capital){
                $xhtmlItems .= '<td class="mask_currency text-right">'.$data_report['total']['cost_capital'].'</td><!--Giá vốn-->
                                <td class="mask_currency text-right">'.$data_report['total']['revenue'].'</td><!--Điểm hòa vốn-->';
            }
            $xhtmlItems .=  '</tr>';

            $cost_capital = '';
            if($show_cost_capital){
                $cost_capital .= '<th rowspan="2">Giá Vốn mặc định</th>
                            	  <th rowspan="2">Điểm hòa vốn </th>';
            }
            $result['reportTable'] = '<thead>
                        				    <tr>
                            					<th class="text-center  fix-head">Tên nhân viên</th>
                            					<th class="text-center">Tổng doanh số</th>
                            					<th class="text-center">Bưu điện đã lấy hàng</th>
                            					<th class="text-center">Đang vận chuyển + đang giao hàng</th>
                            					<th class="text-center">Giảm trừ doanh thu</th>
                            					<th class="text-center">Hàng hoàn</th>
                            					<th class="text-center">DS mới thành công</th>
                            					<th class="text-center">DS chăm sóc thành công</th>
                            					<th class="text-center">Tổng DS thành công</th>
                            					<th class="text-center">% Hoàn</th>
                            					<th class="text-center">COD</th>
                            					<th class="text-center">Chi phí MKT</th>
                            					<th class="text-center">% CPQC/Doanh Thu</th>
                            					'.$cost_capital.'
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
            $ssFilter->report['sale_id']        = $ssFilter->report['sale_id'] ? $ssFilter->report['sale_id'] : '';
            $ssFilter->report['product_group_id'] = $ssFilter->report['product_group_id'] ? $ssFilter->report['product_group_id'] : '';

            // Set giá trị cho form
            $myForm	= new \Report\Form\Sales\Sales($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);

            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['saleGroup']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['caption']        = 'Báo cáo doanh thu sale';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }
}




















