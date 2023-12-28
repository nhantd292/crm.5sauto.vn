<?php
namespace Notifycation\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Db\TableGateway\TableGateway;
use ZendX\System\UserInfo;

class NotifyController extends ActionController {
    
    public function init() {
        $this->_options['tableName'] = 'Notifycation\Model\NotifyTable';

        // Lấy dữ liệu post của form
        $this->_params['data'] = $this->getRequest()->getPost()->toArray();
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
    }
    
    public function indexAction() {
        $this->_params['data']['user_id'] = $this->_userInfo->getUserInfo('id');
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item-account'));

        $this->_viewModel['items']   = $items;
        $this->_viewModel['caption'] = 'Thông báo - Danh sách';
        return new ViewModel($this->_viewModel);
    }
    
    public function addAction() {
        $this->_params['data'] = $data;

        $this->getTable()->saveItem($this->_params, array('task' => 'add-item'));
        return $this->response;
    }
}




















