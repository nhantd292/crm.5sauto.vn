<?php
namespace Admin\Model;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use ZendX\System\UserInfo;

class NotifiTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
        if($options['task'] == null) {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $select -> columns(array('count' => new Expression('COUNT(1)')));
            })->current();
        }
        return $result->count;
    }

    public function listItem($arrParam = null, $options = null){
        if($options['task'] == 'list-item') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
                $date          = new \ZendX\Functions\Date();
                $arrData       = $arrParam['data'];
                $arrRoute      = $arrParam['route'];

                if(!empty($arrData['company_branch_id'])) {
                    $select->where->equalTo('company_branch_id', $arrData['company_branch_id']);
                }
            });
        }

        // Lấy thông báo theo user đăng nhập.
        if($options['task'] == 'list-item-account') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
                $date          = new \ZendX\Functions\Date();
                $arrData       = $arrParam['data'];
                $arrRoute      = $arrParam['route'];

                $current_user = new UserInfo();

                $where =  new Where();

                if(!empty($arrData["type"])){
                    if($arrData["type"] == 'readed'){
                        $select->where->like('user_readed', '%'.$current_user->getUserInfo('id').'%');
                    }
                    if($arrData["type"] == 'unread'){
                        $where 	-> NEST
                            -> isNull('user_readed')
                            ->OR
                            -> notLike('user_readed', '%'.$current_user->getUserInfo('id').'%')
                            -> UNNEST;
                        $select->where($where);
                    }
                }

                $select->order(TABLE_NOTIFY.'.created DESC');
                $select->where->like('user_ids', '%'.$current_user->getUserInfo('id').'%');
//                echo '<pre>';
//                print_r($select->getSqlString());
//                echo '</pre>';
            });
            $result;
        }

        return $result;
    }

    public function getItem($arrParam = null, $options = null){
        if($options['task'] == 'link') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
                $select->where->equalTo('link', $arrParam['link']);
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
                'content'           => $arrData['content'],
                'link'             	=> $arrData['link'],
                'type'          	=> $arrData['type'],
                'options'           => $arrData['options'],
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