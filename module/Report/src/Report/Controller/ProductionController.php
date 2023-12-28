<?php

namespace Report\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class ProductionController extends ActionController {
    
    public function init() {
        $this->setLayout('report');
        
        // Lấy dữ liệu post của form
        $this->_params['data'] = $this->getRequest()->getPost()->toArray();
        
        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__);
        $this->_params['ssFilter']['filter_date_begin']     = $ssFilter->filter_date_begin;
        $this->_params['ssFilter']['filter_date_end']       = $ssFilter->filter_date_end;
        $this->_params['ssFilter']['filter_sale_branch']    = $ssFilter->filter_sale_branch;
        $this->_params['ssFilter']['filter_sale_group']     = $ssFilter->filter_sale_group;
        $this->_params['ssFilter']['filter_user']           = $ssFilter->filter_user;
        
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

    // Báo cáo sản xuất (Tổng quan)
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
            }

            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin']     = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']       = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            $this->_params['ssFilter'] = $ssFilter->report;

            // Xác định ngày tháng tìm kiếm
            $date       = new \ZendX\Functions\Date();
            $day_begin  = strtotime($date->formatToData($ssFilter->report['date_begin']));
            $day_end    = strtotime($date->formatToData($ssFilter->report['date_end']));
            $number_day = abs($day_end - $day_begin) / 86400;

            // Tạo mảng lưu báo cáo.
            $data_report = [];
            for ($i = 0; $i <= $number_day; $i++) {
                $day = date('Y-m-d', $day_begin + $i*86400);

                $data_report[$day]['contract_number']      = 0;
                $data_report[$day]['contract_cancel']      = 0;
                $data_report[$day]['contract_cancel_send'] = 0;
                $data_report[$day]['contract_producted']   = 0;
                $data_report[$day]['t_post_number']        = 0;
                $data_report[$day]['t_sales']              = 0;
                $data_report[$day]['h_post_number']        = 0;
                $data_report[$day]['h_sales']              = 0;
            }
            $data_report['total']['contract_number']      = 0;
            $data_report['total']['contract_cancel']      = 0;
            $data_report['total']['contract_cancel_send'] = 0;
            $data_report['total']['contract_producted']   = 0;
            $data_report['total']['t_post_number']        = 0;
            $data_report['total']['t_sales']              = 0;
            $data_report['total']['h_post_number']        = 0;
            $data_report['total']['h_sales']              = 0;

            // Lấy dữ liệu doanh số.
            $where_contract = array(
                'filter_date_begin' => $ssFilter->report['date_begin'],
                'filter_date_end'   => $ssFilter->report['date_end'],
                'sale_branch_id'    => $ssFilter->report['sale_branch_id'],
                'filter_sales_status_id'    => 'yes',
            );
            $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report(array('ssFilter' => $where_contract), array('task' => 'join-contact'));
            // Loại đơn: Đơn tỉnh, Đơn hà nội.
            $contracts_type	= \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "production-type" )), array('task' => 'cache')), array('key' => 'alias', 'value' => 'id'));
            foreach ($contracts as $key => $value){
                $day = substr($value['created'], 0, 10);

                $data_report[$day]['contract_number'] += 1;
                $data_report['total']['contract_number'] += 1;
                // Đơn hủy sản xuất
                if($value['production_department_type'] == STATUS_CONTRACT_PRODUCT_CANCEL){
                    $data_report[$day]['contract_cancel'] += 1;
                    $data_report['total']['contract_cancel'] += 1;
                }
                // Đơn hủy không gửi
                if($value['production_department_type'] == STATUS_CONTRACT_PRODUCT_NOT_SEND){
                    $data_report[$day]['contract_cancel_send'] += 1;
                    $data_report['total']['contract_cancel_send'] += 1;
                }
                // Đơn đã sản xuất
                if($value['production_department_type'] == STATUS_CONTRACT_PRODUCT_PRODUCTED){
                    $data_report[$day]['contract_producted'] += 1;
                    $data_report['total']['contract_producted'] += 1;
                }
                // Đơn hàng là loại đơn tỉnh
                if($value['production_type_id'] == $contracts_type[DON_TINH]){
                    if($value['production_department_type'] == STATUS_CONTRACT_PRODUCT_POST){
                        $data_report[$day]['t_post_number'] += 1;
                        $data_report['total']['t_post_number'] += 1;

                        $data_report[$day]['t_sales'] += $value['price_total'];
                        $data_report['total']['t_sales'] += $value['price_total'];
                    }
                }
                // Đơn hàng là loại đơn hà nội
                if($value['production_type_id'] == $contracts_type[DON_HA_NOI]){
                    if($value['production_department_type'] == STATUS_CONTRACT_PRODUCT_POST){
                        $data_report[$day]['h_post_number'] += 1;
                        $data_report['total']['h_post_number'] += 1;

                        $data_report[$day]['h_sales'] += $value['price_total'];
                        $data_report['total']['h_sales'] += $value['price_total'];
                    }
                }
            }

            // Tham số bảng báo cáo
            $xhtmlItems = '';
            foreach ($data_report as $key => $value){
                $style_class = $key == 'total' ? 'text-bold text-red': '';
                $name_filter = $key == 'total' ? 'Tổng' : $key;

                $xhtmlItems .= '<tr class="'.$style_class.'">
        		                <td class="text-bold text-center">'.$name_filter.'</td>
        						<td class="mask_currency text-center">'.$value['contract_number'].'</td>
        						<td class="mask_currency text-right">'.$value['contract_cancel'].'</td>
        						<td class="mask_currency text-right">'.($value['contract_number'] - $value['contract_cancel'] - $value['contract_producted'] - $value['t_post_number'] - $value['h_post_number'] - $value['contract_cancel_send']).'</td> 
        						<td class="mask_currency text-right">'.$value['contract_producted'].'</td>
        						<td class="mask_currency text-right">'.$value['t_post_number'].'</td>
        						<td class="mask_currency text-right">'.$value['t_sales'].'</td>
        						<td class="mask_currency text-right">'.$value['h_post_number'].'</td>
        						<td class="mask_currency text-right">'.$value['h_sales'].'</td>
        					</tr>';
            }

            $result['reportTable'] = '<thead>
                        				    <tr>
                            					<th width="150" rowspan="2" class="text-center">Ngày tháng</th>
                            					<th width="150" rowspan="2" class="text-center">Đơn hàng đã nhận</th>
                            					<th width="150" rowspan="2" class="text-center">Hủy không sản xuất</th>
                            					<th width="150" rowspan="2" class="text-center">Chưa sản xuất</th>
                            					<th width="150" rowspan="2" class="text-center">Đã sản xuất</th>
                            					<th width="150" colspan="2" class="text-center">Đơn hàng đi tỉnh</th>
                            					<th width="150" colspan="2" class="text-center">Đơn hàng nội thành</th>
                        					</tr>
                        				    <tr>
                            					<th width="150" style="min-width: 80px;" class="text-center">ĐH đã gửi bưu điện</th>
                            					<th width="150" style="min-width: 80px;" class="text-center">Doanh số</th>
                            					<th width="150" style="min-width: 80px;" class="text-center">ĐH nội thành</th>
                            					<th width="150" style="min-width: 80px;" class="text-center">Doanh số</th>
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
            $this->_viewModel['caption']        = 'Báo cáo sản xuất';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Báo cáo công nợ theo ngày
    public function debtAction() {
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));
        $date     = new \ZendX\Functions\Date();
        $number   = new \ZendX\Functions\Number();

        if($this->getRequest()->isPost()) {
            // Lấy giá trị post từ filter
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();

            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin']     = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']       = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_group_id']  = $this->_params['data']['sale_group_id'];

            $this->_params['ssFilter']  = $ssFilter->report;

            // Xác định ngày tháng tìm kiếm
            $date           = new \ZendX\Functions\Date();
            $day_begin  = strtotime($date->formatToData($ssFilter->report['date_begin']));
            $day_end    = strtotime($date->formatToData($ssFilter->report['date_end']));
            $number_day = abs($day_end - $day_begin) / 86400;

            // Tạo mảng lưu báo cáo.
            $data_report = [];
            for ($i = 0; $i <= $number_day; $i++) {
                $day = date('Y-m-d', $day_begin + $i*86400);

                $data_report[$day]['total_number_contract'] = 0;
                $data_report[$day]['total_cost']            = 0;
            }

            // Lấy danh sách đơn hàng phát sinh trong ngày
            $arr_pram1 = $this->_params;
            $arr_pram1['data']['date_type'] = 'date';
            $contract_created = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report($arr_pram1, array('task' => 'join-user'));

            foreach ($contract_created as $key => $value){
                $day = substr($value['date'],0 ,10);
                // Tính tổng số đơn hàng trong ngày
                $data_report[$day]['total_number_contract'] += 1;

                // Tính tổng giá vốn thương mại trong ngày
//                $options = unserialize($value['options']);
//                if(!empty($options)){
//                    $total_cost = 0;
//                    if(isset($options['product']) && count($options['product'])){
//                        foreach ($options['product'] as $k => $v) {
//                            $total_cost += $v['total_production'];
//                        }
//                    }
//                    $data_report[$day]['total_cost'] += $total_cost;
//                }
            }

            // Lấy danh sách đơn hàng sản xuất phát sinh trong ngày
            $arr_pram2 = $this->_params;
            $arr_pram2['data']['date_type'] = 'production_date';
            $contract_production = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report($arr_pram2, array('task' => 'join-user'));

            foreach ($contract_production as $key => $value){
                $day = substr($value['production_date'],0 ,10);
                // Tính tổng giá vốn thương mại trong ngày
                $options = unserialize($value['options']);
                if(!empty($options)){
                    $total_cost = 0;
                    if(isset($options['product']) && count($options['product'])){
                        foreach ($options['product'] as $k => $v) {
                            $total_cost += $v['total_production'];
                        }
                    }
                    $data_report[$day]['total_cost'] += $total_cost;
                }
            }

            // Tham số bảng báo cáo
            $xhtmlItems = '';
            $xhtmlTotal = '';
            $number_contract_all = 0;
            $cost_all = 0;
            foreach ($data_report as $key => $value){
                $number_contract_all    += $value['total_number_contract'];
                $cost_all               += $value['total_cost'];

                $xhtmlItems .= '<tr>
        		                <td class="text-center text-bold">'.$key.'</td>
        						<td class="mask_currency text-center">'.$value['total_number_contract'].'</td>
        						<td class="mask_currency text-red text-right">'.$value['total_cost'].'</td>
        					</tr>';
            }
            $xhtmlTotal .= '<tr>
        		                <td class="text-center text-bold text-red">Tổng</td>
        						<td class="mask_currency text-bold text-center text-red">'.$number_contract_all.'</td>
        						<td class="mask_currency text-bold text-red text-right text-red">'.$cost_all.'</td>
        					</tr>';



            $result['reportTable'] = '<thead>
                        				    <tr>
                            					<th width="200" class="text-center">Ngày</th>
                            					<th width="250" class="text-center">Số đơn</th>
                            					<th width="250" class="text-right">Giá vốn thương mại</th>
                        					</tr>
                        				</thead>
                        				<tbody>
                        				    '. $xhtmlItems . $xhtmlTotal.'
                        				</tbody>';

            echo json_encode($result);

            return $this->response;
        } else {
            // Khai báo giá trị ngày tháng
            $default_date_begin     = date('01/m/Y');
            $default_date_end       = date('t/m/Y');
            $default_sale_branch_id = $this->_userInfo->getUserInfo('sale_branch_id');
            $default_sale_group_id  = $this->_userInfo->getUserInfo('sale_group_id');

            $ssFilter->report                   = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']     = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $default_date_begin;
            $ssFilter->report['date_end']       = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $default_date_end;
            $ssFilter->report['sale_branch_id'] = $ssFilter->report['sale_branch_id'] ? $ssFilter->report['sale_branch_id'] : $default_sale_branch_id;
            $ssFilter->report['sale_group_id']  = $ssFilter->report['sale_group_id'] ? $ssFilter->report['sale_group_id'] : $default_sale_group_id;

            $this->_params['ssFilter']          = $ssFilter->report;

            // Set giá trị cho form
            $myForm	= new \Report\Form\Report($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);

            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['saleGroup']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['caption']        = 'Báo cáo công nợ';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }
}




















