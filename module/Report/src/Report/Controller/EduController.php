<?php

namespace Report\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class EduController extends ActionController {
    
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
        $this->_viewModel['params'] = $this->_params;
        return new ViewModel($this->_viewModel);
    }
    
    public function dayAction() {
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));
        $date     = new \ZendX\Functions\Date();
    
        if($this->getRequest()->isPost()) {
            // Lấy giá trị post từ filter
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();
    
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin'] = $this->_params['data']['date_begin'];
            $ssFilter->report['edu_location_id'] = $this->_params['data']['edu_location_id'];
    
            $this->_params['ssFilter'] = $ssFilter->report;
    
            // Dữ liệu gốc
            $items = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->report($this->_params, array('task' => 'date-route'));
            $edu_location = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'edu-location')), array('task' => 'cache'));
            $edu_room = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'edu-room')), array('task' => 'cache'));
            $edu_time = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'edu-time')), array('task' => 'cache'));
            $user = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
    
            // Dữ liệu ra bảng
            $xhtmlItems = '';
            if($items->count() > 0) {
                foreach ($items AS $key => $value){
                    $route = unserialize($value['route']);
                    
                    $flag = 0;
                    $stt = 0;
                    $teacher_ids = '';
                    $coach_ids = '';
                    foreach ($route AS $k => $v) {
                        $stt++;
                        if($v['day'] == $ssFilter->report['date_begin']) {
                            $flag = 1;
                            $teacher_ids = $v['teacher_id'];
                            $coach_ids = $v['coach_id'];
                            break;
                        }
                    }
                    if(!empty($flag)) {
                        $name        = '<a href="'. $this->url()->fromRoute('routeAdmin/default', array('controller' => 'edu-class', 'action' => 'detail', 'id' => $value['id'])) .'" target="_blank"><b>'. $value['name'] .'</b></a>';
                        $location    = $edu_location[$value['location_id']]['name'];
                        $room        = $edu_room[$value['room_id']]['name'];
                        $time        = $value['time'];
                        $public_date = $date->formatToView($value['public_date']);
                        $end_date    = $date->formatToView($value['end_date']);
                        $teacher     = '';
                        if(!empty($teacher_ids)) {
                            $teacher_ids = explode(',', $teacher_ids);
                            foreach ($teacher_ids AS $teacher_id) {
                                $teacher .= '<div>'. $user[$teacher_id]['name'] .'</div>';
                            }
                        }
                        
                        $coach     = '';
                        if(!empty($coach_ids)) {
                            $coach_ids = explode(',', $coach_ids);
                            foreach ($coach_ids AS $coach_id) {
                                $coach .= '<div>'. $user[$coach_id]['name'] .'</div>';
                            }
                        }
                        
                        $xhtmlItems .= '<tr>
                                            <td>'. $name .'</td>
                                            <td>'. $location .'</td>
                                            <td>'. $room .'</td>
                                            <td>'. $time .'</td>
                                            <td>'. $stt .'</td>
                                            <td>'. $end_date .'</td>
                                            <td>'. $teacher .'</td>
                                            <td>'. $coach .'</td>
                                        </tr>';
                    }
                }
        
                $result['reportTable'] = '<thead>
                                                <tr>
                                                    <th>Tên lớp</th>
                                                    <th>Cơ sở học</th>
                                                    <th>Phòng học</th>
                                                    <th>Ca học</th>
                                                    <th>Buổi số</th>
                                                    <th>Ngày kết thúc</th>
                                                    <th>Giáo viên</th>
                                                    <th>Trợ giảng</th>
                                                </tr>
                                            </thead>
                                            <tbody>'. $xhtmlItems .'</tbody>';
        
                echo json_encode($result);
            } else {
                echo 'null';
            }
    
            return $this->response;
        } else {
            // Khai báo giá trị ngày tháng
            $default_date_begin = date('d/m/Y');
    
            $ssFilter->report                       = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']         = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $default_date_begin;
            $ssFilter->report['edu_location_id']    = $ssFilter->report['edu_location_id'];
    
            $this->_params['ssFilter']              = $ssFilter->report;
    
            // Set giá trị cho form
            $myForm	= new \Report\Form\Report($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);
    
            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['caption']        = 'Lịch học ngày';
        }
        
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
    
    public function teacherAction() {
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));
        $date     = new \ZendX\Functions\Date();
    
        if($this->getRequest()->isPost()) {
            // Lấy giá trị post từ filter
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();
    
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin'] = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end'] = $this->_params['data']['date_end'];
            $ssFilter->report['edu_location_id'] = $this->_params['data']['edu_location_id'];
            $ssFilter->report['edu_time'] = $this->_params['data']['edu_time'];
    
            $this->_params['ssFilter'] = $ssFilter->report;
    
            // Dữ liệu gốc
            $edu_class = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->report($this->_params, array('task' => 'public_status'));
            $edu_location = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'edu-location')), array('task' => 'cache'));
            $edu_room = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'edu-room')), array('task' => 'cache'));
            $teachers = $this->getServiceLocator()->get('Admin\Model\TeacherTable')->listItem(null, array('task' => 'cache'));
            $edu_time = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'edu-time')), array('task' => 'cache-alias'));
            $arr_edu_time = array();
            foreach ($edu_time AS $key => $val) {
                if(!empty($ssFilter->report['edu_time'])) {
                    if($val['name'] != $ssFilter->report['edu_time']) {
                        continue;
                    }
                }
                $arr_edu_time[$val['name']] = $val['alias'];
            }
            
            // Format dữ liệu lớp học
            $dataClassTeacher = array();
            $end_date = $date->add(date('d/m/Y'), 30, 'd/m/Y');
            foreach ($edu_class AS $key => $val) {
                if($key == 0) {
                    $end_date = $date->formatToView($val['end_date']);
                }
                
                $route = unserialize($val['route']);
                foreach ($route AS $k => $v) {
                    $teacher_ids = explode(',', $v['teacher_id']);
                    foreach ($teacher_ids AS $teacher_id) {
                        if(!empty($val['id'])) {
                            $dataClassTeacher[$teacher_id][$v['day']][$val['time']][] = array(
                                'edu_class_id' => $val['id'],
                                'edu_class_name' => $val['name'],
                                'location_id' => $val['location_id'],
                                'room_id' => $val['room_id'],
                            );
                        }
                    }
                }
            }
            
            // Lộ trình từng ngày học
            $htmlHeadDay = '';
            $htmlHeadTime = '';
            $arrDay = array();
            $weekData = array(1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7', 7 => 'CN');
            for ($i = 0; $i <= $date->diff(date('d/m/Y'), $end_date); $i++) {
                $today = $date->add(date('d/m/Y'), $i, 'd/m/Y');
                $dayofweek = $weekData[date_format(date_create($date->formatToData($today)), 'N')];
                
                $arrDay[$today] = $dayofweek;
                $color = '';
                if($dayofweek == 'CN') {
                    $color = ' class="active"';
                }
                $htmlHeadDay .= '<th colspan="'. count($arr_edu_time) .'"'. $color .'>'. $dayofweek .'<br>'. $today .'</th>';
                foreach ($arr_edu_time AS $k_time => $v_time) {
                    $htmlHeadTime .= '<th'. $color .'>'. strtoupper($v_time) .'</th>';
                }
            }

            // Dữ liệu ra bảng
            $xhtmlItems = '';
            if(count($teachers) > 0) {
                foreach ($teachers AS $key => $value){
                    $name = $value['name'];
                    
                    $htmlBodyTime = '';
                    for ($i = 0; $i <= $date->diff(date('d/m/Y'), $end_date); $i++) {
                        $today = $date->add(date('d/m/Y'), $i, 'd/m/Y');
                        $dayofweek = $weekData[date_format(date_create($date->formatToData($today)), 'N')];
                    
                        $arrDay[$today] = $dayofweek;
                        $color = '';
                        if($dayofweek == 'CN') {
                            $color = ' class="active"';
                        }
                        $html_time = array();
                        if(!empty($dataClassTeacher[$value['id']][$today])) {
                            foreach ($dataClassTeacher[$value['id']][$today] AS $k => $v) {
                                $text = '';
                                $class = (count($v) > 1) ? ' class="text-red"' : '';
                                foreach ($v AS $data) {
                                    $text .= '<a href="'. $this->url()->fromRoute('routeAdmin/default', array('controller' => 'edu-class', 'action' => 'detail', 'id' => $data['edu_class_id'])) .'" title="'. $data['edu_class_name'] .'" target="_blank"'.$class.'>X</a>';
                                }
                                $html_time[$k] = $text;
                            }
                        }
                        
                        foreach ($arr_edu_time AS $k_time => $v_time) {
                            $htmlBodyTime .= '<td'. $color .'>'. $html_time[$k_time] .'</td>';
                        }
                    }
                    
                    $xhtmlItems .= '<tr>
                                        <td class="text-left">'. $name .'</td>
                                        '. $htmlBodyTime .'
                                    </tr>';
                }
        
                $result['reportTable'] = '<thead>
                                                <tr>
                                                    <th rowspan="2">Giáo viên</th>
                                                    '. $htmlHeadDay .'
                                                </tr>
                                                <tr>
                                                    '. $htmlHeadTime .'
                                                </tr>
                                            </thead>
                                            <tbody>'. $xhtmlItems .'</tbody>';
                
                echo json_encode($result);
            } else {
                echo 'null';
            }
    
            return $this->response;
        } else {
            // Khai báo giá trị ngày tháng
            $default_date_begin = '';
            $default_date_end = '';
    
            $ssFilter->report                       = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']         = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $default_date_begin;
            $ssFilter->report['date_end']           = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $default_date_begin;
            $ssFilter->report['edu_location_id']    = $ssFilter->report['edu_location_id'];
            $ssFilter->report['edu_time']           = $ssFilter->report['edu_time'];
    
            $this->_params['ssFilter']              = $ssFilter->report;
    
            // Set giá trị cho form
            $myForm	= new \Report\Form\Report($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);
    
            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['caption']        = 'Lịch giáo viên';
        }
        
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
    
    public function roomAction() {
        $ssFilter = new Container(__CLASS__ . str_replace('-', '_', $this->_params['action']));
        $date     = new \ZendX\Functions\Date();
    
        if($this->getRequest()->isPost()) {
            // Lấy giá trị post từ filter
            $this->_params['data'] = $this->getRequest()->getPost()->toArray();
    
            // Gán dữ liệu lọc vào session
            $ssFilter->report['date_begin'] = $this->_params['data']['date_begin'];
            $ssFilter->report['date_end'] = $this->_params['data']['date_end'];
            $ssFilter->report['edu_location_id'] = $this->_params['data']['edu_location_id'];
            $ssFilter->report['edu_time'] = $this->_params['data']['edu_time'];
    
            $this->_params['ssFilter'] = $ssFilter->report;
    
            // Dữ liệu gốc
            $edu_class = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->report($this->_params, array('task' => 'public_status'));
            $edu_location = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'edu-location')), array('task' => 'cache'));
            $edu_room = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'edu-room')), array('task' => 'cache'));
            $teachers = $this->getServiceLocator()->get('Admin\Model\TeacherTable')->listItem(null, array('task' => 'cache'));
            $edu_time = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'edu-time')), array('task' => 'cache-alias'));
            $arr_edu_time = array();
            foreach ($edu_time AS $key => $val) {
                if(!empty($ssFilter->report['edu_time'])) {
                    if($val['name'] != $ssFilter->report['edu_time']) {
                        continue;
                    }
                }
                $arr_edu_time[$val['name']] = $val['alias'];
            }
            
            // Format dữ liệu lớp học
            $dataClassRoom = array();
            $end_date = $date->add(date('d/m/Y'), 30, 'd/m/Y');
            foreach ($edu_class AS $key => $val) {
                if($key == 0) {
                    $end_date = $date->formatToView($val['end_date']);
                }
                
                $route = unserialize($val['route']);
                foreach ($route AS $k => $v) {
                    $dataClassRoom[$val['room_id']][$v['day']][$val['time']] = array(
                        'edu_class_id' => $val['id'],
                        'edu_class_name' => $val['name'],
                        'location_id' => $val['location_id'],
                        'teacher_id' => $val['teacher_id'],
                    );
                }
            }
            
            // Lộ trình từng ngày học
            $htmlHeadDay = '';
            $htmlHeadTime = '';
            $arrDay = array();
            $weekData = array(1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7', 7 => 'CN');
            for ($i = 0; $i <= $date->diff(date('d/m/Y'), $end_date); $i++) {
                $today = $date->add(date('d/m/Y'), $i, 'd/m/Y');
                $dayofweek = $weekData[date_format(date_create($date->formatToData($today)), 'N')];
                
                $arrDay[$today] = $dayofweek;
                $color = '';
                if($dayofweek == 'CN') {
                    $color = ' class="active"';
                }
                $htmlHeadDay .= '<th colspan="'. count($arr_edu_time) .'"'. $color .'>'. $dayofweek .'<br>'. $today .'</th>';
                foreach ($arr_edu_time AS $k_time => $v_time) {
                    $htmlHeadTime .= '<th'. $color .'>'. strtoupper($v_time) .'</th>';
                }
            }

            // Dữ liệu ra bảng
            $xhtmlItems = '';
            if(count($edu_room) > 0) {
                foreach ($edu_room AS $key => $value){
                    if(!empty($ssFilter->report['edu_location_id'])) {
                        if($value['document_id'] != $ssFilter->report['edu_location_id']) {
                            continue;
                        }
                    }
                    
                    $location = $edu_location[$value['document_id']]['name'];
                    $name = $value['name'];
                    
                    $htmlBodyTime = '';
                    for ($i = 0; $i <= $date->diff(date('d/m/Y'), $end_date); $i++) {
                        $today = $date->add(date('d/m/Y'), $i, 'd/m/Y');
                        $dayofweek = $weekData[date_format(date_create($date->formatToData($today)), 'N')];
                    
                        $arrDay[$today] = $dayofweek;
                        $color = '';
                        if($dayofweek == 'CN') {
                            $color = ' class="active"';
                        }
                        $html_time = array();
                        if(!empty($dataClassRoom[$value['id']][$today])) {
                            foreach ($dataClassRoom[$value['id']][$today] AS $k => $v) {
                                $html_time[$k] = '<a href="'. $this->url()->fromRoute('routeAdmin/default', array('controller' => 'edu-class', 'action' => 'detail', 'id' => $v['edu_class_id'])) .'" title="'. $v['edu_class_name'] .'" target="_blank">X</a>';
                            }
                        }
                        
                        foreach ($arr_edu_time AS $k_time => $v_time) {
                            $htmlBodyTime .= '<td'. $color .'>'. $html_time[$k_time] .'</td>';
                        }
                    }
                    
                    $xhtmlItems .= '<tr>
                                        <td class="text-left">'. $location .'</td>
                                        <td>'. $name .'</td>
                                        '. $htmlBodyTime .'
                                    </tr>';
                }
        
                $result['reportTable'] = '<thead>
                                                <tr>
                                                    <th rowspan="2">Cơ sở</th>
                                                    <th rowspan="2">Phòng học</th>
                                                    '. $htmlHeadDay .'
                                                </tr>
                                                <tr>
                                                    '. $htmlHeadTime .'
                                                </tr>
                                            </thead>
                                            <tbody>'. $xhtmlItems .'</tbody>';
        
                echo json_encode($result);
            } else {
                echo 'null';
            }
    
            return $this->response;
        } else {
            // Khai báo giá trị ngày tháng
            $default_date_begin = '';
            $default_date_end = '';
    
            $ssFilter->report                       = $ssFilter->report ? $ssFilter->report : array();
            $ssFilter->report['date_begin']         = $ssFilter->report['date_begin'] ? $ssFilter->report['date_begin'] : $default_date_begin;
            $ssFilter->report['date_end']           = $ssFilter->report['date_end'] ? $ssFilter->report['date_end'] : $default_date_begin;
            $ssFilter->report['edu_location_id']    = $ssFilter->report['edu_location_id'];
            $ssFilter->report['edu_time']           = $ssFilter->report['edu_time'];
    
            $this->_params['ssFilter']              = $ssFilter->report;
    
            // Set giá trị cho form
            $myForm	= new \Report\Form\Report($this->getServiceLocator(), $ssFilter->report);
            $myForm->setData($ssFilter->report);
    
            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['myForm']         = $myForm;
            $this->_viewModel['caption']        = 'Lịch phòng học';
        }
        
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
}




















