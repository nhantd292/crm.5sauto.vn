<?php
namespace Notifycation\Controller;

use ZendX\Controller\ActionController;
use Zend\Json\Json;
use Zend\Db\TableGateway\TableGateway;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use ZendX\System\UserInfo;

class ApiController extends ActionController {
    
    public function init() {
        // Lấy dữ liệu post của form
        $this->_params['data'] = array_merge($this->getRequest()->getPost()->toArray());
        
        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
    }
    
    // Lấy danh sách thông báo đưa ra view sử dụng API.
    public function listNotifyAction() {
        $items = $this->getServiceLocator()->get('Notifycation\Model\NotifyTable')->listItem($this->_params, array('task' => 'list-item-account'))->toArray();

        $this->_viewModel['data']          = $this->_params['data'];
        $this->_viewModel['items']         = $items;
        $this->_viewModel['curent_id']     = $this->_userInfo->getUserInfo('id');

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
        
        return $viewModel;
    }

    // Add thông báo contact cần chăm sóc trong ngày - cần viết scron job.
    public function addNotifyContactTakeCareAction(){
        $list_sale = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-sale3'));

        $tableContact = new \Notifycation\Model\ApiTable(new TableGateway(TABLE_CONTACT, $this->getServiceLocator()->get('dbConfig'), null));

        foreach ($list_sale as $key => $value) {
            $params['data'] = array(
                'id' => $value['id'],
            );
            // Số contact có ngày hẹn chăm sóc lại ngày hôm nay
            $contact_history_return =  $tableContact->countItem($params, array('task' => 'contact-history-return'));
            // Số contact l1 được phân ngày hôm nay.
            $contact_level_1        =  $tableContact->countItem($params, array('task' => 'contact-level'));

            // Số contact chưa chăm sóc từ ngày hôm trước
            $contact_history_return_yesterday   = $tableContact->countItem($params, array('task' => 'contact-history-return-yesterday'));
            $contact_level_1_yesterday          = $tableContact->countItem($params, array('task' => 'contact-level-yesterday'));
            $contact_take_care_yesterday = $contact_history_return_yesterday + $contact_level_1_yesterday;

            $content_notify = '';
            if($contact_history_return){
                $content_notify .= $contact_history_return.' contact hẹn chăm sóc lại ';
            }
            if($contact_level_1){
                $content_notify .= $contact_level_1.' contact l1 mới được phân ';
            }
            if($content_notify != ''){
                $content_notify .= 'trong ngày hôm nay. ';
            }
            if($contact_take_care_yesterday){
                $content_notify .= $contact_take_care_yesterday.' contact hôm trước chưa chăm sóc.';
            }
            if($content_notify != ''){
                $content_notify = 'Có '.$content_notify;
            }

            if($content_notify != ''){
                $arrNotify['data'] = array(
                    'content'   => $content_notify,
                    'user_ids'  => $value['id'],
                    'link'      => '/xadmin/calendar/index/',
                );
                $this->getServiceLocator()->get('Notifycation\Model\NotifyTable')->saveItem($arrNotify, array('task' => 'add-item'));
            }
        }

        $tableNotify = new \Notifycation\Model\ApiTable(new TableGateway(TABLE_NOTIFY, $this->getServiceLocator()->get('dbNotify'), null));
        return $this->response;
    }

    // Add thông báo contact nhập trùng trong bảng data trùng - cần viết scron job.
    public function addNotifyContactCoincideTodayAction(){
        $tableContactCoincider = new \Notifycation\Model\ApiTable(new TableGateway(TABLE_CONTACT_COINCIDE, $this->getServiceLocator()->get('dbConfig'), null));
        $tableUser = new \Notifycation\Model\ApiTable(new TableGateway(TABLE_USER, $this->getServiceLocator()->get('dbConfig'), null));

        $today = date('d/m/Y');
        $number_contact_coincider_today = $tableContactCoincider->countItem(array('data' => array('today' => $today)), array('task' => 'contact-coincider-today'));
        if($number_contact_coincider_today > 0){
            $list_user_admin_sale = $tableUser->listItem(array('data' => array('permission_ids' => 'sales-admin')), array('task' => 'list-user-admin-sale'));
            $user_ids ='';
            foreach ($list_user_admin_sale as $key => $value) {
                $user_ids .= ','.$value['id'];
            }

            $arrNotify['data'] = array(
                'content'   => 'Hôm nay có '.$number_contact_coincider_today.' contact nhập trùng cần phân chia lại.',
                'user_ids'  => $user_ids,
                'link'      => '/xadmin/contact-coincide/index/',
            );
            $this->getServiceLocator()->get('Notifycation\Model\NotifyTable')->saveItem($arrNotify, array('task' => 'add-item'));
        }
        return $this->response;
    }

    
    // public function updateNotifyAction() {
    //     $count_old = $this->getServiceLocator()->get('Notifycation\Model\NotifyTable')->countItem();
    //     while (1 > 0) {
    //         $count_new = $this->getServiceLocator()->get('Notifycation\Model\NotifyTable')->countItem();
    //         if ($count_new > $count_old) {
    //             echo "string";
    //             return $this->response;
    //         }  
    //         sleep(3);
    //     } 
    // }

    
    public function updateNotifyAction() {
        while (1 > 0) {
            echo "string";
            sleep(5);
            return $this->response;
        } 
    }
    
    // public function checkNewAction() {
    //     try{
    //         $count_old = $this->getServiceLocator()->get('Notifycation\Model\NotifyTable')->countItem();
    //         while (1 > 0) {
    //             $count_new = $this->getServiceLocator()->get('Notifycation\Model\NotifyTable')->countItem();
    //             if ($count_new > $count_old) {
    //                 echo json_encode([
    //                     'status' => true,
    //                 ]);
    //                 exit;
    //             }
    //             echo "string";
    //             sleep(2);
    //             exit;
    //         }
    //     }catch (Exception $e) {
    //         exit(
    //             json_encode(
    //                 array (
    //                     'status' => false,
    //                     'error' => $e -> getMessage()
    //                 )
    //             )
    //         );
    //     }
    // }

    
}














