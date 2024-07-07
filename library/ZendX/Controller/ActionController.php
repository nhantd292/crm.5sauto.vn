<?php

namespace ZendX\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\PluginManager;
use Zend\Mvc\MvcEvent;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;
use Zend\Http\Cookies;

class ActionController extends AbstractActionController {
    protected $_settings;
    protected $_userInfo;
    protected $_urlController;
    protected $_viewModel;
    protected $_params;
    protected $_table;
    protected $_form;
    protected $kiotviet_token;
    protected $viettelPost_token;
    protected $_options = array(
        'tableName', 'formName'
    );
    protected $_paginator = array(
        'itemCountPerPage'	=> 50,
        'pageRange'			=> 5,
        'options'           => array(10, 20, 50, 100, 200, 500, 1000, 2000, 5000)
    );
    protected $_ghtk_status = [3,4,5,6,9,10,11,13,20,21,123,45,49,410];
    
    public function setPluginManager(PluginManager $plugins) {
        $this->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, array($this, 'onInit'), 100);
        $this->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, array($this, 'onDispath'));
        parent::setPluginManager($plugins);
    }
    
    public function onInit(MvcEvent $e) {
        // Set token kiotviet.
//        unset($_COOKIE['kiotviet_token']);
        if(isset($_COOKIE['kiotviet_token'])){
            $this->kiotviet_token = $_COOKIE['kiotviet_token'];
        } else {
            $token = $this->get_kiotviet_token(CLIENT_ID, CLIENT_SECRET);
            setcookie('kiotviet_token', $token,time()+43200);
            $_COOKIE['kiotviet_token'] = $token;
            $this->kiotviet_token = $token;
        }
//        echo $this->kiotviet_token;

//        if(isset($_COOKIE['viettelPost_token'])){
//            $this->viettelPost_token = $_COOKIE['viettelPost_token'];
//        }

        // Lấy thông tin setting
        $this->_settings = $this->getServiceLocator()->get('Admin\Model\SettingTable')->listItem(array('code' => 'General'), array('task' => 'cache-by-code'));
        $this->_params['settings'] = $this->_settings;
        
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
        
        // Kiểm tra quyền đăng nhập
        if($this->_params['action'] != 'login' && $this->_params['action'] != 'logout' && $this->_params['controller'] != 'notice' && $this->_params['controller'] != 'api' && $this->_params['module'] != 'api') {
            $loggedStatus = $this->identity() ? true : false;

            // Check user có bị thay đổi quyền không.
            $curent_user = $this->getServiceLocator()->get('Admin\Model\UserTable')->getItem(array('id' => $this->_userInfo->getUserInfo('id')));
            if($curent_user['flag'] != 0 || $curent_user['status'] == 0){
                return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'user', 'action' => 'logout'));
            }
            
            if($loggedStatus == false) {
                return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'user', 'action' => 'login'));
            } else {
                $userInfo = new \ZendX\System\UserInfo();
                $permission_list = $userInfo->getPermissionListInfo();
                
                if(empty($permission_list['privileges'])) {
                    return $this->redirect()->toRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'no-access'));
                } else {
                    if($permission_list['privileges'] != 'full') {
                        $aclObj = new \ZendX\System\Acl($permission_list['role'], $permission_list['privileges']);
                        
                        if($aclObj->isAllowed($this->_params) == false) {
                            $urlNoAccess = $this->url()->fromRoute('routeAdmin/default', array('controller' => 'notice', 'action' => 'no-access'));
                            $response = $this->getResponse();
                            $response->getHeaders()->addHeaderLine('Location', $urlNoAccess);
                            $response->setStatusCode(302);
                            $response->sendHeaders();
                        
                            $this->getEvent()->stopPropagation();
                            return $response;
                        }
                    }
                }
            }
        }
        
        // Khai báo Adapter mặc định cho từng module
        $zendConfig = $this->getServiceLocator()->get('Config');
        $module_adapter = $zendConfig['module_adapter'][$controllerArray[0]] ? $zendConfig['module_adapter'][$controllerArray[0]] : 'dbConfig';
        GlobalAdapterFeature::setStaticAdapter($this->getServiceLocator()->get($module_adapter));
        
        // Gọi đến function chạy đầu tiên
        $this->init();
    }

//    public function updateToken($codeSecret){
//        setcookie('viettelPost_token', '', time() - 1000);
//        $result_token = json_decode($this->viettelpost("/user/LoginVTP", ["token"=>$codeSecret],"POST"), true);
//        $token = $result_token['data']['token'];
//        setcookie('viettelPost_token', $token,time()+86400*30);
//        return $_COOKIE['viettelPost_token'];
//    }
    
    public function onDispath(MvcEvent $e) {
        // Truyền tất cả params ra ngoài layout
        $viewModel = $e->getApplication()->getMvcEvent()->getViewModel();
        $viewModel->arrParams = $this->_params;
        $viewModel->settings = $this->_settings;
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
    
    public function goRoute($actionInfo = null) {
        $actionInfo['controller'] = !empty($actionInfo['controller']) ? $actionInfo['controller'] : $this->_params['controller'];
        $actionInfo['action'] = !empty($actionInfo['action']) ? $actionInfo['action'] : 'index';
        $actionInfo['route'] = !empty($actionInfo['route']) ? $actionInfo['route'] : $this->_params['route']['routeName'];
        
        $paramRoute = array('controller' => $actionInfo['controller'], 'action' => $actionInfo['action']);
        if(!empty($actionInfo['id'])) {
            $paramRoute['id'] = $actionInfo['id'];
        }
        return $this->redirect()->toRoute($actionInfo['route'], $paramRoute);
    }
    
    public function goUrl($url = null) {
        return $this->redirect()->toUrl($url);
    }
    
    public function getInfoSystem() {
        $manager        = $this->getServiceLocator()->get('ModuleManager');
        $modules        = $manager->getLoadedModules();
        $loadedModules          = array_keys($modules);
        $skipModulesList        = array('api');
        $skipActionsList        = array('notFoundAction', 'getMethodFromAction');
        $skipControllersList    = array('notice', 'nested', 'api');
        
        $arrAction = array();
        
        $wordFirst = new \ZendX\Functions\WordFirst();
        foreach ($loadedModules as $loadedModule) {
            $moduleClass = '\\' .$loadedModule . '\Module';
            $moduleObject = new $moduleClass;
            $config = $moduleObject->getConfig();
        
            $controllers = $config['controllers']['invokables'];
            foreach ($controllers as $key => $moduleClass) {
                $tmpArray = get_class_methods($moduleClass);
                $controllerActions = array();
                if(!empty($tmpArray)) {
                    foreach ($tmpArray as $action) {
                        if (substr($action, strlen($action)-6) === 'Action' && !in_array($action, $skipActionsList)) {
                            $controllerActions[] = substr($wordFirst->lowerFirstUpper($action), 0, -7);
                        }
                    }
                }
        
                $module     = $wordFirst->lowerFirstUpper($loadedModule);
                $controller = explode('\\Controller\\', $moduleClass);
                $controller = $wordFirst->lowerFirstUpper(substr($controller[1], 0, -10));
                $action     = $controllerActions;
        
                if (!in_array($module, $skipModulesList) && !in_array($controller, $skipControllersList)) {
                    $arrAction[$module][$controller] = $action;
                }
            }
    	}
        
        return $arrAction;
    }
    
    public function statusAction() {
        if($this->getRequest()->isXmlHttpRequest()) {
            $this->getTable()->changeStatus($this->_params, array('task' => 'change-status'));
        } else {
            $this->goRoute();
        }
    
        return $this->response;
    }
    
    public function deleteAction() {
        if($this->getRequest()->isPost()) {
            if(!empty($this->_params['data']['cid'])) {
                $result = $this->getTable()->deleteItem($this->_params, array('task' => 'delete-item'));
                $message = 'Xóa '. $result .' phần tử thành công';
                $this->flashMessenger()->addMessage($message);
            }
        }
    
        $this->goRoute();
    }
    
    public function orderingAction() {
        if($this->getRequest()->isPost()) {
            if(!empty($this->_params['data']['cid']) && !empty($this->_params['data']['ordering'])) {
                $result = $this->getTable()->changeOrdering($this->_params, array('task' => 'change-ordering'));
                $message = 'Sắp xếp '. $result .' phần tử thành công';
                $this->flashMessenger()->addMessage($message);
            }
        }
    
        $this->goRoute();
    }

    public function get_kiotviet_token($client_id, $secret_key)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://id.kiotviet.vn/connect/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "scopes=PublicApi.Access&grant_type=client_credentials&client_id=" . $client_id . "&client_secret=" . $secret_key,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return false;
        } else {
            $response = json_decode($response, true);
            if (isset($response['access_token'])) {
                return $response['token_type'] . " " . $response['access_token'];
            }
            return false;
        }
    }

    public function kiotviet_call($retailer, $token, $api_endpoint, $query = array(), $method = 'GET')
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://public.kiotapi.com" . $api_endpoint);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
            if (is_array($query)) {
                $query = json_encode($query);
            }
            curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        }
        $header = array(
            "Authorization: " .$this->kiotviet_token,
            "Cache-control: no-cache",
            "Content-type: application/json",
            "Retailer: " . $retailer
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return false;
        } else {
            return $response;
        }
    }

    public function ghtk_call($api_endpoint, $query = array(), $method = 'GET', $key)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, URLGHTK . $api_endpoint);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
            if (is_array($query)) {
                $query = json_encode($query);
            }
            curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        }
        $header = array(
//            "Token: " .GHTK,
            "Token: " .$key,
            "Cache-control: no-cache",
            "Content-type: application/json",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return false;
        } else {
            return $response;
        }
    }

    public function viettelpost($api_endpoint, $query = array(), $method = 'GET', $token = '')
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://partner.viettelpost.vn/v2" . $api_endpoint);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
            if (is_array($query)) {
                $query = json_encode($query);
            }
            curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        }
        $header = array(
            "Token: " .$token,
            "Cache-control: no-cache",
            "Content-type: application/json",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return false;
        } else {
            return $response;
        }
    }

    public function ghn_call($api_endpoint, $query = array(), $method = 'GET', $token, $shop_id = '')
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, URL_GHN . $api_endpoint);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
            if (is_array($query)) {
                $query = json_encode($query);
            }
            curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        }
        $header = array(
            "Content-type: application/json",
            "Cache-control: no-cache",
            "Token: " .$token,
            "ShopId: " .$shop_id,
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return $err;
        } else {
            return $response;
        }
    }

    // ZALO
    public function zalo_update_token($query = array(), $method = 'POST')
    {
        $secret_key = $this->getServiceLocator()->get('Admin\Model\SettingTable')->getItem(array('code' => 'General.zalo.secret_key'), array('task' => 'code'));

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://oauth.zaloapp.com/v4/oa/access_token");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
            if (is_array($query)) {
                $query = http_build_query($query);
            }
            curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        }
        $header = array(
            "Content-type: application/x-www-form-urlencoded",
            "Secret_key: " .$secret_key['value'],
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return $err;
        } else {
            return $response;
        }
    }

    public function zalo_call($api_endpoint, $query = array(), $method = 'GET')
    {
        $access_token = $this->getServiceLocator()->get('Admin\Model\SettingTable')->getItem(array('code' => 'General.zalo.access_token'), array('task' => 'code'));
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://business.openapi.zalo.me" . $api_endpoint);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
            if (is_array($query)) {
                $query = json_encode($query);
            }
            curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        }
        $header = array(
            "Content-type: application/json",
            "Cache-control: no-cache",
            "Access_token: " .$access_token['value'],
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return $err;
        } else {
            return $response;
        }
    }

    public function zalo_send_notify($code_template, $phone, $data){
        $template = $this->getServiceLocator()->get('Admin\Model\ZaloNotifyConfigTable')->getItem(array('code' => $code_template), array('task' => 'code'));
        $branchs = explode(',', $template['sale_branch_ids']);
        if(in_array($data['sale_branch_id'], $branchs) and $template['status'] == 1){
            $dateFormat = new \ZendX\Functions\Date();
            $unit_arr = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'transport-unit')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name'));
            if($data['name']){
                $template_data['name'] = $data['name'];
                $template_data['customer_name'] = $data['name'];
            }
            if($data['code']){
                $template_data['order_code'] = $data['code'];
            }
            if($data['phone']){
                $template_data['phone_number'] = $data['phone'];
            }
            if($data['date']){
                $template_data['date'] = $dateFormat->formatToView($data['date']);
                $template_data['order_date'] = $dateFormat->formatToView($data['date']);
            }
            $template_data['status'] = $template['note'];
            if($data['price_total']){
                $template_data['price'] = $data['price_total'];
            }
            if($data['unit_transport']){
                $template_data['unit_transport'] = $unit_arr[$data['unit_transport']];
            }
            if($code_template == ZALO_NOTIFY_CONFIG_XUATKHO || $code_template == ZALO_NOTIFY_CONFIG_DATHANG){
                $location_city          = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache'));
                $location_district      = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 2), array('task' => 'cache'));
                $location_town          = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 3), array('task' => 'cache'));

                $address            = $data['address'];
                $location_city      = !empty($data['location_city_id']) ?  ', '.$location_city[$data['location_city_id']]['name'] : '';
                $location_district  = !empty($data['location_district_id']) ? ', '.$location_district[$data['location_district_id']]['name'] : '';
                $location_town      = !empty($data['location_town_id']) ?  ', '.$location_town[$data['location_town_id']]['name'] : '';

                $address_full = $address .$location_town.$location_district.$location_city;
                $template_data['address'] = mb_strlen ($address_full) > 80 ? mb_substr($address_full, 0, 76, "UTF-8").'...' : $address_full;
                
                $options  = unserialize($data['options']);
                $products = [];
                foreach($options['product'] as $item_product) {
                    $products[] = $item_product['full_name'];
                }
                $product_name = implode(', ', $products);
                $template_data['products'] = mb_strlen ($product_name) > 100 ? mb_substr($product_name, 0, 96, "UTF-8").'...' : $product_name;
            }

            $data_send['phone']         = $phone;
            $data_send['template_id']   = $template['template_id'];
            $data_send['template_data'] = $template_data;
            
            $res = $this->zalo_call('/message/template', $data_send, 'POST');
            // Tạo kết quả thông báo
            $this->getServiceLocator()->get('Admin\Model\ZaloNotifyResultTable')->saveItem(array('data' => $data_send, 'res' => json_decode($res, true)), array('task' => 'add-auto'));

//            echo "<pre>";
//            print_r($res);
//            echo "</pre>";
//            exit;

            return $res;
        }
    }

    public function check_send_zalo_notify($arrParam, $contract_item){
        if($arrParam['unit_transport'] != $contract_item['unit_transport'] and !empty($arrParam['unit_transport'])){
            $contract_item['unit_transport'] = $arrParam['unit_transport'];
        }
        $numberFormat = new \ZendX\Functions\Number();
        $template_xuat_kho = $this->getServiceLocator()->get('Admin\Model\ZaloNotifyConfigTable')->getItem(array('code' => ZALO_NOTIFY_CONFIG_XUATKHO, 'status' => 1, 'sale_branch_id' => $contract_item['sale_branch_id']), array('task' => 'code'));
        if(!empty($template_xuat_kho)){
            $xuat_kho_order_status = explode(',', str_replace(' ', '', $template_xuat_kho['order_status']));
            // trạng thái trước của đơn hàng k thuộc trạng thái xuất kho trạng thái sau khi cập nhật thuộc trạng thái xuất kho thì thông báo xuất kho
            if(!in_array($contract_item['ghtk_status'], $xuat_kho_order_status) && in_array($arrParam['ghtk_status'], $xuat_kho_order_status)){
                $this->zalo_send_notify(ZALO_NOTIFY_CONFIG_XUATKHO, $numberFormat->convertToInternational($contract_item['phone']), $contract_item);
            }
        }

        $template_giao_hang = $this->getServiceLocator()->get('Admin\Model\ZaloNotifyConfigTable')->getItem(array('code' => ZALO_NOTIFY_CONFIG_GIAOHANG, 'status' => 1, 'sale_branch_id' => $contract_item['sale_branch_id']), array('task' => 'code'));
        if(!empty($template_giao_hang)){
            $giao_hang_order_status = explode(',', str_replace(' ', '', $template_giao_hang['order_status']));
            // trạng thái trước của đơn hàng k thuộc trạng thái giao hàng trạng thái sau khi cập nhật thuộc trạng thái giao hàng thì thông báo giao hàng
            if(!in_array($contract_item['ghtk_status'], $giao_hang_order_status) && in_array($arrParam['ghtk_status'], $giao_hang_order_status)){
                $this->zalo_send_notify(ZALO_NOTIFY_CONFIG_GIAOHANG, $numberFormat->convertToInternational($contract_item['phone']), $contract_item);
            }
        }

        $template_thanh_toan = $this->getServiceLocator()->get('Admin\Model\ZaloNotifyConfigTable')->getItem(array('code' => ZALO_NOTIFY_CONFIG_THANHTOAN, 'status' => 1, 'sale_branch_id' => $contract_item['sale_branch_id']), array('task' => 'code'));
        if(!empty($template_thanh_toan)){
            $thanh_toan_order_status = explode(',', str_replace(' ', '', $template_thanh_toan['order_status']));
            // trạng thái trước của đơn hàng k thuộc trạng thái thanh toán trạng thái sau khi cập nhật thuộc trạng thái thanh toán thì thông báo thanh toán
            if(!in_array($contract_item['ghtk_status'], $thanh_toan_order_status) && in_array($arrParam['ghtk_status'], $thanh_toan_order_status)){
                $this->zalo_send_notify(ZALO_NOTIFY_CONFIG_THANHTOAN, $numberFormat->convertToInternational($contract_item['phone']), $contract_item);
            }
        }
    }
    // END ZALO

    public function postJson($json)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://webhook.site/7421599b-15ee-4810-868a-f3b26fbb9b4f');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
        $header = array(
            "cache-control: no-cache",
            "content-type: application/json",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return false;
        } else {
            return $response;
        }
    }

    public function addNew($array){
        foreach($array as $key => $value){
            $array[$key] = $this->changeText($array[$key]);
            if(count($value['children']) > 0){
                $array[$key]['children'] = $this->addNew($array[$key]['children']);
            }
        }
        return $array;
    }

    public function changeText($arr){
        if(isset($arr['parentId']) && $arr['hasChild']){
            $arr['categoryName'] = '-  '.$arr['categoryName'];
        }
        elseif (isset($arr['parentId']) && !$arr['hasChild']){
            $arr['categoryName'] = '- - '.$arr['categoryName'];
        }
        return $arr;
    }

    public function updateNumberKiotviet($contract_item){
        $result = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/orders/'.$contract_item['id_kov']);
        $result = json_decode($result, true);
        if(isset($result['id'])){
            $invoiceDetails = $result['orderDetails'];
            foreach($invoiceDetails as $key => $value){
                unset($invoiceDetails[$key]['viewDiscount']);
            }
            $invoiceOrderSurcharges = $result['invoiceOrderSurcharges'];
            foreach($invoiceOrderSurcharges as $key => $value){
                $invoiSurcharges[$key]['id']    = $value['id'];
                $invoiSurcharges[$key]['code']  = $value['surchargeCode'];
                $invoiSurcharges[$key]['price'] = $value['price'];
            }

            $invoi_data['branchId']         = $result['branchId'];
            $invoi_data['customerId']       = $result['customerId'];
            $invoi_data['discount']         = $result['discount'];
            $invoi_data['totalPayment']     = $result['totalPayment'];
            $invoi_data['soldById']         = $result['soldById'];
            $invoi_data['orderId']          = $result['id'];
            $invoi_data['invoiceDetails']   = $invoiceDetails;
            $invoi_data['deliveryDetail']   = array(
                'status' => 2,
                'surchages' => $invoiSurcharges
            );
        }
        $result_kov = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/invoices', $invoi_data, 'POST');
        $result_kov = json_decode($result_kov, true);

        if(isset($result_kov['id'])){
            $params['data']['id']       = $contract_item['id'];
            $params['data']['shipped']  = 1;
            $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($params, array('task' => 'update-shipped'));
        }
    }

    public function getNameCat($array, &$result){
        foreach($array as $key => $value){
            $result[$value['categoryId']] = $value['categoryName'];
            if(count($value['children']) > 0){
                $this->getNameCat($array[$key]['children'], $result);
            }
        }
        return $result;
    }
}