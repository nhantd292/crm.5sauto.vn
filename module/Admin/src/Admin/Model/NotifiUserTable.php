<?php
namespace Admin\Model;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use ZendX\System\UserInfo;

class NotifiUserTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){

        if($options['task'] == 'list-item') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $date = new \ZendX\Functions\Date();
                $ssFilter  = $arrParam['ssFilter'];
                $current_user = new UserInfo();
                $current_user_id = $current_user->getUserInfo('id');

                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));

                $select -> join(TABLE_NOTIFI, TABLE_NOTIFI .'.id = '. TABLE_NOTIFI_USER .'.notifi_id', array(), 'inner');

                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select->where->equalTo(TABLE_NOTIFI_USER.'.status', $ssFilter['filter_status']);
                }

                $select->where->equalTo('user_id', $current_user_id);

                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo(TABLE_NOTIFI_USER.'.created', $date->formatToData($ssFilter['filter_date_begin']))
                        ->AND
                        -> lessThanOrEqualTo(TABLE_NOTIFI_USER.'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select -> where -> greaterThanOrEqualTo(TABLE_NOTIFI_USER.'.created', $date->formatToData($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select -> where -> lessThanOrEqualTo(TABLE_NOTIFI_USER.'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                }
            })->current();
        }


        if($options['task'] == 'list-item-unread') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $date = new \ZendX\Functions\Date();
                $paginator = $arrParam['paginator'];
                $ssFilter  = $arrParam['ssFilter'];
                $current_user = new UserInfo();
                $current_user_id = $current_user->getUserInfo('id');

                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
                $select -> join(TABLE_NOTIFI, TABLE_NOTIFI .'.id = '. TABLE_NOTIFI_USER .'.notifi_id', array(), 'inner');

                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select->where->equalTo(TABLE_NOTIFI_USER.'.status', $ssFilter['filter_status']);
                }

                $select->where->equalTo(TABLE_NOTIFI_USER.'.status', 0);
                $select->where->equalTo('user_id', $current_user_id);
            })->current();
        }

        if($options['task'] == null) {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $select -> columns(array('count' => new Expression('COUNT(1)')));
            })->current();
        }
        return $result->count;
    }

    public function listItem($arrParam = null, $options = null){
        if($options['task'] == 'list-item') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $date = new \ZendX\Functions\Date();
                $paginator = $arrParam['paginator'];
                $ssFilter  = $arrParam['ssFilter'];
                $current_user = new UserInfo();
                $current_user_id = $current_user->getUserInfo('id');

                $select -> join(TABLE_NOTIFI, TABLE_NOTIFI .'.id = '. TABLE_NOTIFI_USER .'.notifi_id', array(
                    'notifi_content' => 'content',
                    'notifi_link' => 'link',
                ), 'inner');

                $select -> limit($paginator['itemCountPerPage'])
                    -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);

                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select->where->equalTo(TABLE_NOTIFI_USER.'.status', $ssFilter['filter_status']);
                }

                $select->where->equalTo('user_id', $current_user_id);

                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo(TABLE_NOTIFI_USER.'.created', $date->formatToData($ssFilter['filter_date_begin']))
                        ->AND
                        -> lessThanOrEqualTo(TABLE_NOTIFI_USER.'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select -> where -> greaterThanOrEqualTo(TABLE_NOTIFI_USER.'.created', $date->formatToData($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select -> where -> lessThanOrEqualTo(TABLE_NOTIFI_USER.'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                }
            });
        }
        if($options['task'] == 'list-item-unread') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $date = new \ZendX\Functions\Date();
                $paginator = $arrParam['paginator'];
                $ssFilter  = $arrParam['ssFilter'];
                $current_user = new UserInfo();
                $current_user_id = $current_user->getUserInfo('id');

                $select ->ORder(array('created' => 'DESC'));
                $select -> join(TABLE_NOTIFI, TABLE_NOTIFI .'.id = '. TABLE_NOTIFI_USER .'.notifi_id', array(
                    'notifi_content' => 'content',
                    'notifi_link' => 'link',
                ), 'inner');

                $select -> limit(15)
                    -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);

                $select->where->equalTo('user_id', $current_user_id);
            });
        }

        // Lấy thông báo theo user đăng nhập.
        if($options['task'] == 'list-all') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $date = new \ZendX\Functions\Date();
                $paginator = $arrParam['paginator'];
                $ssFilter  = $arrParam['ssFilter'];
                $current_user = new UserInfo();
                $current_user_id = $current_user->getUserInfo('id');

                $select -> join(TABLE_NOTIFI, TABLE_NOTIFI .'.id = '. TABLE_NOTIFI_USER .'.notifi_id', array(
                    'notifi_content' => 'content',
                    'notifi_link' => 'link',
                ), 'inner');

                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select->where->equalTo(TABLE_NOTIFI_USER.'.status', $ssFilter['filter_status']);
                }

                if(isset($ssFilter['filter_user_id']) && $ssFilter['filter_user_id'] != '') {
                    $select->where->equalTo(TABLE_NOTIFI_USER.'.user_id', $ssFilter['filter_user_id']);
                }

                if(isset($ssFilter['filter_notifi_id']) && $ssFilter['filter_notifi_id'] != '') {
                    $select->where->equalTo(TABLE_NOTIFI_USER.'.notifi_id', $ssFilter['filter_notifi_id']);
                }
            });
        }

        return $result;
    }

    public function getItem($arrParam = null, $options = null){

        if($options['task'] == 'notifi') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
                $select->where->equalTo('user_id', $arrParam['user_id']);
                $select->where->equalTo('notifi_id', $arrParam['notifi_id']);
            })->current();
        }

        if($options == null) {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
                $select->where->equalTo('id', $arrParam['id']);
            })->current();
        }
        return $result;
    }

    public function saveItem($arrParam = null, $options = null){
        $arrData  = $arrParam['data'];
        $gid      = new \ZendX\Functions\Gid();

        if($options['task'] == 'add-item') {
            $id = $gid->getId();
            $data	= array(
                'id'                => $id,
                'user_id'           => $arrData['user_id'],
                'notifi_id'         => $arrData['notifi_id'],
                'status'            => 0,
                'created'           => date('Y-m-d H:i:s'),
            );

            $this->tableGateway->insert($data);
            return $id;
        }
    }

    public function deleteItem($arrParam = null, $options = null){
        if($options['task'] == 'delete-item') {
            $result = $this->defaultDelete($arrParam, null);
        }

        return $result;
    }

    public function changeStatus($arrParam = null, $options = null){
        if($options['task'] == 'change-status') {
            $result = $this->defaultStatus($arrParam, null);
        }

        return $result;
    }

    public function changeOrdering($arrParam = null, $options = null){
        if($options['task'] == 'change-ordering') {
            $result = $this->defaultOrdering($arrParam, null);
        }
        return $result;
    }
}