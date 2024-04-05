<?php

namespace Admin\Controller;

use kcfinder\zipFolder;
use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class ContactController extends ActionController
{

    public function init()
    {
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\ContactTable';
        $this->_options['formName']  = 'formAdminContact';

        // Thiết lập session filter
        $ssFilter                                              = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']                 = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'date';
        $this->_params['ssFilter']['order']                    = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';
        $this->_params['ssFilter']['filter_type']              = $ssFilter->filter_type;
        $this->_params['ssFilter']['filter_keyword']           = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['filter_date_begin']        = $ssFilter->filter_date_begin;
        $this->_params['ssFilter']['filter_date_end']          = $ssFilter->filter_date_end;
        $this->_params['ssFilter']['filter_date_type']         = $ssFilter->filter_date_type;
        $this->_params['ssFilter']['filter_sale_branch']       = $ssFilter->filter_sale_branch;
        $this->_params['ssFilter']['filter_sale_group']        = $ssFilter->filter_sale_group;
        $this->_params['ssFilter']['filter_user']              = $ssFilter->filter_user;
        $this->_params['ssFilter']['filter_marketer_id']       = $ssFilter->filter_marketer_id;
        $this->_params['ssFilter']['filter_source_group']      = $ssFilter->filter_source_group;
        $this->_params['ssFilter']['filter_history_result']    = $ssFilter->filter_history_result;
        $this->_params['ssFilter']['filter_contact_type']      = $ssFilter->filter_contact_type;
        $this->_params['ssFilter']['filter_product_interest']  = $ssFilter->filter_product_interest;
        $this->_params['ssFilter']['filter_last_action']       = $ssFilter->filter_last_action;
        $this->_params['ssFilter']['filter_location_city']     = $ssFilter->filter_location_city;
        $this->_params['ssFilter']['filter_location_district'] = $ssFilter->filter_location_district;
        $this->_params['ssFilter']['filter_history_status']    = $ssFilter->filter_history_status;
        $this->_params['ssFilter']['filter_number_contract']   = $ssFilter->filter_number_contract;
        $this->_params['ssFilter']['filter_number_contract2']   = $ssFilter->filter_number_contract2;
        $this->_params['ssFilter']['filter_product_group_id']   = $ssFilter->filter_product_group_id;
        $this->_params['ssFilter']['filter_history_type_id']   = $ssFilter->filter_history_type_id;

        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage']  = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : $this->_paginator['itemCountPerPage'];
        $this->_paginator['currentPageNumber'] = $this->params()->fromRoute('page', 1);
        $this->_params['paginator']            = $this->_paginator;

        // Lấy dữ liệu post của form
        $this->_params['data'] = array_merge($this->getRequest()->getPost()->toArray(), $this->getRequest()->getFiles()->toArray());

        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
        $this->_viewModel['curent_user']  = $this->_userInfo->getUserInfo();
//        $this->_viewModel['encode_phone'] = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'encode-phone')), array('task' => 'list-all')), array('key' => 'content', 'value' => 'status'));
    }

    public function filterAction()
    {
        $ssFilter = new Container(__CLASS__);
        $data     = $this->_params['data'];

        if ($this->getRequest()->isPost()) {

            $ssFilter->pagination_option        = intval($data['pagination_option']);
            $ssFilter->order_by                 = $data['order_by'];
            $ssFilter->order                    = $data['order'];
            $ssFilter->filter_type              = $data['filter_type'];
            $ssFilter->filter_keyword           = $data['filter_keyword'];
            $ssFilter->filter_date_begin        = $data['filter_date_begin'];
            $ssFilter->filter_date_end          = $data['filter_date_end'];
            $ssFilter->filter_date_type         = $data['filter_date_type'];
            $ssFilter->filter_source_group      = $data['filter_source_group'];
            $ssFilter->filter_history_result    = $data['filter_history_result'];
            $ssFilter->filter_contact_type      = $data['filter_contact_type'];
            $ssFilter->filter_product_interest  = $data['filter_product_interest'];
            $ssFilter->filter_last_action       = $data['filter_last_action'];
            $ssFilter->filter_location_city     = $data['filter_location_city'];
            $ssFilter->filter_location_district = $data['filter_location_district'];
            $ssFilter->filter_history_status    = $data['filter_history_status'];
            $ssFilter->filter_number_contract   = $data['filter_number_contract'];
            $ssFilter->filter_number_contract2   = $data['filter_number_contract2'];
            $ssFilter->filter_product_group_id   = $data['filter_product_group_id'];
            $ssFilter->filter_history_type_id   = $data['filter_history_type_id'];

            $ssFilter->filter_user              = $data['filter_user'];
            $ssFilter->filter_marketer_id       = $data['filter_marketer_id'];
            $ssFilter->filter_sale_group        = $data['filter_sale_group'];

            if (!empty($data['filter_sale_branch'])) {
                if ($ssFilter->filter_sale_branch != $data['filter_sale_branch']) {
                    $ssFilter->filter_sale_group  = null;
                    $ssFilter->filter_sale_branch = $data['filter_sale_branch'];
                }
            }
            else {
                $ssFilter->filter_sale_group  = null;
                $ssFilter->filter_sale_branch = $data['filter_sale_branch'];
            }
        }

        if ($this->_params['route']['type'] == 'history-return') {
            $ssFilter->filter_date_begin     = date('d/m/Y');
            $ssFilter->filter_date_end       = date('d/m/Y');
            $ssFilter->filter_date_type      = 'history_return';
            $ssFilter->filter_history_status = '';
        }

        if ($this->_params['route']['type'] == 'history-status') {
            $ssFilter->filter_date_begin     = '';
            $ssFilter->filter_date_end       = '';
            $ssFilter->filter_date_type      = '';
            $ssFilter->filter_history_status = 'no';
        }

        $this->goRoute();
    }

    public function indexAction()
    {
//        $listProducts = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/products');
        $ssFilter = new Container(__CLASS__);
        $user_id = $this->_userInfo->getUserInfo('id');
        if(!empty($this->_params['route']['nid'])) {
            $id_item = $this->_params['route']['nid'];
            $notifyTable = $this->getServiceLocator()->get('Notifycation\Model\NotifyTable');
            $notify_item = $notifyTable->getItem(array("id" => $id_item), null);
            if($this->_params['route']['type'] == 'phone'){
                $notify_options = unserialize($notify_item['options']);
                $this->_params['ssFilter']['filter_keyword'] = $notify_options['phone'];
            }
            // Nếu chưa có trong ds user đã đọc
            if (strpos($notify_item['user_readed'], $user_id) === false) {
                $this->_params['item'] = $notify_item;
                $this->_params['data']['user_id'] = $user_id;
                $notifyTable->saveItem($this->_params, array('task' => 'update-readed'));
            }
        }

        // Phân quyền view
        $curent_user = $this->_userInfo->getUserInfo();
        $permission_ids = explode(',', $curent_user['permission_ids']);
        if(!in_array(SYSTEM, $permission_ids) && !in_array(ADMIN, $permission_ids) && !in_array(MANAGER, $permission_ids)){
            if(in_array(GDCN, $permission_ids) || in_array(SALEADMIN, $permission_ids)){
                $this->_params['ssFilter']['filter_sale_branch'] = $curent_user['sale_branch_id'];
                $ssFilter->filter_sale_branch = $curent_user['sale_branch_id'];
            }
            elseif (in_array(GROUP_SALES_LEADER, $permission_ids)){
                $this->_params['ssFilter']['filter_sale_branch'] = $curent_user['sale_branch_id'];
                $this->_params['ssFilter']['filter_sale_group'] = $curent_user['sale_group_id'];
                $ssFilter->filter_sale_branch = $curent_user['sale_branch_id'];
                $ssFilter->filter_sale_group = $curent_user['filter_sale_group'];
            }
            else{
                $this->_params['ssFilter']['filter_user'] = $curent_user['id'];
            }
        }

        $ssFilter                    = new Container(__CLASS__);
        $ssFilter->currentPageNumber = $this->_paginator['currentPageNumber'];

        $myForm = new \Admin\Form\Search\Contact($this->getServiceLocator(), $this->_params['ssFilter']);
        $myForm->setData($this->_params['ssFilter']);

        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));

        $fdata['ssFilter']['filter_active'] = 'unactive';
        $fdata['ssFilter']['filter_sale_branch'] = $curent_user['sale_branch_id'];
        $fdata['ssFilter']['filter_cancel_share'] = 0;
        $this->_viewModel['count_form_data']     = $this->getServiceLocator()->get('Admin\Model\FormDataTable')->countItem($fdata, array('task' => 'list-item-contact'));

        $this->_viewModel['myForm']              = $myForm;
        $this->_viewModel['items']               = $items;
        $this->_viewModel['count']               = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['user']                = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_group']          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']         = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['userInfo']            = $this->_userInfo->getUserInfo();
        $this->_viewModel['product_group']       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-group')), array('task' => 'cache'));
        $this->_viewModel['sale_history_action'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-action')), array('task' => 'cache'));
        $this->_viewModel['sale_history_result'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-result')), array('task' => 'cache'));
        $this->_viewModel['sale_history_type']   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-type')), array('task' => 'cache'));


        $this->_viewModel['locations']         = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(null, array('task' => 'cache'));


        $this->_viewModel['caption']             = 'Liên hệ - Danh sách';
        return new ViewModel($this->_viewModel);
    }

    public function formAction()
    {
        $dateFormat = new \ZendX\Functions\Date();
        $ssFilter   = new Container(__CLASS__);

//        $myForm = $this->getForm();

//        $myForm = new \Admin\Form\Contact($this);
        $curent_user_id  = $this->_userInfo->getUserInfo('id');
        $phone_code = true;

        $task    = 'add-item';
        $caption = 'Liên hệ - Thêm mới';
        $item    = array();
        if (!empty($this->params('id'))) {
            $this->_params['data']['id'] = $this->params('id');
            $item                        = $this->getTable()->getItem($this->_params['data']);

            if (!empty($item)) {
                $item_options        = !empty($item['options']) ? unserialize($item['options']) : array();
                $item['date_return'] = $dateFormat->formatToView($item['date_return']);
                $item                = array_merge($item, $item_options);

                if($curent_user_id == $item['user_id'] || $curent_user_id == $item['care_id']) {
                    $phone_code = false;
                }

                $myForm = new \Admin\Form\Contact($this, $item);
                if (!$this->getRequest()->isPost()) {
                    unset($item['history_action_id']);
                    unset($item['history_result_id']);
                    unset($item['history_type_id']);
                    unset($item['history_return']);
                    unset($item['history_content']);

                    $item['phone'] = $phone_code ? substr_replace($item['phone'], "***", 4, 3): $item['phone'];
                    $myForm->setData($item);
                }
                $task    = 'edit-item';
                $caption = 'Liên hệ - Sửa';
            }
        }
        else{
            $myForm = new \Admin\Form\Contact($this);
        }

        if ($this->getRequest()->isPost()) {
            $history_type_alias = '';// Phân loại lịch sử chăm sóc
            if(!empty($this->_params['data']['history_type_id'])){
                $history_type = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(array('id' => $this->_params['data']['history_type_id'],), null);
                $history_type_alias = $history_type['alias'];
            }
            $this->_params['data']['history_type_alias'] = $history_type_alias;

            $myForm->setInputFilter(new \Admin\Filter\Contact(array('id' => $this->_params['data']['id'], 'data' => $this->_params['data'], 'item' => $item, 'route' => $this->_params['route'])));
            $myForm->setData($this->_params['data']);

            $controlAction = $this->_params['data']['control-action'];

            if ($myForm->isValid()) {
                $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                $this->_params['item'] = $item;
                // Phân loại lịch sử chăm sóc
                $this->_params['data']['history_type_alias'] = $history_type_alias;

                $result = $this->getTable()->saveItem($this->_params, array('task' => $task));

                // Thêm lịch sử chăm sóc nếu có
                if (!empty($this->_params['data']['history_action_id'])) {
                    $this->_params['item']['id'] = $result;
                    $history                     = $this->getServiceLocator()->get('Admin\Model\HistoryTable')->saveItem($this->_params, array('task' => 'add-item'));
                }

                $this->flashMessenger()->addMessage('Dữ liệu đã được cập nhật thành công');

                if ($controlAction == 'save-new') {
                    $this->goRoute(array('action' => 'form'));
                } else if ($controlAction == 'save') {
                    $this->goRoute(array('action' => 'form', 'id' => $result));
                } else {
                    return $this->redirect()->toRoute('routeAdmin/paginator', array('controller' => $this->_params['controller'], 'action' => 'index', 'page' => $ssFilter->currentPageNumber));
                }
            }
        }

        $this->_viewModel['myForm']            = $myForm;
        $this->_viewModel['item']              = $item;
        $this->_viewModel['location_city']     = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache'));
        $this->_viewModel['location_district'] = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 2), array('task' => 'cache'));
        $this->_viewModel['locations']         = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(null, array('task' => 'cache'));

        $this->_viewModel['caption'] = $caption;
        return new ViewModel($this->_viewModel);
    }

    public function viewAction()
    {
        $item = $this->getTable()->getItem(array('id' => $this->_params['data']['id']));
        if (empty($item)) {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }

        $this->_viewModel['item']                 = $item;
        $this->_viewModel['user']                 = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_group']           = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['sale_history_action']  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-action')), array('task' => 'cache'));
        $this->_viewModel['sale_history_result']  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-result')), array('task' => 'cache'));
        $this->_viewModel['sale_source_group']    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-source-group')), array('task' => 'cache'));
        $this->_viewModel['sale_contact_type']    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contact-type')), array('task' => 'cache-alias'));
        $this->_viewModel['sale_lost']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-lost')), array('task' => 'cache'));
        $this->_viewModel['sale_contact_subject'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contact-subject')), array('task' => 'cache-alias'));
        $this->_viewModel['location_city']        = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache'));
        $this->_viewModel['location_district']    = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 2), array('task' => 'cache'));
        $this->_viewModel['product_interest']     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-interest')), array('task' => 'cache'));
        $this->_viewModel['sex']                  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sex')), array('task' => 'cache-alias'));
        $this->_viewModel['caption']              = 'Xem thông tin chi tiết khách hàng';

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function storeAction()
    {
        $myForm = new \Admin\Form\Contact\Store($this->getServiceLocator());
        $myForm->setInputFilter(new \Admin\Filter\Contact\Store());

        $item = $this->getTable()->getItem($this->_params['route']);
        $myForm->setData($item);
        $this->_params['item'] = $item;

        if ($this->getRequest()->isPost()) {
            $myForm->setData($this->_params['data']);
            $controlAction = $this->_params['data']['control-action'];
            if ($myForm->isValid()) {
                $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                $this->_params['item'] = $item;
                $result                = $this->getTable()->saveItem($this->_params, array('task' => 'register-store'));
                $this->flashMessenger()->addMessage('Bạn đã là người quản lý khách hàng kho: ' . $item['phone'] . ' - ' . $item['name']);
                $this->goRoute();
            }
        }

        $this->_viewModel['myForm']  = $myForm;
        $this->_viewModel['item']    = $item;
        $this->_viewModel['caption'] = 'Nhập lại kho';
        return new ViewModel($this->_viewModel);
    }

    public function searchAction()
    {
        $this->_viewModel['user']                = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_group']          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']         = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['sale_contact_type']   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contact-type')), array('task' => 'cache'));
        $this->_viewModel['sale_history_action'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-action')), array('task' => 'cache'));
        $this->_viewModel['sale_history_result'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-result')), array('task' => 'cache'));
        $this->_viewModel['location_city']       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'location-city')), array('task' => 'cache'));
        $this->_viewModel['caption']             = 'Tìm kiếm nhanh liên hệ';

        $ssFilter                    = new Container(__CLASS__);
        $this->_viewModel['keyword'] = $ssFilter->keyword;

        $viewModel = new ViewModel($this->_viewModel);

        if ($this->getRequest()->isXmlHttpRequest()) {
            $ssFilter->keyword                           = $this->_params['data']['keyword'];
            $this->_params['ssFilter']['filter_keyword'] = $ssFilter->keyword;

            $items = $this->getTable()->listItem($this->_params, array('task' => 'search'));
            $viewModel->setVariable('items', $items);
            $viewModel->setTerminal(true);
            $viewModel->setTemplate('admin/contact/search-contact.phtml');
        }

        return $viewModel;
    }

    public function historyAddAction()
    {
        $myForm = new \Admin\Form\Contact\History($this->getServiceLocator());

        if (!empty($this->_params['data']['id'])) {
            $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $this->_params['data']['id']));
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }

        if ($this->getRequest()->isPost()) {
            if ($this->_params['data']['modal'] == 'success') {
                if(!empty($this->_params['data']['history_type_id'])){
                    $history_type = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(array('id' => $this->_params['data']['history_type_id'],), null);
                    $this->_params['data']['history_type_alias'] = $history_type['alias'];
                }

                $myForm->setInputFilter(new \Admin\Filter\Contact\History($this->_params));
                $myForm->setData($this->_params['data']);

                if ($myForm->isValid()) {
                    $this->_params['data']     = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['item']     = $contact;
                    $this->_params['settings'] = $this->_settings;

                    // Thêm lịch sử chăm sóc
                    $this->getServiceLocator()->get('Admin\Model\HistoryTable')->saveItem($this->_params, array('task' => 'add-item'));

                    // Cập nhật lịch sử chăm sóc cuối cho liên hệ
                    $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($this->_params, array('task' => 'edit-item'));

                    $this->flashMessenger()->addMessage('Thêm lịch sử thành công');
                    echo 'success';
                    return $this->response;
                }
            } else {
                $contact_options = !empty($contact['options']) ? unserialize($contact['options']) : array();
                $contact         = array_merge($contact, $contact_options);

                unset($contact['history_action_id']);
                unset($contact['history_result_id']);
                unset($contact['history_type_id']);
                unset($contact['history_content']);
                unset($contact['history_return']);
                $myForm->setData($contact);
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }

        $this->_viewModel['myForm']   = $myForm;
        $this->_viewModel['contact']  = $contact;
        $this->_viewModel['settings'] = $this->_settings;
        $this->_viewModel['userInfo'] = $this->_userInfo->getUserInfo();
        $this->_viewModel['caption']  = 'Thêm lịch sử chăm sóc';

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function historyListAction()
    {
        $items   = $this->getServiceLocator()->get('Admin\Model\HistoryTable')->listItem(array('data' => array('contact_id' => $this->_params['data']['id'])), array('task' => 'list-ajax'));
        $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $this->_params['data']['id']));

        $this->_viewModel['items']               = $items;
        $this->_viewModel['user']                = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['sale_group']          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $this->_viewModel['sale_branch']         = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['sale_history_action'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-action')), array('task' => 'cache'));
        $this->_viewModel['sale_history_result'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-result')), array('task' => 'cache'));
        $this->_viewModel['sale_history_type']   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-type')), array('task' => 'cache'));
        $this->_viewModel['caption']             = 'Danh sách lịch sử chăm sóc của khách hàng: ' . $contact['name'] . ' - ' . $contact['phone'];

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function changeUserAction()
    {
        $myForm  = new \Admin\Form\Contact\ChangeUser($this->getServiceLocator(), $this->_userInfo->getUserInfo());
        $caption = 'Liên hệ - Chuyển quyền quản lý';

        if ($this->getRequest()->isPost()) {
            if (!empty($this->_params['data']['contact_ids'])) {
                $contact_ids = $this->_params['data']['contact_ids'];
                $myForm->setInputFilter(new \Admin\Filter\Contact\ChangeUser(array('data' => $this->_params['data'])));
                $myForm->setData($this->_params['data']);

                if ($myForm->isValid()) {
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['user'] = $this->getServiceLocator()->get('Admin\Model\UserTable')->getItem(array('id' => $this->_params['data']['user_id']));

                    $result = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($this->_params, array('task' => 'change-user'));

                    $this->flashMessenger()->addMessage('Chuyển quyền quản lý ' . $result . ' liên hệ thành công');
                    $this->goRoute();
                }
            } else {
                $contact_ids = @implode(',', $this->_params['data']['cid']);
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }

        $this->_viewModel['contact_ids'] = $contact_ids;
        $this->_viewModel['myForm']      = $myForm;
        $this->_viewModel['caption']     = $caption;
        return new ViewModel($this->_viewModel);
    }

    public function changePasswordAction()
    {
        $item = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $this->_params['data']['id']));
        if (empty($item)) {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }

        if ($this->getRequest()->isPost()) {
            $myForm = new \Admin\Form\Contact\ChangePassword($this->getServiceLocator());
            $myForm->setInputFilter(new \Admin\Filter\Contact\ChangePassword($this->_params));

            $myForm->setData($item);
            if ($this->_params['data']['modal'] == 'success') {
                $myForm->setData($this->_params['data']);
                if ($myForm->isValid()) {
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['item'] = $item;

                    $result = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($this->_params, array('task' => 'change-password'));

                    $this->flashMessenger()->addMessage('Đổi mật khẩu thành công');
                    echo 'success';
                    return $this->response;
                }
            }
        } else {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'not-found'));
        }

        $this->_viewModel['myForm']  = $myForm;
        $this->_viewModel['caption'] = 'Đổi mật khẩu';
        $this->_viewModel['item']    = $item;

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function importAction()
    {
        $dateFormat = new \ZendX\Functions\Date();
        $myForm = new \Admin\Form\Contact\Import($this->getServiceLocator(), $this->_params);
        $myForm->setInputFilter(new \Admin\Filter\Contact\Import($this->_params));

        $this->_viewModel['caption'] = 'Import liên hệ';
        $this->_viewModel['myForm']  = $myForm;
        $viewModel                   = new ViewModel($this->_viewModel);

        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($this->getRequest()->isPost()) {
                $check_date = $dateFormat->check_date_format_to_data($this->_params['data']['date']);
                if($check_date == true) {
                    if (empty($this->_params['data']['sales_code'])) {
                        echo 'Không có nhân viên Sales';
                    } else {
                        $user_mkt   = $this->getServiceLocator()->get('Admin\Model\UserTable')->getItem(array('code' => $this->_params['data']['marketer_code']), array('task' => 'by-code'));
                        $user_sales = $this->getServiceLocator()->get('Admin\Model\UserTable')->getItem(array('code' => $this->_params['data']['sales_code']), array('task' => 'by-code'));
                        // Check liên hệ
                        $item = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('phone' => $this->_params['data']['phone'], 'marketer_id' => $user_mkt['id'], 'user_id' => $user_sales['id']), array('task' => 'check-share-data'));
                        if (!empty($item)) {
                            echo 'Tồn tại';
                        }
                        else {
                            $this->_params['data']['marketer_id']      = !empty($user_mkt) ? $user_mkt['id'] : null;
                            $this->_params['data']['user_id']          = $user_sales['id'];
                            $this->_params['data']['sale_branch_id']   = $user_sales['sale_branch_id'];
                            $this->_params['data']['sale_group_id']    = $user_sales['sale_group_id'];

                            $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($this->_params, array('task' => 'import-insert'));
                            echo 'Hoàn thành';
                        }
                    }
                }
                else{
                    echo 'ngày không đúng định dạng';
                }
                return $this->response;
            }
        }
        else {
            if ($this->getRequest()->isPost()) {
                $myForm->setData($this->_params['data']);

                if ($myForm->isValid()) {
                    if (!empty($this->_params['data']['file_import']['tmp_name'])) {
                        $upload      = new \ZendX\File\Upload();
                        $file_import = $upload->uploadFile('file_import', PATH_FILES . '/import/', array());
                    }
                    $viewModel->setVariable('file_import', $file_import);
                    $viewModel->setVariable('import', true);

                    require_once PATH_VENDOR . '/Excel/PHPExcel/IOFactory.php';
                    $objPHPExcel = \PHPExcel_IOFactory::load(PATH_FILES . '/import/' . $file_import);

                    $sheetData = $objPHPExcel->getActiveSheet(1)->toArray(null, true, true, true);
                    $viewModel->setVariable('sheetData', $sheetData);
                }
            }
        }

        return $viewModel;
    }

    public function exportAction() {
        $dateFormat = new \ZendX\Functions\Date();

        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item', 'paginator' => false));

        $user                = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
        $sale_group          = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $sale_branch         = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $sale_contact_type   = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contact-type')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $sale_history_action = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-action')), array('task' => 'cache'));
        $sale_history_result = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-result')), array('task' => 'cache'));
        $sale_source_group   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-source-group')), array('task' => 'cache'));
        $sale_source_access  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-source-access')), array('task' => 'cache'));
        $location_city       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'location-city')), array('task' => 'cache'));
        $location_district   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'location-district')), array('task' => 'cache'));

        //Include PHPExcel
        require_once PATH_VENDOR . '/Excel/PHPExcel.php';

        // Config
        $config = array('sheetData' => 0, 'headRow' => 1, 'startRow' => 2, 'startColumn' => 0,);

        // Column
        $arrColumn = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ');

        // Data Export
        $arrData = array(
            array('field' => 'date', 'title' => 'Ngày tiếp nhận', 'type' => 'date', 'format' => 'd/m/Y H:i:s'),
            array('field' => 'phone', 'title' => 'Điện thoại', 'type' => 'phone'),
            array('field' => 'name', 'title' => 'Họ tên'),
            array('field' => 'type', 'title' => 'Phân loại', 'type' => 'data_source', 'data_source' => $sale_contact_type),
            array('field' => 'contract_number', 'title' => 'Số lần mua sản phẩm'),
            array('field' => 'note', 'title' => 'Tên xe - Năm sản xuất', 'type' => 'data_serialize', 'data_serialize_field' => 'options'),
            array('field' => 'content', 'title' => 'Nội dung tư vấn', 'type' => 'data_serialize', 'data_serialize_field' => 'options'),
            array('field' => 'history_success', 'title' => 'Số lần được chăm sóc'),
            array('field' => 'history_content', 'title' => 'Lịch sử chăm sóc cuối', 'type' => 'data_serialize', 'data_serialize_field' => 'options'),
//            array('field' => 'date_return', 'title' => 'Chăm sóc lại', 'type' => 'date', 'format' => 'd/m/Y'),
            array('field' => 'history_return', 'title' => 'Chăm sóc lại', 'type' => 'date', 'format' => 'd/m/Y'),
            array('field' => 'user_id', 'title' => 'Người quản lý', 'type' => 'data_source', 'data_source' => $user, 'data_source_field' => 'name'),
        );

        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator($this->_userInfo->getUserInfo('name'))->setLastModifiedBy($this->_userInfo->getUserInfo('username'))->setTitle("Export");

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
                        $value      = $dateFormat->formatToView($item[$data['field']], $formatDate);
                        break;
                    case 'data_source':
                        $field = $data['data_source_field'] ? $data['data_source_field'] : 'name';
                        $value = $data['data_source'][$item[$data['field']]][$field];
                        break;
                    case 'data_serialize':
                        $data_serialize = $item[$data['data_serialize_field']] ? unserialize($item[$data['data_serialize_field']]) : array();
                        $value          = $data_serialize[$data['field']];

                        if (!empty($data['data_source'])) {
                            $field = $data['data_source_field'] ? $data['data_source_field'] : 'name';
                            $value = $data['data_source'][$data_serialize[$data['field']]][$field];
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
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;

        return $this->response;
    }

    public function receiveAction() {
        $myForm = new \Admin\Form\Contact\Receive($this->getServiceLocator(), $this->_params);

        if($this->getRequest()->isPost()){
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\Contact\Receive($this->_params));
                $myForm->setData($this->_params['data']);

                if($myForm->isValid()){
                    $curent_user = $this->_userInfo->getUserInfo();
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $fdata['ssFilter']['filter_active'] = 'unactive';
                    $fdata['ssFilter']['filter_sale_branch'] = $curent_user['sale_branch_id'];
                    $fdata['ssFilter']['filter_cancel_share'] = 0;
                    $fdata['ssFilter']['order_by'] = 'date';
                    $fdata['ssFilter']['order'] = 'desc';
                    $fdata['paginator']['itemCountPerPage'] = (int)$this->_params['data']['number_data'];
                    $fdata['paginator']['currentPageNumber'] = 1;
                    $datas     = $this->getServiceLocator()->get('Admin\Model\FormDataTable')->ListItem($fdata, array('task' => 'list-item-contact'));
                    foreach ($datas as $item_data) {
                        $items[] = array(
                            'id' => $item_data['id']
                        );
                    }
                    $this->_params['data']['items'] = $items;
                    $this->_params['data']['user_id'] = [$curent_user['id']];
                    $result = $this->getServiceLocator()->get('Admin\Model\FormDataTable')->shareData($this->_params);

                    $this->flashMessenger()->addMessage('Nhận data thành công');
                    echo 'success';
                    return $this->response;
                }
            } else {
                $myForm->setData($this->_params['data']);
            }
        }

        $this->_viewModel['myForm']     = $myForm;
        $this->_viewModel['caption']    = 'Thêm tiền hỗ trợ ship';

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }
}
















