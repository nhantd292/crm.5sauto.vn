<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class CapitalDefaultController extends ActionController {
    
    public function init() {
        
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\ProductListedTable';
        $this->_options['formName'] = 'formAdminProductListed';
        
        // Thiết lập session filter
        $ssFilter = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']              = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'date';
        $this->_params['ssFilter']['order']                 = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';
        $this->_params['ssFilter']['filter_product']        = $ssFilter->filter_product;
        $this->_params['ssFilter']['filter_carpet_color']   = $ssFilter->filter_carpet_color;
        $this->_params['ssFilter']['filter_tangled_color']  = $ssFilter->filter_tangled_color;
        
        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage'] = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : $this->_paginator['itemCountPerPage'];
        $this->_paginator['currentPageNumber'] = $this->params()->fromRoute('page', 1);
        $this->_params['paginator'] = $this->_paginator;
        
        // Lấy dữ liệu post của form
        $this->_params['data'] = array_merge($this->getRequest()->getPost()->toArray(), $this->getRequest()->getFiles()->toArray());
        
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
            $ssFilter->filter_product           = $data['filter_product'];
            $ssFilter->filter_carpet_color      = $data['filter_carpet_color'];
            $ssFilter->filter_tangled_color     = $data['filter_tangled_color'];
        }
    
        $this->goRoute();
    }
    
    // Danh sách
    public function indexAction() {
        $myForm	= new \Admin\Form\Search\ProductListed($this->getServiceLocator(), $this->_params['ssFilter']);
        $myForm->setData($this->_params['ssFilter']);
        $this->_params['ssFilter']['filter_type'] = 'default';
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        $count = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));;
        
        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $items;
        $this->_viewModel['count']                  = $count;
        $this->_viewModel['carpet_color']           = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\ColorGroupTable')->listItem(array('type' => CARPET_COLOR), array('task' => 'by-type')), array('key' => 'id', 'value' => 'object'));
        $this->_viewModel['tangled_color']          = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\ColorGroupTable')->listItem(array('type' => TANGLED_COLOR), array('task' => 'by-type')), array('key' => 'id', 'value' => 'object'));
        $this->_viewModel['flooring']               = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'flooring')), array('task' => 'cache'));
        $this->_viewModel['product']                = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
        $this->_viewModel['caption']                = 'Giá vốn mặc định - Danh sách';
        
        return new ViewModel($this->_viewModel);
    }
    
    // Thêm mới
    public function addAction() {
        $myForm = $this->getForm();
        
        if($this->getRequest()->isPost()) {
            $myForm->setInputFilter(new \Admin\Filter\ProductListed(array('data' => $this->_params['data'], 'route' => $this->_params['route'])));
            
            $myForm->setData($this->_params['data']);
            $controlAction = $this->_params['data']['control-action'];
            $this->_params['data']['type'] = 'default';
            $ProductListed = $this->getTable()->getItem($this->_params, array('task' => 'by-ajax'));
            $errors = '';
            if (!empty($ProductListed)) {
                $errors = 'Dữ liệu đã tồn tại! Vui lòng kiểm tra và nhập lại.';
            } else {
                if($myForm->isValid()){
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['data']['type'] = 'default';
                    $result = $this->getTable()->saveItem($this->_params, array('task' => 'add-item'));
                    $this->flashMessenger()->addMessage('Dữ liệu đã được cập nhật thành công');
                    if($controlAction == 'save-new') {
                        $this->goRoute(array('action' => 'add'));
                    } else if($controlAction == 'save') {
                        $this->goRoute(array('action' => 'edit', 'id' => $result));
                    } else {
                        $this->goRoute();
                    }
                }
            }
        }

        $this->_viewModel['product_type']  = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-type')), array('task' => 'cache'));
        $this->_viewModel['myForm']	    = $myForm;
        $this->_viewModel['errors']	    = $errors;
        $this->_viewModel['caption']    = 'Giá vốn mặc định - Thêm mới';
        return new ViewModel($this->_viewModel);
    }
    
    // Sửa
    public function editAction() {
        $dateFormat = new \ZendX\Functions\Date();
        $numberFormat = new \ZendX\Functions\Number();
        $myForm = new \Admin\Form\ProductListed\Edit($this->getServiceLocator(), $this->_params);
        
        if(!empty($this->_params['data']['id'])) {
            $ProductListed = $this->getServiceLocator()->get('Admin\Model\ProductListedTable')->getItem(array('id' => $this->_params['data']['id']));         
            $myForm->setData($ProductListed);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found', 'type' => 'modal'));
        }
    
        if($this->getRequest()->isPost()){
            if($this->_params['data']['modal'] == 'success') {
                $myForm->setInputFilter(new \Admin\Filter\ProductListed\Edit($this->_params));
                $myForm->setData($this->_params['data']);
    
                if($myForm->isValid()){
                    $this->_params['data'] = $myForm->getData(FormInterface::VALUES_AS_ARRAY);
                    $this->_params['item'] = $ProductListed;
                    $result = $this->getServiceLocator()->get('Admin\Model\ProductListedTable')->saveItem($this->_params, array('task' => 'edit-item'));
    
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
    
        $this->_viewModel['myForm']         = $myForm;
        $this->_viewModel['product_type']   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-type')), array('task' => 'cache'));
        $this->_viewModel['caption']        = 'Sửa giá vốn mặc định';
    
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
    
        return $viewModel;
    }

    // Xóa
    public function deleteAction() {
        if($this->getRequest()->isPost()) {
            $contract_delete = $this->getTable()->deleteItem($this->_params, array('task' => 'delete-item'));
            $this->flashMessenger()->addMessage('Xóa thành công');
            $this->goRoute();
        }
    }

    //
    public function importAction()
    {
        $myForm = new \Admin\Form\ProductListed\Import($this->getServiceLocator(), $this->_params);
        $myForm->setInputFilter(new \Admin\Filter\ProductListed\Import($this->_params));
        $this->_viewModel['caption'] = 'Import giá vốn mặc định';
        $this->_viewModel['myForm']  = $myForm;
        $viewModel                   = new ViewModel($this->_viewModel);

        if ($this->getRequest()->isXmlHttpRequest()) {          
            if ($this->getRequest()->isPost()) {
                $itemProduct        = $this->getServiceLocator()->get('Admin\Model\ProductTable')->getItem(array('name' => $this->_params['data']['product']), array('task' => 'by-name'));
                $itemCarpetColor    = $this->getServiceLocator()->get('Admin\Model\ColorGroupTable')->getItem(array('name' => $this->_params['data']['carpet_color'], 'type' => CARPET_COLOR), array('task' => 'by-name'));
                $itemTangledColor   = $this->getServiceLocator()->get('Admin\Model\ColorGroupTable')->getItem(array('name' => $this->_params['data']['tangled_color'], 'type' => TANGLED_COLOR), array('task' => 'by-name'));
                $itemFlooring       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(array('name' => $this->_params['data']['flooring'], 'code' => 'flooring'), array('task' => 'by-name'));

                $productListed      = $this->getServiceLocator()->get('Admin\Model\ProductListedTable')->getItem(array(
                                                                                                    'data' => array (
                                                                                                        'product_id' => $itemProduct['id'],
                                                                                                        'group_carpet_color_id' => $itemCarpetColor['id'],
                                                                                                        'group_tangled_color_id' => $itemTangledColor['id'],
                                                                                                        'flooring_id' => $itemFlooring['id'],
                                                                                                        'type' => 'default',
                                                                                                    )
                                             
                                                                                                ), array('task' => 'by-ajax'));
                if (!empty($productListed)) {
                    echo 'Đã tồn tại';
                } else {
                    $data['data'] = array(
                        'product_id'                => $itemProduct['id'],
                        'group_carpet_color_id'     => $itemCarpetColor['id'],
                        'group_tangled_color_id'    => $itemTangledColor['id'],
                        'flooring_id'               => $itemFlooring['id'],
                        'price'                     => $this->_params['data']['price'],
                    );
                    
                    $contact = $this->getServiceLocator()->get('Admin\Model\ProductListedTable')->saveItem($data, array('task' => 'import-insert-capital-default'));
                    echo 'Hoàn thành';
                }

                return $this->response;
            }
        } else {
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
}