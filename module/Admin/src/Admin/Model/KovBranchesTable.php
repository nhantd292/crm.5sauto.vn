<?php
namespace Admin\Model;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use ZendX\System\UserInfo;

class KovBranchesTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){

        if($options['task'] == 'list-item') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter  = $arrParam['ssFilter'];

                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));

                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select->where->equalTo('status', $ssFilter['filter_status']);
                }

                if(isset($ssFilter['filter_keyword']) && trim($ssFilter['filter_keyword']) != '') {
                    $select->where->like('branchName', '%'.$ssFilter['filter_keyword'].'%');
                }
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
                $paginator = $arrParam['paginator'];
                $ssFilter  = $arrParam['ssFilter'];

                $select -> limit($paginator['itemCountPerPage'])
                    -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);

                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select->where->equalTo('status', $ssFilter['filter_status']);
                }

                if(isset($ssFilter['filter_keyword']) && trim($ssFilter['filter_keyword']) != '') {
                    $select->where->like('branchName', '%'.$ssFilter['filter_keyword'].'%');
                }
            });
        }

        if($options['task'] == 'list-all') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter  = $arrParam['ssFilter'];

                $select -> order('branchName ASC');

                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select->where->equalTo('status', $ssFilter['filter_status']);
                }
            });
        }
        if($options['task'] == 'cache') {
            $cache = $this->getServiceLocator()->get('cache');
            $cache_key = 'AdminKovBranches';
            $result = $cache->getItem($cache_key);

            if (empty($result)) {
                $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
                    $select -> order(array('branchName' => 'ASC'));
                });
                $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));

                $cache->setItem($cache_key, $result);
            }
        }

        return $result;
    }

    public function getItem($arrParam = null, $options = null){

        if($options == null) {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
                $select->where->equalTo('id', $arrParam['id']);
            })->current();
        }
        return $result;
    }

    public function saveItem($arrParam = null, $options = null){
        $arrData  = $arrParam['data'];

        if($options['task'] == 'edit-item') {
            $data = array(
                'branchName'            => $arrData['branchName'],
                'address'               => $arrData['address'],
                'locationName'          => $arrData['locationName'],
                'wardName'              => $arrData['wardName'],
                'contactNumber'         => $arrData['contactNumber'],
                'retailerId'            => $arrData['retailerId'],
            );

            $this->tableGateway->update($data, array('id' => $arrData['id']));
            return $arrData['id'];
        }

        if($options['task'] == 'update') {
            $data = array(
                'id'                    => $arrData['id'],
                'branchName'            => $arrData['branchName'],
                'address'               => $arrData['address'],
                'locationName'          => $arrData['locationName'],
                'wardName'              => $arrData['wardName'],
                'contactNumber'         => $arrData['contactNumber'],
                'retailerId'            => $arrData['retailerId'],
                'created'               => date('Y-m-d H:i:s'),
                'created_by'            => $this->userInfo->getUserInfo('id'),
            );

            $this->tableGateway->insert($data); // Thực hiện lưu database
            return $arrData['id'];
        }
    }

    public function changeStatus($arrParam = null, $options = null){
        if($options['task'] == 'change-status') {
            $result = $this->defaultStatus($arrParam, null);
        }

        return $result;
    }
}