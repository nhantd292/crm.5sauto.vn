<?php

namespace Admin\Controller;

use kcfinder\zipFolder;
use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class   MaterialController extends ActionController
{

    public function init()
    {
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\MaterialTable';
        $this->_options['formName']  = 'formAdminMaterial';

        // Thiết lập session filter
        $ssFilter                                    = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']       = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'year';
        $this->_params['ssFilter']['order']          = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';

        $this->_params['ssFilter']['filter_month']   = $ssFilter->filter_month;
        $this->_params['ssFilter']['filter_year']    = $ssFilter->filter_year;
        $this->_params['ssFilter']['color_group_id'] = $ssFilter->color_group_id;

        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage']  = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : 50;
        $this->_paginator['currentPageNumber'] = $this->params()->fromRoute('page', 1);
        $this->_params['paginator']            = $this->_paginator;

        // Lấy dữ liệu post của form
        $this->_params['data'] = $this->getRequest()->getPost()->toArray();

        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
    }

    public function filterAction()
    {
        if ($this->getRequest()->isPost()) {
            $ssFilter = new Container(__CLASS__);
            $data     = $this->_params['data'];

            $ssFilter->pagination_option = intval($data['pagination_option']);
            $ssFilter->order_by          = $data['order_by'];
            $ssFilter->order             = $data['order'];

            $ssFilter->filter_month   = $data['filter_month'];
            $ssFilter->filter_year    = $data['filter_year'];
            $ssFilter->color_group_id = $data['color_group_id'];
        }

        $this->goRoute();
    }

    public function indexAction()
    {
        $myForm = new \Admin\Form\Search\Material($this->getServiceLocator(), $this->_params['ssFilter']);

        if(empty($this->_params['ssFilter']['filter_month']))
            $this->_params['ssFilter']['filter_month'] = date(m);
        if(empty($this->_params['ssFilter']['filter_year']))
            $this->_params['ssFilter']['filter_year'] = date(Y);

        $myForm->setData($this->_params['ssFilter']);
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item-type', ));

        $units = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'unit')), array('task' => 'cache'));
        $units = \ZendX\Functions\CreateArray::create($units, array('key' => 'id', 'value' => 'name'));
        $color_group = $this->getServiceLocator()->get('Admin\Model\ColorGroupTable')->listItem(null, array('task' => 'list-item'));

        $carpetColorLists  = $this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem($this->_params, array('task' => 'list-all'))->toArray();
        $tangledColorLists = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem($this->_params, array('task' => 'list-all'))->toArray();
        $productList	   = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'list-item'));
        $arr_metarial      = array_merge($carpetColorLists, $tangledColorLists, $productList);

        $list_name = [];
        foreach ($arr_metarial as $key => $value){
            $list_name[$value['id']]['unit_id'] = $value['unit_id'];
            $list_name[$value['id']]['name']    = $value['name'];
            $list_name[$value['id']]['parent']  = $value['parent'];
        }

        $this->_viewModel['myForm']       = $myForm;
        $this->_viewModel['items']        = $items;
        $this->_viewModel['color_group']  = $color_group;
        $this->_viewModel['productList']  = $productList;
        $this->_viewModel['units']        = $units;
        $this->_viewModel['list_name']    = $list_name;
        $this->_viewModel['count']        = $this->getTable()->countItem($this->_params, array('task' => 'list-item-type'));
        $this->_viewModel['caption']      = 'Danh sách nguyên liệu đầu kì';
        return new ViewModel($this->_viewModel);
    }

    public function formAction()
    {
        $myForm     = $this->getForm();
        $dateFormat = new \ZendX\Functions\Date();

        $task    = 'add-item';
        $caption = 'Thêm mới nguyên liệu đầu kỳ';
        $item    = array();
        if (!empty($this->params('id'))) {
            $this->_params['data']['id'] = $this->params('id');
            $item                        = $this->getTable()->getItem($this->_params['data']);

            if (!empty($item)) {
                $myForm->setInputFilter(new \Admin\Filter\Material());
                $item['date'] = $dateFormat->formatToView($item['date']);
                $myForm->bind($item);
                $task    = 'edit-item';
                $caption = 'Cập nhật nguyên liệu đầu kỳ';
            }
        } else {
            $data_default = array(
                'date' => date('d/m/Y'),
            );
            $myForm->setData($data_default);
        }

        if ($this->getRequest()->isPost()) {
            // Truyền các giá trị post vào filter để đặt điều kiện lại
            $myForm->setInputFilter(new \Admin\Filter\Material());

            $this->_params['data']['type']  = "product";
            $myForm->setData($this->_params['data']);
            $controlAction = $this->_params['data']['control-action'];
            $params        = $this->_params['data']['params'];

            if ($myForm->isValid()) {
                if($task == 'add-item'){
                    $item = $this->getTable()->getItem(array('month' => $this->_params['data']['month'], 'year' => $this->_params['data']['year'], 'material_id' => $this->_params['data']['material_id']), array('task' => "month-year"));

                    if(empty($item)){
                        $result = $this->getTable()->saveItem($this->_params, array('task' => $task));
                        $this->flashMessenger()->addMessage('Dữ liệu đã được thêm mới thành công');
                    }
                    else{
                        $this->flashMessenger()->addMessage('Sản phẩm đã tồn tại trong bảng nhập số dư đầu kỳ');
                    }
                }
                elseif ($task == 'edit-item'){
                    $item                            = $this->getTable()->getItem($this->_params['data']);
                    $this->_params['item']           = $item;
                    $this->_params['data']['params'] = $params;
                    $result                          = $this->getTable()->saveItem($this->_params, array('task' => $task));
                    $this->flashMessenger()->addMessage('Dữ liệu đã được cập nhật thành công');
                }

                if ($controlAction == 'save-new') {
                    $this->goRoute(array('action' => 'form'));
                } else if ($controlAction == 'save') {
                    $this->goRoute(array('action' => 'form',
                                         'id'     => $result));
                } else {
                    $this->goRoute();
                }
            }
        }

        $this->_viewModel['myForm'] = $myForm;
        $this->_viewModel['item']   = $item;
        $this->_viewModel['user'] = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-marketing'));
        $this->_viewModel['branch'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['group'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group', 'type' => 'marketing')), array('task' => 'cache'));
        $this->_viewModel['sale_group_type'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'group-type', 'alias' => 'marketing')), array('task' => 'cache'));
        $this->_viewModel['product_type'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-type')), array('task' => 'cache'));
        $this->_viewModel['caption'] = $caption;

        return new ViewModel($this->_viewModel);
    }

    public function addAllAction()
    {
        $myForm     = new \Admin\Form\Material\addAll($this);

        $caption = 'Tạo bảng nhập nguyên liệu đầu kỳ';

        $data_default = array(
            'month' => date('m'),
            'year'  => date('Y'),
        );
        $myForm->setData($data_default);

        if ($this->getRequest()->isPost()) {
            // Truyền các giá trị post vào filter để đặt điều kiện lại
            $myForm->setInputFilter(new \Admin\Filter\Material\addAll());
            $this->_params['data']['type']  = "nomal";

            $myForm->setData($this->_params['data']);
            $controlAction = $this->_params['data']['control-action'];

            if ($myForm->isValid()) {
                $carpetColorLists = $this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem($this->_params, array('task' => 'list-all'))->toArray();
                $tangledColorLists = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem($this->_params, array('task' => 'list-all'))->toArray();

                $arr_metarial = array_merge($carpetColorLists, $tangledColorLists);

                // Tạo bảng nhập màu thảm
                foreach ($arr_metarial as $c) {
                    $this->_params['data']['material_id']  = $c['id'];
                    $item = $this->getTable()->getItem(array('month' => $this->_params['data']['month'], 'year' => $this->_params['data']['year'], 'material_id' => $c['id']), array('task' => "month-year"));
                    if(empty($item)){
                        $this->getTable()->saveItem($this->_params, array('task' => "add-all"));
                    }
                }

                $this->flashMessenger()->addMessage('Tạo bảng thành công');
                $this->goRoute();
            }
        }

        $this->_viewModel['myForm'] = $myForm;
        $this->_viewModel['caption'] = $caption;
        return new ViewModel($this->_viewModel);
    }

    public function saveAjaxAction()
    {
        $number   = new \ZendX\Functions\Number();
        $arrParams['data']['id']                 = $this->_params['data']['data_array'][0];
        $arrParams['data']['params']['number']   = $number->formatToData($this->_params['data']['data_array'][1]);
        $arrParams['item'] = $this->getTable()->getItem(array('id' => $arrParams['data']['id']), null);

        $this->getTable()->saveItem($arrParams, array('task' => 'save-ajax'));
        return $this->response;
    }
}


