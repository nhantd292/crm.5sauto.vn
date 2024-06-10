<?php

namespace Report\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class AcountingController extends ActionController {
    
    public function init() {
        $this->setLayout('report');
        
        // Lấy dữ liệu post của form
        $this->_params['data'] = $this->getRequest()->getPost()->toArray();
        
        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__);
        $this->_params['ssFilter']['filter_date_begin']     = $ssFilter->report['date_begin'];
        $this->_params['ssFilter']['filter_date_end']       = $ssFilter->report['date_end'];
        $this->_params['ssFilter']['year']                  = $ssFilter->report['year'];
        $this->_params['ssFilter']['month']                 = $ssFilter->report['month'];
        $this->_params['ssFilter']['color_group_id']        = $ssFilter->report['color_group_id'];

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

    // Báo cáo nhập hàng cơ sở
    public function branchAction() {
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
            $ssFilter->report['date_begin']             = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']               = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id']         = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['production_type_id']     = $this->_params['data']['production_type_id'];
            $ssFilter->report['product_cat_id']         = $this->_params['data']['product_cat_id'];
            $ssFilter->report['code']                   = $this->_params['data']['code'];

            $this->_params['ssFilter'] = $ssFilter->report;
            $branchs = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));

            // Lấy dữ liệu doanh số.
            $where_contract = array(
                'filter_date_begin'         => $ssFilter->report['date_begin'],
                'filter_date_end'           => $ssFilter->report['date_end'],
                'sale_branch_id'            => $ssFilter->report['sale_branch_id'],
                'production_type_id'        => $ssFilter->report['production_type_id'],
                'code'                      => $ssFilter->report['code'],
//                'filter_status_type'        => 'production_department_type',
//                'filter_status'             => 'success',
            );
            $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report(array('ssFilter' => $where_contract), array('task' => 'join-contact'));

            // Tạo dòng tính tổng cở cuối cùng
            $total_table['stock'] = '';
            $total_table['new'] = 0;
            $total_table['production'] = 0;
            $total_table['sales'] = 0;
            // mảng chứa tổng giá của các cơ sở
            foreach ($branchs as $key => $value){
                $total_table_branch[$key] = 0;
            }

            $xhtmlItems = '';
            foreach ($contracts as $keys => $values){
                $options = unserialize($values['options']);
                $code_return = '';// Mã đơn hàng có sẵn
                $cost_new = 0;// Chi phí sản xuất mới
                $cost_total = 0; // Tổng giá vốn
                $sales_total = 0; // Tổng giá bán

                // Tạo biến lưu trữ dữ liệu chi phí nhập hàng từ các cơ sở khác
                $cost_other_branch = array();
                foreach ($branchs as $key_b => $value_b){
                    $cost_other_branch[$key_b] = 0;
                }

                foreach ($options['product'] as $key => $value){
                    if($ssFilter->report['product_cat_id']){
                        if($ssFilter->report['product_cat_id'] == $value['product_id']){
                            $cost_total += $value['total_production'];
                            $sales_total += $value['price'];

                            if(!empty($value['stock']) && strpos($code_return, $value['stock']) === false){
                                $code_return .=  $value['stock'].',';
                            }
                            if(empty($value['stock'])){
                                $cost_new += $value['total_production'];
                            }
                            else{
                                if(!empty($value['sale_branch_id'])){
                                    $cost_other_branch[$value['sale_branch_id']] += $value['total_production'];
                                    $total_table_branch[$value['sale_branch_id']] += $value['total_production'];
                                }
                            }
                        }
                    }
                    else{
                        $cost_total += $value['total_production'];
                        $sales_total += $value['price'];

                        if(!empty($value['stock']) && strpos($code_return, $value['stock']) === false){
                            $code_return .=  $value['stock'].',';
                        }
                        if(empty($value['stock'])){
                            $cost_new += $value['total_production'];
                        }
                        else{
                            if(!empty($value['sale_branch_id'])){
                                $cost_other_branch[$value['sale_branch_id']] += $value['total_production'];
                                $total_table_branch[$value['sale_branch_id']] += $value['total_production'];
                            }
                        }
                    }
                }

                $list_value_cost_blanch = '';
                foreach ($cost_other_branch as $key => $value){
                    $list_value_cost_blanch .= '<td class="mask_currency text-right">'.$value.'</td>';
                }
                $percent_production  = ($sales_total > 0 ? round($cost_total / $sales_total * 100, 2) : 0);
                $xhtmlItems .= '<tr>
        		                <td class="text-bold">'.$values['code'].'</td>
        						<td class="mask_currency">'.$code_return.'</td>
        						<td class="mask_currency text-right">'.$cost_new.'</td>
        						'.$list_value_cost_blanch.'
        						<td class="mask_currency text-right">'.$cost_total.'</td>
        						<td class="mask_currency text-right">'.$sales_total.'</td>
        						<td class="mask_currency text-right">'.$percent_production.'%</td>';
                $xhtmlItems .=  '</tr>';

                $total_table['stock']      .= $code_return;
                $total_table['new']        += $cost_new;
                $total_table['production'] += $cost_total;
                $total_table['sales']      += $sales_total;
            }

            $list_value_total_blanch = '';
            foreach ($total_table_branch as $key => $value){
                $list_value_total_blanch .= '<td class="mask_currency text-right">'.$value.'</td>';
            }
            $percent_production_total   = ($total_table['sales'] > 0 ? round($total_table['production'] / $total_table['sales'] * 100, 2) : 0);
            $xhtmlItems .= '<tr class="text-red text-bold">
        		                <td class="text-bold">Tổng</td>
        						<td class="mask_currency"></td>
        						<td class="mask_currency text-right">'.$total_table['new'].'</td>
        						'.$list_value_total_blanch.'
        						<td class="mask_currency text-right">'.$total_table['production'].'</td>
        						<td class="mask_currency text-right">'.$total_table['sales'].'</td>
        						<td class="mask_currency text-right">'.$percent_production_total.'%</td>';
            $xhtmlItems .=  '</tr>';

            $list_branch = '';
            foreach ($branchs as $key => $value){
                $list_branch .= '<th width="140" rowspan="2">'.$value['name'].'</th>';
            }
            $result['reportTable'] = '<thead>
                        				    <tr>
                            					<th width="120" class="text-center">Mã đơn</th>
                            					<th width="200" class="text-center">Mã đơn hàng có sẵn</th>
                            					<th width="140" class="text-center">Sản xuất mới</th>
                            					'.$list_branch.'
                            					<th width="140" class="text-center">Tổng giá vốn</th>
                            					<th width="140" class="text-center">Giá bán</th>
                            					<th width="140" class="text-center">% Giá vốn</th>
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
            $this->_viewModel['caption']        = 'Báo cáo nhập hàng cơ sở';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Báo cáo nhập hàng cơ sở 2
    public function branch2Action() {
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
            $ssFilter->report['date_begin']             = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']               = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id']         = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['production_type_id']     = $this->_params['data']['production_type_id'];
            $ssFilter->report['contract_type_bh']       = $this->_params['data']['contract_type_bh'];
            $ssFilter->report['product_cat_id']         = $this->_params['data']['product_cat_id'];
            $ssFilter->report['code']                   = $this->_params['data']['code'];

            $this->_params['ssFilter'] = $ssFilter->report;
            $branchs = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));

            // Lấy dữ liệu doanh số.
            $where_contract = array(
                'filter_date_begin'         => $ssFilter->report['date_begin'],
                'filter_date_end'           => $ssFilter->report['date_end'],
                'sale_branch_id'            => $ssFilter->report['sale_branch_id'],
                'production_type_id'        => $ssFilter->report['production_type_id'],
                'contract_type_bh'          => $ssFilter->report['contract_type_bh'],
                'code'                      => $ssFilter->report['code'],
                'filter_status_type'        => 'production_department_type',
                'filter_status'             => 'success',
                'date_type'                 => 'production_date',
            );
            $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report(array('ssFilter' => $where_contract), array('task' => 'join-contact'));
            $kovProduct             = $this->getServiceLocator()->get('Admin\Model\KovProductsTable')->listItem(null, array('task' => 'cache'));
            $productReturn          = $this->getServiceLocator()->get('Admin\Model\ProductReturnTable')->listItem(null, array('task' => 'cache'));

            // Tạo dòng tính tổng cở cuối cùng
            $total_table['stock'] = '';
            $total_table['new'] = 0;
            $total_table['production'] = 0;
            $total_table['sales'] = 0;
            // mảng chứa tổng giá của các cơ sở
            foreach ($branchs as $key => $value){
                $total_table_branch[$key] = 0;
            }

            $xhtmlItems = '';
            foreach ($contracts as $keys => $values){
                $options = unserialize($values['options']);
                $code_return    = '';// Mã đơn hàng có sẵn
                $cost_new       = 0;// Chi phí sản xuất mới
                $cost_total     = 0; // Tổng giá vốn mặc định
                $sales_total    = 0; // Tổng giá bán

                // Tạo biến lưu trữ dữ liệu chi phí nhập hàng từ các cơ sở khác
                $cost_other_branch = array();
                foreach ($branchs as $key_b => $value_b){
                    $cost_other_branch[$key_b] = 0;
                }

                foreach ($options['product'] as $key => $value){
                    if($ssFilter->report['product_cat_id']){
                        if($ssFilter->report['product_cat_id'] == $value['product_id']){
                            $cost_total += $value['capital_default'];
                            $sales_total += $value['price'];

                            if(!empty($value['stock']) && strpos($code_return, $value['stock']) === false){
                                $code_return .=  $value['stock'].',';
                            }
                            if(empty($value['stock'])){
                                $cost_new += $value['capital_default'];
                            }
                            else{
                                if(!empty($value['sale_branch_id'])){
                                    $cost_other_branch[$value['sale_branch_id']] += $value['capital_default'];
                                    $total_table_branch[$value['sale_branch_id']] += $value['capital_default'];
                                }
                            }
                        }
                    }
                    else{

                        if($values['kov_status']){
                            if($kovProduct[$value['product_id']]['product_type'] == 1){
                                if($values['shipped'] == 1){
                                    $cost_total += $value['capital_default'];
//                                    $sales_total += $value['price'];
                                    $sales_total += $value['total'];
                                    if(!empty($value['product_return_id']) && strpos($code_return, $productReturn[$value['product_return_id']]['contract_code']) === false){
                                        $code_return .=  $productReturn[$value['product_return_id']]['contract_code'].',';
                                    }
                                    if(empty($value['product_return_id'])){
                                        $cost_new += $value['capital_default'];
                                    }
                                    else{
                                        if(!empty($productReturn[$value['product_return_id']]['sale_branch_id'])){
                                            $cost_other_branch[$productReturn[$value['product_return_id']]['sale_branch_id']] += $value['capital_default'];
                                            $total_table_branch[$productReturn[$value['product_return_id']]['sale_branch_id']] += $value['capital_default'];
                                        }
                                    }
                                }
                            }
                            else{
                                $cost_total += $value['capital_default'];
//                                $sales_total += $value['price'];
                                $sales_total += $value['total'];
                                if(!empty($value['product_return_id']) && strpos($code_return, $productReturn[$value['product_return_id']]['contract_code']) === false){
                                    $code_return .=  $productReturn[$value['product_return_id']]['contract_code'].',';
                                }
                                if(empty($value['product_return_id'])){
                                    $cost_new += $value['capital_default'];
                                }
                                else{
                                    if(!empty($productReturn[$value['product_return_id']]['sale_branch_id'])){
                                        $cost_other_branch[$productReturn[$value['product_return_id']]['sale_branch_id']] += $value['capital_default'];
                                        $total_table_branch[$productReturn[$value['product_return_id']]['sale_branch_id']] += $value['capital_default'];
                                    }
                                }
                            }
                        }
                        else{
                            $cost_total += $value['capital_default'];
                            $sales_total += $value['price'];
                            if(!empty($value['stock']) && strpos($code_return, $value['stock']) === false){
                                $code_return .=  $value['stock'].',';
                            }
                            if(empty($value['stock'])){
                                $cost_new += $value['capital_default'];
                            }
                            else{
                                if(!empty($value['sale_branch_id'])){
                                    $cost_other_branch[$value['sale_branch_id']] += $value['capital_default'];
                                    $total_table_branch[$value['sale_branch_id']] += $value['capital_default'];
                                }
                            }
                        }
                    }
                }

                $list_value_cost_blanch = '';
                foreach ($cost_other_branch as $key => $value){
                    $list_value_cost_blanch .= '<td class="mask_currency text-right">'.$value.'</td>';
                }
                $percent_production  = ($sales_total > 0 ? round($cost_total / $sales_total * 100, 2) : 0);
                $xhtmlItems .= '<tr data-key="'.$values['id'].'" data-same="'.($code_return && substr($values['code'],0,5) == substr($code_return, 0, 5) ? 'same' : ($code_return && substr($values['code'],0,5) !== substr($code_return, 0,5) ? 'other' :'')).'">
        		                <td class="text-bold">'.$values['code'].'</td>
        						<td class="mask_currency">'.$code_return.'</td>
        						<td class="mask_currency text-right">'.$cost_new.'</td>
        						'.$list_value_cost_blanch.'
        						<td class="mask_currency text-right">'.$cost_total.'</td>
        						<td class="mask_currency text-right">'.$sales_total.'</td>
        						<td class="mask_currency text-right">'.$percent_production.'%</td>';
                $xhtmlItems .=  '</tr>';

                $total_table['stock']      .= $code_return;
                $total_table['new']        += $cost_new;
                $total_table['production'] += $cost_total;
                $total_table['sales']      += $sales_total;
            }

            $list_value_total_blanch = '';
            foreach ($total_table_branch as $key => $value){
                $list_value_total_blanch .= '<td class="mask_currency text-right">'.$value.'</td>';
            }
            $percent_production_total   = ($total_table['sales'] > 0 ? round($total_table['production'] / $total_table['sales'] * 100, 2) : 0);
            $xhtmlItems .= '<tr class="text-red text-bold">
        		                <td class="text-bold">Tổng</td>
        						<td class="mask_currency"></td>
        						<td class="mask_currency text-right">'.$total_table['new'].'</td>
        						'.$list_value_total_blanch.'
        						<td class="mask_currency text-right">'.$total_table['production'].'</td>
        						<td class="mask_currency text-right">'.$total_table['sales'].'</td>
        						<td class="mask_currency text-right">'.$percent_production_total.'%</td>';
            $xhtmlItems .=  '</tr>';

            $list_branch = '';
            foreach ($branchs as $key => $value){
                $list_branch .= '<th width="140" rowspan="2">'.$value['name'].'</th>';
            }
            $result['reportTable'] = '<thead>
                        				    <tr>
                            					<th width="120" class="text-center">Mã đơn</th>
                            					<th width="200" class="text-center">Mã đơn hàng có sẵn</th>
                            					<th width="140" class="text-center">Sản xuất mới</th>
                            					'.$list_branch.'
                            					<th width="140" class="text-center">Tổng giá vốn mặc định</th>
                            					<th width="140" class="text-center">Giá bán</th>
                            					<th width="140" class="text-center">% Giá vốn mặc định</th>
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
            $this->_params['ssFilter']          = $ssFilter->report;

            // Set giá trị cho form
            $myForm	= new \Report\Form\Report($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);

            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['saleGroup']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['caption']        = 'Báo cáo nhập hàng cơ sở';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Báo cáo theo status
    public function statusAction() {
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
            $ssFilter->report['date_begin']         = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']           = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id']     = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['production_type_id'] = $this->_params['data']['production_type_id'];

            $this->_params['ssFilter'] = $ssFilter->report;

            // Xác định ngày tháng tìm kiếm
            $date           = new \ZendX\Functions\Date();
            $day_begin  = strtotime($date->formatToData($ssFilter->report['date_begin']));
            $day_end    = strtotime($date->formatToData($ssFilter->report['date_end']));
            $number_day = abs($day_end - $day_begin) / 86400;

            // Tạo mảng lưu báo cáo.
            $data_report = [];
            for ($i = 0; $i <= $number_day; $i++) {
                $day = date('Y-m-d', $day_begin + $i*86400);

                $data_report[$day]['cancel_contract'] = 0;
                $data_report[$day]['cancel_sales']    = 0;
                $data_report[$day]['return_contract'] = 0;
                $data_report[$day]['return_sales']    = 0;
                $data_report[$day]['money_contract']  = 0;
                $data_report[$day]['money_sales']     = 0;
                $data_report[$day]['success_contract']  = 0;
                $data_report[$day]['success_sales']     = 0;
            }

            $data_report['total']['cancel_contract'] = 0;
            $data_report['total']['cancel_sales']    = 0;
            $data_report['total']['return_contract'] = 0;
            $data_report['total']['return_sales']    = 0;
            $data_report['total']['money_contract']  = 0;
            $data_report['total']['money_sales']     = 0;
            $data_report['total']['success_contract']  = 0;
            $data_report['total']['success_sales']     = 0;

            // Lấy dữ liệu doanh số.
            $where_contract = array(
                'filter_date_begin'  => $ssFilter->report['date_begin'],
                'filter_date_end'    => $ssFilter->report['date_end'],
                'sale_branch_id'     => $ssFilter->report['sale_branch_id'],
                'production_type_id' => $ssFilter->report['production_type_id'],
                'date_type'          => 'production_date_send', // lọc theo ngày trạng thái đã giao hàng
            );
            $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report(array('ssFilter' => $where_contract), array('task' => 'join-contact'));
            foreach ($contracts as $key => $value){
                $options  = unserialize($value['options']);
                $products = $options['product'];
                if($value['kov_status']){
                    $vat = $value['vat'];
                }
                else{
                    $vat      = 0;
                    foreach ($products as $key_p => $value_p){
                        $vat += $value_p['vat'];
                    }
                }

                // SẢN XUẤT - Hủy không gửi
                if($value['production_department_type'] == STATUS_CONTRACT_PRODUCT_NOT_SEND) {
                    $data_report[$value['date']]['cancel_contract'] += 1;
                    $data_report['total']['cancel_contract'] += 1;

                    $data_report[$value['date']]['cancel_sales'] += $value['price_total'];
                    $data_report['total']['cancel_sales'] += $value['price_total'];

                    $data_report[$value['date']]['cancle_vat'] += $vat;
                    $data_report['total']['cancle_vat'] += $vat;
                }
                if($value['status_acounting_id'] == STATUS_CONTRACT_ACOUNTING_RETURN || $value['status_acounting_id'] == STATUS_CONTRACT_ACOUNTING_CANCEL_RETURN) {
                    $data_report[$value['date']]['return_contract'] += 1;
                    $data_report['total']['return_contract'] += 1;

                    $data_report[$value['date']]['return_sales'] += $value['price_total'];
                    $data_report['total']['return_sales'] += $value['price_total'];

                    $data_report[$value['date']]['return_vat'] += $vat;
                    $data_report['total']['return_vat'] += $vat;
                }
                if($value['status_acounting_id'] == STATUS_CONTRACT_ACOUNTING_MONEY) {
                    $data_report[$value['date']]['money_contract'] += 1;
                    $data_report['total']['money_contract'] += 1;

                }
                $data_report[$value['date']]['money_sales'] += $value['price_paid'];
                $data_report['total']['money_sales'] += $value['price_paid'];

                // Giữ lại bưu điện
                if($value['status_check_id'] == STATUS_CONTRACT_CHECK_KEEP){
                    $data_report[$value['date']]['keep_contract'] += 1;
                    $data_report['total']['keep_contract'] += 1;
                    $data_report[$value['date']]['keep_sales'] += $value['price_total'];
                    $data_report['total']['keep_sales'] += $value['price_total'];
                }
                // Đang vận chuyển
                if($value['status_check_id'] == STATUS_CONTRACT_CHECK_SENDING){
                    $data_report[$value['date']]['send_contract'] += 1;
                    $data_report['total']['send_contract'] += 1;
                    $data_report[$value['date']]['send_sales'] += $value['price_total'];
                    $data_report['total']['send_sales'] += $value['price_total'];
                }
                // Đang phát
                if($value['status_check_id'] == STATUS_CONTRACT_CHECK_POST){
                    $data_report[$value['date']]['post_contract'] += 1;
                    $data_report['total']['post_contract'] += 1;
                    $data_report[$value['date']]['post_sales'] += $value['price_total'];
                    $data_report['total']['post_sales'] += $value['price_total'];
                }
                // Hàng hoàn
                if($value['status_check_id'] == STATUS_CONTRACT_CHECK_RETURN){
                    $data_report[$value['date']]['return_contract'] += 1;
                    $data_report['total']['return_contract'] += 1;
                    $data_report[$value['date']]['return2_sales'] += $value['price_total'];
                    $data_report['total']['return2_sales'] += $value['price_total'];
                }
                // Thành công
                if($value['status_check_id'] == STATUS_CONTRACT_CHECK_SUCCESS) {
                    $data_report[$value['date']]['success_contract'] += 1;
                    $data_report['total']['success_contract'] += 1;

                    $data_report[$value['date']]['success_sales'] += $value['price_total'];
                    $data_report['total']['success_sales'] += $value['price_total'];

                    $data_report[$value['date']]['success_vat'] += $vat;
                    $data_report['total']['success_vat'] += $vat;
                }

                if($value['production_department_type'] == STATUS_CONTRACT_PRODUCT_POST){
                    $data_report[$value['date']]['sending_contract'] += 1;
                    $data_report['total']['sending_contract'] += 1;

                    $data_report[$value['date']]['sending_sales'] += $value['price_total'] ;
                    $data_report['total']['sending_sales'] += $value['price_total'];

                    $data_report[$value['date']]['sending_vat'] += $vat;
                    $data_report['total']['sending_vat'] += $vat;
                }
            }

            // Tham số bảng báo cáo
            $xhtmlItems = '';
            foreach ($data_report as $key => $value){
                $style_class = $key == 'total' ? 'text-bold text-red': '';
                $title = $key == 'total' ? 'Tổng' : $key;
                $xhtmlItems .= '<tr class="'.$style_class.'">
        		                <td class="text-bold text-center">'.$title.'</td>
        		                
        						<td class="mask_currency text-right">'.$value['sending_contract'].'</td>
        						<td class="mask_currency text-right">'.$value['sending_sales'].'</td>
        						<td class="mask_currency text-right">'.$value['sending_vat'].'</td>
        		                
        						<td class="mask_currency text-right">'.($value['sending_contract']-$value['keep_contract']-$value['send_contract']-$value['post_contract']-$value['return2_contract']-$value['success_contract']).'</td>
        						<td class="mask_currency text-right">'.($value['sending_sales']-$value['keep_sales']-$value['send_sales']-$value['post_sales']-$value['return2_sales']-$value['success_sales']).'</td>
        		                
        						<td class="mask_currency text-right">'.$value['cancel_contract'].'</td>
        						<td class="mask_currency text-right">'.$value['cancel_sales'].'</td>
        						<td class="mask_currency text-right">'.$value['cancel_vat'].'</td>
        						
        						<td class="mask_currency text-right">'.$value['return_contract'].'</td>
        						<td class="mask_currency text-right">'.$value['return_sales'].'</td>
        						<td class="mask_currency text-right">'.$value['return_vat'].'</td>
        						
        						<td class="mask_currency text-right">'.$value['success_contract'].'</td>
        						<td class="mask_currency text-right">'.$value['success_sales'].'</td>
        						<td class="mask_currency text-right">'.$value['success_vat'].'</td>
        						
        						<td class="mask_currency text-right">'.$value['money_contract'].'</td>
        						<td class="mask_currency text-right">'.$value['money_sales'].'</td>
        						
        						<td class="mask_currency text-right">'.($value['success_contract'] - $value['money_contract']).'</td>
        						<td class="mask_currency text-right">'.($value['success_sales'] - $value['money_sales']).'</td>
        					</tr>';
            }

            $result['reportTable'] = '<thead>
                        				    <tr>
                            					<th width="150" rowspan="2" class="text-center">Ngày tháng</th>
                            					<th width="120" colspan="3" class="text-center">Gửi đi</th>
                            					<th width="120" colspan="2" class="text-center">Đang xử lý</th>
                            					<th width="120" colspan="3" class="text-center">Hủy không gửi</th>
                            					<th width="120" colspan="3" class="text-center">Đã nhận hoàn</th>
                            					<th width="120" colspan="3" class="text-center">Thành công </th>
                            					<th width="120" colspan="2" class="text-center">Đã nhận tiền </th>
                            					<th width="120" colspan="2" class="text-center">Công nợ </th>
                        					</tr>
                        				    <tr>
                            					<th width="120" class="text-center">Số Đơn </th>
                            					<th width="120" class="text-center">Thành tiền </th>
                            					<th width="120" class="text-center">VAT </th>
                            					
                            					<th width="120" class="text-center">Số Đơn </th>
                            					<th width="120" class="text-center">Thành tiền </th>
                            					
                            					<th width="120" class="text-center">Số Đơn </th>
                            					<th width="120" class="text-center">Thành tiền </th>
                            					<th width="120" class="text-center">VAT </th>
                            					
                            					<th width="120" class="text-center">Số Đơn </th>
                            					<th width="120" class="text-center">Thành tiền </th>
                            					<th width="120" class="text-center">VAT </th>
                            					
                            					<th width="120" class="text-center">Số Đơn </th>
                            					<th width="120" class="text-center">Thành tiền </th>
                            					<th width="120" class="text-center">VAT </th>
                            					
                            					<th width="120" class="text-center">Số Đơn </th>
                            					<th width="120" class="text-center">Thành tiền </th>
                            					
                            					<th width="120" class="text-center">Số Đơn </th>
                            					<th width="120" class="text-center">Thành tiền </th>
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
            $this->_viewModel['caption']        = 'Báo cáo theo trạng thái';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function returnAction() {
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));
        $date     = new \ZendX\Functions\Date();
        $number   = new \ZendX\Functions\Number();

        if($this->getRequest()->isPost()) {
            // Lấy giá trị post từ filter
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();

            // Quyền user
//            $curent_user = $this->_userInfo->getUserInfo();
//            $permission_ids = explode(',', $curent_user['permission_ids']);
//            if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids) && !in_array(MANAGER, $permission_ids)){
//                if(in_array(GDCN, $permission_ids)){
//                    $this->_params['data']['sale_branch_id'] = $curent_user['sale_branch_id'];
//                }
//            }

            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin']         = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']           = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id']     = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_id']            = $this->_params['data']['sale_id'];
            $ssFilter->report['product_id']         = $this->_params['data']['product_id'];
            $ssFilter->report['product_type']       = $this->_params['data']['product_type'];

            $this->_params['ssFilter'] = $ssFilter->report;

            // Lấy dữ liệu doanh số.
            $where_return = array(
                'filter_date_begin'         => $ssFilter->report['date_begin'],
                'filter_date_end'           => $ssFilter->report['date_end'],
                'filter_sale_branch_id'     => $ssFilter->report['sale_branch_id'],
                'filter_user_id'            => $ssFilter->report['sale_id'],
                'filter_product_id'         => $ssFilter->report['product_id'],
                'filter_type'               => $ssFilter->report['product_type'],
                'date_type'                 => 'created',
                'order_by'                  => 'created',
                'order'                     => 'desc',
            );
            $returns = $this->getServiceLocator()->get('Admin\Model\ProductReturnKovTable')->listItem(array('ssFilter' => $where_return), array('task' => 'list-item', 'paginator' => false));
            $users   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
            $branchs = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));

            // Tham số bảng báo cáo
            $xhtmlItems = '';
            $total_number = $total_cost = $total_price = 0;
            $total_number_cancle = $total_cost_cancle = $total_price_cancle = 0;
            $total_number_return = $total_cost_return = $total_price_return = 0;

            foreach ($returns as $key => $value){
                $products = $value['products'] ? unserialize($value['products']) : array();
                $countProduct = count($products);

                if ($countProduct > 0) {
                    $rowSpan = 'rowspan="'.$countProduct.'"';
                } else {
                    $rowSpan = '';
                }

                $xhtmlItems .= '<tr>
                                    <td '.$rowSpan.' class="text-center text-middle">'.($key + 1).'</td>
                                    <td '.$rowSpan.' class="text-center text-middle">'.$date->formatToView($value['created'],'d/m/Y H:i:s').'</td>
                                    <td '.$rowSpan.' class="text-bold  text-center text-middle">'.$value['code'].'</td>
                                    <td '.$rowSpan.' class=" text-middle">'.$branchs[$value['sale_branch_id']]['name'].'</td>
                                    <td '.$rowSpan.' class=" text-middle">'.$users[$value['user_id']]['name'].'</td>';

                if ($countProduct > 0) {
                    foreach($products as $key => $item_product) {
                        if ($key == 0) {
                            $total_number += $item_product['numbers'];
                            $total_cost += $item_product['cost'];
                            $total_price += $item_product['price'];

                            $total_number_return += ($item_product['numbers'] - $item_product['numbers_cancle']);
                            $total_cost_return += (($item_product['numbers'] - $item_product['numbers_cancle']) * $item_product['cost']);
                            $total_price_return += (($item_product['numbers'] - $item_product['numbers_cancle']) * $item_product['price']);

                            $total_number_cancle += $item_product['numbers_cancle'];
                            $total_cost_cancle += ($item_product['numbers_cancle'] * $item_product['cost']);
                            $total_price_cancle += ($item_product['numbers_cancle'] * $item_product['price']);
                            
                            $xhtmlItems .= '
                                    <td>' . $item_product['product_code'] . '</td>
                                    <td>' . $item_product['full_name'] . '</td>
                                    <td class="text-center">' . $item_product['numbers'] . '</td>
                                    <td class="text-right">' . number_format($item_product['cost']) . '</td>
                                    <td class="text-right">' . number_format($item_product['price']) . '</td>
                                    
                                    <td class="text-center">' . ($item_product['numbers'] - $item_product['numbers_cancle']) . '</td>
                                    <td class="text-right">' . number_format(($item_product['numbers'] - $item_product['numbers_cancle']) * $item_product['cost']) . '</td>
                                    <td class="text-right">' . number_format(($item_product['numbers'] - $item_product['numbers_cancle']) * $item_product['price']) . '</td>
                                    
                                    <td class="text-center">' . $item_product['numbers_cancle'] . '</td>
                                    <td class="text-right">' . number_format($item_product['numbers_cancle'] * $item_product['cost']) . '</td>
                                    <td class="text-right">' . number_format($item_product['numbers_cancle'] * $item_product['price']). '</td>';
                        }
                    }
                }
                else {
                    $xhtmlItems .= '<td></td><td></td><td></td><td></td><td></td><td></td>';
                }
                $xhtmlItems .= '</tr>';
                if ($countProduct > 0) {
                    foreach($products as $key => $item_product) {
                        if ($key > 0) :
                            $total_number += $item_product['numbers'];
                            $total_cost += $item_product['cost'];
                            $total_price += $item_product['price'];

                            $total_number_return += ($item_product['numbers'] - $item_product['numbers_cancle']);
                            $total_cost_return += (($item_product['numbers'] - $item_product['numbers_cancle']) * $item_product['cost']);
                            $total_price_return += (($item_product['numbers'] - $item_product['numbers_cancle']) * $item_product['price']);

                            $total_number_cancle += $item_product['numbers_cancle'];
                            $total_cost_cancle += ($item_product['numbers_cancle'] * $item_product['cost']);
                            $total_price_cancle += ($item_product['numbers_cancle'] * $item_product['price']);

                            $xhtmlItems .= '
                                    <td>' . $item_product['product_code'] . '</td>
                                    <td>' . $item_product['full_name'] . '</td>
                                    <td class="text-center">' . $item_product['numbers'] . '</td>
                                    <td class="text-right">' . number_format($item_product['cost']) . '</td>
                                    <td class="text-right">' . number_format($item_product['price']) . '</td>
                                    
                                    <td class="text-center">' . ($item_product['numbers'] - $item_product['numbers_cancle']) . '</td>
                                    <td class="text-right">' . number_format(($item_product['numbers'] - $item_product['numbers_cancle']) * $item_product['cost']) . '</td>
                                    <td class="text-right">' . number_format(($item_product['numbers'] - $item_product['numbers_cancle']) * $item_product['price']) . '</td>
                                    
                                    <td class="text-center">' . $item_product['numbers_cancle'] . '</td>
                                    <td class="text-right">' . number_format($item_product['numbers_cancle'] * $item_product['cost']) . '</td>
                                    <td class="text-right">' . number_format($item_product['numbers_cancle'] * $item_product['price']). '</td>
                                </tr>';
                        endif;
                    }
                }
            }

            $xhtmlItems .= '<tr class="text-red">
                                <td colspan="7"></td>
                                <td class="text-center">'.number_format($total_number).'</td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                                
                                <td class="text-center">'.number_format($total_number_return).'</td>
                                <td class="text-right">'.number_format($total_cost_return).'</td>
                                <td class="text-right">'.number_format($total_price_return).'</td>
                                
                                <td class="text-center">'.number_format($total_number_cancle).'</td>
                                <td class="text-right">'.number_format($total_cost_cancle).'</td>
                                <td class="text-right">'.number_format($total_price_cancle).'</td>
                            </tr>';

            $result['reportTable'] = '<thead>
                        				    <tr>
                            					<th rowspan="2" class="text-center">STT</th>
                            					<th rowspan="2" class="text-center">Ngày nhận hoàn</th>
                            					<th rowspan="2" class="text-center">Mã đơn hàng</th>
                            					<th rowspan="2" class="text-center">Chi nhánh</th>
                            					<th rowspan="2" class="text-center">Nhân viên</th>
                            					<th rowspan="2" class="text-center">Mã sản phẩm</th>
                            					<th rowspan="2" class="text-center">Tên sản phẩm</th>
                            					<th rowspan="2" class="text-center">SL sản phẩm</th>
                            					<th rowspan="2" class="text-center">Giá vốn</th>
                            					<th rowspan="2" class="text-center">Giá bán</th>
                            					
                            					<th colspan="3" class="text-center">Hàng hoàn kho</th>
                            					<th colspan="3" class="text-center">Hàng hủy</th>
                            					
                            					
                        					</tr>
                        					<tr>
                        					    <th class="text-center">SL Hoàn</th>
                            					<th class="text-center">Vốn hoàn</th>
                            					<th class="text-center">Giá bán hoàn</th>
                            					
                            					<th class="text-center">SL hủy</th>
                            					<th class="text-center">Vốn hủy</th>
                            					<th class="text-center">Giá bán hủy</th>
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
            $this->_viewModel['caption']        = 'Báo cáo hoàn đơn';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    // Báo cáo kho
    public function stockAction() {
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));
        $date     = new \ZendX\Functions\Date();
        $number   = new \ZendX\Functions\Number();

        if($this->getRequest()->isPost()) {
            // Lấy giá trị post từ filter
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();
            // Gán dữ liệu lọc vào session
            $ssFilter->report['year']           = $this->_params['data']['year'];
            $ssFilter->report['month']          = $this->_params['data']['month'];
            $ssFilter->report['color_group_id'] = $this->_params['data']['color_group_id'];
            $this->_params['ssFilter']          = $ssFilter->report;

            $materials = $this->getServiceLocator()->get('Admin\Model\MaterialTable')->report(array('ssFilter' => $this->_params['ssFilter']), array('task' => 'list-item'));

            if(!empty($materials)){
                // Tạo mảng lưu báo cáo.
                $data_report = [];
                foreach ($materials as $key => $value){
                    $data_report[$value['material_id']]['residual']  = unserialize($value['params'])['number'];
                    $data_report[$value['material_id']]['producted'] = 0;
                }

                // Xác định ngày tháng tìm kiếm
                $date_begin     =  date($this->_params['data']['year'] . '-'. $this->_params['data']['month'] .'-01' );
                $date_end       =  date("Y-m-t", strtotime($date_begin));

                // Lấy dữ liệu doanh số.
                $where_contract = array(
                    'filter_date_begin'  => $date_begin,
                    'filter_date_end'    => $date_end,
                );

                $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report(array('ssFilter' => $where_contract), array('task' => 'join-contact'));
                foreach ($contracts as $key => $value){
                    $options = unserialize($value['options']);

                    foreach ($options['product'] as $kp => $vp){
                        // lấy số lượng sản xuất các sản phẩm có trong bản nhập nguyên liệu đầu kỳ
                        if($vp['number_production'] > 0){
                            if (array_key_exists($vp['product_id'], $data_report)){
                                $data_report[$vp['product_id']]['producted'] += $vp['number_production'];
                            }
                        }

                        // lấy số lượng màu thảm có trong bản nhập nguyên liệu đầu kỳ
                        if($vp['number_carpet'] > 0){
                            if (array_key_exists($vp['carpet_color_id'], $data_report)){
                                $data_report[$vp['carpet_color_id']]['producted'] += $vp['number_carpet'];
                            }
                        }

                        // lấy số lượng màu rối có trong bản nhập nguyên liệu đầu kỳ
                        if($vp['number_tangled'] > 0){
                            if (array_key_exists($vp['tangled_color_id'], $data_report)){
                                $data_report[$vp['tangled_color_id']]['producted'] += $vp['number_tangled'];
                            }
                        }
                    }
                }

                $units = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'unit')), array('task' => 'cache'));
                $units = \ZendX\Functions\CreateArray::create($units, array('key' => 'id', 'value' => 'name'));
                $color_group = $this->getServiceLocator()->get('Admin\Model\ColorGroupTable')->listItem(null, array('task' => 'list-item'));

                // Lấy ra danh sách nguyên liệu và sản phẩm bản theo số lượng
                $carpetColorLists  = $this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem($this->_params, array('task' => 'list-all'))->toArray();
                $tangledColorLists = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem($this->_params, array('task' => 'list-all'))->toArray();
                $productList	   = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'list-item'));
                $arr_metarial      = array_merge($carpetColorLists, $tangledColorLists, $productList);
                $list_name = [];

                foreach ($arr_metarial as $key => $value){
                    $unit = '';
                    if(!empty($value['unit_id'])){
                        $unit = $units[$value['unit_id']];
                    }
                    else{
                        $unit = $units[$color_group[$value['parent']]['unit_id']];
                    }

                    $list_name[$value['id']]['unit']    = $unit;
                    $list_name[$value['id']]['name']    = $value['name'];
                    $list_name[$value['id']]['parent']  = $value['parent'];
                }

                // Tham số bảng báo cáo
                $xhtmlItems = '';
                foreach ($data_report as $key => $value){
                    $title = $list_name[$key]['name'];
                    $unit  = $list_name[$key]['unit'];

                    // Nếu thời gian tìm kiếm trùng với tháng hiện tại thì: số lượng xuất TB/Ngày = Đã xuất / Số ngày tính tới thời gian hiện tại.
                    // Nếu khác thì: số lượng xuất TB/Ngày = Đã xuất / Số ngày trong tháng tìm kiếm.
                    if($this->_params['data']['year'] == date('Y') && $this->_params['data']['month'] == date('m')){
                        $day = date('d');
                        $producted_day = (int)($value['producted'] / $day);
                    }
                    else{
                        $day = explode('-', $date_end)[2];
                        $producted_day = (int)($value['producted'] / $day);
                    }
                    // Dự kiến ngày còn lại
                    $expected = $producted_day > 0 ? (int)(($value['residual'] - $value['producted']) / $producted_day) : 0;
                    if(!empty($this->_params['data']['color_group_id'])){
                        if($list_name[$key]['parent'] == $this->_params['data']['color_group_id']){
                            $xhtmlItems .= '<tr>
                                            <td class="text-bold">'.$title.'</td>
                                            <td class="mask_currency text-center">'.$unit.'</td>
                                            <td class="mask_currency text-right">'.$value['residual'].'</td>
                                            <td class="mask_currency text-right">'.$value['producted'].'</td>
                                            <td class="mask_currency text-right">'.$producted_day.'</td>
                                            <td class="mask_currency text-right">'.($value['residual'] - $value['producted']).'</td>
                                            <td class="mask_currency text-right">'.$expected.'</td>
                                        </tr>';
                        }
                    }
                    else{
                        $xhtmlItems .= '<tr>
                                        <td class="text-bold">'.$title.'</td>
                                        <td class="mask_currency text-center">'.$unit.'</td>
                                        <td class="mask_currency text-right">'.$value['residual'].'</td>
                                        <td class="mask_currency text-right">'.$value['producted'].'</td>
                                        <td class="mask_currency text-right">'.$producted_day.'</td>
                                        <td class="mask_currency text-right">'.($value['residual'] - $value['producted']).'</td>
                                        <td class="mask_currency text-right">'.$expected.'</td>
                                    </tr>';
                    }
                }
            }
            else{
                $xhtmlItems = '';
                $xhtmlItems .= '<tr>
                                    <td colspan="7">Chưa có dữ liệu số dư đầu kỳ</td>
                                </tr>';
            }

            $result['reportTable'] = '<thead>
                        				    <tr>
                            					<th width="200" >Tên + Mã sản phẩm</th>
                            					<th width="150" class="text-center">Đơn vị tính</th>
                            					<th width="150" class="text-center">Số dư đầu kỳ</th>
                            					<th width="150" class="text-center">Đã xuất</th>
                            					<th width="170" class="text-center">Số lượng xuất TB/Ngày</th>
                            					<th width="150" class="text-center">Còn lại</th>
                            					<th width="150" class="text-center">Dự kiến ngày còn lại</th>
                        					</tr>
                        				</thead>
                        				<tbody>
                        				    '. $xhtmlItems .'
                        				</tbody>';

            echo json_encode($result);

            return $this->response;
        } else {
            // Khai báo giá trị ngày tháng
            $default_month     = date('m');
            $default_year       = date('Y');

            $ssFilter->report                   = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['month']          = $ssFilter->report['month'] ? $ssFilter->report['month'] : $default_month;
            $ssFilter->report['year']           = $ssFilter->report['year'] ? $ssFilter->report['year'] : $default_year;
            $ssFilter->report['color_group_id'] = $ssFilter->report['color_group_id'] ? $ssFilter->report['color_group_id'] : '';
            $this->_params['ssFilter']          = $ssFilter->report;

            // Set giá trị cho form
            $myForm	= new \Report\Form\Report($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);

            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['saleGroup']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['caption']        = 'Báo cáo kho nguyên liệu';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function codAction() {
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));
        $date     = new \ZendX\Functions\Date();
        $number   = new \ZendX\Functions\Number();

        if($this->getRequest()->isPost()) {
            // Lấy giá trị post từ filter
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin']         = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']           = $this->_params['data']['date_end'];
            $ssFilter->report['code']               = $this->_params['data']['code'];
            $ssFilter->report['production_type_id'] = $this->_params['data']['production_type_id'];
            $ssFilter->report['product_cat_id']   = $this->_params['data']['product_cat_id'];
            $ssFilter->report['sale_id']   = $this->_params['data']['sale_id'];
            $ssFilter->report['sale_branch_id']   = $this->_params['data']['sale_branch_id'];
            $this->_params['ssFilter']              = $ssFilter->report;

            $users = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));

            // Tạo mảng lưu báo cáo.

            // Lấy dữ liệu doanh số.
            $where_contract = array(
                'filter_date_begin'  => $ssFilter->report['date_begin'],
                'filter_date_end'    => $ssFilter->report['date_end'],
                'code'               => $ssFilter->report['code'],
                'production_type_id' => $ssFilter->report['production_type_id'],
                'sale_id'            => $ssFilter->report['sale_id'],
                'sale_branch_id'     => $ssFilter->report['sale_branch_id'],
                'product_cat_id'     => $ssFilter->report['product_cat_id'],
                'filter_status_type' => 'production_department_type',
                'filter_status'      => 'success',
                'date_type'          => 'production_date',
            );
            if ($this->_params['data']['sale_branch_id']) {
                $where_contract['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            }
            $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report(array('ssFilter' => $where_contract), array('task' => 'join-contact'));
            
            // Tham số bảng báo cáo
            $xhtmlItems = '';
            $avg = [];
            foreach ($contracts as $key => $value){
                $price_transport += $value['price_transport'];
                $price_total += $value['price_total'];
                $avg[] = $value['price_total']?round($value['price_transport']/$value['price_total']*100,2):0;
                $xhtmlItems .= '<tr>
                                <td class="text-bold">'.($key+1).'</td>
                                <td class="text-bold">'.$value['code'].'</td>
                                <td class="mask_currency">'.$users[$value['user_id']]['name'].'</td>
                                <td class="mask_currency text-right">'.$value['price_transport'].'</td>
                                <td class="mask_currency text-right">'.$value['price_total'].'</td>
                                <td class="mask_currency text-right">'.number_format($value['price_total']?round($value['price_transport']/$value['price_total']*100,2):0,2).'%</td>
                            </tr>';
            }
            $xhtmlItems .= '<tr class="text-bold text-red">
                            <td class="text-bold" colspan="2">Tổng</td>
                            <td class="mask_currency"></td>
                            <td class="mask_currency text-right">'.$price_transport.'</td>
                            <td class="mask_currency text-right">'.$price_total.'</td>
                            <td class="mask_currency text-right">'.number_format(round(array_sum($avg) / count($avg),2),2).'%</td>
                        </tr>';

            $result['reportTable'] = '<thead>
                        				    <tr>
                            					<th width="100">STT</th>
                            					<th width="200" >Mã đơn hàng</th>
                            					<th width="150" class="text-center">Sales</th>
                            					<th width="150" class="text-center">Cước COD</th>
                            					<th width="150" class="text-center">Tổng giá trị đơn hàng</th>
                            					<th width="170" class="text-center">% COD / Tổng GT đơn hàng</th>
                        					</tr>
                        				</thead>
                        				<tbody>
                        				    '. $xhtmlItems .'
                        				</tbody>';

            echo json_encode($result);

            return $this->response;
        } else {
            // Khai báo giá trị ngày tháng
            $default_month     = date('m');
            $default_year       = date('Y');

            $ssFilter->report                   = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['month']          = $ssFilter->report['month'] ? $ssFilter->report['month'] : $default_month;
            $ssFilter->report['year']           = $ssFilter->report['year'] ? $ssFilter->report['year'] : $default_year;
            $ssFilter->report['color_group_id'] = $ssFilter->report['color_group_id'] ? $ssFilter->report['color_group_id'] : '';
            $this->_params['ssFilter']          = $ssFilter->report;

            // Set giá trị cho form
            $myForm	= new \Report\Form\Report($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);

            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['saleGroup']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['caption']        = 'Báo cáo Cước vận chuyển';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    # báo cáo nhập hàng
    public function importAction() {
        $date     = new \ZendX\Functions\Date();
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));
        if($this->getRequest()->isPost()) {
            $user               = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
            $status_check_arr   = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'ghtk-status')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
            $status_check_vtp   = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'viettel-status')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
            $status_accounting  = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-acounting')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
            $status_sales_arr   = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));

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
            $ssFilter->report['date_begin']             = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end']               = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id']         = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['paid_cost']              = $this->_params['data']['paid_cost'];
            $ssFilter->report['code']                   = $this->_params['data']['code'];
            $ssFilter->report['sale_branch_id']         = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_id']                = $this->_params['data']['sale_id'];
//            $ssFilter->report['filter_status_type']     = $this->_params['data']['filter_status_type'];
            $ssFilter->report['filter_status_sale']     = $this->_params['data']['filter_status_sale'];
            $ssFilter->report['filter_status_check']    = $this->_params['data']['filter_status_check'];
            $ssFilter->report['filter_status_accounting']= $this->_params['data']['filter_status_accounting'];

            $this->_params['ssFilter'] = $ssFilter->report;

            // Lấy dữ liệu doanh số.
            $where_contract = array(
                'filter_date_begin'         => $ssFilter->report['date_begin'],
                'filter_date_end'           => $ssFilter->report['date_end'],
                'code'                      => $ssFilter->report['code'],
                'paid_cost'                 => $ssFilter->report['paid_cost'],
                'sale_branch_id'            => $ssFilter->report['sale_branch_id'],
                'sale_id'                   => $ssFilter->report['sale_id'],
                'filter_status_sale'        => $ssFilter->report['filter_status_sale'],
                'filter_status_check'       => $ssFilter->report['filter_status_check'],
                'filter_status_accounting'  => $ssFilter->report['filter_status_accounting'],
                'date_type'                 => 'shipped_date',
                'order'                     => 'shipped_date',
            );
            $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report(array('ssFilter' => $where_contract), array('task' => 'join-contact'));

            $xhtmlItems = '';
            $sum_cost_crm = $sum_cost_kov = $sum_price_total = $sum_price_paid = $sum_price_transport = $sum_number = 0;
            foreach ($contracts as $keys => $item){
                $id = $item['id'];
                $options = unserialize($item['options']);
                $rowSpan = 'rowspan="'.count($options['product']).'"';
                $product_row_1 = $product_row_2 = '';
                $total_cost_kov = $total_cost_crm = 0;
                foreach ($options['product'] as $key => $value){
                    $cost_crm = $value['cost_new'] * $value['numbers'];
                    $cost_kov = $value['cost'] * $value['numbers'];

                    $total_cost_crm += $cost_crm;
                    $total_cost_kov += $cost_kov;

                    $sum_cost_crm += $cost_crm;
                    $sum_cost_kov += $cost_kov;
                    $sum_number += $value['numbers'];

                    if($key == 0){
                        $product_row_1 .= '<td width="200">'.$value['full_name'].'</td>';
                        $product_row_1 .= '<td class="text-center">'.$value['numbers'].'</td>';
                        if(in_array(SYSTEM, $permission_ids) || in_array(ADMIN, $permission_ids)){
                            $product_row_1 .= '<td class="mask_currency text-right">'.$cost_kov.'</td>';
                        }
                        $product_row_1 .= '<td class="mask_currency text-right">'.$cost_crm.'</td>';
                    }
                    else{
                        $product_row_2 .= '<tr>';
                        $product_row_2 .= '<td width="200">'.$value['full_name'].'</td>';
                        $product_row_2 .= '<td class="text-center">'.$value['numbers'].'</td>';
                        if(in_array(SYSTEM, $permission_ids) || in_array(ADMIN, $permission_ids)){
                            $product_row_2 .= '<td class="mask_currency text-right">'.$cost_kov.'</td>';
                        }
                        $product_row_2 .= '<td class="mask_currency text-right">'.$cost_crm.'</td>';
                        $product_row_2 .= '</tr>';
                    }
                }
                $status_sales           = $item['status_id'] ? $status_sales_arr[$item['status_id']]['name'] : '';
                if($item['unit_transport'] == 'viettel')
                    $status_check           = $item['ghtk_status'] ? $status_check_vtp[$item['ghtk_status']]['name'] : '';
                else
                    $status_check           = $item['ghtk_status'] ? $status_check_arr[$item['ghtk_status']]['name'] : '';
                $status_acccounting     = $item['status_acounting_id'] ? $status_accounting[$item['status_acounting_id']]['name'] : '';

                $status         = 'Sales: '.$status_sales.'<br>'.'Giục đơn: '.$status_check.'<br>'.'Kế toán: '.$status_acccounting;
                $paid_cost      = $item['paid_cost'] == 't' ? 'Đã thanh toán' : 'Chưa thanh toán';
                $shipped_date   = $date->formatToView($item['shipped_date']);
                $code           = $item['code'];
                $user_name      = $item['user_id'] ? $user[$item['user_id']]['name'] : '';
                $price_transport= $item['price_transport'] + $item['ship_ext'];
                $price_total    = $item['price_total'];
                $price_paid     = $item['price_paid'] - $item['price_deposits'];

                $sum_price_paid         += $price_paid;
                $sum_price_total        += $price_total;
                $sum_price_transport    += $price_transport;

                if(in_array(SYSTEM, $permission_ids) || in_array(ADMIN, $permission_ids)){
                    $b1     =   '<td '.$rowSpan.' class="mask_currency text-right">'. ($total_cost_crm - $total_cost_kov) .'</td>';
                }



                $xhtmlItems .= '<tr>
        						<td '.$rowSpan.' class="text-center"><input type="checkbox" name="cid[]" class="checkboxes" id="cid[]" value="'.$id.'"></td>
        						<td '.$rowSpan.' class="text-center">'.($keys+1).'</td>
        						<td '.$rowSpan.' class="text-center"><a href="javascript:;" class="" onclick="javascript:popupAction(\'/xadmin/contract/update-price-cost/\', {\'id\': \''.$item['id'].'\'});"><i class="fa fa-pencil-square-o"></i></a></td>
        						<td '.$rowSpan.'>'.$status.'</td>
        						<td '.$rowSpan.' class="text-center">'.$paid_cost.'</td>
        						<td '.$rowSpan.' class="text-center">'.$shipped_date.'</td>
        		                <td '.$rowSpan.' class="text-bold text-center">'.$code.'</td>
        						<td '.$rowSpan.' class="">'.$user_name.'</td>
        						'.$product_row_1.'
        						<td '.$rowSpan.' class="mask_currency text-right">'.$price_transport.'</td>
        						<td '.$rowSpan.' class="mask_currency text-right">'.$price_total.'</td>
        						<td '.$rowSpan.' class="mask_currency text-right">'.$price_paid.'</td>
        						'.$b1.'
        						';
                $xhtmlItems .=  '</tr>';
                $xhtmlItems .=  $product_row_2;
            }
            if(in_array(SYSTEM, $permission_ids) || in_array(ADMIN, $permission_ids)){
                $h1 = '<th width="140" class="text-center">Phí dịch vụ</th>';
                $h2 = '<th width="140" class="mask_currency text-right text-red">'. abs($sum_cost_crm - $sum_cost_kov) .'</th>';

                $kov1 = '<th width="140" class="text-center">Giá vốn kov</th>';
                $kov2 = '<th width="140" class="mask_currency text-right text-red">'.$sum_cost_kov.'</th>';
            }
            $result['reportTable'] = '<thead>
                        				    <tr>
                                                <th width="30" class="table-checkbox fix-head"><input type="checkbox" class="group-checkable" data-set="#table-manager .checkboxes"/></th>
                            					<th width="50" class="text-center">STT</th>
                            					<th width="20" class="text-center"></th>
                            					<th width="120" class="text-center">Trạng thái</th>
                            					<th width="140" class="text-center">Thanh toán giá vốn</th>
                            					<th width="140" class="text-center">Ngày tháng</th>
                            					<th width="140" class="text-center">Mã số đơn</th>
                            					<th width="140" class="text-center">Nhân viên</th>
                            					<th width="200" class="text-center">Sản phẩm</th>
                            					<th width="140" class="text-center">Số lượng</th>
                            					'.$kov1.'
                            					<th width="140" class="text-center">Giá vốn crm</th>
                            					<th width="140" class="text-center">Phí ship</th>
                            					<th width="140" class="text-center">Giá bán</th>
                            					<th width="140" class="text-center">Đã thanh toán</th>
                            					'.$h1.'
                        					</tr>
                        				    <tr>
                            					<th width="50" class="text-center"></th>
                            					<th width="50" class="text-center"></th>
                            					<th width="120" class="text-center"></th>
                            					<th width="140" class="text-center"></th>
                            					<th width="140" class="text-center"></th>
                            					<th width="140" class="text-center"></th>
                            					<th width="140" class="text-center"></th>
                            					<th width="200" class="text-center"></th>
                            					<th width="200" class="text-center"></th>
                            					<th width="140" class="mask_currency text-center text-red">'.$sum_number.'</th>
                            					'.$kov2.'
                            					<th width="140" class="mask_currency text-right text-red">'.$sum_cost_crm.'</th>
                            					<th width="140" class="mask_currency text-right text-red">'.$sum_price_transport.'</th>
                            					<th width="140" class="mask_currency text-right text-red">'.$sum_price_total.'</th>
                            					<th width="140" class="mask_currency text-right text-red">'.$sum_price_paid.'</th>
                            					'.$h2.'
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

            $ssFilter->report                       = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']         = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $default_date_begin;
            $ssFilter->report['date_end']           = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $default_date_end;
            $ssFilter->report['sale_branch_id']     = $ssFilter->report['sale_branch_id'] ? $ssFilter->report['sale_branch_id'] : '';
            $ssFilter->report['sale_id']            = $ssFilter->report['sale_id'] ? $ssFilter->report['sale_id'] : '';
            $ssFilter->report['code']               = $ssFilter->report['code'] ? $ssFilter->report['code'] : '';
            $ssFilter->report['paid_cost']          = $ssFilter->report['paid_cost'] ? $ssFilter->report['paid_cost'] : '';
//            $ssFilter->report['filter_status_type'] = $ssFilter->report['filter_status_type'] ? $ssFilter->report['filter_status_type'] : '';
            $ssFilter->report['filter_status_sale'] = $ssFilter->report['filter_status_sale'] ? $ssFilter->report['filter_status_sale'] : '';
            $ssFilter->report['filter_status_check']= $ssFilter->report['filter_status_check'] ? $ssFilter->report['filter_status_check'] : '';
            $ssFilter->report['filter_status_accounting']= $ssFilter->report['filter_status_accounting'] ? $ssFilter->report['filter_status_accounting'] : '';

            $this->_params['ssFilter']          = $ssFilter->report;

            // Set giá trị cho form
//            $myForm	= new \Report\Form\Report($this->getServiceLocator(), $ssFilter->report);
            $myForm	= new \Report\Form\Report($this->getServiceLocator(), $this->_params);
            $myForm->setData($ssFilter->report);

            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['caption']        = 'Báo cáo nhập hàng';
        }

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }
}




















