<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;

class NoticeController extends ActionController {
    
    public function init() {
        // Lấy dữ liệu post của form
        $this->_params['data'] = array_merge($this->getRequest()->getPost()->toArray(), $this->getRequest()->getFiles()->toArray());
        
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
    }
    
    public function noAccessAction() {
        if($this->_params['route']['type'] == 'modal') {
            $this->_viewModel['caption'] = 'Thông báo từ hệ thống';
            $viewModel = new ViewModel($this->_viewModel);
            $viewModel->setTerminal(true);
        
            return $viewModel;
        } else {
            if($this->getRequest()->isXmlHttpRequest()) {
                echo 'no-access';
                return $this->response;
            }
        }
    }
    
    public function notFoundAction() {
        if($this->_params['route']['type'] == 'modal') {
            $this->_viewModel['caption'] = 'Thông báo từ hệ thống';
            $viewModel = new ViewModel($this->_viewModel);
            $viewModel->setTerminal(true);
            
            return $viewModel;
        } else {
            if($this->getRequest()->isXmlHttpRequest()) {
                echo 'not-found';
                return $this->response;
            }
        }
    }

    public function lockAction() {
        if($this->_params['route']['type'] == 'modal') {
            $this->_viewModel['caption'] = 'Thông báo từ hệ thống';
            $viewModel = new ViewModel($this->_viewModel);
            $viewModel->setTerminal(true);

            return $viewModel;
        } else {
            if($this->getRequest()->isXmlHttpRequest()) {
                echo 'not-found';
                return $this->response;
            }
        }
    }
    
    public function createdByAction() {
        if($this->_params['route']['type'] == 'modal') {
            $this->_viewModel['caption'] = 'Thông báo từ hệ thống';
            $viewModel = new ViewModel($this->_viewModel);
            $viewModel->setTerminal(true);
            
            return $viewModel;
        } else {
            if($this->getRequest()->isXmlHttpRequest()) {
                echo 'created-by';
                return $this->response;
            }
        }
    }
}
