<?php

namespace Report\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class CareController extends ActionController {
    
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

    // Báo cáo chăm sóc 1
    public function care1Action() {
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
                elseif (in_array(GROUP_SALES_LEADER, $permission_ids)){
                    $this->_params['data']['sale_branch_id'] = $curent_user['sale_branch_id'];
                    $this->_params['data']['sale_group_id'] = $curent_user['sale_group_id'];
                }
                else{
                    $this->_params['data']['care_id'] = $curent_user['id'];
                }
            }
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin']     = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']       = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_group_id']  = $this->_params['data']['sale_group_id'];
            $ssFilter->report['care_id']        = $this->_params['data']['care_id'];
            $ssFilter->report['product_cat_id'] = $this->_params['data']['product_cat_id'];

            // Xác định ngày tháng tìm kiếm
            $date       = new \ZendX\Functions\Date();
            $day_begin  = strtotime($date->formatToData($ssFilter->report['date_begin']));
            $day_end    = strtotime($date->formatToData($ssFilter->report['date_end']));
            $number_day = abs($day_end - $day_begin) / 86400;

            $sales = $this->getServiceLocator()->get('Admin\Model\UserTable')->report($this->_params, array('task' => 'list-care'));

            // Tạo mảng lưu báo cáo.
            $data_report = [];
            for ($i = 0; $i <= $number_day; $i++) {
                $data_report['total'] = null;
                foreach ($sales as $key => $value) {
                    $data_report['total'][$value['id']]['name'] = $value['name'];
                }
                $data_report['total']['total']['name'] = 'Tổng tất cả';

                $day = date('Y-m-d', $day_begin + $i*86400);
                foreach ($sales as $key => $value) {
                    $data_report[$day][$value['id']]['name']              = $value['name'];
                    $data_report[$day][$value['id']]['target']            = 0; // Mục tiêu
                    $data_report[$day][$value['id']]['discount']          = 0; // Giảm giá
                    $data_report[$day][$value['id']]['sales_total']       = 0; // Tổng doanh số
                    $data_report[$day][$value['id']]['sales_cancel_sale'] = 0; // Hủy sales
                    $data_report[$day][$value['id']]['sales_cancel']      = 0; // Doanh số Hủy không sản xuất
                    $data_report[$day][$value['id']]['sales_not_send']    = 0; // Doanh số Hủy không gửi
                    $data_report[$day][$value['id']]['sales_sending']     = 0; // Doanh số Đang vận chuyển
                    $data_report[$day][$value['id']]['sales_success']     = 0; // Doanh thu - Thành công
                    $data_report[$day][$value['id']]['sales_money']       = 0; // Đã nhận tiền
                    $data_report[$day][$value['id']]['deposit']           = 0; // Thanh toán trước
                    $data_report[$day][$value['id']]['sales_return']      = 0; // Hàng hoàn
                    $data_report[$day][$value['id']]['check_keep']        = 0; // Giữ lại bưu điện
                    $data_report[$day][$value['id']]['sales_refund']      = 0; // giảm trừ doanh thu
                    $data_report[$day][$value['id']]['cost_ads']          = 0; // Chi phí MKT
                    $data_report[$day][$value['id']]['cost_capital']      = 0; // Giá vốn
                    $data_report[$day][$value['id']]['cod_total']         = 0; // COD
                }
                $data_report[$day]['total']['name']              = 'Tổng'; // Tổng trong ngày
                $data_report[$day]['total']['target']            = 0; // Mục tiêu
                $data_report[$day]['total']['discount']          = 0; // Giảm giá
                $data_report[$day]['total']['sales_total']       = 0; // Tổng doanh số
                $data_report[$day]['total']['sales_cancel_sale'] = 0; // Hủy sales
                $data_report[$day]['total']['sales_cancel']      = 0; // Doanh số Hủy không sản xuất
                $data_report[$day]['total']['sales_not_send']    = 0; // Doanh số Hủy không gửi
                $data_report[$day]['total']['sales_sending']     = 0; // Doanh số Đang vận chuyển
                $data_report[$day]['total']['sales_success']     = 0; // Doanh thu - Thành công
                $data_report[$day]['total']['sales_money']       = 0; // Đã nhận tiền
                $data_report[$day]['total']['deposit']           = 0; // Thanh toán trước
                $data_report[$day]['total']['sales_return']      = 0; // Hàng hoàn
                $data_report[$day]['total']['check_keep']        = 0; // Giữ lại bưu điện
                $data_report[$day]['total']['sales_refund']      = 0; // giảm trừ doanh thu
                $data_report[$day]['total']['cost_ads']          = 0; // Chi phí MKT
                $data_report[$day]['total']['cost_capital']      = 0; // Giá vốn
                $data_report[$day]['total']['cod_total']         = 0; // COD
            }

            // Lấy dữ mục tiêu sales.
            $where_report = array(
                'filter_type'       => 'sales_target',
                'filter_date_begin' => $ssFilter->report['date_begin'],
                'filter_date_end'   => $ssFilter->report['date_end'],
            );
            $sales_target = $this->getServiceLocator()->get('Admin\Model\TargetTable')->report(array('ssFilter' => $where_report), array('task' => 'list-item-type'));
            foreach ($sales_target as $key => $value){
                if(!empty($value['params']) && array_key_exists($value['user_id'], $data_report[$value['date']])){
                    $params = unserialize($value['params']);
                    $data_report[$value['date']][$value['user_id']]['target'] +=  str_replace(",","",$params['sales']);
                    $data_report['total']['total']['target'] +=  str_replace(",","",$params['sales']);
                    $data_report['total'][$value['user_id']]['target'] +=  str_replace(",","",$params['sales']);
                    $data_report[$value['date']]['total']['target'] +=  str_replace(",","",$params['sales']);
                }
            }

            // Lấy dữ liệu doanh số.
            $where_contract = array(
                'filter_date_begin'         => $ssFilter->report['date_begin'],
                'filter_date_end'           => $ssFilter->report['date_end'],
                'date_type'           => "production_date",
//                'filter_sales_status_id'    => 'yes',
            );
            $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report(array('ssFilter' => $where_contract), array('task' => 'join-contact'));
            foreach ($contracts as $key => $value){
                // Nếu người lên đơn nằm trong danh sách nhân viên chăm sóc.
                if (array_key_exists($value['user_id'], $data_report[$value['date']])){
                    $data_report[$value['date']][$value['user_id']]['cod_total']    += $value['price_transport'];
                    $data_report['total']['total']['cod_total']    += $value['price_transport'];
                    $data_report['total'][$value['user_id']]['cod_total'] += $value['price_transport'];
                    $data_report[$value['date']]['total']['cod_total'] += $value['price_transport'];

                    if($ssFilter->report['product_cat_id']){
                        if (!empty($value['options'])) {
                            $options = unserialize($value['options']);
                            if (count($options['product'])) {
                                foreach ($options['product'] as $k => $v) {
                                    if($v['product_id'] == $ssFilter->report['product_cat_id']) {
                                        $data_report[$value['date']][$value['user_id']]['sales_total']    += $v['total'];
                                        $data_report['total']['total']['sales_total']    += $v['total'];
                                        $data_report['total'][$value['user_id']]['sales_total']    += $v['total'];
                                        $data_report[$value['date']]['total']['sales_total']    += $v['total'];

                                        // Sales - Hủy sales
                                        if ($value['status_id'] == HUY_SALES) {
                                            $data_report[$value['date']][$value['user_id']]['sales_cancel_sale'] += $v['total'];
                                            $data_report['total']['total']['sales_cancel_sale'] += $v['total'];
                                            $data_report['total'][$value['user_id']]['sales_cancel_sale'] += $v['total'];
                                            $data_report[$value['date']]['total']['sales_cancel_sale'] += $v['total'];
                                        }
                                        // Sản xuất - Hủy sản xuất
                                        if ($value['production_department_type'] == STATUS_CONTRACT_PRODUCT_CANCEL) {
                                            $data_report[$value['date']][$value['user_id']]['sales_cancel'] += $v['total'];
                                            $data_report['total']['total']['sales_cancel'] += $v['total'];
                                            $data_report['total'][$value['user_id']]['sales_cancel'] += $v['total'];
                                            $data_report[$value['date']]['total']['sales_cancel'] += $v['total'];
                                        }
                                        // SẢN XUẤT _ Hủy không gửi
                                        if ($value['production_department_type'] == STATUS_CONTRACT_PRODUCT_NOT_SEND) {
                                            $data_report[$value['date']][$value['user_id']]['sales_not_send'] += $v['total'];
                                            $data_report['total']['total']['sales_not_send'] += $v['total'];
                                            $data_report['total'][$value['user_id']]['sales_not_send'] += $v['total'];
                                            $data_report[$value['date']]['total']['sales_not_send'] += $v['total'];
                                        }
                                        // Dục đơn - Đang vận chuyển
                                        if ($value['status_check_id'] == STATUS_CONTRACT_CHECK_SENDING || $value['status_check_id'] == STATUS_CONTRACT_CHECK_POST) {
                                            $data_report[$value['date']][$value['user_id']]['sales_sending'] += $v['total'];
                                            $data_report['total']['total']['sales_sending'] += $v['total'];
                                            $data_report['total'][$value['user_id']]['sales_sending'] += $v['total'];
                                            $data_report[$value['date']]['total']['sales_sending'] += $v['total'];
                                        }
                                        // dục đơn - hoàn
                                        if ($value['status_check_id'] == STATUS_CONTRACT_CHECK_RETURN) {
                                            $data_report[$value['date']][$value['user_id']]['sales_return'] += $v['total'];
                                            $data_report['total']['total']['sales_return'] += $v['total'];
                                            $data_report['total'][$value['user_id']]['sales_return'] += $v['total'];
                                            $data_report[$value['date']]['total']['sales_return'] += $v['total'];
                                        }
                                        // dục đơn - Giữ lại bưu điện
                                        if ($value['status_check_id'] == STATUS_CONTRACT_CHECK_KEEP) {
                                            $data_report[$value['date']][$value['user_id']]['check_keep'] += $v['total'];
                                            $data_report['total']['total']['check_keep'] += $v['total'];
                                            $data_report['total'][$value['user_id']]['check_keep'] += $v['total'];
                                            $data_report[$value['date']]['total']['check_keep'] += $v['total'];
                                        }
                                        // Dục đơn - Thành công
                                        if ($value['status_check_id'] == STATUS_CONTRACT_CHECK_SUCCESS) {
                                            $data_report[$value['date']][$value['user_id']]['sales_success'] += $v['total'];
                                            $data_report['total']['total']['sales_success'] += $v['total'];
                                            $data_report['total'][$value['user_id']]['sales_success'] += $v['total'];
                                            $data_report[$value['date']]['total']['sales_success'] += $v['total'];
                                        }
                                        // Kế toán - Đã nhận tiền
                                        if ($value['status_acounting_id'] == STATUS_CONTRACT_ACOUNTING_MONEY) {
                                            $data_report[$value['date']][$value['user_id']]['sales_money'] += $v['total'];
                                            $data_report['total']['total']['sales_money'] += $v['total'];
                                            $data_report['total'][$value['user_id']]['sales_money'] += $v['total'];
                                            $data_report[$value['date']]['total']['sales_money'] += $v['total'];
                                        }

                                        // Thanh toán trước
                                        $data_report[$value['date']][$value['user_id']]['deposit'] += $v['price_deposits'];
                                        $data_report['total']['total']['deposit'] += $v['price_deposits'];
                                        $data_report['total'][$value['user_id']]['deposit'] += $v['price_deposits'];
                                        $data_report[$value['date']]['total']['deposit'] += $v['price_deposits'];

                                        // Giảm trừ doanh thu
                                        $data_report[$value['date']][$value['user_id']]['sales_refund'] += $v['price_reduce_sale'];
                                        $data_report['total']['total']['sales_refund'] += $v['price_reduce_sale'];
                                        $data_report['total'][$value['user_id']]['sales_refund'] += $v['price_reduce_sale'];
                                        $data_report[$value['date']]['total']['sales_refund'] += $v['price_reduce_sale'];

//                                        $data_report[$value['date']][$value['user_id']]['cost_capital'] += $v['total_production'];
//                                        $data_report['total'][$value['user_id']]['cost_capital'] += $v['total_production'];
//                                        $data_report['total']['total']['cost_capital'] += $v['total_production'];
//                                        $data_report[$value['date']]['total']['cost_capital'] += $v['total_production'];
                                        $data_report[$value['date']][$value['user_id']]['cost_capital'] += $v['capital_default'];
                                        $data_report['total'][$value['user_id']]['cost_capital'] += $v['capital_default'];
                                        $data_report['total']['total']['cost_capital'] += $v['capital_default'];
                                        $data_report[$value['date']]['total']['cost_capital'] += $v['capital_default'];

                                        $data_report[$value['date']][$value['user_id']]['discount']     += $v['sale_price'];
                                        $data_report['total']['total']['discount']     += $v['sale_price'];
                                        $data_report['total'][$value['user_id']]['discount']     += $v['sale_price'];
                                        $data_report[$value['date']]['total']['discount']     += $v['sale_price'];
                                    }
                                }
                            }
                        }
                    }
                    else {
                        $data_report[$value['date']][$value['user_id']]['sales_total'] += $value['price_total'] - $value['vat'];
                        $data_report['total']['total']['sales_total'] += $value['price_total'] - $value['vat'];
                        $data_report['total'][$value['user_id']]['sales_total'] += $value['price_total'] - $value['vat'];
                        $data_report[$value['date']]['total']['sales_total'] += $value['price_total'] - $value['vat'];

                        // Sales - Hủy sales
                        if ($value['status_id'] == HUY_SALES) {
                            $data_report[$value['date']][$value['user_id']]['sales_cancel_sale'] += $value['price_total'] - $value['vat'];
                            $data_report['total']['total']['sales_cancel_sale'] += $value['price_total'] - $value['vat'];
                            $data_report['total'][$value['user_id']]['sales_cancel_sale'] += $value['price_total'] - $value['vat'];
                            $data_report[$value['date']]['total']['sales_cancel_sale'] += $value['price_total'] - $value['vat'];
                        }
                        // Sản xuất - Hủy sản xuất
                        if ($value['production_department_type'] == STATUS_CONTRACT_PRODUCT_CANCEL) {
                            $data_report[$value['date']][$value['user_id']]['sales_cancel'] += $value['price_total'] - $value['vat'];
                            $data_report['total']['total']['sales_cancel'] += $value['price_total'] - $value['vat'];
                            $data_report['total'][$value['user_id']]['sales_cancel'] += $value['price_total'] - $value['vat'];
                            $data_report[$value['date']]['total']['sales_cancel'] += $value['price_total'] - $value['vat'];
                        }
                        // SẢN XUẤT _ Hủy không gửi
                        if ($value['production_department_type'] == STATUS_CONTRACT_PRODUCT_NOT_SEND) {
                            $data_report[$value['date']][$value['user_id']]['sales_not_send'] += $value['price_total'] - $value['vat'];
                            $data_report['total']['total']['sales_not_send'] += $value['price_total'] - $value['vat'];
                            $data_report['total'][$value['user_id']]['sales_not_send'] += $value['price_total'] - $value['vat'];
                            $data_report[$value['date']]['total']['sales_not_send'] += $value['price_total'] - $value['vat'];
                        }
                        // Dục đơn - Đang vận chuyển
                        if ($value['status_check_id'] == STATUS_CONTRACT_CHECK_SENDING || $value['status_check_id'] == STATUS_CONTRACT_CHECK_POST) {
                            $data_report[$value['date']][$value['user_id']]['sales_sending'] += $value['price_total'] - $value['vat'];
                            $data_report['total']['total']['sales_sending'] += $value['price_total'] - $value['vat'];
                            $data_report['total'][$value['user_id']]['sales_sending'] += $value['price_total'] - $value['vat'];
                            $data_report[$value['date']]['total']['sales_sending'] += $value['price_total'] - $value['vat'];
                        }
                        // Sản xuất - hoàn
                        if ($value['status_check_id'] == STATUS_CONTRACT_CHECK_RETURN) {
                            $data_report[$value['date']][$value['user_id']]['sales_return'] += $value['price_total'] - $value['vat'];
                            $data_report['total']['total']['sales_return'] += $value['price_total'] - $value['vat'];
                            $data_report['total'][$value['user_id']]['sales_return'] += $value['price_total'] - $value['vat'];
                            $data_report[$value['date']]['total']['sales_return'] += $value['price_total'] - $value['vat'];
                        }
                        // Sản xuất - Giữ lại bưu điện
                        if ($value['status_check_id'] == STATUS_CONTRACT_CHECK_KEEP) {
                            $data_report[$value['date']][$value['user_id']]['check_keep'] += $value['price_total'] - $value['vat'];
                            $data_report['total']['total']['check_keep'] += $value['price_total'] - $value['vat'];
                            $data_report['total'][$value['user_id']]['check_keep'] += $value['price_total'] - $value['vat'];
                            $data_report[$value['date']]['total']['check_keep'] += $value['price_total'] - $value['vat'];
                        }
                        // Dục đơn - Thành công
                        if ($value['status_check_id'] == STATUS_CONTRACT_CHECK_SUCCESS) {
                            $data_report[$value['date']][$value['user_id']]['sales_success'] += $value['price_total'] - $value['vat'];
                            $data_report['total']['total']['sales_success'] += $value['price_total'] - $value['vat'];
                            $data_report['total'][$value['user_id']]['sales_success'] += $value['price_total'] - $value['vat'];
                            $data_report[$value['date']]['total']['sales_success'] += $value['price_total'] - $value['vat'];
                        }
                        // Kế toán - Đã nhận tiền
//                        if ($value['status_acounting_id'] == STATUS_CONTRACT_ACOUNTING_MONEY) {
//                            $data_report[$value['date']][$value['user_id']]['sales_money'] += $value['price_total'] - $value['vat'];
//                            $data_report['total']['total']['sales_money'] += $value['price_total'] - $value['vat'];
//                            $data_report['total'][$value['user_id']]['sales_money'] += $value['price_total'] - $value['vat'];
//                            $data_report[$value['date']]['total']['sales_money'] += $value['price_total'] - $value['vat'];
//                        }
                        $price_paid = $value['price_paid'] < $value['price_total'] ? $value['price_paid'] : $value['price_total'];
                        $data_report[$value['date']][$value['user_id']]['sales_money'] += $price_paid;
                        $data_report['total']['total']['sales_money'] += $price_paid;
                        $data_report['total'][$value['user_id']]['sales_money'] += $price_paid;
                        $data_report[$value['date']]['total']['sales_money'] += $price_paid;

                        // Thanh toán trước
                        $data_report[$value['date']][$value['user_id']]['deposit'] += $value['price_deposits'];
                        $data_report['total']['total']['deposit'] += $value['price_deposits'];
                        $data_report['total'][$value['user_id']]['deposit'] += $value['price_deposits'];
                        $data_report[$value['date']]['total']['deposit'] += $value['price_deposits'];
                        // Giảm trừ doanh thu
                        $data_report[$value['date']][$value['user_id']]['sales_refund'] += $value['price_reduce_sale'];
                        $data_report['total']['total']['sales_refund'] += $value['price_reduce_sale'];
                        $data_report['total'][$value['user_id']]['sales_refund'] += $value['price_reduce_sale'];
                        $data_report[$value['date']]['total']['sales_refund'] += $value['price_reduce_sale'];

                        // Tính chi phí giá vốn và giảm giá
                        if (!empty($value['options'])) {
                            $options = unserialize($value['options']);
                            if (count($options['product'])) {
                                foreach ($options['product'] as $k => $v) {
                                    if($value['production_department_type'] == STATUS_CONTRACT_PRODUCT_PRODUCTED || $value['production_department_type'] == STATUS_CONTRACT_PRODUCT_POST || $value['production_department_type'] == STATUS_CONTRACT_PRODUCT_NOT_SEND){
                                        $data_report[$value['date']][$value['user_id']]['cost_capital'] += $v['capital_default'];
                                        $data_report['total']['total']['cost_capital'] += $v['capital_default'];
                                        $data_report['total'][$value['user_id']]['cost_capital'] += $v['capital_default'];
                                        $data_report[$value['date']]['total']['cost_capital'] += $v['capital_default'];
                                    }

                                    $data_report[$value['date']][$value['user_id']]['discount']     += $v['sale_price'];
                                    $data_report['total']['total']['discount']     += $v['sale_price'];
                                    $data_report['total'][$value['user_id']]['discount']     += $v['sale_price'];
                                    $data_report[$value['date']]['total']['discount']     += $v['sale_price'];
                                }
                            }
                        }

                        if ($value['status_check_id'] == STATUS_CONTRACT_CHECK_SUCCESS) {

                        }
                    }
                }
            }

            // Tính chi phí quảng cáo cho chăm sóc
            $contracts_cost_ads = $this->getServiceLocator()->get('Admin\Model\FormDataTable')->report($this->_params, array('task' => 'list-item-shared'));
            foreach ($contracts_cost_ads as $key => $value){
                $day = substr($value['date'], 0, 10 );
                if(array_key_exists($value['sales_id'], $data_report[$day])){
                    $data_report[$day][$value['sales_id']]['cost_ads'] +=  $value['cost_ads'];
                    $data_report['total']['total']['cost_ads'] += $value['cost_ads'];
                    $data_report['total'][$value['sales_id']]['cost_ads'] += $value['cost_ads'];
                    $data_report[$day]['total']['cost_ads'] += $value['cost_ads'];
                }
            }

            // Tham số bảng báo cáo
            foreach ($data_report as $keys => $values){
                $rows_span = count($values) > 1 ? count($values) : '';
                if($rows_span > 1) {
                    foreach ($values as $key => $value) {
                        $percent_target   = ($value['target'] > 0 ? round($value['sales_total'] / $value['target'] * 100, 2) : 0);
                        $percent_return = (($value['sales_total'] - $value['sales_cancel']) > 0 ? round(($value['sales_return'] + $value['check_keep'] + $value['sales_refund'] + $value['sales_not_send']) / ($value['sales_total'] - $value['sales_cancel']) * 100, 2) : 0);
                        $revenue = $value['sales_money'] - $value['cost_capital'] -$value['cost_ads'] -$value['cod_total'];

                        $data_report[$keys][$key]['percent_target'] = $percent_target;
                        $data_report[$keys][$key]['percent_return'] = $percent_return > 0 ? $percent_return : 0;
                        $data_report[$keys][$key]['revenue']        = $revenue;
                    }
                }
            }
            // sắp xếp theo doanh tổng doanh thu
            $key_sort = [];
            foreach ($data_report as $keys => $values) {
                $i = 0;
                foreach ($values as $key => $value){
                    if($key != 'total'){
                        $key_sort[$keys][$i]['id'] = $key;
                        $key_sort[$keys][$i]['sales_success'] = $data_report[$keys][$key]['sales_success'];
                        $i++;
                    }
                }
            }
            foreach ($key_sort as $keys => $values){
                for($i = 0; $i < count($values) - 1; $i++){
                    for($j = $i+1; $j < count($values); $j++){
                        if($values[$i]['sales_success'] < $values[$j]['sales_success']){
                            $tm                  = $key_sort[$keys][$i];
                            $key_sort[$keys][$i] = $key_sort[$keys][$j];
                            $key_sort[$keys][$j] = $tm;
                        }
                    }
                }
            }

            $xhtmlItems = '';
            foreach ($key_sort as $keys => $values){
                $rows_span = count($values) > 1 ? count($values)+1 : '';
                $index = 1;
                    foreach ($values as $key => $value) {
                        $style = '';
                        $group = $keys;
                        if ($keys == 'total') {
                            $style = 'color: #35aa47; font-weight:bold;';
                            $group = "Tổng tất cả";
                        }
                        if ($data_report[$keys][$value['id']] == 'total') {
                            $style = 'background-color: #dff0d8 !important; font-weight:bold;';
                        }
                        if ($keys == 'total' && $data_report[$keys][$value['id']] == 'total') {
                            $style = 'color: #FF0000; font-weight:bold; background-color: #d8d8d8 !important;';
                        }

                        $date_string = '';
                        if ($index == 1) {
                            $date_string = '<th rowspan="' . $rows_span . '" class="text-bold text-center text-middle">' . $group . '</th>';
                        }
                        if (empty($date_string)){
                            $class_name = 'left-2';
                        }

                        if ($rows_span == 2) {
                            if ($index == 1) {
                                $xhtmlItems .= '<tr style="' . $style . '">
                                            <th class="text-bold text-center" style="vertical-align: middle">' . $group . '</th>
                                            <th class="text-bold">' . $data_report[$keys][$value['id']]['name'] . '</th>
                                            <!--<td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['target'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['percent_target'] . '%</td>-->
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['discount'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['sales_total'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['sales_cancel_sale'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['sales_cancel'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['sales_not_send'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['sales_sending'] . '</td>
                                            <!--<td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['deposit'] . '</td>-->
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['sales_return'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['check_keep'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['sales_refund'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['sales_money'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['percent_return'] . '%</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['cod_total'] . '%</td>
        						            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['cost_ads'].'</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['cost_capital'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['revenue'] . '</td>
                                        </tr>';
                            }
                        }
                        else {
                            $xhtmlItems .= '<tr style="' . $style . '">
                                            ' . $date_string . '
                                            <th class="text-bold '.$class_name.'" style="'.$style.'">' . $data_report[$keys][$value['id']]['name'] . '</th>
                                            <!--<td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['target'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['percent_target'] . '%</td>-->
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['discount'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['sales_total'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['sales_cancel_sale'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['sales_cancel'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['sales_not_send'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['sales_sending'] . '</td>
                                            <!--<td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['deposit'] . '</td>-->
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['sales_return'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['check_keep'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['sales_refund'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['sales_money'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['percent_return'] . '%</td>
        						            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['cod_total'].'</td>
        						            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['cost_ads'].'</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['cost_capital'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['revenue'] . '</td>
                                        </tr>';
                        }
                        $index++;
                    }

                    $style = 'background-color: #dff0d8 !important; font-weight:bold;';
                    if ($keys == 'total') {
                        $style = 'color: #FF0000; font-weight:bold; background-color: #d8d8d8 !important;';
                    }

                if($rows_span > 1) {
                    // Hiển thị dòng tổng.
                    $xhtmlItems .= '<tr style="' . $style . '">
                                            <th class="text-bold left-2" style="'.$style.'">' . $data_report[$keys]['total']['name'] . '</th>
                                            <!--<td class="mask_currency text-right">' . $data_report[$keys]['total']['target'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys]['total']['percent_target'] . '%</td>-->
                                            <td class="mask_currency text-right">' . $data_report[$keys]['total']['discount'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys]['total']['sales_total'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys]['total']['sales_cancel_sale'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys]['total']['sales_cancel'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys]['total']['sales_not_send'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys]['total']['sales_sending'] . '</td>
                                            <!--<td class="mask_currency text-right">' . $data_report[$keys]['total']['deposit'] . '</td>-->
                                            <td class="mask_currency text-right">' . $data_report[$keys]['total']['sales_return'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys]['total']['check_keep'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys]['total']['sales_refund'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys]['total']['sales_money'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys]['total']['percent_return'] . '%</td>
        						            <td class="mask_currency text-right">' . $data_report[$keys]['total']['cod_total'].'</td>
        						            <td class="mask_currency text-right">' . $data_report[$keys]['total']['cost_ads'].'</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys]['total']['cost_capital'] . '</td>
                                            <td class="mask_currency text-right">' . $data_report[$keys]['total']['revenue'] . '</td>
                                        </tr>';
                }
            }

            $result['reportTable'] = '<thead>
                        				    <tr>
                            					<th class="text-center fix-head">Ngày tháng</th>
                            					<th class="text-center fix-head">Tên nhân viên</th>
                            					<!--<th class="text-center">Mục tiêu</th>
                            					<th class="text-center">% Mục tiêu</th>-->
                            					<th class="text-center">Giảm giá</th>
                            					<th class="text-center">Tổng doanh số</th>
                            					<th class="text-center">Hủy sales</th>
                            					<th class="text-center">Hủy không sản xuất</th>
                            					<th class="text-center">Hủy không gửi</th>
                            					<th class="text-center">Đang Gửi</th>
                            					<!--<th class="text-center">Thanh toán trước</th>-->
                            					<th class="text-center">Hàng hoàn</th>
                            					<th class="text-center">Giữ lại bưu điện</th>
                            					<th class="text-center">Giảm trừ doanh thu</th>
                            					<th class="text-center">Doanh thu</th>
                            					<th class="text-center">% Hoàn</th>
                            					<th class="text-center">COD</th>
                            					<th class="text-center">Chi phí MKT</th>
                            					<th class="text-center">Giá vốn mặc định</th>
                            					<th class="text-center">Doanh thu thực</th>
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
            $ssFilter->report['sale_branch_id'] = $ssFilter->report['sale_branch_id'] ? $ssFilter->report['sale_branch_id'] : $this->_userInfo->getUserInfo('sale_branch_id');
            $ssFilter->report['sale_group_id']  = $ssFilter->report['sale_group_id'] ? $ssFilter->report['sale_group_id'] : $this->_userInfo->getUserInfo('sale_group_id');
            $ssFilter->report['care_id']        = $ssFilter->report['care_id'] ? $ssFilter->report['care_id'] : '';
            $ssFilter->report['product_cat_id'] = $ssFilter->report['product_cat_id'] ? $ssFilter->report['product_cat_id'] : '';

            // Set giá trị cho form
            $myForm	= new \Report\Form\Care\Care($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);

            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['saleGroup']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['caption']        = 'Báo cáo doanh thu chăm sóc';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Báo cáo chăm sóc 2
    public function care2Action() {
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
                elseif (in_array(GROUP_SALES_LEADER, $permission_ids)){
                    $this->_params['data']['sale_branch_id'] = $curent_user['sale_branch_id'];
                    $this->_params['data']['sale_group_id'] = $curent_user['sale_group_id'];
                }
                else{
                    $this->_params['data']['care_id'] = $curent_user['id'];
                }
            }
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin']     = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']       = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_group_id']  = $this->_params['data']['sale_group_id'];
            $ssFilter->report['care_id']        = $this->_params['data']['care_id'];
            $ssFilter->report['product_cat_id'] = $this->_params['data']['product_cat_id'];

            // Xác định ngày tháng tìm kiếm
            $date       = new \ZendX\Functions\Date();
            $day_begin  = strtotime($date->formatToData($ssFilter->report['date_begin']));
            $day_end    = strtotime($date->formatToData($ssFilter->report['date_end']));
            $number_day = abs($day_end - $day_begin) / 86400;

            $sales = $this->getServiceLocator()->get('Admin\Model\UserTable')->report($this->_params, array('task' => 'list-care'));

            // Tạo mảng lưu báo cáo.
            $data_report = [];
            for ($i = 0; $i <= $number_day; $i++) {
                $data_report['total'] = null;
                foreach ($sales as $key => $value) {
                    $data_report['total'][$value['id']]['name'] = $value['name'];
                }
                $data_report['total']['total']['name'] = 'Tổng tất cả';

                $day = date('Y-m-d', $day_begin + $i*86400);
                foreach ($sales as $key => $value) {
                    $data_report[$day][$value['id']]['name']           = $value['name'];
                    $data_report[$day][$value['id']]['phone']          = 0; // số điện thoại
                    $data_report[$day][$value['id']]['positive']       = 0; // tích cực
                    $data_report[$day][$value['id']]['negative']       = 0; // Tiêu cực
                    $data_report[$day][$value['id']]['guarantee']      = 0; // Bảo hành
                    $data_report[$day][$value['id']]['contract_care']  = 0; // Số đơn chăm sóc
                    $data_report[$day][$value['id']]['sales_care']     = 0; // Doanh số chăm sóc
                    $data_report[$day][$value['id']]['contract_cross'] = 0; // Số đơn bán chéo
                    $data_report[$day][$value['id']]['sales_cross']    = 0; // Doanh số bán chéo
                }
                $data_report[$day]['total']['name']           = 'Tổng'; // Tổng trong ngày
                $data_report[$day]['total']['phone']          = 0;
                $data_report[$day]['total']['positive']       = 0;
                $data_report[$day]['total']['negative']       = 0;
                $data_report[$day]['total']['maintenance']    = 0;
                $data_report[$day]['total']['contract_care']  = 0;
                $data_report[$day]['total']['sales_care']     = 0;
                $data_report[$day]['total']['contract_cross'] = 0;
                $data_report[$day]['total']['sales_cross']    = 0;
            }

            // Lấy số điện thoại của nv chăm sóc đã được nhận.
            $contacts = $this->getServiceLocator()->get('Admin\Model\ContactTable')->report($this->_params, array('task' => 'date'))->toArray();

            foreach ($contacts as $key => $value){
                $day = substr($value['date'], 0, 10);
                // Nếu người contact được phụ người trong danh sách nhân viên chăm sóc quản lý
                if (array_key_exists($value['user_id'], $data_report[$day])){
                    $data_report[$day][$value['user_id']]['phone'] += 1;
                    $data_report['total']['total']['phone'] += 1;
                    $data_report['total'][$value['user_id']]['phone'] += 1;
                    $data_report[$day]['total']['phone'] += 1;


                    // Đếm số contact tích cực tiêu cực.
                    $options = !empty($value['options']) ? unserialize($value['options']) : null;
                    if(!empty($options)){
                        $sale_history_type = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-type')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'id'));

                        // Số tích cực
                        $id_status_positive = $sale_history_type[STATUS_CONTACT_POSITIVE];
                        if($options['history_type_id'] == $id_status_positive && $options['history_created_by'] == $value['user_id']){
                            $data_report[$day][$value['user_id']]['positive'] += 1;
                            $data_report['total']['total']['positive'] += 1;
                            $data_report['total'][$value['user_id']]['positive'] += 1;
                            $data_report[$day]['total']['positive'] += 1;
                        }

                        // Số tiêu cực
                        $id_status_negative = $sale_history_type[STATUS_CONTACT_NEGATIVE];
                        if($options['history_type_id'] == $id_status_negative && $options['history_created_by'] == $value['user_id']){
                            $data_report[$day][$value['user_id']]['negative'] += 1;
                            $data_report['total']['total']['negative'] += 1;
                            $data_report['total'][$value['user_id']]['negative'] += 1;
                            $data_report[$day]['total']['negative'] += 1;
                        }
                    }
                }
            }

            // Lấy dữ liệu doanh số
            $where_contract = array(
                'filter_date_begin'         => $ssFilter->report['date_begin'],
                'filter_date_end'           => $ssFilter->report['date_end'],
                'date_type'           => "production_date",
                'filter_sales_status_id'    => 'yes',
            );
            $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report(array('ssFilter' => $where_contract), array('task' => 'join-contact'));

            foreach ($contracts as $key => $value){
                $day = substr($value['created'], 0, 10);
                if(!empty($value['user_id']) && array_key_exists($value['user_id'], $data_report[$day])){
                    if($ssFilter->report['product_cat_id']){
                        if (!empty($value['options'])) {
                            $options = unserialize($value['options']);
                            if (count($options['product'])) {
                                foreach ($options['product'] as $k => $v) {
                                    if($v['product_id'] == $ssFilter->report['product_cat_id']) {
                                        // Nếu đơn hàng phát sinh doanh số chăm sóc
                                        if(!empty($v['sales_care'])){
                                            // Số đơn hàng chăm sóc
                                            $data_report[$day][$value['user_id']]['contract_care']    += 1;
                                            $data_report['total']['total']['contract_care']           += 1;
                                            $data_report['total'][$value['user_id']]['contract_care'] += 1;
                                            $data_report[$day]['total']['contract_care']              += 1;

                                            // Doanh số
                                            $data_report[$day][$value['user_id']]['sales_care']    += $v['sales_care'];
                                            $data_report['total']['total']['sales_care']           += $v['sales_care'];
                                            $data_report['total'][$value['user_id']]['sales_care'] += $v['sales_care'];
                                            $data_report[$day]['total']['sales_care']              += $v['sales_care'];
                                        }
                                        // Nếu đơn hàng phát sinh doanh số bán chéo
                                        if(!empty($v['sales_cross'])){
                                            // Số đơn hàng chăm sóc
                                            $data_report[$day][$value['user_id']]['contract_cross']    += 1;
                                            $data_report['total']['total']['contract_cross']           += 1;
                                            $data_report['total'][$value['user_id']]['contract_cross'] += 1;
                                            $data_report[$day]['total']['contract_cross']              += 1;

                                            // Doanh số
                                            $data_report[$day][$value['user_id']]['sales_cross']    += $v['sales_cross'];
                                            $data_report['total']['total']['sales_cross']           += $v['sales_cross'];
                                            $data_report['total'][$value['user_id']]['sales_cross'] += $v['sales_cross'];
                                            $data_report[$day]['total']['sales_cross']              += $v['sales_cross'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                    else {
                        // Nếu đơn hàng là đơn bảo hành
                        if($value['status_guarantee_id']) {
                            $data_report[$day][$value['user_id']]['guarantee']    += 1;
                            $data_report['total']['total']['guarantee']           += 1;
                            $data_report['total'][$value['user_id']]['guarantee'] += 1;
                            $data_report[$day]['total']['guarantee']              += 1;
                        }
                        // Nếu đơn hàng phát sinh doanh số chăm sóc
                        if(!empty($value['sales_care'])){
                            // Số đơn hàng chăm sóc
                            $data_report[$day][$value['user_id']]['contract_care']    += 1;
                            $data_report['total']['total']['contract_care']           += 1;
                            $data_report['total'][$value['user_id']]['contract_care'] += 1;
                            $data_report[$day]['total']['contract_care']              += 1;

                            // Doanh số
                            $data_report[$day][$value['user_id']]['sales_care']    += $value['sales_care'];
                            $data_report['total']['total']['sales_care']           += $value['sales_care'];
                            $data_report['total'][$value['user_id']]['sales_care'] += $value['sales_care'];
                            $data_report[$day]['total']['sales_care']              += $value['sales_care'];
                        }
                        // Nếu đơn hàng phát sinh doanh số bán chéo
                        if(!empty($value['sales_cross'])){
                            // Số đơn hàng chăm sóc
                            $data_report[$day][$value['user_id']]['contract_cross']    += 1;
                            $data_report['total']['total']['contract_cross']           += 1;
                            $data_report['total'][$value['user_id']]['contract_cross'] += 1;
                            $data_report[$day]['total']['contract_cross']              += 1;

                            // Doanh số
                            $data_report[$day][$value['user_id']]['sales_cross']    += $value['sales_cross'];
                            $data_report['total']['total']['sales_cross']           += $value['sales_cross'];
                            $data_report['total'][$value['user_id']]['sales_cross'] += $value['sales_cross'];
                            $data_report[$day]['total']['sales_cross']              += $value['sales_cross'];
                        }
                    }
                }
            }

            // Tham số bảng báo cáo
            foreach ($data_report as $keys => $values) {
                $rows_span = count($values) > 1 ? count($values) : '';
                if($rows_span > 1) {
                    foreach ($values as $key => $value) {
                        $total_sale     = $value['sales_care'] + $value['sales_cross'];
                        $total_contract = $value['contract_care'] + $value['contract_cross'];
                        $new_tlcs       = ($value['phone'] > 0 ? round($total_contract / $value['phone'] * 100, 2) : 0);
                        $value_contract = ($total_contract > 0 ? (int)($total_sale / $total_contract) : 0);

                        $data_report[$keys][$key]['total_sale']     = $total_sale;
                        $data_report[$keys][$key]['total_contract'] = $total_contract;
                        $data_report[$keys][$key]['new_tlcs']       = $new_tlcs;
                        $data_report[$keys][$key]['value_contract'] = $value_contract;
                    }
                }
            }

            // sắp xếp theo doanh tổng doanh thu
            $key_sort = [];
            foreach ($data_report as $keys => $values) {
                $i = 0;
                foreach ($values as $key => $value){
                    if($key != 'total'){
                        $key_sort[$keys][$i]['id'] = $key;
                        $key_sort[$keys][$i]['total_sale'] = $data_report[$keys][$key]['total_sale'];
                        $i++;
                    }
                }
            }
            foreach ($key_sort as $keys => $values){
                for($i = 0; $i < count($values) - 1; $i++){
                    for($j = $i+1; $j < count($values); $j++){
                        if($values[$i]['total_sale'] < $values[$j]['total_sale']){
                            $tm                  = $key_sort[$keys][$i];
                            $key_sort[$keys][$i] = $key_sort[$keys][$j];
                            $key_sort[$keys][$j] = $tm;
                        }
                    }
                }
            }

            $xhtmlItems = '';
            foreach ($key_sort as $keys => $values) {
                $rows_span = count($values) > 1 ? count($values) + 1 : '';
                $index = 1;
                    foreach ($values as $key => $value) {
                        $style = '';
                        $group = $keys;
                        if ($keys == 'total') {
                            $style = 'color: #35aa47; font-weight:bold;';
                            $group = "Tổng tất cả";
                        }
                        if ($data_report[$keys][$value['id']] == 'total') {
                            $style = 'background-color: #dff0d8 !important; font-weight:bold;';
                        }
                        if ($keys == 'total' && $data_report[$keys][$value['id']] == 'total') {
                            $style = 'color: #FF0000; font-weight:bold; background-color: #d8d8d8 !important;';
                        }

                        $date_string = '';
                        if ($index == 1) {
                            $date_string = '<th rowspan="' . $rows_span . '" class="text-bold text-center text-middle">' . $group . '</th>';
                        }
                        if (empty($date_string)){
                            $class_name = 'left-2';
                        }

                        if ($rows_span == 2) {
                            if ($index == 1) {
                                $xhtmlItems .= '<tr style="' . $style . '">
                                                <th class="text-bold text-center text-middle">' . $group . '</th>
                                                <th class="text-bold">' . $data_report[$keys][$value['id']]['name'] . '</th>
                                                <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['phone'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['positive'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['negative'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['guarantee'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['contract_care'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['sales_care'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['contract_cross'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['sales_cross'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['total_contract'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['total_sale'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['new_tlcs'] . '%</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['value_contract'] . '</td>
                                        </tr>';
                            }
                        } else {
                            $xhtmlItems .= '<tr style="' . $style . '">
                                                ' . $date_string . '
                                                <th class="text-bold '.$class_name.'" style="' . $style . '">' . $data_report[$keys][$value['id']]['name'] . '</th>
                                                <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['phone'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['positive'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['negative'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['guarantee'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['contract_care'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['sales_care'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['contract_cross'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['sales_cross'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['total_contract'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['total_sale'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['new_tlcs'] . '%</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys][$value['id']]['value_contract'] . '</td>
                                        </tr>';
                        }
                        $index++;
                    }

                    $style = 'background-color: #dff0d8 !important; font-weight:bold;';
                    if ($keys == 'total') {
                        $style = 'color: #FF0000; font-weight:bold; background-color: #d8d8d8 !important;';
                    }

                if($rows_span > 1) {
                    // Hiển thị dòng tổng
                    $xhtmlItems .= '<tr style="' . $style . '">
                                                <th class="text-bold left-2" style="' . $style . '">' . $data_report[$keys]['total']['name'] . '</th>
                                                <td class="mask_currency text-right">' . $data_report[$keys]['total']['phone'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys]['total']['positive'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys]['total']['negative'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys]['total']['guarantee'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys]['total']['contract_care'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys]['total']['sales_care'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys]['total']['contract_cross'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys]['total']['sales_cross'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys]['total']['total_contract'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys]['total']['total_sale'] . '</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys]['total']['new_tlcs'] . '%</td>
                                                <td class="mask_currency text-right">' . $data_report[$keys]['total']['value_contract'] . '</td>
                                        </tr>';
                }
            }

            $result['reportTable'] = '<thead>
                        				    <tr>
                            					<th rowspan="2" class="text-center fix-head">Ngày tháng</th>
                            					<th rowspan="2" class="text-center fix-head">Tên nhân viên</th>
                            					<th rowspan="2" class="text-center">Số điện thoại</th>
                            					<th rowspan="2" class="text-center">Tích cực</th>
                            					<th rowspan="2" class="text-center">Tiêu cực</th>
                            					<th rowspan="2" class="text-center">Đơn bảo hành</th>
                            					<th colspan="2" class="text-center">Doanh số chăm sóc</th>
                            					<th colspan="2" class="text-center">Doanh số bán chéo</th>
                            					<th colspan="2" class="text-center">Tổng doanh số</th>
                            					<th rowspan="2" class="text-center">Tỉ lệ % chăm sóc</th>
                            					<th rowspan="2" class="text-center">Giá trị / đơn hàng</th>
                        					</tr>
                        				    <tr>
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
            $day = date('w');
            $week_start = date('d/m/Y', strtotime('-'.$day.' days') + 86400);
            $week_end = date('d/m/Y', strtotime('+'.(6-$day).' days')+86400);

            $ssFilter->report                   = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']     = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $week_start;
            $ssFilter->report['date_end']       = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $week_end;
            $ssFilter->report['sale_branch_id'] = $ssFilter->report['sale_branch_id'] ? $ssFilter->report['sale_branch_id'] : $this->_userInfo->getUserInfo('sale_branch_id');
            $ssFilter->report['sale_group_id']  = $ssFilter->report['sale_group_id'] ? $ssFilter->report['sale_group_id'] : $this->_userInfo->getUserInfo('sale_group_id');
            $ssFilter->report['care_id']        = $ssFilter->report['care_id'] ? $ssFilter->report['care_id'] : '';
            $ssFilter->report['product_cat_id'] = $ssFilter->report['product_cat_id'] ? $ssFilter->report['product_cat_id'] : '';

            // Set giá trị cho form
            $myForm	= new \Report\Form\Care\Care($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);

            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['saleGroup']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['caption']        = 'Báo cáo chăm sóc chi tiết';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }
}




















