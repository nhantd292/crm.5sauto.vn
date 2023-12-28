<?php

namespace Admin\Controller;

use kcfinder\zipFolder;
use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class KovBranchesController extends ActionController
{

    public function init()
    {
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\KovBranchesTable';

        // Thiết lập session filter
        $action = str_replace('-', '_', $this->_params['action']);
        $ssFilter                                    = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']       = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'year';
        $this->_params['ssFilter']['order']          = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';
        $this->_params['ssFilter']['filter_keyword']        = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['filter_status']         = $ssFilter->filter_status;

        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage']  = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : 50;
        $this->_paginator['currentPageNumber'] = $this->params()->fromRoute('page', 1);
        $this->_params['paginator']            = $this->_paginator;

        // Lấy dữ liệu post của form
        $this->_params['data'] = array_merge($this->getRequest()->getPost()->toArray(), $this->getRequest()->getFiles()->toArray());

        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
    }

    public function filterAction()
    {
        if ($this->getRequest()->isPost()) {
            $action = !empty($this->_params['data']['filter_action']) ? $this->_params['data']['filter_action'] : 'index';
            $ssFilter = new Container(__CLASS__);
            $data     = $this->_params['data'];

            $ssFilter->pagination_option = intval($data['pagination_option']);
            $ssFilter->order_by          = $data['order_by'];
            $ssFilter->order             = $data['order'];
            $ssFilter->filter_status      = $data['filter_status'];
            $ssFilter->filter_keyword     = $data['filter_keyword'];
        }

        $this->goRoute(['action' => $action]);
    }

    public function indexAction()
    {
        $ssFilter = new Container(__CLASS__. 'sales');
        $ssFilter->currentPageNumber = $this->_paginator['currentPageNumber'];

        $myForm	= new \Admin\Form\Search\ComboProduct($this->getServiceLocator(), $this->_params);
        $myForm->setData($this->_params['ssFilter']);
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        $count = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));

        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $items;
        $this->_viewModel['count']                  = $count;
        $this->_viewModel['caption']                = 'Danh sách kho hàng';

        return new ViewModel($this->_viewModel);
    }

    // Đồng bộ kho từ kiotviet
    public function updateAction(){
        $branches = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/branches?pageSize=100');
        $branches = json_decode($branches, true)['data'];
        $number_add = 0; $number_update = 0;

        foreach($branches as $branch){
            $item = $this->getTable()->getItem(array('id' => $branch['id']));
            if($item){
                $this->getTable()->saveItem(array('data' => $branch), array('task' => 'edit-item'));
                $number_update++;
            }
            else{
                $this->getTable()->saveItem(array('data' => $branch), array('task' => 'update'));
                $number_add++;
            }
        }
        if($number_update > 0 && $number_add > 0){
            $this->flashMessenger()->addMessage('Tạo mới '.$number_add.' và Cập nhật '.$number_update.' bản ghi thành công');
        }
        else if($number_add > 0){
            $this->flashMessenger()->addMessage('Tạo mới '.$number_add.' bản ghi thành công');
        }
        else if($number_update > 0){
            $this->flashMessenger()->addMessage('Cập nhật '.$number_update.' bản ghi thành công');
        }
        $this->goRoute(array('action' => 'index'));
    }

    public function statusAction() {
    if($this->getRequest()->isXmlHttpRequest()) {
        $this->getTable()->changeStatus($this->_params, array('task' => 'change-status'));
    } else {
        $this->goRoute();
    }

    return $this->response;
}
}


