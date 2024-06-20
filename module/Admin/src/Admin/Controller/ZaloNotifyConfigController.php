<?php

namespace Admin\Controller;

use kcfinder\zipFolder;
use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;
use ZendX\System\UserInfo;

class ZaloNotifyConfigController extends ActionController{

    public function init() {
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\ZaloNotifyConfigTable';
        $this->_options['formName'] = 'formAdminZaloNotifyConfig';

        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__ . $this->_params['action']);
        $this->_params['ssFilter']['order_by']                  = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'ordering';
        $this->_params['ssFilter']['order']                     = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';
        $this->_params['ssFilter']['filter_status']             = $ssFilter->filter_status;
        $this->_params['ssFilter']['filter_keyword']            = $ssFilter->filter_keyword;

        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage']               = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : 50;
        $this->_paginator['currentPageNumber']              = $this->params()->fromRoute('page', 1);
        $this->_params['paginator'] = $this->_paginator;

        // Lấy dữ liệu post của form
        $this->_params['data'] = array_merge($this->getRequest()->getPost()->toArray(), $this->getRequest()->getFiles()->toArray());
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;

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
        }

        if (!empty($this->_params['route']['id'])) {
            $ssFilter->filter_product = $this->_params['route']['id'];
        }

        $this->goRoute(array('action' => $action));
    }

    public function indexAction() {
        $myForm    = new \Admin\Form\Search\ZaloNotifyConfig($this->getServiceLocator(), $this->_params['ssFilter']);
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
        $this->_viewModel['branch']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['caption']            = 'Cấu hình thông báo Zalo';

        return new ViewModel($this->_viewModel);
    }

    public function formAction() {
        $myForm = new \Admin\Form\ZaloNotifyConfig($this);
        $task = 'add-item';
        $caption = 'Cấu hình thông báo ZALO - Thêm mới';
        if(!empty($this->params('id'))) {
            $this->_params['data']['id'] = $this->params('id');
            $item = $this->getTable()->getItem($this->_params['data']);
            if(!empty($item)) {
                $item['sale_branch_ids'] = explode(',', $item['sale_branch_ids']);
                if(!$this->getRequest()->isPost()){
                    $myForm->setData($item);
                }
                $task = 'edit-item';
                $caption = 'Cấu hình thông báo ZALO - Sửa';
            }
        }

        if($this->getRequest()->isPost()){
            $myForm->setInputFilter(new \Admin\Filter\ZaloNotifyConfig(array('id' => $this->_params['data']['id'])));
            $myForm->setData($this->_params['data']);

            $controlAction = $this->_params['data']['control-action'];
            $sale_branch_ids = $this->_params['data']['sale_branch_ids'];

            if($myForm->isValid()){
                $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                $this->_params['data']['sale_branch_ids'] = $sale_branch_ids;
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


    public function addAction() {
        $myForm = new \Admin\Form\ZaloNotifyConfig($this, array('action' => 'add'));

        if($this->getRequest()->isPost()){
            $myForm->setInputFilter(new \Admin\Filter\ZaloNotifyConfig());
            $myForm->setData($this->_params['data']);
            $controlAction = $this->_params['data']['control-action'];
            if($myForm->isValid()){
                $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                $result = $this->getServiceLocator()->get('Admin\Model\MarketingAdsTable')->saveItem($this->_params, array('task' => 'add-item'));
                $this->flashMessenger()->addMessage('Thêm mới cấu hình thông báo ZALO thành công');

                if($controlAction == 'save-new') {
                    $this->goRoute(array('action' => 'add'));
                } else if($controlAction == 'save') {
                    $this->goRoute(array('action' => 'edit', 'id' => $result));
                } else {
                    $this->goRoute();
                }
            }
        }

        $this->_viewModel['myForm']	    = $myForm;
        $this->_viewModel['caption']    = 'Thêm mới Cấu hình thông báo ZALO';
        return new ViewModel($this->_viewModel);
    }

    public function editAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $myForm = $this->getForm();
        $item_id = $this->params('id');
        if (!empty($item_id)) {
            $this->_params['data']['id'] = $item_id;
            $item = $this->getTable()->getItem($this->_params['data']);
            if (!empty($item)) {
                if (!$this->getRequest()->isPost()) {
                    $item['from_date'] = $dateFormat->formatToView($item['from_date']);
                    $item['to_date'] = $dateFormat->formatToView($item['to_date']);
                    $myForm->setData($item);
                }
            }
            else {
                return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'not-found'));
            }
        }
        if ($this->getRequest()->isPost()) {
            $myForm->setInputFilter(new \Admin\Filter\ZaloNotifyConfig(array('id' => $this->_params['data']['id'], 'data' => $this->_params['data'], 'route' => $this->_params['route'])));
            $myForm->setData($this->_params['data']);
            $controlAction = $this->_params['data']['control-action'];

            if ($myForm->isValid()) {
                $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                $this->_params['item'] = $item;
                $this->getServiceLocator()->get('Admin\Model\ZaloNotifyConfigTable')->saveItem($this->_params, array('task' => 'edit-item'));
                $this->flashMessenger()->addMessage('Cấu hình thông báo Zalo đã được cập nhật');

                if($controlAction == 'save-new') {
                    $this->goRoute(array('action' => 'add'));
                } else if($controlAction == 'save') {
                    $this->goRoute(array('action' => 'edit', 'id' => $item_id));
                } else {
                    $this->goRoute();
                }
            }
        }

        $this->_viewModel['myForm']     = $myForm;
        $this->_viewModel['item']       = $item;
        $this->_viewModel['caption']    = 'Sửa cấu hình thông báo Zalo';
        return new ViewModel($this->_viewModel);
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
