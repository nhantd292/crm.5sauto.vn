<?php

namespace ZendX\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\PluginManager;
use Zend\Mvc\MvcEvent;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

class ApiController extends AbstractActionController {
    protected $_settings;
    protected $_userInfo;
    protected $_urlController;
    protected $_viewModel;
    protected $_params;
    protected $_table;
    protected $_form;
    protected $_options = array(
        'tableName', 'formName'
    );
    protected $_paginator = array(
        'itemCountPerPage'	=> 20,
        'pageRange'			=> 5,
        'options'           => array(10, 20, 50, 100, 200, 500, 1000)
    );
    
    public function setPluginManager(PluginManager $plugins) {
        $this->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, array($this, 'onInit'), 100);
        $this->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, array($this, 'onDispath'));
        parent::setPluginManager($plugins);
    }
    
    public function onInit(MvcEvent $e) {
        // Lấy thông tin setting
        $this->_settings = $this->getServiceLocator()->get('Admin\Model\SettingTable')->listItem(array('code' => 'General'), array('task' => 'cache-by-code'));
        
        // Thông tin tài khoản đăng nhập
        $this->_userInfo = new \ZendX\System\UserInfo();
        
        // Lấy thông tin config
        $config = $this->getServiceLocator()->get('config');
        
        // Get Module - Controller - Action
        $routeMatch = $e->getRouteMatch();
        $controllerArray = explode('\\', $routeMatch->getParam('controller'));
        
        // Truyền một phần tử ra ngoài view
        $this->_params['module']        = strtolower(preg_replace('/\B([A-Z])/', '-$1', $controllerArray[0]));
        $this->_params['controller']    = strtolower(preg_replace('/\B([A-Z])/', '-$1', $controllerArray[2]));
        $this->_params['action']        = $routeMatch->getParam('action');
        
        // Lấy thông tin route
        $route = $this->params()->fromRoute();
        $route['routeName'] = $routeMatch->getMatchedRouteName();
        $this->_params['route'] = $route;
        
        // Thiết lập link controller
        $this->_urlController = $this->_params['module'] . '/' . $this->_params['controller'];
        
        // Thiết lập layout cho Controller
        $this->layout($config['module_layouts'][$controllerArray[0]]);
        
        // Thiết lập các tham số của template
        $template = explode('/',  $config['module_layouts'][$controllerArray[0]]);
        $this->_params['template'] = array(
            'pathTheme'             => PATH_TEMPLATE . '/'. $template[1],
            'pathThemeTemplate'     => PATH_TEMPLATE . '/'. $template[1] .'/template',
            'pathImg'               => PATH_TEMPLATE .'/'. $template[1] .'/img',
            'pathCss' 	            => PATH_TEMPLATE .'/'. $template[1] .'/css',
            'pathJs' 	            => PATH_TEMPLATE .'/'. $template[1] .'/js',
            'pathPlugin'	        => PATH_TEMPLATE .'/'. $template[1] .'/plugins',
            'pathHtml'	            => PATH_TEMPLATE .'/'. $template[1] .'/html',
        	'urlTheme'              => URL_TEMPLATE .'/'. $template[1],
        	'urlThemeTemplate'      => URL_TEMPLATE .'/'. $template[1] .'/template',
        	'urlImg'                => URL_TEMPLATE .'/'. $template[1] .'/img',
        	'urlCss' 	            => URL_TEMPLATE .'/'. $template[1] .'/css',
        	'urlJs' 	            => URL_TEMPLATE .'/'. $template[1] .'/js',
        	'urlPlugin'	            => URL_TEMPLATE .'/'. $template[1] .'/plugins',
        	'urlHtml'	            => URL_TEMPLATE .'/'. $template[1] .'/html',
        );
        
        // Khai báo Adapter mặc định cho từng module
        $zendConfig = $this->getServiceLocator()->get('Config');
        $module_adapter = $zendConfig['module_adapter'][$controllerArray[0]] ? $zendConfig['module_adapter'][$controllerArray[0]] : 'dbConfig';
        GlobalAdapterFeature::setStaticAdapter($this->getServiceLocator()->get($module_adapter));
        
        // Kiểm tra những tên miền sau thì được phép post
        switch ($_SERVER['HTTP_ORIGIN']) {
        	case 'http://web.langmaster':
        	case 'http://langmaster.lmc:81':
        	case 'http://langmaster.edu.vn':
        		header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
        		header('Access-Control-Allow-Methods: GET, POST');
        		header('Access-Control-Max-Age: 1000');
        		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        		break;
        }
        
        // Gọi đến function chạy đầu tiên
        $this->init();
    }
    
    public function onDispath(MvcEvent $e) {
        // Truyền tất cả params ra ngoài layout
        $viewModel = $e->getApplication()->getMvcEvent()->getViewModel();
        $viewModel->arrParams = $this->_params;
    }
    
    public function init() {
    	
    }
    
    public function getTable() {
        if(empty($this->_table)) {
            $this->_table = $this->getServiceLocator()->get($this->_options['tableName']);
        }
    
        return $this->_table;
    }
    
    public function getForm() {
        if(empty($this->_form)) {
            $this->_form = $this->getServiceLocator()->get('FormElementManager')->get($this->_options['formName']);
        }
    
        return $this->_form;
    }
    
    public function setLayout($layout) {
        $this->_params['layout'] = $layout;
    }
}