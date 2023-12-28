<?php

namespace Admin\Controller;

use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;

class IndexController extends ActionController {
    
    public function init() {
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\UserTable';
        $this->_options['formName'] = 'formAdminUser';
        
        // Lấy dữ liệu post của form
        $this->_params['data'] = $this->getRequest()->getPost()->toArray();
        
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
    }
    
    public function indexAction() {
      	$this->_viewModel['userInfo'] = $this->_userInfo;
      	$viewModel = new ViewModel($this->_viewModel);
      	return $viewModel;
    }
    
    public function backupAction() {
        $dir    = PATH_APPLICATION . '/_backup/database';
        $files  = scandir($dir, 1);
        
        $this->_viewModel['dir']      = URL_APPLICATION . '/_backup/database';
        $this->_viewModel['files']    = $files;
        $this->_viewModel['caption']  = 'Backup dữ liệu';
      	$this->_viewModel['userInfo'] = $this->_userInfo;
      	$viewModel = new ViewModel($this->_viewModel);
      	
      	return $viewModel;
    }
}
