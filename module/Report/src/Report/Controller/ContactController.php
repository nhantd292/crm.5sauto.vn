<?php

namespace Report\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class ContactController extends ActionController {
    
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
            $this->_params['route']['id'] = 'history-action';
        }
        
        $this->_viewModel['params'] = $this->_params;
        return new ViewModel($this->_viewModel);
    }
    
    public function typeAction() {
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));
        $date     = new \ZendX\Functions\Date();
    
        if($this->getRequest()->isPost()) {
            // Lấy giá trị post từ filter
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();
    
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin'] = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end'] = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_group_id'] = $this->_params['data']['sale_group_id'];
    
            $this->_params['ssFilter']  = $ssFilter->report;
    
            // Xác định ngày tháng tìm kiếm
            $month          = date('m', strtotime($date->formatToData($ssFilter->report['date_begin'])));
            $year           = date('Y', strtotime($date->formatToData($ssFilter->report['date_begin'])));
            $date_begin     = $date->formatToData($ssFilter->report['date_begin']);
            $date_end       = $date->formatToData($ssFilter->report['date_end']);
    
            // Dữ liệu gốc
            $items                  = $this->getServiceLocator()->get('Admin\Model\ContactTable')->report($this->_params, array('task' => 'date'));
            $sale_contact_type      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contact-type')), array('task' => 'cache-alias'));
    
            // Format dữ liệu
            $arrData = array();
            $total = 0;
            foreach ($items AS $item){
                $arrData[$item['type']]++;
                $total++;
            }
            arsort($arrData);
            
            // Dữ liệu ra bảng
            $xhtmlItems = '';
            if(!empty($arrData)) {
                foreach ($arrData AS $key => $value){
                    $name        = $sale_contact_type[$key]['name'] ? $sale_contact_type[$key]['name'] : 'Không xác định';
                    $xhtmlItems .= '<tr>
                                        <td>'. $name .'</td>
                                        <td>'. $value .'</td>
                                    </tr>';
        
                    // Dữ liệu ra biểu đồ
                    $result['reportChart'][0]['categories'][] = $name;
                    $result['reportChart'][0]['series'][0]['data'][] = $value ? $value : 0;
                }
                $xhtmlItems .= '<tr class="text-bold text-red"><td>Tổng</td><td>'. $total .'</td>';
        
                $result['reportTable'] = '<thead>
                                                <tr>
                                                    <th>Tên</th>
                                                    <th>Số lượng</th>
                                                </tr>
                                            </thead>
                                            <tbody>'. $xhtmlItems .'</tbody>';
        
                // Dữ liệu ra biểu đồ
                $result['reportChart'][0]['series'][0]['name'] = 'Số lượng';
                
                echo json_encode($result);
            } else {
                echo 'null';
            }
    
            return $this->response;
        } else {
            // Khai báo giá trị ngày tháng
            $default_date_begin = date('01/m/Y');
            $default_date_end   = date('t/m/Y');
    
            $ssFilter->report                   = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']     = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $default_date_begin;
            $ssFilter->report['date_end']       = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $default_date_end;
            $ssFilter->report['sale_branch_id'] = $ssFilter->report['sale_branch_id'];
            $ssFilter->report['sale_group_id']  = $ssFilter->report['sale_group_id'];
    
            $this->_params['ssFilter']          = $ssFilter->report;
    
            // Set giá trị cho form
            $myForm	= new \Report\Form\Report($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);
    
            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['caption']        = 'Báo cáo - Liên hệ - Phân loại';
        }
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
    
    public function sexAction() {
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));
        $date     = new \ZendX\Functions\Date();
    
        if($this->getRequest()->isPost()) {
            // Lấy giá trị post từ filter
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();
    
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin'] = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end'] = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_group_id'] = $this->_params['data']['sale_group_id'];
    
            $this->_params['ssFilter']  = $ssFilter->report;
    
            // Dữ liệu gốc
            $items          = $this->getServiceLocator()->get('Admin\Model\ContactTable')->report($this->_params, array('task' => 'date'));
            $sex            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sex')), array('task' => 'cache-alias'));
    
            // Format dữ liệu
            $arrData = array();
            $total = 0;
            foreach ($items AS $item){
                $arrData[$item['sex']]++;
                $total++;
            }
            arsort($arrData);
            
            // Dữ liệu ra bảng
            $xhtmlItems = '';
            if(!empty($arrData)) {
                foreach ($arrData AS $key => $value){
                    $name        = $sex[$key]['name'] ? $sex[$key]['name'] : 'Không xác định';
                    $xhtmlItems .= '<tr>
                                        <td>'. $name .'</td>
                                        <td>'. $value .'</td>
                                    </tr>';
        
                    // Dữ liệu ra biểu đồ
                    $result['reportChart'][0]['categories'][] = $name;
                    $result['reportChart'][0]['series'][0]['data'][] = $value ? $value : 0;
                }
                $xhtmlItems .= '<tr class="text-bold text-red"><td>Tổng</td><td>'. $total .'</td>';
        
                $result['reportTable'] = '<thead>
                                                <tr>
                                                    <th>Tên</th>
                                                    <th>Số lượng</th>
                                                </tr>
                                            </thead>
                                            <tbody>'. $xhtmlItems .'</tbody>';
        
                // Dữ liệu ra biểu đồ
                $result['reportChart'][0]['series'][0]['name'] = 'Số lượng';
                
                echo json_encode($result);
            } else {
                echo 'null';
            }
    
            return $this->response;
        } else {
            // Khai báo giá trị ngày tháng
            $default_date_begin = date('01/m/Y');
            $default_date_end   = date('t/m/Y');
    
            $ssFilter->report                   = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']     = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $default_date_begin;
            $ssFilter->report['date_end']       = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $default_date_end;
            $ssFilter->report['sale_branch_id'] = $ssFilter->report['sale_branch_id'];
            $ssFilter->report['sale_group_id']  = $ssFilter->report['sale_group_id'];
    
            $this->_params['ssFilter']          = $ssFilter->report;
    
            // Set giá trị cho form
            $myForm	= new \Report\Form\Report($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);
    
            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['caption']        = 'Báo cáo - Liên hệ - Giới tính';
        }
        
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
    
    public function locationAction() {
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));
        $date     = new \ZendX\Functions\Date();
    
        if($this->getRequest()->isPost()) {
            // Lấy giá trị post từ filter
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();
    
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin'] = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end'] = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_group_id'] = $this->_params['data']['sale_group_id'];
            $ssFilter->report['location_city_id'] = $this->_params['data']['location_city_id'];
    
            $this->_params['ssFilter']  = $ssFilter->report;
    
            // Dữ liệu gốc
            $items          = $this->getServiceLocator()->get('Admin\Model\ContactTable')->report($this->_params, array('task' => 'date'));
            $document       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(null, array('task' => 'cache'));
            
            // Format dữ liệu
            $arrData = array();
            $total = 0;
            $field = !empty($ssFilter->report['location_city_id']) ? 'location_district_id' : 'location_city_id';
            foreach ($items AS $item){
                $arrData[$item[$field]]++;
                $total++;
            }
            arsort($arrData);
            
            // Dữ liệu ra bảng
            $xhtmlItems = '';
            if(!empty($arrData)) {
                foreach ($arrData AS $key => $value){
                    $name        = $document[$key]['name'] ? $document[$key]['name'] : 'Không xác định';
                    $xhtmlItems .= '<tr>
                                        <td>'. $name .'</td>
                                        <td>'. $value .'</td>
                                    </tr>';
        
                    // Dữ liệu ra biểu đồ
                    $result['reportChart'][0]['categories'][] = $name;
                    $result['reportChart'][0]['series'][0]['data'][] = $value ? $value : 0;
                }
                $xhtmlItems .= '<tr class="text-bold text-red"><td>Tổng</td><td>'. $total .'</td>';
        
                $result['reportTable'] = '<thead>
                                                <tr>
                                                    <th>Tên</th>
                                                    <th>Số lượng</th>
                                                </tr>
                                            </thead>
                                            <tbody>'. $xhtmlItems .'</tbody>';
        
                // Dữ liệu ra biểu đồ
                $result['reportChart'][0]['series'][0]['name'] = 'Số lượng';
                
                echo json_encode($result);
            } else {
                echo 'null';
            }
            
            return $this->response;
        } else {
            // Khai báo giá trị ngày tháng
            $default_date_begin = date('01/m/Y');
            $default_date_end   = date('t/m/Y');
    
            $ssFilter->report                       = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']         = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $default_date_begin;
            $ssFilter->report['date_end']           = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $default_date_end;
            $ssFilter->report['sale_branch_id']     = $ssFilter->report['sale_branch_id'];
            $ssFilter->report['sale_group_id']      = $ssFilter->report['sale_group_id'];
            $ssFilter->report['location_city_id']   = $ssFilter->report['location_city_id'];
    
            $this->_params['ssFilter']          = $ssFilter->report;
    
            // Set giá trị cho form
            $myForm	= new \Report\Form\Report($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);
    
            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['caption']        = 'Báo cáo - Liên hệ - Vị trí địa lý';
        }
        
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
    
    public function birthdayAction() {
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));
        $date     = new \ZendX\Functions\Date();
    
        if($this->getRequest()->isPost()) {
            // Lấy giá trị post từ filter
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();
    
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin'] = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end'] = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_group_id'] = $this->_params['data']['sale_group_id'];
    
            $this->_params['ssFilter']  = $ssFilter->report;
    
            // Dữ liệu gốc
            $items = $this->getServiceLocator()->get('Admin\Model\ContactTable')->report($this->_params, array('task' => 'date'));
    
            // Format dữ liệu
            $arrData = array();
            $total = 0;
            foreach ($items as $item){
                $arrData[$item['birthday_year']]++;
                $total++;
            }
            krsort($arrData);
            
            // Dữ liệu ra bảng
            $xhtmlItems = '';
            if(!empty($arrData)) {
                foreach ($arrData AS $key => $value){
                    $name        = $key ? $key : 'Không xác định';
                    $xhtmlItems .= '<tr>
                                        <td>'. $name .'</td>
                                        <td>'. $value .'</td>
                                    </tr>';
        
                    // Dữ liệu ra biểu đồ
                    $result['reportChart'][0]['categories'][] = $name;
                    $result['reportChart'][0]['series'][0]['data'][] = $value ? $value : 0;
                }
                $xhtmlItems .= '<tr class="text-bold text-red"><td>Tổng</td><td>'. $total .'</td>';
        
                $result['reportTable'] = '<thead>
                                                <tr>
                                                    <th>Năm sinh</th>
                                                    <th>Số lượng</th>
                                                </tr>
                                            </thead>
                                            <tbody>'. $xhtmlItems .'</tbody>';
        
                // Dữ liệu ra biểu đồ
                $result['reportChart'][0]['series'][0]['name'] = 'Số lượng';
                
                echo json_encode($result);
            } else {
                echo 'null';
            }
    
            return $this->response;
        }else{
            // Khai báo giá trị ngày tháng
            $default_date_begin = date('01/m/Y');
            $default_date_end   = date('t/m/Y');
    
            $ssFilter->report                   = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']     = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $default_date_begin;
            $ssFilter->report['date_end']       = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $default_date_end;
            $ssFilter->report['sale_branch_id'] = $ssFilter->report['sale_branch_id'];
            $ssFilter->report['sale_group_id']  = $ssFilter->report['sale_group_id'];
    
            $this->_params['ssFilter']          = $ssFilter->report;
    
            // Set giá trị cho form
            $myForm	= new \Report\Form\Report($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);
    
            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['caption']        = 'Báo cáo - Liên hệ - Năm sinh';
        }
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
    
    public function sourceGroupAction() {
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));
        $date     = new \ZendX\Functions\Date();
    
        if($this->getRequest()->isPost()) {
            // Lấy giá trị post từ filter
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();
    
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin'] = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end'] = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_group_id'] = $this->_params['data']['sale_group_id'];
    
            $this->_params['ssFilter']  = $ssFilter->report;
    
            // Dữ liệu gốc
            $items      = $this->getServiceLocator()->get('Admin\Model\ContactTable')->report($this->_params, array('task' => 'date'));
            $document   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(null, array('task' => 'cache'));
            
            // Format dữ liệu
            $arrData = array();
            $total = 0;
            foreach ($items as $item){
                $arrData[$item['source_group_id']]++;
                $total++;
            }
            arsort($arrData);
            
            // Dữ liệu ra bảng
            $xhtmlItems = '';
            if(!empty($arrData)) {
                foreach ($arrData AS $key => $value){
                    $name        = $document[$key]['name'] ? $document[$key]['name'] : 'Không xác định';
                    $xhtmlItems .= '<tr>
                                        <td>'. $name .'</td>
                                        <td>'. $value .'</td>
                                    </tr>';
        
                    // Dữ liệu ra biểu đồ
                    $result['reportChart'][0]['categories'][] = $name;
                    $result['reportChart'][0]['series'][0]['data'][] = $value ? $value : 0;
                }
                $xhtmlItems .= '<tr class="text-bold text-red"><td>Tổng</td><td>'. $total .'</td>';
        
                $result['reportTable'] = '<thead>
                                                <tr>
                                                    <th>Tên</th>
                                                    <th>Số lượng</th>
                                                </tr>
                                            </thead>
                                            <tbody>'. $xhtmlItems .'</tbody>';
        
                // Dữ liệu ra biểu đồ
                $result['reportChart'][0]['series'][0]['name'] = 'Số lượng';
                
                echo json_encode($result);
            } else {
                echo 'null';
            }
    
            return $this->response;
        } else {
            // Khai báo giá trị ngày tháng
            $default_date_begin = date('01/m/Y');
            $default_date_end   = date('t/m/Y');
    
            $ssFilter->report                   = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']     = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $default_date_begin;
            $ssFilter->report['date_end']       = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $default_date_end;
            $ssFilter->report['sale_branch_id'] = $ssFilter->report['sale_branch_id'];
            $ssFilter->report['sale_group_id']  = $ssFilter->report['sale_group_id'];
    
            $this->_params['ssFilter']          = $ssFilter->report;
    
            // Set giá trị cho form
            $myForm	= new \Report\Form\Report($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);
    
            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['caption']        = 'Báo cáo - Liên hệ - Nguồn';
        }
        
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
    
    public function productInterestAction() {
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));
        $date     = new \ZendX\Functions\Date();
    
        if($this->getRequest()->isPost()) {
            // Lấy giá trị post từ filter
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();
    
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin'] = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end'] = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_group_id'] = $this->_params['data']['sale_group_id'];
    
            $this->_params['ssFilter']  = $ssFilter->report;
    
            // Dữ liệu gốc
            $items      = $this->getServiceLocator()->get('Admin\Model\ContactTable')->report($this->_params, array('task' => 'date', 'columns' => array('id', 'product_id')));
            $document   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(null, array('task' => 'cache'));
            
            // Format dữ liệu
            $arrData = array();
            $total = 0;
            foreach ($items as $item){
                $arrData[$item['product_id']]++;
                $total++;
            }
            arsort($arrData);
            
            // Dữ liệu ra bảng
            $xhtmlItems = '';
            if(!empty($arrData)) {
                foreach ($arrData AS $key => $value){
                    $name        = $document[$key]['name'] ? $document[$key]['name'] : 'Không xác định';
                    $xhtmlItems .= '<tr>
                                        <td>'. $name .'</td>
                                        <td>'. $value .'</td>
                                    </tr>';
        
                    // Dữ liệu ra biểu đồ
                    $result['reportChart'][0]['categories'][] = $name;
                    $result['reportChart'][0]['series'][0]['data'][] = $value ? $value : 0;
                }
                $xhtmlItems .= '<tr class="text-bold text-red"><td>Tổng</td><td>'. $total .'</td>';
        
                $result['reportTable'] = '<thead>
                                                <tr>
                                                    <th>Tên</th>
                                                    <th>Số lượng</th>
                                                </tr>
                                            </thead>
                                            <tbody>'. $xhtmlItems .'</tbody>';
        
                // Dữ liệu ra biểu đồ
                $result['reportChart'][0]['series'][0]['name'] = 'Số lượng';
                
                echo json_encode($result);
            } else {
                echo 'null';
            }
    
            return $this->response;
        } else {
            // Khai báo giá trị ngày tháng
            $default_date_begin = date('01/m/Y');
            $default_date_end   = date('t/m/Y');
    
            $ssFilter->report                   = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']     = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $default_date_begin;
            $ssFilter->report['date_end']       = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $default_date_end;
            $ssFilter->report['sale_branch_id'] = $ssFilter->report['sale_branch_id'];
            $ssFilter->report['sale_group_id']  = $ssFilter->report['sale_group_id'];
    
            $this->_params['ssFilter']          = $ssFilter->report;
    
            // Set giá trị cho form
            $myForm	= new \Report\Form\Report($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);
    
            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['caption']        = 'Báo cáo - Liên hệ - Sản phẩm quan tâm';
        }
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
    
    public function schoolAction() {
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));
        $date     = new \ZendX\Functions\Date();
    
        if($this->getRequest()->isPost()) {
            // Lấy giá trị post từ filter
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();
    
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin'] = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end'] = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_group_id'] = $this->_params['data']['sale_group_id'];
    
            $this->_params['ssFilter']  = $ssFilter->report;
    
            // Dữ liệu gốc
            $items      = $this->getServiceLocator()->get('Admin\Model\ContactTable')->report($this->_params, array('task' => 'date', 'columns' => array('id', 'options')));
            $document   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(null, array('task' => 'cache'));
            
            // Format dữ liệu
            $arrData = array();
            $total = 0;
            foreach ($items as $item){
                $options = !empty($item['options']) ? unserialize($item['options']) : array();
                if(!empty($options['school_name'])) {
                    $arrData[$options['school_name']]++;
                    $total++;
                }
            }
            arsort($arrData);
            
            // Dữ liệu ra bảng
            $xhtmlItems = '';
            if(!empty($arrData)) {
                foreach ($arrData AS $key => $value){
                    if(!empty($key)) {
                        $name        = $key;
                        $xhtmlItems .= '<tr>
                                            <td>'. $name .'</td>
                                            <td>'. $value .'</td>
                                        </tr>';
            
                        // Dữ liệu ra biểu đồ
                        $result['reportChart'][0]['categories'][] = $name;
                        $result['reportChart'][0]['series'][0]['data'][] = $value ? $value : 0;
                    }
                }
                if(!empty($xhtmlItems)) {
                    $xhtmlItems .= '<tr class="text-bold text-red"><td>Tổng</td><td>'. $total .'</td>';
            
                    $result['reportTable'] = '<thead>
                                                    <tr>
                                                        <th>Tên</th>
                                                        <th>Số lượng</th>
                                                    </tr>
                                                </thead>
                                                <tbody>'. $xhtmlItems .'</tbody>';
            
                    // Dữ liệu ra biểu đồ
                    $result['reportChart'][0]['series'][0]['name'] = 'Số lượng';
                    
                    echo json_encode($result);
                } else {
                    echo 'null';
                }
            } else {
                echo 'null';
            }
    
            return $this->response;
        } else {
            // Khai báo giá trị ngày tháng
            $default_date_begin = date('01/m/Y');
            $default_date_end   = date('t/m/Y');
    
            $ssFilter->report                   = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']     = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $default_date_begin;
            $ssFilter->report['date_end']       = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $default_date_end;
            $ssFilter->report['sale_branch_id'] = $ssFilter->report['sale_branch_id'];
            $ssFilter->report['sale_group_id']  = $ssFilter->report['sale_group_id'];
    
            $this->_params['ssFilter']          = $ssFilter->report;
    
            // Set giá trị cho form
            $myForm	= new \Report\Form\Report($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);
    
            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['caption']        = 'Báo cáo - Liên hệ - Trường học';
        }
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
    
    public function majorAction() {
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));
        $date     = new \ZendX\Functions\Date();
    
        if($this->getRequest()->isPost()) {
            // Lấy giá trị post từ filter
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();
    
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin'] = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end'] = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_group_id'] = $this->_params['data']['sale_group_id'];
    
            $this->_params['ssFilter']  = $ssFilter->report;
    
            // Dữ liệu gốc
            $items      = $this->getServiceLocator()->get('Admin\Model\ContactTable')->report($this->_params, array('task' => 'date', 'columns' => array('id', 'options')));
            $document   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(null, array('task' => 'cache'));
            
            // Format dữ liệu
            $arrData = array();
            $total = 0;
            foreach ($items as $item){
                $options = !empty($item['options']) ? unserialize($item['options']) : array();
                if(!empty($options['major_name'])) {
                    $arrData[$options['major_name']]++;
                    $total++;
                }
            }
            arsort($arrData);
            
            // Dữ liệu ra bảng
            $xhtmlItems = '';
            if(!empty($arrData)) {
                foreach ($arrData AS $key => $value){
                    if(!empty($key)) {
                        $name        = $key;
                        $xhtmlItems .= '<tr>
                                            <td>'. $name .'</td>
                                            <td>'. $value .'</td>
                                        </tr>';
            
                        // Dữ liệu ra biểu đồ
                        $result['reportChart'][0]['categories'][] = $name;
                        $result['reportChart'][0]['series'][0]['data'][] = $value ? $value : 0;
                    }
                }
                if(!empty($xhtmlItems)) {
                    $xhtmlItems .= '<tr class="text-bold text-red"><td>Tổng</td><td>'. $total .'</td>';
            
                    $result['reportTable'] = '<thead>
                                                    <tr>
                                                        <th>Tên</th>
                                                        <th>Số lượng</th>
                                                    </tr>
                                                </thead>
                                                <tbody>'. $xhtmlItems .'</tbody>';
            
                    // Dữ liệu ra biểu đồ
                    $result['reportChart'][0]['series'][0]['name'] = 'Số lượng';
                    
                    echo json_encode($result);
                } else {
                    echo 'null';
                }
            } else {
                echo 'null';
            }
    
            return $this->response;
        } else {
            // Khai báo giá trị ngày tháng
            $default_date_begin = date('01/m/Y');
            $default_date_end   = date('t/m/Y');
    
            $ssFilter->report                   = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']     = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $default_date_begin;
            $ssFilter->report['date_end']       = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $default_date_end;
            $ssFilter->report['sale_branch_id'] = $ssFilter->report['sale_branch_id'];
            $ssFilter->report['sale_group_id']  = $ssFilter->report['sale_group_id'];
    
            $this->_params['ssFilter']          = $ssFilter->report;
    
            // Set giá trị cho form
            $myForm	= new \Report\Form\Report($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);
    
            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['caption']        = 'Báo cáo - Liên hệ - Trường học';
        }
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
    
    public function classNameAction() {
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));
        $date     = new \ZendX\Functions\Date();
    
        if($this->getRequest()->isPost()) {
            // Lấy giá trị post từ filter
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();
    
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin'] = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end'] = $this->_params['data']['date_end'];
            $ssFilter->report['sale_branch_id'] = $this->_params['data']['sale_branch_id'];
            $ssFilter->report['sale_group_id'] = $this->_params['data']['sale_group_id'];
    
            $this->_params['ssFilter']  = $ssFilter->report;
    
            // Dữ liệu gốc
            $items      = $this->getServiceLocator()->get('Admin\Model\ContactTable')->report($this->_params, array('task' => 'date', 'columns' => array('id', 'options')));
            $document   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(null, array('task' => 'cache'));
    
            // Format dữ liệu
            $arrData = array();
            $total = 0;
            foreach ($items as $item){
                $options = !empty($item['options']) ? unserialize($item['options']) : array();
                if(!empty($options['class_name'])) {
                    $arrData[$options['class_name']]++;
                    $total++;
                }
            }
            arsort($arrData);
    
            // Dữ liệu ra bảng
            $xhtmlItems = '';
            if(!empty($arrData)) {
                foreach ($arrData AS $key => $value){
                    if(!empty($key)) {
                        $name        = $key;
                        $xhtmlItems .= '<tr>
                                            <td>'. $name .'</td>
                                            <td>'. $value .'</td>
                                        </tr>';
        
                        // Dữ liệu ra biểu đồ
                        $result['reportChart'][0]['categories'][] = $name;
                        $result['reportChart'][0]['series'][0]['data'][] = $value ? $value : 0;
                    }
                }
                if(!empty($xhtmlItems)) {
                    $xhtmlItems .= '<tr class="text-bold text-red"><td>Tổng</td><td>'. $total .'</td>';
        
                    $result['reportTable'] = '<thead>
                                                    <tr>
                                                        <th>Tên</th>
                                                        <th>Số lượng</th>
                                                    </tr>
                                                </thead>
                                                <tbody>'. $xhtmlItems .'</tbody>';
        
                    // Dữ liệu ra biểu đồ
                    $result['reportChart'][0]['series'][0]['name'] = 'Số lượng';
        
                    echo json_encode($result);
                } else {
                    echo 'null';
                }
            } else {
                echo 'null';
            }
    
            return $this->response;
        } else {
            // Khai báo giá trị ngày tháng
            $default_date_begin = date('01/m/Y');
            $default_date_end   = date('t/m/Y');
    
            $ssFilter->report                   = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']     = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $default_date_begin;
            $ssFilter->report['date_end']       = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $default_date_end;
            $ssFilter->report['sale_branch_id'] = $ssFilter->report['sale_branch_id'];
            $ssFilter->report['sale_group_id']  = $ssFilter->report['sale_group_id'];
    
            $this->_params['ssFilter']          = $ssFilter->report;
    
            // Set giá trị cho form
            $myForm	= new \Report\Form\Report($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);
    
            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['caption']        = 'Báo cáo - Liên hệ - Lớp học';
        }
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
}




















