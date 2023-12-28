<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class BcController extends ActionController {
    
    public function init() {
        
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\BcTable';
        $this->_options['formName'] = 'formAdminBc';
        
        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']                  = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'date';
        $this->_params['ssFilter']['order']                     = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';
        $this->_params['ssFilter']['filter_keyword']            = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['filter_date_begin']         = $ssFilter->filter_date_begin;
        $this->_params['ssFilter']['filter_date_end']           = $ssFilter->filter_date_end;
        $this->_params['ssFilter']['filter_date_type']          = $ssFilter->filter_date_type;
        $this->_params['ssFilter']['filter_sale_branch']        = $ssFilter->filter_sale_branch;
        $this->_params['ssFilter']['filter_sale_group']         = $ssFilter->filter_sale_group;
        $this->_params['ssFilter']['filter_user']               = $ssFilter->filter_user;
        
        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage'] = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : $this->_paginator['itemCountPerPage'];
        $this->_paginator['currentPageNumber'] = $this->params()->fromRoute('page', 1);
        $this->_params['paginator'] = $this->_paginator;
        
        // Lấy dữ liệu post của form
        $this->_params['data'] = $this->getRequest()->getPost()->toArray();
        
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
    }
    
    // Tìm kiếm
    public function filterAction() {
    
        if($this->getRequest()->isPost()) {
            $ssFilter	= new Container(__CLASS__);
            $data = $this->_params['data'];
    
            $ssFilter->pagination_option        = intval($data['pagination_option']);
            $ssFilter->order_by                 = $data['order_by'];
            $ssFilter->order                    = $data['order'];
            $ssFilter->filter_keyword           = $data['filter_keyword'];
            $ssFilter->filter_date_begin        = $data['filter_date_begin'];
            $ssFilter->filter_date_end          = $data['filter_date_end'];
            $ssFilter->filter_date_type         = $data['filter_date_type'];
            
            $ssFilter->filter_user              = $data['filter_user'];
            
            if(!empty($data['filter_sale_group'])) {
                if($ssFilter->filter_sale_group != $data['filter_sale_group']) {
                    $ssFilter->filter_user = null;
                    $ssFilter->filter_sale_group = $data['filter_sale_group'];
                }
            } else {
                $ssFilter->filter_user = null;
                $ssFilter->filter_sale_group = $data['filter_sale_group'];
            }
            
            if(!empty($data['filter_sale_branch'])) {
                if($ssFilter->filter_sale_branch != $data['filter_sale_branch']) {
                    $ssFilter->filter_sale_group = null;
                    $ssFilter->filter_user = null;
                    $ssFilter->filter_sale_branch = $data['filter_sale_branch'];
                }
            } else {
                $ssFilter->filter_sale_group = null;
                $ssFilter->filter_user = null;
                $ssFilter->filter_sale_branch = $data['filter_sale_branch'];
            }
        }
    
        $this->goRoute();
    }
    
    // Danh sách
    public function indexAction() {
        $myForm	= new \Admin\Form\Search\Bc($this->getServiceLocator(), $this->_params['ssFilter']);
        $myForm->setData($this->_params['ssFilter']);
        
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        
        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $items;
        $this->_viewModel['count']                  = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['user']                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_group']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['caption']                = 'Đăng ký thi Hội Đồng Anh - Danh sách';
        
        return new ViewModel($this->_viewModel);
    }
    
    // Xem chi tiết đơn hàng
    public function viewAction() {
        if(!empty($this->params('id'))) {
            $item = $this->getServiceLocator()->get('Admin\Model\BcTable')->getItem(array('id' => $this->params('id')));
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
    
        $this->_viewModel['item']                       = $item;
        $this->_viewModel['contact']                    = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $item['contact_id']));
        $this->_viewModel['user']                       = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_group']                 = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']                = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['sale_history_action']        = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-action')), array('task' => 'cache'));
        $this->_viewModel['sale_history_result']        = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-result')), array('task' => 'cache'));
        $this->_viewModel['sale_source_group']          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-source-group')), array('task' => 'cache'));
        $this->_viewModel['sale_contact_type']          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contact-type')), array('task' => 'cache-alias'));
        $this->_viewModel['sale_contact_subject']       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contact-subject')), array('task' => 'cache'));
        $this->_viewModel['sale_lost']                  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-lost')), array('task' => 'cache'));
        $this->_viewModel['location_city']              = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'location-city')), array('task' => 'cache'));
        $this->_viewModel['location_district']          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'location-district')), array('task' => 'cache'));
        $this->_viewModel['product_interest']           = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-interest')), array('task' => 'cache'));
        $this->_viewModel['school']                     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'school')), array('task' => 'cache'));
        $this->_viewModel['major']                      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'major')), array('task' => 'cache'));
        $this->_viewModel['sex']                        = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sex')), array('task' => 'cache-alias'));
        $this->_viewModel['caption']                    = 'Xem chi tiết đơn hàng đăng ký thi Hội Đồng Anh';
        return new ViewModel($this->_viewModel);
    }
    
    // Sửa đơn hàng
    public function editAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $numberFormat = new \ZendX\Functions\Number();
        $myForm = new \Admin\Form\Bc\Edit($this->getServiceLocator(), $this->_params);
        
        if(!empty($this->_params['data']['id'])) {
            $contract = $this->getServiceLocator()->get('Admin\Model\BcTable')->getItem(array('id' => $this->_params['data']['id']));
            $contract_options = !empty($contract['options']) ? unserialize($contract['options']) : array();
            $contract = array_merge($contract, $contract_options);
            $contract['date'] = $dateFormat->formatToView($contract['date']);
            $contract['date_register'] = $dateFormat->formatToView($contract['date_register']);
            $contract['date_speaking'] = $dateFormat->formatToView($contract['date_speaking']);
            
            $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']));
            
            $myForm->setData($contract);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }
    
        if($this->getRequest()->isPost()){
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\Bc\Edit($this->_params));
                $myForm->setData($this->_params['data']);
    
                if($myForm->isValid()){
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['item'] = $contract;
                    $this->_params['contact'] = $contact;
    
                    // Tính lại giá tiền khi thay đổi sản phẩm
                    $price = $numberFormat->formatToNumber($this->_params['data']['price']);
                    $price_promotion = 0;
                    $price_promotion_percent = $numberFormat->formatToNumber($this->_params['data']['price_promotion_percent']);
                    $price_promotion_price = $numberFormat->formatToNumber($this->_params['data']['price_promotion_price']);
                    $price_paid = $contract['price_paid'];
                    $price_accrued = $contract['price_accrued'];
                    
                    if(!empty($this->_params['data']['price_promotion_percent'])) {
                        $price_promotion = $numberFormat->formatToNumber($this->_params['data']['price_promotion_percent']) / 100 * $price;
                    }
                    if(!empty($this->_params['data']['price_promotion_price'])) {
                        $price_promotion = $price_promotion + $numberFormat->formatToNumber($this->_params['data']['price_promotion_price']);
                    }
                    
                    $price_total = $price - $price_promotion;
                    $price_owed = $price_total - $price_paid + $price_accrued;
                    
                    $this->_params['data']['price'] = $price;
                    $this->_params['data']['price_promotion'] = $price_promotion;
                    $this->_params['data']['price_promotion_percent'] = $price_promotion_percent;
                    $this->_params['data']['price_promotion_price'] = $price_promotion_price;
                    $this->_params['data']['price_total'] = $price_total;
                    $this->_params['data']['price_owed'] = $price_owed;
                    
                    $result = $this->getServiceLocator()->get('Admin\Model\BcTable')->saveItem($this->_params, array('task' => 'edit-item'));
    
                    $this->flashMessenger()->addMessage('Cập nhật dữ liệu thành công');
                    echo 'success';
                    return $this->response;
                }
            } else {
                $myForm->setData($this->_params['data']);
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
    
        $this->_viewModel['myForm']     = $myForm;
        $this->_viewModel['contract']   = $contract;
        $this->_viewModel['caption']    = 'Sửa đơn hàng';
    
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
    
    // Sửa ngày
    public function editDateAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $numberFormat = new \ZendX\Functions\Number();
        $myForm = new \Admin\Form\Bc\EditDate($this->getServiceLocator(), $this->_params);
    
        if(!empty($this->_params['data']['id'])) {
            $contract = $this->getServiceLocator()->get('Admin\Model\BcTable')->getItem(array('id' => $this->_params['data']['id']));
            $contract_options = !empty($contract['options']) ? unserialize($contract['options']) : array();
            $contract = array_merge($contract, $contract_options);
            $contract['date'] = $dateFormat->formatToView($contract['date']);
            $contract['date_register'] = $dateFormat->formatToView($contract['date_register']);
            $contract['date_speaking'] = $dateFormat->formatToView($contract['date_speaking']);
            
            $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']));
            
            $myForm->setData($contract);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }
    
        if($this->getRequest()->isPost()){
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\Bc\EditDate($this->_params));
                $myForm->setData($this->_params['data']);
    
                if($myForm->isValid()){
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['item'] = $contract;
                    $this->_params['contact'] = $contact;
    
                    $result = $this->getServiceLocator()->get('Admin\Model\BcTable')->saveItem($this->_params, array('task' => 'edit-item'));
    
                    $this->flashMessenger()->addMessage('Cập nhật dữ liệu thành công');
                    echo 'success';
                    return $this->response;
                }
            } else {
                $myForm->setData($this->_params['data']);
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
    
        $this->_viewModel['myForm']     = $myForm;
        $this->_viewModel['contract']   = $contract;
        $this->_viewModel['caption']    = 'Sửa ngày';
    
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
    
    // Sửa ghi chú
    public function editNoteAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $numberFormat = new \ZendX\Functions\Number();
        $myForm = new \Admin\Form\Bc\EditNote($this->getServiceLocator(), $this->_params);
    
        if(!empty($this->_params['data']['id'])) {
            $contract = $this->getServiceLocator()->get('Admin\Model\BcTable')->getItem(array('id' => $this->_params['data']['id']));
            $contract_options = !empty($contract['options']) ? unserialize($contract['options']) : array();
            $contract = array_merge($contract, $contract_options);
            $contract['date'] = $dateFormat->formatToView($contract['date']);
            $contract['date_register'] = $dateFormat->formatToView($contract['date_register']);
            $contract['date_speaking'] = $dateFormat->formatToView($contract['date_speaking']);
            
            $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']));
            
            $myForm->setData($contract);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }
    
        if($this->getRequest()->isPost()){
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\Bc\EditNote($this->_params));
                $myForm->setData($this->_params['data']);
    
                if($myForm->isValid()){
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['item'] = $contract;
                    $this->_params['contact'] = $contact;
    
                    $result = $this->getServiceLocator()->get('Admin\Model\BcTable')->saveItem($this->_params, array('task' => 'edit-item'));
    
                    $this->flashMessenger()->addMessage('Cập nhật dữ liệu thành công');
                    echo 'success';
                    return $this->response;
                }
            } else {
                $myForm->setData($this->_params['data']);
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
    
        $this->_viewModel['myForm']     = $myForm;
        $this->_viewModel['contract']   = $contract;
        $this->_viewModel['caption']    = 'Sửa ghi chú';
    
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
    
    // Xóa đơn hàng
    public function deleteAction() {
        $item = $this->getServiceLocator()->get('Admin\Model\BcTable')->getItem(array('id' => $this->params('id')));
    
        if(empty($item)) {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
    
        if($this->getRequest()->isPost()) {
            // Xóa hoa đồng
            $this->_params['item'] = $item;
            $contract_delete = $this->getTable()->deleteItem($this->_params, array('task' => 'delete-item'));
    
            $this->flashMessenger()->addMessage('Xóa đơn hàng thành công');
    
            $this->goRoute();
        }
    
    
        $this->_viewModel['item']               = $item;
        $this->_viewModel['contact']            = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $item['contact_id']));
        $this->_viewModel['user']               = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_group']         = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']        = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['sale_source_group']  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-source-group')), array('task' => 'cache'));
        $this->_viewModel['sex']                = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sex')), array('task' => 'cache-alias'));
        $this->_viewModel['caption']            = 'Đăng ký thi Hội Đồng Anh - Xóa';
        return new ViewModel($this->_viewModel);
    }
    
    // Sửa thông tin khách hàng
    public function contactEditAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $myForm = new \Admin\Form\Bc\Contact($this->getServiceLocator(), $this->_params);
    
        if(!empty($this->_params['data']['id'])) {
            $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $this->_params['data']['id']));
            $contact_options = !empty($contact['options']) ? unserialize($contact['options']) : array();
            $contact = array_merge($contact, $contact_options);
            $contact['birthday'] = !empty($contact['birthday']) ? $dateFormat->formatToView($contact['birthday']) : null;
    
            $myForm->setData($contact);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }
    
        if($this->getRequest()->isPost()){
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\Bc\Contact($this->_params));
                $myForm->setData($this->_params['data']);
    
                if($myForm->isValid()){
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['item'] = $contact;
    
                    $result = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($this->_params, array('task' => 'edit-item'));
    
                    $this->flashMessenger()->addMessage('Cập nhật dữ liệu thành công');
                    echo 'success';
                    return $this->response;
                }
            } else {
                $myForm->setData($this->_params['data']);
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
    
        $this->_viewModel['myForm']             = $myForm;
        $this->_viewModel['item']               = $contact;
        $this->_viewModel['location_district']  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'location-district')), array('task' => 'cache'));
        $this->_viewModel['caption']            = 'Cập nhật thông tin khách hàng';
    
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }    
    
    // Thêm hóa đơn
    public function billAddAction() {
        $dateFormat = new \ZendX\Functions\Date();
        
        if(!empty($this->_params['data']['id'])) {
            $contract   = $this->getServiceLocator()->get('Admin\Model\BcTable')->getItem(array('id' => $this->_params['data']['id']));
            $contact    = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']));
            $contact['birthday'] = !empty($contact['birthday']) ? $dateFormat->formatToView($contact['birthday']) : null;
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }
        
        if($this->getRequest()->isPost()){
            $myForm = new \Admin\Form\Bc\Bill($this->getServiceLocator());
            $myForm->setData($this->_params['data']);
            
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\Bc\Bill(array('data' => $this->_params['data'], 'contract' => $contract, 'contact' => $contact)));
                if($myForm->isValid()){
                    $this->_params['data']      = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['contract']  = $contract;
                    $this->_params['contact']   = $contact;
                    
                    if(!empty($this->_params['data']['paid_price']) || !empty($this->_params['data']['accrued_price'])) {
                        // Thêm hóa đơn
                        $bill = $this->getServiceLocator()->get('Admin\Model\BcBillTable')->saveItem($this->_params, array('task' => 'add-item'));
                        
                        // Cập nhật lại thông tin thanh toán đơn hàng
                        $number = new \ZendX\Functions\Number();
                        
                        $price_paid     = $contract['price_paid'] + $number->formatToNumber($this->_params['data']['paid_price']);
                        $price_accrued  = $contract['price_accrued'] + $number->formatToNumber($this->_params['data']['accrued_price']);
                        $price_owed     = $contract['price_total'] - $price_paid + $price_accrued;
                        
                        $arrContract = array();
                        $arrContract['id'] = $contract['id'];
                        $arrContract['price_paid'] = $price_paid;
                        $arrContract['price_accrued'] = $price_accrued;
                        $arrContract['price_owed'] = $price_owed;
                        $contract = $this->getServiceLocator()->get('Admin\Model\BcTable')->saveItem(array('data' => $arrContract), array('task' => 'edit-item'));
                    }
            
                    $this->flashMessenger()->addMessage('Thêm hóa đơn thành công');
                    echo 'success';
                    return $this->response;
                }
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
        
        $this->_viewModel['myForm']     = $myForm;
        $this->_viewModel['caption']    = 'Thêm hóa đơn';
        $this->_viewModel['contract']   = $contract;
        $this->_viewModel['contact']    = $contact;
        
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
        
        return $viewModel;
    }
    
    // Sửa hóa đơn
    public function billEditAction() {
        $dateFormat = new \ZendX\Functions\Date();
        
        if(!empty($this->_params['data']['id'])) {
            $item       = $this->getServiceLocator()->get('Admin\Model\BcBillTable')->getItem(array('id' => $this->_params['data']['id']));
            $contract   = $this->getServiceLocator()->get('Admin\Model\BcTable')->getItem(array('id' => $item['contract_id']));
            $contact    = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']));
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }
        
        if($this->getRequest()->isPost()){
            $myForm = new \Admin\Form\Bc\BillEdit($this->getServiceLocator());
            $myForm->setInputFilter(new \Admin\Filter\Bc\BillEdit(array('data' => $this->_params['data'], 'item' => $item)));
            if ($item['type'] == 'Thu') {
                $caption = 'Sửa phiếu thu';
                $message = 'Sửa phiếu thu thành công';
            } elseif ($item['type'] == 'Chi') {
                $caption = 'Sửa phiếu chi';
                $message = 'Sửa phiếu chi thành công';
            }
            
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setData($this->_params['data']);
                if($myForm->isValid()){
                    $this->_params['data']      = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['contract']  = $contract;
                    $this->_params['contact']   = $contact;
                    $this->_params['item']      = $item;
                    
                    $result = $this->getServiceLocator()->get('Admin\Model\BcBillTable')->saveItem($this->_params, array('task' => 'contract-edit-item'));
            
                    $this->flashMessenger()->addMessage($message);
                    echo 'success';
                    return $this->response;
                }
            } else {
                $item_options = !empty($item['options']) ? unserialize($item['options']) : array();
                $item = array_merge($item, $item_options);
                $item['date'] = $dateFormat->formatToView($item['date']);
                
                $myForm->setData($item);
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
        
        $this->_viewModel['myForm']     = $myForm;
        $this->_viewModel['caption']    = $caption;
        $this->_viewModel['item']       = $item;
        $this->_viewModel['contract']   = $contract;
        
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
        
        return $viewModel;
    }
    
    // Xóa hóa đơn
    public function billDeleteAction() {
        if(!empty($this->_params['data']['id'])) {
            $item       = $this->getServiceLocator()->get('Admin\Model\BcBillTable')->getItem(array('id' => $this->_params['data']['id']));
            $contract   = $this->getServiceLocator()->get('Admin\Model\BcTable')->getItem(array('id' => $item['contract_id']));
            $contact    = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']));
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }
    
        if($this->getRequest()->isPost()){
            $myForm = new \Admin\Form\Bc\BillDelete($this->getServiceLocator());
            $myForm->setInputFilter(new \Admin\Filter\Bc\BillDelete($this->_params));
            $myForm->setData($item);
            
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setData($this->_params['data']);
                if($myForm->isValid()){
                    $this->_params['data']      = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['contract']  = $contract;
                    $this->_params['contact']   = $contact;
                    $this->_params['item']      = $item;
    
                    $result = $this->getServiceLocator()->get('Admin\Model\BcBillTable')->deleteItem($this->_params, array('task' => 'contract-delete-item'));
    
                    $this->flashMessenger()->addMessage('Xóa hóa đơn thành công');
                    echo 'success';
                    return $this->response;
                }
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
    
        $this->_viewModel['myForm']         = $myForm;
        $this->_viewModel['caption']        = 'Xóa hóa đơn';
        $this->_viewModel['item']           = $item;
        $this->_viewModel['contract']       = $contract;
        $this->_viewModel['bill_type']      = array('paid' => 'Thu', 'accrued' => 'Chi', 'surcharge' => 'Phụ phí');
        $this->_viewModel['paid_type']      = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "table" => "document", "where" => array( "code" => "bill-type-paid" ), "order" => array("ordering" => "ASC", "created" => "ASC", "name" => "ASC"), "view"  => array( "key" => "id", "value" => "name", "sprintf" => "%s" ) ), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $this->_viewModel['accrued_type']   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "table" => "document", "where" => array( "code" => "bill-type-accrued" ), "order" => array("ordering" => "ASC", "created" => "ASC", "name" => "ASC"), "view"  => array( "key" => "id", "value" => "name", "sprintf" => "%s" ) ), array('task' => 'cache'));
        $this->_viewModel['surcharge_type'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "table" => "document", "where" => array( "code" => "bill-type-surcharge" ), "order" => array("ordering" => "ASC", "created" => "ASC", "name" => "ASC"), "view"  => array( "key" => "id", "value" => "name", "sprintf" => "%s" ) ), array('task' => 'cache'));
    
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
    
    // Nghỉ học
    public function eduClassLeaveAction() {
        if(!empty($this->_params['data']['id'])) {
            $contract   = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->_params['data']['id']));
            $contact    = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']));
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }
    
        if($this->getRequest()->isPost()){
            $myForm = new \Admin\Form\Contract\EduClassLeave($this->getServiceLocator());
            $myForm->setInputFilter(new \Admin\Filter\Contract\EduClassLeave($this->_params));
            $myForm->setData($this->_params['data']);
    
            if($this->_params['data']['modal'] == 'success') {
                if($myForm->isValid()){
                    $this->_params['data']      = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['contract']  = $contract;
                    $this->_params['contact']   = $contact;
    
                    // Cập nhật vào đơn hàng
                    $update = $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params, array('task' => 'edu-class-leave'));
    
                    $this->flashMessenger()->addMessage('Cập nhật dữ liệu thành công');
                    echo 'success';
                    return $this->response;
                }
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
    
        $this->_viewModel['myForm']     = $myForm;
        $this->_viewModel['caption']    = 'Nghỉ học';
        $this->_viewModel['contract']   = $contract;
        $this->_viewModel['contact']    = $contact;
    
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
    
    // Hủy nghỉ học
    public function eduClassLeaveCancelAction() {
        if(!empty($this->_params['data']['id'])) {
            $contract   = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->_params['data']['id']));
            $contact    = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']));
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }
    
        if($this->getRequest()->isPost()){
            $myForm = new \Admin\Form\Contract\EduClassLeaveCancel($this->getServiceLocator());
            $myForm->setInputFilter(new \Admin\Filter\Contract\EduClassLeaveCancel($this->_params));
            $myForm->setData($this->_params['data']);
    
            if($this->_params['data']['modal'] == 'success') {
                if($myForm->isValid()){
                    $this->_params['data']      = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['contract']  = $contract;
                    $this->_params['contact']   = $contact;
    
                    // Cập nhật vào đơn hàng
                    $update = $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params, array('task' => 'edu-class-leave-cancel'));
    
                    $this->flashMessenger()->addMessage('Cập nhật dữ liệu thành công');
                    echo 'success';
                    return $this->response;
                }
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }
    
        $this->_viewModel['myForm']     = $myForm;
        $this->_viewModel['caption']    = 'Hủy nghỉ học';
        $this->_viewModel['contract']   = $contract;
        $this->_viewModel['contact']    = $contact;
    
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }
    
    // Chuyển người quản lý
    public function changeUserAction(){
        
        if($this->getRequest()->isXmlHttpRequest()) {
            $contract = $this->getServiceLocator()->get('Admin\Model\BcTable')->getItem(array('id' => $this->_params['data']['id']), null);
            $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contract['contact_id']), null);
          
            if(!empty($contract)) {
                if($this->getRequest()->isPost()){
                    $bill = $this->getServiceLocator()->get('Admin\Model\BcBillTable')->listItem(array('data' => array('contract_id' => $contract['id'])), array('task' => 'list-all'));
                    
                    $this->_params['user'] = $this->getServiceLocator()->get('Admin\Model\UserTable')->getItem(array('id' => $this->_params['data']['user_id']));
                    $this->_params['contract'] = $contract;
                    $this->_params['contact'] = $contact;
                    $this->_params['bill'] = $bill;
    
                    $result = $this->getServiceLocator()->get('Admin\Model\BcTable')->saveItem($this->_params, array('task' => 'change-user'));
                }
            }
            
            return $this->response;
        } else {
            if($this->getRequest()->isPost()){
                $myForm = new \Admin\Form\Contract\ChangeUser($this->getServiceLocator(), $this->_params);
                
                if($this->getRequest()->isPost()){
                    $items = $this->getServiceLocator()->get('Admin\Model\BcTable')->listItem(array('ids' => $this->_params['data']['cid']), array('task' => 'list-item-multi'));
                }
                
                $this->_viewModel['myForm']	                = $myForm;
                $this->_viewModel['caption']                = 'Đăng ký thi Hội Đồng Anh - Chuyển quyền quản lý';
                $this->_viewModel['items']                  = $items;
                $this->_viewModel['user']                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
                $this->_viewModel['sale_group']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
                $this->_viewModel['sale_branch']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
            } else {
                return $this->redirect()->toRoute('routeAdmin/default', array('controller' => $this->_params['controller'], 'action' => 'index'));
            }
        }
    
        return new ViewModel($this->_viewModel);
    }
    
    public function exportAction() {
        $dateFormat             = new \ZendX\Functions\Date();
        
        $items                  = $this->getTable()->listItem($this->_params, array('task' => 'list-item', 'paginator' => false));
        $user                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $sale_group             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $sale_branch            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $contract_type          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contract-type')), array('task' => 'cache'));
        $contract_use_status    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contract-use-status')), array('task' => 'cache'));
        $sale_contact_type      = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contact-type')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $sale_history_action    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-action')), array('task' => 'cache'));
        $sale_history_result    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-result')), array('task' => 'cache'));
        $sale_source_group      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-source-group')), array('task' => 'cache'));
        $sale_source_access     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-source-access')), array('task' => 'cache'));
        $location_city          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'location-city')), array('task' => 'cache'));
        $location_district      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'location-district')), array('task' => 'cache'));
        $product                = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
        $edu_class              = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->listItem(null, array('task' => 'cache'));
        
        //Include PHPExcel
        require_once PATH_VENDOR . '/Excel/PHPExcel.php';
        
        // Config
        $config = array(
            'sheetData' => 0,
            'headRow' => 1,
            'startRow' => 2,
            'startColumn' => 0,
        );
        
        // Column
        $arrColumn = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ','BA','BB','BC','BD','BE','BF','BG','BH','BI','BJ','BK','BL','BM','BN','BO','BP','BQ','BR','BS','BT','BU','BV','BW','BX','BY','BZ');
        
        // Data Export
        $arrData = array(
            array('field' => 'date', 'title' => 'Ngày', 'type' => 'date', 'format' => 'd/m/Y'),
            array('field' => 'date', 'title' => 'Tháng', 'type' => 'date', 'format' => 'm'),
            array('field' => 'price', 'title' => 'Đơn giá'),
            array('field' => 'price_promotion', 'title' => 'Ưu đãi'),
            array('field' => 'price_total', 'title' => 'Thành tiền'),
            array('field' => 'price_paid', 'title' => 'Đã đóng'),
            array('field' => 'price_accrued', 'title' => 'Đã chi'),
            array('field' => 'price_owed', 'title' => 'Công nợ'),
            array('field' => 'price_surcharge', 'title' => 'Phụ phí'),
            array('field' => 'promotion_content', 'title' => 'Lý do ưu đãi', 'type' => 'data_serialize', 'data_serialize_field' => 'options'),
            array('field' => 'product_id', 'title' => 'Sản phẩm', 'type' => 'data_source', 'data_source' => $product),
            array('field' => 'edu_class_id', 'title' => 'Lớp học', 'type' => 'data_source', 'data_source' => $edu_class),
            array('field' => 'contact_phone', 'title' => 'Điện thoại', 'type' => 'phone'),
            array('field' => 'contact_name', 'title' => 'Họ tên'),
            array('field' => 'contact_email', 'title' => 'Email'),
            array('field' => 'contact_birthday_year', 'title' => 'Năm sinh'),
            array('field' => 'contact_location_city_id', 'title' => 'Tỉnh thành', 'type' => 'data_source', 'data_source' => $location_city),
            array('field' => 'contact_location_district_id', 'title' => 'Quận/huyện', 'type' => 'data_source', 'data_source' => $location_district),
            array('field' => 'address', 'title' => 'Địa chỉ', 'type' => 'data_serialize', 'data_serialize_field' => 'contact_options'),
            array('field' => 'facebook', 'title' => 'Facebook', 'type' => 'data_serialize', 'data_serialize_field' => 'contact_options'),
            array('field' => 'user_id', 'title' => 'Người quản lý', 'type' => 'data_source', 'data_source' => $user, 'data_source_field' => 'name'),
            array('field' => 'sale_group_id', 'title' => 'Đội nhóm', 'type' => 'data_source', 'data_source' => $sale_group),
            array('field' => 'sale_branch_id', 'title' => 'Cơ sở', 'type' => 'data_source', 'data_source' => $sale_branch),
            array('field' => 'contact_type', 'title' => 'Phân loại khách hàng', 'type' => 'data_serialize', 'data_serialize_field' => 'options', 'data_source' => $sale_contact_type, 'data_source_field' => 'name'),
            array('field' => 'contact_source_group_id', 'title' => 'Nguồn khách hàng', 'type' => 'data_serialize', 'data_serialize_field' => 'options', 'data_source' => $sale_source_group, 'data_source_field' => 'name'),
            array('field' => 'contact_history_created', 'title' => 'Ngày chăm sóc cuối', 'type' => 'data_serialize', 'data_serialize_field' => 'options', 'data_date_format' => 'd/m/Y'),
            array('field' => 'contact_store', 'title' => 'Ngày kho', 'type' => 'data_serialize', 'data_serialize_field' => 'options', 'data_date_format' => 'd/m/Y'),
        );
        
        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();
        
        // Set document properties
        $objPHPExcel->getProperties()->setCreator($this->_userInfo->getUserInfo('name'))
        							 ->setLastModifiedBy($this->_userInfo->getUserInfo('username'))
        							 ->setTitle("Export");
        
        // Dữ liệu tiêu đề cột
        $startColumn = $config['startColumn'];
        foreach ($arrData AS $key => $data) {
            $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[$startColumn] . $config['headRow'], $data['title']);
            $objPHPExcel->getActiveSheet()->getStyle($arrColumn[$startColumn] . $config['headRow'])->getFont()->setBold(true);
            $startColumn++;
        }
        
        // Dữ liệu data
        $startRow = $config['startRow'];
        foreach ($items AS $item) {
            $startColumn = $config['startColumn'];
            foreach ($arrData AS $key => $data) {
                switch ($data['type']) {
                    case 'date':
                        $formatDate = $data['format'] ? $data['format'] : 'd/m/Y';
                        $value = $dateFormat->formatToView($item[$data['field']], $formatDate);
                        break;
                    case 'data_source':
                        $field = $data['data_source_field'] ? $data['data_source_field'] : 'name';
                        $value = $data['data_source'][$item[$data['field']]][$field];
                        break;
                   	case 'data_serialize':
                        $data_serialize = $item[$data['data_serialize_field']] ? unserialize($item[$data['data_serialize_field']]) : array();
                        $value = $data_serialize[$data['field']];
                        
                        if(!empty($data['data_source'])) {
                            $field = $data['data_source_field'] ? $data['data_source_field'] : 'name';
                            $value = $data['data_source'][$data_serialize[$data['field']]][$field];
                        }
                        if(!empty($data['data_date_format'])) {
                            $value = $dateFormat->formatToView($data_serialize[$data['field']], $data['data_date_format']);
                        }
                        break;
                    default:
                        $value = $item[$data['field']];
                }
                
                $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[$startColumn] . $startRow, $value);
                $startColumn++;
            }
            $startRow++;
        }
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Export.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header ('Cache-Control: cache, must-revalidate');
        header ('Pragma: public');
        
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
        
        return $this->response;
    }
}


