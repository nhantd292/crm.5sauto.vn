<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;
use Zend\Db\TableGateway\TableGateway;

class DocumentController extends ActionController {
    
    public function init() {
        // Cấu hình dynamic
        $dynamic = $this->getServiceLocator()->get('Admin\Model\DynamicTable')->getItem(array('code' => $this->_params['route']['slug']), array('task' => 'code'));
        if(empty($dynamic['option'])) {
            die('Lỗi đường dẫn. Vui lòng liên hệ admin');
        } else {
            $dynamic_option = $dynamic['option'];
//            echo '<pre>';
//            print_r($dynamic_option);
//            echo '</pre>';
            eval("\$this->_params[\"configs\"] = $dynamic_option;");
        }
        
        $access = false;
        $permission = $this->_userInfo->getPermissionInfo();
        $permissionList = $this->_userInfo->getPermissionListInfo();
        $permission_ids = explode(',', $dynamic['permission_ids']);
        if($permissionList['privileges'] != 'full') {
            foreach ($permission AS $key => $val) {
                if(in_array($val['id'], $permission_ids)) {
                    $access = true;
                    break;
                }
            }
        } else {
            $access = true;
        }
        
        if($access == false) {
            return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'no-access'));
        }
        
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\DocumentTable';
        $this->_options['formName'] = 'formAdminDocument';
        
        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__);
        
        if($ssFilter->slug != $this->_params['route']['slug']) {
            $ssFilter->filter_status = '';
            $ssFilter->filter_keyword = '';
            $ssFilter->filter_document = '';
        }
        
        $this->_params['ssFilter']['order_by']          = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'name';
        $this->_params['ssFilter']['order']             = !empty($ssFilter->order) ? $ssFilter->order : 'ASC';
        $this->_params['ssFilter']['filter_status']     = $ssFilter->filter_status;
        $this->_params['ssFilter']['filter_keyword']    = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['filter_document']   = $ssFilter->filter_document;
        
        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage'] = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : 50;
        $this->_paginator['currentPageNumber'] = $this->params()->fromRoute('page', 1);
        $this->_params['paginator'] = $this->_paginator;
        
        // Lấy dữ liệu post của form
        $this->_params['data'] = $this->getRequest()->getPost()->toArray();
        
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
    }
    
    public function filterAction() {
        $configs = $this->_params['configs'];
        
        if($this->getRequest()->isPost()) {
            $ssFilter = new Container(__CLASS__);
            $data = $this->_params['data'];
    
            $ssFilter->pagination_option = intval($data['pagination_option']);
            $ssFilter->order_by         = $data['order_by'];
            $ssFilter->order            = $data['order'];
            $ssFilter->filter_status    = $data['filter_status'];
            $ssFilter->filter_keyword   = $data['filter_keyword'];
            $ssFilter->filter_document  = $data['filter_document'];
        }
    
        return $this->redirect()->toRoute('routeAdminDocument/default', array('slug' => $configs['code'], 'action' => 'index'));
    }
    
    public function indexAction() {
        $ssFilter = new Container(__CLASS__);
        $ssFilter->slug = $this->_params['route']['slug'];
        
        $configs = $this->_params['configs'];
        $adapter = $this->getServiceLocator()->get('dbConfig');

        $myForm	= new \Admin\Form\Search\Document($this->getServiceLocator(), $this->_params);
        $myForm->setData($this->_params['ssFilter']);
        
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        
        foreach ($configs['list']['fields'] AS $field) {
            if(!empty($field['data_source'])) {
		        $tableGateway = new TableGateway(TABLE_PREFIX . $field['data_source']['table'], $adapter, null);
		        $table        = new \Admin\Model\DocumentTable($tableGateway);
		        $service      = $table->setServiceLocator($this->getServiceLocator());
		        $task         = $field['data_source']['task'] ? $field['data_source']['task'] : 'cache';
		        $data_source  = $table->listItem($field['data_source'], array('task' => $task));
		        if(!empty($field['data_source']['view'])) {
		            $data_source = \ZendX\Functions\CreateArray::create($data_source, $field['data_source']['view']);
		        }
		        
		        $this->_viewModel['data_source'][$field['data_source']['table'] . $field['name']] = $data_source;
		    }
        }
        
        $this->_viewModel['myForm']	    = $myForm;
        $this->_viewModel['items']      = $items;
        $this->_viewModel['count']      = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));
        $this->_viewModel['userInfo']   = $this->_userInfo->getUserInfo();
        $this->_viewModel['caption']    = $configs['general']['title']['value'] . ' - Danh sách';
        return new ViewModel($this->_viewModel);
    }
    
    public function formAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $configs    = $this->_params['configs'];
        
        $myForm     = new \Admin\Form\Document($this->getServiceLocator(), $this->_params['configs']);
        $myForm->setInputFilter(new \Admin\Filter\Document(array('configs' => $this->_params['configs'])));
        
        $task = 'add-item';
        $caption = $configs['general']['title']['value'] . ' - Thêm mới';
        $item = array();
        if(!empty($this->params('id'))) {
            $this->_params['data']['id'] = $this->params('id');
            $item = $this->getTable()->getItem($this->_params['data']);
            $item['date_begin'] = !empty($item['date_begin']) ? $dateFormat->formatToView($item['date_begin']) : null;
            $item['date_end'] = !empty($item['date_end']) ? $dateFormat->formatToView($item['date_end']) : null;
            if(!empty($item)) {
                $myForm->setInputFilter(new \Admin\Filter\Document(array('id' => $this->_params['data']['id'], 'configs' => $this->_params['configs'])));
                $myForm->bind($item);
                $task = 'edit-item';
                $caption = $configs['general']['title']['value'] . ' - Sửa';
            }
        }
        
        if($this->_params['route']['code'] == 'copy') {
            $caption = $configs['general']['title']['value'] . ' - Copy';
            $task = 'add-item';
            unset($this->_params['data']['id']);
        }
    
        if($this->getRequest()->isPost()){
            $myForm->setData($this->_params['data']);
    
            $controlAction = $this->_params['data']['control-action'];
            
            if($myForm->isValid()){
                $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                $result = $this->getTable()->saveItem($this->_params, array('task' => $task));
    
                $this->flashMessenger()->addMessage('Dữ liệu đã được cập nhật thành công');
    
                if($controlAction == 'save-new') {
                    return $this->redirect()->toRoute('routeAdminDocument/default', array('slug' => $configs['code'], 'action' => 'form'));
                } else if($controlAction == 'save') {
                    return $this->redirect()->toRoute('routeAdminDocument/default', array('slug' => $configs['code'], 'action' => 'form', 'id' => $result));
                } else {
                    return $this->redirect()->toRoute('routeAdminDocument/default', array('slug' => $configs['code'], 'action' => 'index'));
                }
            }
        }
    
        $this->_viewModel['myForm']	    = $myForm;
        $this->_viewModel['item']       = $item;
        $this->_viewModel['userInfo']   = $this->_userInfo->getUserInfo();
        $this->_viewModel['caption']    = $caption;
        return new ViewModel($this->_viewModel);
    }
    
    public function statusAction() {
        $configs    = $this->_params['configs'];
        
        if($this->getRequest()->isXmlHttpRequest()) {
            $this->getTable()->changeStatus($this->_params, array('task' => 'change-status'));
        } else {
            return $this->redirect()->toRoute('routeAdminDocument/default', array('slug' => $configs['code'], 'action' => 'index'));
        }
    
        return $this->response;
    }
    
    public function deleteAction() {
        $configs    = $this->_params['configs'];
        
        if($this->getRequest()->isPost()) {
            if(!empty($this->_params['data']['cid'])) {
                $result = $this->getTable()->deleteItem($this->_params, array('task' => 'delete-item'));
                $message = 'Xóa '. $result .' phần tử thành công';
                $this->flashMessenger()->addMessage($message);
            }
        }
    
        return $this->redirect()->toRoute('routeAdminDocument/default', array('slug' => $configs['code'], 'action' => 'index'));
    }
    
    public function orderingAction() {
        $configs    = $this->_params['configs'];
        
        if($this->getRequest()->isPost()) {
            if(!empty($this->_params['data']['cid']) && !empty($this->_params['data']['ordering'])) {
                $result = $this->getTable()->changeOrdering($this->_params, array('task' => 'change-ordering'));
                $message = 'Sắp xếp '. $result .' phần tử thành công';
                $this->flashMessenger()->addMessage($message);
            }
        }
    
        return $this->redirect()->toRoute('routeAdminDocument/default', array('slug' => $configs['code'], 'action' => 'index'));
    }
}

/* array(
    "code"  => "history-action",
    "general" => array(
        "title" => array(
            "value" => "Lịch sử chăm sóc - Hành động",
        )
    ),
    "list" => array(
        "general" => array(
            "showIndex" => array(
                "value" => true,
            ),
            "showCheckbox" => array(
                "value" => true,
            ),
            "showControl" => array(
                "value" => true,
            ),
        ),
        "pagination" => array(
            "active" => array(
                "value" => true,
            ),
            "itemCountPerPage" => array(
                "value" => 50,
            ),
            "pageRange" => array(
                "value" => 5,
            ),
            "options" => array(
                "value" => array(10, 20, 50, 100, 200, 500, 1000),
            ),
        ),
        "fields" => array(
            array(
                "caption"       => "Tên",
                "name"          => "name",
            ),
            array(
                "caption"       => "Thứ tự",
                "name"          => "ordering",
                "type"          => "text",
                "attributes"    => array(
                    "class" => "col-80"
                )
            ),
        ),
    ),
    "form" => array(
        "general" => array(
        ),
        "fields" => array(
            array(
                "caption"       => "Tên",
                "name"          => "name",
                "type"          => "text",
                "attributes"    => array(
                    "class"			=> "form-control",
                    "id"			=> "name",
                    "placeholder"	=> "Nhập tên"
                ),
                "validators"      => array(
                    "require"       => 1
                )
            ),
            array(
                "caption"       => "Tỉnh thành",
                "name"          => "location_city_id",
                "type"          => "select",
                "attributes"	=> array(
                    "class"		=> "form-control select2 select2_basic",
                ),
                "options"		=> array(
                    "empty_option"	=> "- Chọn -",
                    "value_options"	=> array(),
                    "data_source" => array(
                        "table" => "location_city",
                        "where" => array(
                            "location_country_id" => "1471336155-76f0-224d-ke95-l103t2b7z988"
                        ),
                        "order" => array("ordering" => "ASC", "name" => "ASC"),
                        "view"  => array(
                            "key" => "id",
                            "value" => "name,ordering",
                            "sprintf" => "%s - %s"
                        )
                    )
                ),
                "validators"      => array(
                    "require"       => 1
                )
            ),
            array(
                "caption"       => "Thứ tự",
                "name"			=> "ordering",
    		    "type"			=> "text",
    		    "attributes"	=> array(
    		        "value"         => 255,
    		        "class"			=> "form-control",
    		        "id"			=> "ordering",
    		        "placeholder"	=> "Thứ tự"
    		    )
            ),
            array(
                "caption"       => "Trạng thái",
                "name"			=> "status",
    			"type"			=> "select",
    			"attributes"	=> array(
    				"class"		=> "form-control select2 select2_basic",
    			),
    		    "options"		=> array(
    		        "value_options"	=> array( 1	=> "Hiển thị", 0 => "Không hiển thị"),
    		    )
            )
        ),
    )
) */