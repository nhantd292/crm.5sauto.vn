<?php

namespace Report\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class IndexController extends ActionController {
    
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

    public function branchAction() {
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));
        $date     = new \ZendX\Functions\Date();
        $number   = new \ZendX\Functions\Number();
        
        if($this->getRequest()->isPost()) {
            // Lấy giá trị post từ filter
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();
            
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin'] = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end'] = $this->_params['data']['date_end'];
            
            $this->_params['ssFilter']  = $ssFilter->report;
            
            // Xác định ngày tháng tìm kiếm
            $month          = date('m', strtotime($date->formatToData($ssFilter->report['date_begin'])));
            $year           = date('Y', strtotime($date->formatToData($ssFilter->report['date_begin'])));
            $date_begin     = $date->formatToData($ssFilter->report['date_begin']);
            $date_end       = $date->formatToData($ssFilter->report['date_end']);
            
            // Dữ liệu gốc
            $saleBranch     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
            $salesTarget    = $this->getServiceLocator()->get('Admin\Model\SalesTargetTable')->getItem(array('month' => $month, 'year' => $year), array('task' => 'month-year'));
            $contract       = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report($this->_params, array('task' => 'date'));
            $bill           = $this->getServiceLocator()->get('Admin\Model\BillTable')->report($this->_params, array('task' => 'date'));
            
            // Số liệu chỉ tiêu
            $arrSalesTarget = unserialize($salesTarget['params']);
            
            // Số liệu tạm tính
            $arrContract = array();
            foreach ($contract AS $keyContract => $valueContract){
                $arrContract[$valueContract['sale_branch_id']] = $arrContract[$valueContract['sale_branch_id']] + (float)$valueContract['price_total'];
            }
            
            // Số liệu hóa đơn
            $arrBillPaidNew     = array(); // Số liệu thực thu mới - đơn hàng trong tháng
            $arrBillPaidOld     = array(); // Số liệu thực thu cũ - đơn hàng nhỏ hơn begin_date
            $arrBillPaid        = array(); // Tổng thực thu
            $arrBillAccrued     = array(); // Tổng thực chi
            $arrBillTotal       = array(); // Tổng thành tiền
            foreach ($bill AS $keyBill => $valueBill){
                if($valueBill['type'] == 'Thu') {
                    if($valueBill['contract_date'] >= $date_begin && $valueBill['contract_date'] <= $date_end) {
                        $arrBillPaidNew[$valueBill['sale_branch_id']] = $arrBillPaidNew[$valueBill['sale_branch_id']] + (float)$valueBill['paid_price'];
                    } elseif ($valueBill['contract_date'] <= $date_begin) {
                        $arrBillPaidOld[$valueBill['sale_branch_id']] = $arrBillPaidOld[$valueBill['sale_branch_id']] + (float)$valueBill['paid_price'];
                    }
                    $arrBillPaid[$valueBill['sale_branch_id']] = $arrBillPaid[$valueBill['sale_branch_id']] + (float)$valueBill['paid_price'];
                } elseif ($valueBill['type'] == 'Chi') {
                    $arrBillAccrued[$valueBill['sale_branch_id']] = $arrBillAccrued[$valueBill['sale_branch_id']] + (float)$valueBill['accrued_price'];
                }
            
                $arrBillTotal[$valueBill['sale_branch_id']] = $arrBillTotal[$valueBill['sale_branch_id']] + (float)$valueBill['paid_price'] - (float)$valueBill['accrued_price'];
            }
            
            // Tham số biểu đồ
            $reportCategories   = array();
            $reportDataTarget   = array('name' => 'Chỉ tiêu');
            $reportDataContract = array('name' => 'Tạm tính');
            $reportDataBill     = array('name' => 'Thực thu');
            
            // Tham số bảng báo cáo
            $xhtmlItems = '';
            foreach ($saleBranch AS $key => $item) {
                $column_name = $item['name'];
                
                $column_salesTarget    = $arrSalesTarget[$key]['price'] ? (float)$arrSalesTarget[$key]['price'] : 0;
                $column_contract       = $arrContract[$key] ? (float)$arrContract[$key] : 0;
                $column_billPaidNew    = $arrBillPaidNew[$key] ? $arrBillPaidNew[$key] : 0;
                $column_billPaidOld    = $arrBillPaidOld[$key] ? $arrBillPaidOld[$key] : 0;
                $column_billPaid       = $arrBillPaid[$key] ? $arrBillPaid[$key] : 0;
                $column_billAccrued    = $arrBillAccrued[$key] ? $arrBillAccrued[$key] : 0;
                $column_billTotal      = $arrBillTotal[$key] ? $arrBillTotal[$key] : 0;
                $column_perContract    = $column_salesTarget ? $column_contract / $column_salesTarget * 100 : 0;
                $column_perBillTotal   = $column_salesTarget ? $column_billTotal / $column_salesTarget * 100 : 0;
                
                $total_salesTarget    += $column_salesTarget;
                $total_contract       += $column_contract;
                $total_billPaidNew    += $column_billPaidNew;
                $total_billPaidOld    += $column_billPaidOld;
                $total_billPaid       += $column_billPaid;
                $total_billAccrued    += $column_billAccrued;
                $total_billTotal      += $column_billTotal;
                
                $reportDataTarget['data'][]     = $column_salesTarget;
                $reportDataContract['data'][]   = $column_contract;
                $reportDataBill['data'][]       = $column_billTotal;
                
                // Dữ liệu bảng
                $xhtmlItems .= '<tr>
                                    <td><b>'. $column_name .'</b></td>
                                    <td class="mask_currency">'. $column_salesTarget .'</td>
                                    <td class="mask_currency active">'. $column_contract .'</td>
                                    <td class="mask_currency">'. $column_billPaidNew .'</td>
                                    <td class="mask_currency">'. $column_billPaidOld .'</td>
                                    <td class="mask_currency">'. $column_billPaid .'</td>
                                    <td class="mask_currency">'. $column_billAccrued .'</td>
                                    <td class="mask_currency success">'. $column_billTotal .'</td>
                                    <td class="text-center"><span style="color: '. $number->colorRevenue($column_perContract) .';">'. round($column_perContract, 2) .'%</span></td>
                                    <td class="text-center"><span style="color: '. $number->colorRevenue($column_perBillTotal) .';">'. round($column_perBillTotal, 2) .'%</span></td>
        						</tr>';
                
                // Tên danh mục biểu đồ
                $reportCategories[] = $column_name;
            }
            
            $total_perContract   = $total_salesTarget ? $total_contract / $total_salesTarget * 100 : 0;
            $total_perBillTotal  = $total_salesTarget ? $total_billTotal / $total_salesTarget * 100 : 0;
            
            $xhtmlItems .= '<tr class="text-red">
        		                <td><b>Tổng</b></td>
        						<td class="mask_currency">'. $total_salesTarget .'</td>
        						<td class="mask_currency active">'. $total_contract .'</td>
        						<td class="mask_currency">'. $total_billPaidNew .'</td>
        						<td class="mask_currency">'. $total_billPaidOld .'</td>
        						<td class="mask_currency">'. $total_billPaid .'</td>
        						<td class="mask_currency">'. $total_billAccrued .'</td>
        						<td class="mask_currency success">'. $total_billTotal .'</td>
        						<td class="text-center"><span style="color: '. $number->colorRevenue($total_perContract) .';">'. round($total_perContract, 2) .'%</span></td>
        						<td class="text-center"><span style="color: '. $number->colorRevenue($total_perBillTotal) .';">'. round($total_perBillTotal, 2) .'%</span></td>
        					</tr>';
            
            $result['reportTable'] = '<thead>
                        				    <tr>
                            					<th>Cơ sở</th>
                            					<th>Chỉ tiêu</th>
                            					<th class="active">Tạm tính</th>
                            					<th>Thực thu mới</th>
                            					<th>Thực thu nợ</th>
                            					<th>Tổng thực thu</th>
                            					<th>Thực chi</th>
                            					<th class="success">Doanh thu</th>
                            					<th class="text-center">Tạm tính</th>
                        						<th class="text-center">Thực thu</th>
                        					</tr>
                        				</thead>
                        				<tbody>
                        				    '. $xhtmlItems .'
                        				</tbody>';

            $result['reportChart'] = array(
                'categories' => $reportCategories,
                'series' => array($reportDataTarget, $reportDataContract, $reportDataBill)
            );
            echo json_encode($result);
            
            return $this->response;
        } else {
            // Khai báo giá trị ngày tháng
            $default_date_begin = date('01/m/Y');
            $default_date_end   = date('t/m/Y');
            
            $ssFilter->report                   = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']     = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $default_date_begin;
            $ssFilter->report['date_end']       = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $default_date_end;
            
            $this->_params['ssFilter']          = $ssFilter->report;
            
            // Set giá trị cho form
            $myForm	= new \Report\Form\Report($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);
            
            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['caption']        = 'Báo cáo - Doanh thu - Cơ sở';
        }
        
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
        
        return $viewModel;
    }
    
    // Báo cáo đội nhóm
    public function groupAction() {
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));
        $date     = new \ZendX\Functions\Date();
        $number   = new \ZendX\Functions\Number();
        
        if($this->getRequest()->isPost()) {
            // Lấy giá trị post từ filter
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();
            
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin'] = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end'] = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            
            $this->_params['ssFilter']  = $ssFilter->report;
            
            // Xác định ngày tháng tìm kiếm
            $month          = date('m', strtotime($date->formatToData($ssFilter->report['date_begin'])));
            $year           = date('Y', strtotime($date->formatToData($ssFilter->report['date_begin'])));
            $date_begin     = $date->formatToData($ssFilter->report['date_begin']);
            $date_end       = $date->formatToData($ssFilter->report['date_end']);
            
            // Dữ liệu gốc
            $saleBranch     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
            $saleGroup      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $saleGroupType  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'group-type')), array('task' => 'cache'));
            $salesTarget    = $this->getServiceLocator()->get('Admin\Model\SalesTargetTable')->getItem(array('month' => $month, 'year' => $year), array('task' => 'month-year'));
            $contract       = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report($this->_params, array('task' => 'date'));
            $bill           = $this->getServiceLocator()->get('Admin\Model\BillTable')->report($this->_params, array('task' => 'date'));
            
            // Số liệu chỉ tiêu
            $arrSalesTarget = unserialize($salesTarget['params']);
            
            // Số liệu tạm tính
            $arrContract = array();
            foreach ($contract AS $keyContract => $valueContract){
                $sale_group_type_id = $saleGroupType[$saleGroup[$valueContract['sale_group_id']]['type']]['id'];
                $arrContract[$valueContract['sale_branch_id']] = $arrContract[$valueContract['sale_branch_id']] + (float)$valueContract['price_total'];
                $arrContract[$valueContract['sale_group_id']] = $arrContract[$valueContract['sale_group_id']] + (float)$valueContract['price_total'];
                $arrContract[$valueContract['sale_branch_id'] .'_'. $sale_group_type_id] = $arrContract[$valueContract['sale_branch_id'] .'_'. $sale_group_type_id] + (float)$valueContract['price_total'];
            }
            
            // Số liệu hóa đơn
            $arrBillPaidNew     = array(); // Số liệu thực thu mới - đơn hàng trong tháng
            $arrBillPaidOld     = array(); // Số liệu thực thu cũ - đơn hàng nhỏ hơn begin_date
            $arrBillPaid        = array(); // Tổng thực thu
            $arrBillAccrued     = array(); // Tổng thực chi
            $arrBillTotal       = array(); // Tổng thành tiền
            foreach ($bill AS $keyBill => $valueBill){
                $sale_group_type_id = $saleGroupType[$saleGroup[$valueBill['sale_group_id']]['type']]['id'];
                
                if($valueBill['type'] == 'Thu') {
                    if($valueBill['contract_date'] >= $date_begin && $valueBill['contract_date'] <= $date_end) {
                        $arrBillPaidNew[$valueBill['sale_branch_id']] = $arrBillPaidNew[$valueBill['sale_branch_id']] + (float)$valueBill['paid_price'];
                        $arrBillPaidNew[$valueBill['sale_group_id']] = $arrBillPaidNew[$valueBill['sale_group_id']] + (float)$valueBill['paid_price'];
                        $arrBillPaidNew[$valueBill['sale_branch_id'] .'_'. $sale_group_type_id] = $arrBillPaidNew[$valueBill['sale_branch_id'] .'_'. $sale_group_type_id] + (float)$valueBill['paid_price'];
                    } elseif ($valueBill['contract_date'] <= $date_begin) {
                        $arrBillPaidOld[$valueBill['sale_branch_id']] = $arrBillPaidOld[$valueBill['sale_branch_id']] + (float)$valueBill['paid_price'];
                        $arrBillPaidOld[$valueBill['sale_group_id']] = $arrBillPaidOld[$valueBill['sale_group_id']] + (float)$valueBill['paid_price'];
                        $arrBillPaidOld[$valueBill['sale_branch_id'] .'_'. $sale_group_type_id] = $arrBillPaidOld[$valueBill['sale_branch_id'] .'_'. $sale_group_type_id] + (float)$valueBill['paid_price'];
                    }
                    $arrBillPaid[$valueBill['sale_branch_id']] = $arrBillPaid[$valueBill['sale_branch_id']] + (float)$valueBill['paid_price'];
                    $arrBillPaid[$valueBill['sale_group_id']] = $arrBillPaid[$valueBill['sale_group_id']] + (float)$valueBill['paid_price'];
                    $arrBillPaid[$valueBill['sale_branch_id'] .'_'. $sale_group_type_id] = $arrBillPaid[$valueBill['sale_branch_id'] .'_'. $sale_group_type_id] + (float)$valueBill['paid_price'];
                } elseif ($valueBill['type'] == 'Chi') {
                    $arrBillAccrued[$valueBill['sale_branch_id']] = $arrBillAccrued[$valueBill['sale_branch_id']] + (float)$valueBill['accrued_price'];
                    $arrBillAccrued[$valueBill['sale_group_id']] = $arrBillAccrued[$valueBill['sale_group_id']] + (float)$valueBill['accrued_price'];
                    $arrBillAccrued[$valueBill['sale_branch_id'] .'_'. $sale_group_type_id] = $arrBillAccrued[$valueBill['sale_branch_id'] .'_'. $sale_group_type_id] + (float)$valueBill['accrued_price'];
                }
            
                $arrBillTotal[$valueBill['sale_branch_id']] = $arrBillTotal[$valueBill['sale_branch_id']] + (float)$valueBill['paid_price'] - (float)$valueBill['accrued_price'];
                $arrBillTotal[$valueBill['sale_group_id']] = $arrBillTotal[$valueBill['sale_group_id']] + (float)$valueBill['paid_price'] - (float)$valueBill['accrued_price'];
                $arrBillTotal[$valueBill['sale_branch_id'] .'_'. $sale_group_type_id] = $arrBillTotal[$valueBill['sale_branch_id'] .'_'. $sale_group_type_id] + (float)$valueBill['paid_price'] - (float)$valueBill['accrued_price'];
            }
            
            // Tham số biểu đồ
            $reportCategories   = array();
            $reportDataTarget   = array('name' => 'Chỉ tiêu');
            $reportDataContract = array('name' => 'Tạm tính');
            $reportDataBill     = array('name' => 'Thực thu');
            
            // Tham số bảng báo cáo
            $arrData = array();
            $xhtmlItems = '';
            foreach ($saleBranch AS $key_branch => $val_branch) {
                if(!empty($ssFilter->report['sale_branch_id'])) {
                    if($key_branch != $ssFilter->report['sale_branch_id']) {
                        continue;
                    } 
                }
                
                $column_salesTarget    = $arrSalesTarget[$key_branch]['price'] ? (float)$arrSalesTarget[$key_branch]['price'] : 0;
                $column_contract       = $arrContract[$key_branch] ? (float)$arrContract[$key_branch] : 0;
                $column_billPaidNew    = $arrBillPaidNew[$key_branch] ? $arrBillPaidNew[$key_branch] : 0;
                $column_billPaidOld    = $arrBillPaidOld[$key_branch] ? $arrBillPaidOld[$key_branch] : 0;
                $column_billPaid       = $arrBillPaid[$key_branch] ? $arrBillPaid[$key_branch] : 0;
                $column_billAccrued    = $arrBillAccrued[$key_branch] ? $arrBillAccrued[$key_branch] : 0;
                $column_billTotal      = $arrBillTotal[$key_branch] ? $arrBillTotal[$key_branch] : 0;
                $column_perContract    = $column_salesTarget ? $column_contract / $column_salesTarget * 100 : 0;
                $column_perBillTotal   = $column_salesTarget ? $column_billTotal / $column_salesTarget * 100 : 0;
                
                $total_salesTarget    += $column_salesTarget;
                $total_contract       += $column_contract;
                $total_billPaidNew    += $column_billPaidNew;
                $total_billPaidOld    += $column_billPaidOld;
                $total_billPaid       += $column_billPaid;
                $total_billAccrued    += $column_billAccrued;
                $total_billTotal      += $column_billTotal;
                
                // Dữ liệu bảng
                $xhtmlItems .= '<tr class="success">
                                    <td><b>'. $val_branch['name'] .'</b></td>
                                    <td class="mask_currency">'. $column_salesTarget .'</td>
                                    <td class="mask_currency active">'. $column_contract .'</td>
                                    <td class="mask_currency">'. $column_billPaidNew .'</td>
                                    <td class="mask_currency">'. $column_billPaidOld .'</td>
                                    <td class="mask_currency">'. $column_billPaid .'</td>
                                    <td class="mask_currency">'. $column_billAccrued .'</td>
                                    <td class="mask_currency success">'. $column_billTotal .'</td>
                                    <td class="text-center"><span style="color: '. $number->colorRevenue($column_perContract) .';">'. round($column_perContract, 2) .'%</span></td>
                                    <td class="text-center"><span style="color: '. $number->colorRevenue($column_perBillTotal) .';">'. round($column_perBillTotal, 2) .'%</span></td>
        						</tr>';
                
                foreach ($saleGroupType AS $key_group_type => $val_group_type) {
                    $column_salesTarget    = $arrSalesTarget[$key_branch .'_'. $key_group_type]['price'] ? (float)$arrSalesTarget[$key_branch .'_'. $key_group_type]['price'] : 0;
                    $column_contract       = $arrContract[$key_branch .'_'. $key_group_type] ? (float)$arrContract[$key_branch .'_'. $key_group_type] : 0;
                    $column_billPaidNew    = $arrBillPaidNew[$key_branch .'_'. $key_group_type] ? $arrBillPaidNew[$key_branch .'_'. $key_group_type] : 0;
                    $column_billPaidOld    = $arrBillPaidOld[$key_branch .'_'. $key_group_type] ? $arrBillPaidOld[$key_branch .'_'. $key_group_type] : 0;
                    $column_billPaid       = $arrBillPaid[$key_branch .'_'. $key_group_type] ? $arrBillPaid[$key_branch .'_'. $key_group_type] : 0;
                    $column_billAccrued    = $arrBillAccrued[$key_branch .'_'. $key_group_type] ? $arrBillAccrued[$key_branch .'_'. $key_group_type] : 0;
                    $column_billTotal      = $arrBillTotal[$key_branch .'_'. $key_group_type] ? $arrBillTotal[$key_branch .'_'. $key_group_type] : 0;
                    $column_perContract    = $column_salesTarget ? $column_contract / $column_salesTarget * 100 : 0;
                    $column_perBillTotal   = $column_salesTarget ? $column_billTotal / $column_salesTarget * 100 : 0;
                    
                    $xhtmlItems .= '<tr class="active">
                                        <td><b>'. $val_group_type['name'] .'</b></td>
                                        <td class="mask_currency">'. $column_salesTarget .'</td>
                                        <td class="mask_currency active">'. $column_contract .'</td>
                                        <td class="mask_currency">'. $column_billPaidNew .'</td>
                                        <td class="mask_currency">'. $column_billPaidOld .'</td>
                                        <td class="mask_currency">'. $column_billPaid .'</td>
                                        <td class="mask_currency">'. $column_billAccrued .'</td>
                                        <td class="mask_currency success">'. $column_billTotal .'</td>
                                        <td class="text-center"><span style="color: '. $number->colorRevenue($column_perContract) .';">'. round($column_perContract, 2) .'%</span></td>
                                        <td class="text-center"><span style="color: '. $number->colorRevenue($column_perBillTotal) .';">'. round($column_perBillTotal, 2) .'%</span></td>
            						</tr>';
                    
                    foreach ($saleGroup AS $key_group => $val_group) {
                        if(($val_group_type['id'] == $val_group['type']) && ($val_group['document_id'] == $val_branch['id']) && ($val_group['status'] == 1 || !empty($arrSalesTarget[$key_group]) || !empty($val_group['arrBillPaidNew']) || !empty($val_group['arrBillPaidOld']))) {
                            $column_salesTarget    = $arrSalesTarget[$key_group]['price'] ? (float)$arrSalesTarget[$key_group]['price'] : 0;
                            $column_contract       = $arrContract[$key_group] ? (float)$arrContract[$key_group] : 0;
                            $column_billPaidNew    = $arrBillPaidNew[$key_group] ? $arrBillPaidNew[$key_group] : 0;
                            $column_billPaidOld    = $arrBillPaidOld[$key_group] ? $arrBillPaidOld[$key_group] : 0;
                            $column_billPaid       = $arrBillPaid[$key_group] ? $arrBillPaid[$key_group] : 0;
                            $column_billAccrued    = $arrBillAccrued[$key_group] ? $arrBillAccrued[$key_group] : 0;
                            $column_billTotal      = $arrBillTotal[$key_group] ? $arrBillTotal[$key_group] : 0;
                            $column_perContract    = $column_salesTarget ? $column_contract / $column_salesTarget * 100 : 0;
                            $column_perBillTotal   = $column_salesTarget ? $column_billTotal / $column_salesTarget * 100 : 0;
                            
                            $xhtmlItems .= '<tr>
                                                <td><b>'. $val_group['name'] .'</b></td>
                                                <td class="mask_currency">'. $column_salesTarget .'</td>
                                                <td class="mask_currency active">'. $column_contract .'</td>
                                                <td class="mask_currency">'. $column_billPaidNew .'</td>
                                                <td class="mask_currency">'. $column_billPaidOld .'</td>
                                                <td class="mask_currency">'. $column_billPaid .'</td>
                                                <td class="mask_currency">'. $column_billAccrued .'</td>
                                                <td class="mask_currency success">'. $column_billTotal .'</td>
                                                <td class="text-center"><span style="color: '. $number->colorRevenue($column_perContract) .';">'. round($column_perContract, 2) .'%</span></td>
                                                <td class="text-center"><span style="color: '. $number->colorRevenue($column_perBillTotal) .';">'. round($column_perBillTotal, 2) .'%</span></td>
                    						</tr>';
                            
                            unset($saleGroup[$key_group]);
                            
                            $keyData = substr_replace("0000000000000", $column_billTotal, '-'. strlen($column_billTotal));
                            $arrData[$keyData . '_' . $key_group] = array(
                                'chart' => array(
                                    'name' => $val_branch['name'] .' - '. $val_group['name'],
                                    'data_bill' => $column_billTotal
                                ),
                            );
                        }
                    }
                }
            }
            
            $total_perContract   = $total_salesTarget ? $total_contract / $total_salesTarget * 100 : 0;
            $total_perBillTotal  = $total_salesTarget ? $total_billTotal / $total_salesTarget * 100 : 0;
            
            $xhtmlItems .= '<tr class="text-red">
        		                <td><b>Tổng</b></td>
        						<td class="mask_currency">'. $total_salesTarget .'</td>
        						<td class="mask_currency active">'. $total_contract .'</td>
        						<td class="mask_currency">'. $total_billPaidNew .'</td>
        						<td class="mask_currency">'. $total_billPaidOld .'</td>
        						<td class="mask_currency">'. $total_billPaid .'</td>
        						<td class="mask_currency">'. $total_billAccrued .'</td>
        						<td class="mask_currency success">'. $total_billTotal .'</td>
        						<td class="text-center"><span style="color: '. $number->colorRevenue($total_perContract) .';">'. round($total_perContract, 2) .'%</span></td>
        						<td class="text-center"><span style="color: '. $number->colorRevenue($total_perBillTotal) .';">'. round($total_perBillTotal, 2) .'%</span></td>
        					</tr>';
            
            $result['reportTable'] = '<thead>
                        				    <tr>
                            					<th>Cơ sở</th>
                            					<th>Chỉ tiêu</th>
                            					<th class="active">Tạm tính</th>
                            					<th>Thực thu mới</th>
                            					<th>Thực thu nợ</th>
                            					<th>Tổng thực thu</th>
                            					<th>Thực chi</th>
                            					<th class="success">Doanh thu</th>
                            					<th class="text-center">Tạm tính</th>
                        						<th class="text-center">Thực thu</th>
                        					</tr>
                        				</thead>
                        				<tbody>
                        				    '. $xhtmlItems .'
                        				</tbody>';

            krsort($arrData);
            foreach ($arrData AS $key_data => $val_data) {
                $reportDataBill['data'][] = $val_data['chart']['data_bill'];
                $reportCategories[] = $val_data['chart']['name'];
            }
            $result['reportChart'] = array(
                'categories' => $reportCategories,
                'series' => array($reportDataBill)
            );
            echo json_encode($result);
            
            return $this->response;
        } else {
            // Khai báo giá trị ngày tháng
            $default_date_begin = date('01/m/Y');
            $default_date_end   = date('t/m/Y');
            $default_sale_branch_id   = $this->_userInfo->getUserInfo('sale_branch_id');
            
            $ssFilter->report                   = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']     = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $default_date_begin;
            $ssFilter->report['date_end']       = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $default_date_end;
            $ssFilter->report['sale_branch_id'] = $ssFilter->report['sale_branch_id'] ? $ssFilter->report['sale_branch_id'] : $default_sale_branch_id;
            
            $this->_params['ssFilter']          = $ssFilter->report;
            
            // Set giá trị cho form
            $myForm	= new \Report\Form\Report($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);
            
            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['caption']        = 'Báo cáo - Doanh thu - Đội nhóm';
        }
        
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
        
        return $viewModel;
    }

    // Báo cáo nhân viên
    public function userAction() {
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
            $month          = date('m', strtotime($date->formatToData($ssFilter->report['date_begin'])));
            $year           = date('Y', strtotime($date->formatToData($ssFilter->report['date_begin'])));
            $date_begin     = $date->formatToData($ssFilter->report['date_begin']);
            $date_end       = $date->formatToData($ssFilter->report['date_end']);
    
            // Dữ liệu gốc
            $saleBranch     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
            $saleGroup      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $saleGroupType  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'group-type')), array('task' => 'cache'));
            $saleUser       = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
            $salesTarget    = $this->getServiceLocator()->get('Admin\Model\SalesTargetTable')->getItem(array('month' => $month, 'year' => $year), array('task' => 'month-year'));
            $contract       = $this->getServiceLocator()->get('Admin\Model\ContractTable')->report($this->_params, array('task' => 'date'));
            $bill           = $this->getServiceLocator()->get('Admin\Model\BillTable')->report($this->_params, array('task' => 'date'));
    
            // Số liệu chỉ tiêu
            $arrSalesTarget = unserialize($salesTarget['params']);
    
            // Số liệu tạm tính
            $arrContract = array();
            foreach ($contract AS $keyContract => $valueContract){
                $arrContract[$valueContract['user_id']] = $arrContract[$valueContract['user_id']] + (float)$valueContract['price_total'];
            }
    
            // Số liệu hóa đơn
            $arrBillPaidNew     = array(); // Số liệu thực thu mới - đơn hàng trong tháng
            $arrBillPaidOld     = array(); // Số liệu thực thu cũ - đơn hàng nhỏ hơn begin_date
            $arrBillPaid        = array(); // Tổng thực thu
            $arrBillAccrued     = array(); // Tổng thực chi
            $arrBillTotal       = array(); // Tổng thành tiền
            foreach ($bill AS $keyBill => $valueBill){
                if($valueBill['type'] == 'Thu') {
                    if($valueBill['contract_date'] >= $date_begin && $valueBill['contract_date'] <= $date_end) {
                        $arrBillPaidNew[$valueBill['user_id']] = $arrBillPaidNew[$valueBill['user_id']] + (float)$valueBill['paid_price'];
                    } elseif ($valueBill['contract_date'] <= $date_begin) {
                        $arrBillPaidOld[$valueBill['user_id']] = $arrBillPaidOld[$valueBill['user_id']] + (float)$valueBill['paid_price'];
                    }
                    $arrBillPaid[$valueBill['user_id']] = $arrBillPaid[$valueBill['user_id']] + (float)$valueBill['paid_price'];
                } elseif ($valueBill['type'] == 'Chi') {
                    $arrBillAccrued[$valueBill['user_id']] = $arrBillAccrued[$valueBill['user_id']] + (float)$valueBill['accrued_price'];
                }
    
                $arrBillTotal[$valueBill['user_id']] = $arrBillTotal[$valueBill['user_id']] + (float)$valueBill['paid_price'] - (float)$valueBill['accrued_price'];
            }
    
            // Tham số biểu đồ
            $reportCategories   = array();
            $reportDataTarget   = array('name' => 'Chỉ tiêu');
            $reportDataContract = array('name' => 'Tạm tính');
            $reportDataBill     = array('name' => 'Thực thu');
    
            // Tham số bảng báo cáo
            $arrData = array();
            foreach ($saleUser AS $key_user => $val_user) {
                if(!empty($ssFilter->report['sale_branch_id'])) {
                    if($val_user['sale_branch_id'] != $ssFilter->report['sale_branch_id']) {
                        continue;
                    }
                }
                if(!empty($ssFilter->report['sale_group_id'])) {
                    if($val_user['sale_group_id'] != $ssFilter->report['sale_group_id']) {
                        continue;
                    }
                }
                if(empty($arrContract[$key_user]) && empty($arrBillTotal[$key_user])) {
                    continue;
                }
            
                $column_salesTarget    = $arrSalesTarget[$key_user]['price'] ? (float)$arrSalesTarget[$key_user]['price'] : 0;
                $column_contract       = $arrContract[$key_user] ? (float)$arrContract[$key_user] : 0;
                $column_billPaidNew    = $arrBillPaidNew[$key_user] ? $arrBillPaidNew[$key_user] : 0;
                $column_billPaidOld    = $arrBillPaidOld[$key_user] ? $arrBillPaidOld[$key_user] : 0;
                $column_billPaid       = $arrBillPaid[$key_user] ? $arrBillPaid[$key_user] : 0;
                $column_billAccrued    = $arrBillAccrued[$key_user] ? $arrBillAccrued[$key_user] : 0;
                $column_billTotal      = $arrBillTotal[$key_user] ? $arrBillTotal[$key_user] : 0;
            
                $total_salesTarget    += $column_salesTarget;
                $total_contract       += $column_contract;
                $total_billPaidNew    += $column_billPaidNew;
                $total_billPaidOld    += $column_billPaidOld;
                $total_billPaid       += $column_billPaid;
                $total_billAccrued    += $column_billAccrued;
                $total_billTotal      += $column_billTotal;
            
                // Dữ liệu bảng
                $xhtml = '<tr>
                            <td><b>'. $val_user['name'] .'</b></td>
                            <td><b>'. $saleBranch[$val_user['sale_branch_id']]['name'] .'</b></td>
                            <td><b>'. $saleGroup[$val_user['sale_group_id']]['name'] .'</b></td>
                            <td class="mask_currency active">'. $column_contract .'</td>
                            <td class="mask_currency">'. $column_billPaidNew .'</td>
                            <td class="mask_currency">'. $column_billPaidOld .'</td>
                            <td class="mask_currency">'. $column_billPaid .'</td>
                            <td class="mask_currency">'. $column_billAccrued .'</td>
                            <td class="mask_currency success">'. $column_billTotal .'</td>
						</tr>';
                
                $keyData = substr_replace("0000000000000", $column_billTotal, '-'. strlen($column_billTotal));
                $arrData[$keyData . '_' . $key_user] = array(
                    'chart' => array(
                        'name' => $val_user['name'] .' - '. $saleBranch[$val_user['sale_branch_id']]['name'],
                        'data_bill' => $column_billTotal
                    ),
                    'table' => $xhtml
                );
            
                unset($saleUser[$key_user]);
            }
            krsort($arrData);
            
            // Tham số bảng báo cáo
            $xhtmlItems = '';
            foreach ($arrData AS $key_data => $val_data) {
                $xhtmlItems .= $val_data['table'];
                
                // Dữ liệu biểu đồ
                $reportDataBill['data'][] = $val_data['chart']['data_bill'];
                $reportCategories[] = $val_data['chart']['name'];
            }
            
            $xhtmlItems .= '<tr class="text-red">
        		                <td colspan="3" class="text-right"><b>Tổng</b></td>
        						<td class="mask_currency active">'. $total_contract .'</td>
        						<td class="mask_currency">'. $total_billPaidNew .'</td>
        						<td class="mask_currency">'. $total_billPaidOld .'</td>
        						<td class="mask_currency">'. $total_billPaid .'</td>
        						<td class="mask_currency">'. $total_billAccrued .'</td>
        						<td class="mask_currency success">'. $total_billTotal .'</td>
        					</tr>';
    
            $result['reportTable'] = '<thead>
                        				    <tr>
                            					<th>Nhân viên</th>
                            					<th>Cơ sở</th>
                            					<th>Đội nhóm</th>
                            					<th class="active">Tạm tính</th>
                            					<th>Thực thu mới</th>
                            					<th>Thực thu nợ</th>
                            					<th>Tổng thực thu</th>
                            					<th>Thực chi</th>
                            					<th class="success">Doanh thu</th>
                        					</tr>
                        				</thead>
                        				<tbody>
                        				    '. $xhtmlItems .'
                        				</tbody>';
    
            $result['reportChart'] = array(
                'categories' => $reportCategories,
                'series' => array($reportDataBill)
            );
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
            $this->_viewModel['caption']        = 'Báo cáo - Doanh thu - Nhân viên';
        }
    
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
}




















