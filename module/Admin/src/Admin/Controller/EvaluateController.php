<?php

namespace Admin\Controller;

use kcfinder\zipFolder;
use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class EvaluateController extends ActionController
{

    public function init()
    {
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\EvaluateTable';
        $this->_options['formName']  = 'formAdminEvaluate';

        // Thiết lập session filter
        $action = str_replace('-', '_', $this->_params['action']);
        $ssFilter                                    = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']       = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'year';
        $this->_params['ssFilter']['order']          = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';

        $this->_params['ssFilter']['filter_date_type']      = $ssFilter->filter_date_type;
        $this->_params['ssFilter']['filter_date_begin']     = $ssFilter->filter_date_begin;
        $this->_params['ssFilter']['filter_date_end']       = $ssFilter->filter_date_end;
        $this->_params['ssFilter']['filter_level_id']       = $ssFilter->filter_level_id;
        $this->_params['ssFilter']['filter_user_id']        = $ssFilter->filter_user_id;
        $this->_params['ssFilter']['filter_technical_id']   = $ssFilter->filter_technical_id;
        $this->_params['ssFilter']['filter_tailors_id']     = $ssFilter->filter_tailors_id;

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
            $action = !empty($this->_params['data']['filter_action']) ? $this->_params['data']['filter_action'] : 'index';
            $ssFilter = new Container(__CLASS__);
            $data     = $this->_params['data'];

            $ssFilter->pagination_option = intval($data['pagination_option']);
            $ssFilter->order_by          = $data['order_by'];
            $ssFilter->order             = $data['order'];

            $ssFilter->filter_date_type   = $data['filter_date_type'];
            $ssFilter->filter_date_begin  = $data['filter_date_begin'];
            $ssFilter->filter_date_end    = $data['filter_date_end'];

            $ssFilter->filter_level_id      = $data['filter_level_id'];
            $ssFilter->filter_technical_id  = $data['filter_technical_id'];
            $ssFilter->filter_tailors_id    = $data['filter_tailors_id'];
        }

        $this->goRoute(['action' => $action]);
    }

    public function indexSalesAction()
    {
        $ssFilter = new Container(__CLASS__. 'sales');
        $ssFilter->currentPageNumber = $this->_paginator['currentPageNumber'];

        $myForm	= new \Admin\Form\Search\Evaluate($this->getServiceLocator(), $this->_params);
        $myForm->setData($this->_params['ssFilter']);
        $this->_params['ssFilter']['filter_type'] = 'sale';
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item-sales'));
        $count = $this->getTable()->countItem($this->_params, array('task' => 'list-item-sales'));

        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $items;
        $this->_viewModel['count']                  = $count;
        $this->_viewModel['user']                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['product']                = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['caption']                = 'Đánh giá sales';

        return new ViewModel($this->_viewModel);
    }

    public function indexTechnicalAction()
    {
        $ssFilter = new Container(__CLASS__. 'technical');
        $ssFilter->currentPageNumber = $this->_paginator['currentPageNumber'];

        $myForm	= new \Admin\Form\Search\Evaluate($this->getServiceLocator(), $this->_params);
        $myForm->setData($this->_params['ssFilter']);
        $this->_params['ssFilter']['filter_type'] = 'technical';
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item-technical'));
        $count = $this->getTable()->countItem($this->_params, array('task' => 'list-item-technical'));
        $technical = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "technical" )), array('task' => 'cache'));

        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $items;
        $this->_viewModel['count']                  = $count;
        $this->_viewModel['technical']              = $technical;
        $this->_viewModel['user']                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['product']                = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'list-item', 'paginator' => false));
        $this->_viewModel['caption']                = 'Đánh giá kỹ thuật';

        return new ViewModel($this->_viewModel);
    }

    public function indexTailorsAction()
    {
        $ssFilter = new Container(__CLASS__. 'tailors');
        $ssFilter->currentPageNumber = $this->_paginator['currentPageNumber'];

        $myForm	= new \Admin\Form\Search\Evaluate($this->getServiceLocator(), $this->_params);
        $myForm->setData($this->_params['ssFilter']);
        $this->_params['ssFilter']['filter_type'] = 'tailors';
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item-tailors'));
        $count = $this->getTable()->countItem($this->_params, array('task' => 'list-item-tailors'));
        $tailors = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "tailors" )), array('task' => 'cache'));

        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $items;
        $this->_viewModel['count']                  = $count;
        $this->_viewModel['tailors']                = $tailors;
        $this->_viewModel['user']                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['product']                = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'list-item', 'paginator' => false));
        $this->_viewModel['caption']                = 'Đánh giá thợ may';

        return new ViewModel($this->_viewModel);
    }

    public function formAction()
    {
        if (!empty($this->_params['data']['code'])) {
            $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('code' => $this->_params['data']['code']), array('task' => 'by-code'));
        }

        if($contract){
            if(empty($contract['evaluate'])){
                $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']));
                $this->_viewModel['check_order']   = true;
                $this->_viewModel['contact']       = $contact;
                $this->_viewModel['contract']      = $contract;
                $products = unserialize($contract['options'])['product'];

                $list_product = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'list-item', 'paginator' => false));
                $evaluate_tailors = false; // có đanh giá thợ may không
                foreach($products as $key => $value){
                    $pro[$value['product_id']] = $list_product[$value['product_id']]['name'];
                    if($list_product[$value['product_id']]['tailors_status']){
                        $evaluate_tailors = true;
                        $pro_tailors[$value['product_id']] = $list_product[$value['product_id']]['name'];
                    }
                }
                $this->_viewModel['evaluate_tailors']   = $evaluate_tailors;
                $myForm = new \Admin\Form\Evaluate($this->getServiceLocator(), array('products' => $pro, 'product_tailors' => $pro_tailors) );
                $this->_params['data']['contract_id'] = $contract['id'];
                $myForm->setData($this->_params['data']);
            }
            else{
                $myForm = new \Admin\Form\Evaluate($this->getServiceLocator());
                $this->_viewModel['check_order']   = false;
                $this->_viewModel['notify']   = "Thông báo !<br>Đơn hàng ".$contract['code']." bạn vừa tìm kiếm đã được đánh giá.<br>Vui lòng tìm kiếm đơn khác.";
            }
        }
        else{
            $myForm = new \Admin\Form\Evaluate($this->getServiceLocator());
            $this->_viewModel['check_order']   = false;
            $this->_viewModel['notify']   = "Tìm kiếm đơn hàng cần nhận đánh giá từ khách hàng";
        }

        if ($this->getRequest()->isPost()) {
            if ($this->_params['data']['modal'] == 'success') {
                $this->_params['data']['evaluate_tailors'] = $evaluate_tailors;
                $myForm->setInputFilter(new \Admin\Filter\Evaluate($this->_params));
                $myForm->setData($this->_params['data']);
                if ($myForm->isValid()) {
                    $this->_params['data']     = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['item'] = $contract;

                    // Tạo đánh giá
                    $this->getServiceLocator()->get('Admin\Model\EvaluateTable')->saveItem($this->_params, array('task' => 'add-item-sale'));
                    $this->getServiceLocator()->get('Admin\Model\EvaluateTable')->saveItem($this->_params, array('task' => 'add-item-technical'));
                    if($evaluate_tailors){
                        $this->getServiceLocator()->get('Admin\Model\EvaluateTable')->saveItem($this->_params, array('task' => 'add-item-tailors'));
                    }
                    // Cập nhật trạng thái đã đánh giá cho đơn hàng
                    $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params, array('task' => 'update-evaluate'));

//                    echo 'success';
                    echo 'thank';
                    return $this->response;
                }
            }
        }
        else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }

        $this->_viewModel['myForm']   = $myForm;
        $this->_viewModel['settings'] = $this->_settings;
        $this->_viewModel['userInfo'] = $this->_userInfo->getUserInfo();
        $this->_viewModel['caption']  = 'Khảo sát ý kiến khách hàng';

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }
}


