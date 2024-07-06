<?php

namespace Admin\Controller;

use kcfinder\zipFolder;
use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;
use ZendX\System\UserInfo;

class ZaloNotifyResultController extends ActionController{

    public $_field_name = array(
        'name' => 'Tên khách hàng',
        'customer_name' => 'Tên khách hàng',
        'order_code' => 'Mã đơn hàng',
        'phone_number' => 'Số điện thoại',
        'date' => 'Ngày lên đơn',
        'order_date' => 'Ngày lên đơn',
        'status' => 'Trạng thái',
        'price' => 'Tổng tiền hàng',
        'unit_transport' => 'Đơn vị vận chuyển',
        'address' => 'Địa chỉ',
        'products' => 'Sản phẩm'
    );

    public function init() {
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\ZaloNotifyResultTable';
        $this->_options['formName'] = 'formAdminZaloNotifyResult';

        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__ . $this->_params['action']);
        $this->_params['ssFilter']['order_by']                  = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'ordering';
        $this->_params['ssFilter']['order']                     = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';
        $this->_params['ssFilter']['filter_status']             = $ssFilter->filter_status;
        $this->_params['ssFilter']['filter_keyword']            = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['filter_error']              = $ssFilter->filter_error;
        $this->_params['ssFilter']['filter_date_begin']         = $ssFilter->filter_date_begin;
        $this->_params['ssFilter']['filter_date_end']           = $ssFilter->filter_date_end;
        $this->_params['ssFilter']['filter_result_error']       = $ssFilter->filter_result_error;

        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage']               = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : 50;
        $this->_paginator['currentPageNumber']              = $this->params()->fromRoute('page', 1);
        $this->_params['paginator'] = $this->_paginator;

        // Lấy dữ liệu post của form
        $this->_params['data'] = array_merge($this->getRequest()->getPost()->toArray(), $this->getRequest()->getFiles()->toArray());
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
        $this->_viewModel['field_name'] = $this->_field_name;

    }

    public function filterAction() {
        if ($this->getRequest()->isPost()) {
            $action = !empty($this->getRequest()->getPost('filter_action')) ? $this->getRequest()->getPost('filter_action') : 'index';
            
            $ssFilter	= new Container(__CLASS__ . $action);
            $data = $this->_params['data'];
            
            $ssFilter->pagination_option        = intval($data['pagination_option']);
            $ssFilter->order_by                 = $data['order_by'];
            $ssFilter->order                    = $data['order'];
            $ssFilter->filter_status            = $data['filter_status'];
            $ssFilter->filter_keyword           = $data['filter_keyword'];
            $ssFilter->filter_error             = $data['filter_error'];
            $ssFilter->filter_date_begin        = $data['filter_date_begin'];
            $ssFilter->filter_date_end          = $data['filter_date_end'];
            $ssFilter->filter_result_error      = $data['filter_result_error'];
        }

        if (!empty($this->_params['route']['id'])) {
            $ssFilter->filter_product = $this->_params['route']['id'];
        }

        $this->goRoute(array('action' => $action));
    }

    public function indexAction() {
        $myForm    = new \Admin\Form\Search\ZaloNotifyResult($this->getServiceLocator(), $this->_params['ssFilter']);
        $myForm->setData($this->_params['ssFilter']);
        // Danh sách data
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        $templates = json_decode($this->zalo_call("/template/all?offset=0&limit=100&status=1", [], 'GET'), true);
        $template_array = \ZendX\Functions\CreateArray::create($templates['data'], array('key' => 'templateId', 'value' => 'templateName'));

        $this->_viewModel['myForm']             = $myForm;
        $this->_viewModel['items']              = $items;
        $this->_viewModel['template']           = $template_array;
        $this->_viewModel['model']              = $this->getTable();
        $this->_viewModel['count']              = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['user']               = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['caption']            = 'Kết quả gửi thông báo Zalo';

        return new ViewModel($this->_viewModel);
    }

    public function formAction() {
        $myForm = new \Admin\Form\ZaloNotifyResult($this);
        $task = 'add-item';
        $caption = 'Kết quả thông báo zalo - Thêm mới';
        if(!empty($this->params('id'))) {
            $this->_params['data']['id'] = $this->params('id');
            $item = $this->getTable()->getItem($this->_params['data']);
            if(!empty($item)) {
                $this->_viewModel['item']	    = $item;
                if(!$this->getRequest()->isPost()){
                    $myForm->setData($item);
                }
                $task = 'edit-item';
                $caption = 'Kết quả thông báo zalo - Cập nhật';
            }
        }

        if($this->getRequest()->isPost()){
            $myForm->setInputFilter(new \Admin\Filter\ZaloNotifyResult(array('id' => $this->_params['data']['id'])));
            $myForm->setData($this->_params['data']);

            $controlAction = $this->_params['data']['control-action'];

            if($myForm->isValid()){
//                $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                $this->_params['item'] = $item;
                $result = $this->getTable()->saveItem($this->_params, array('task' => $task));

                $this->flashMessenger()->addMessage('Dữ liệu đã được cập nhật thành công');

                if($controlAction == 'save-new') {
                    $this->goRoute(array('action' => 'form'));
                } else if($controlAction == 'save') {
                    $this->goRoute(array('action' => 'form', 'id' => $result));
                } else {
                    $this->goRoute();
                }
            }
        }

        $this->_viewModel['myForm']	    = $myForm;
        $this->_viewModel['caption']    = $caption;
        return new ViewModel($this->_viewModel);
    }

    public function resendAction() {
        if($this->getRequest()->isPost()) {
            if(!empty($this->_params['data']['cid'])) {
                $cid = $this->_params['data']['cid'];
                $cid_update = array();
                foreach ($cid as $id){
                    $item = $this->getTable()->getItem(array('id' => $id));

                    if($item['result_error'] != 0){
                        $data_send['phone']         = $item['phone'];
                        $data_send['template_id']   = $item['template_id'];
                        $data_send['template_data'] = unserialize($item['template_data']);

                        $res = json_decode($this->zalo_call('/message/template', $data_send, 'POST'), true);
                        if($res['error'] == 0){
                            $cid_update[] = $data_send['phone'];
                        }
                        $this->getServiceLocator()->get('Admin\Model\ZaloNotifyResultTable')->saveItem(array('item' => $item, 'data' => $data_send, 'res' => $res), array('task' => 'update-item'));
                    }
                }
                $message = 'Đã gửi lại '. count($cid_update) .' thông báo thành công';
                $this->flashMessenger()->addMessage($message);
            }
        }

        $this->goRoute(array('action' => 'index'));
    }

    public function deleteAction() {
        if($this->getRequest()->isPost()) {
            if(!empty($this->_params['data']['cid'])) {
                // Xóa data đã chọn
                $cdata = $this->getTable()->deleteItem($this->_params, array('task' => 'delete-item'));
                $message = 'Xóa '. $cdata .' phần tử thành công';
                $this->flashMessenger()->addMessage($message);
            }
        }

        $this->goRoute(array('action' => 'index'));
    }
}
