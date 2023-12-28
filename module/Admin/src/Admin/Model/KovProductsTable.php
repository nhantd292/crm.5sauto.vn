<?php
namespace Admin\Model;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use ZendX\System\UserInfo;

class KovProductsTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){

        if($options['task'] == 'list-item') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $date = new \ZendX\Functions\Date();
                $ssFilter  = $arrParam['ssFilter'];

                $select -> join(TABLE_KOV_PRODUCT_BRANCH, TABLE_KOV_PRODUCT_BRANCH .'.productId = '. TABLE_KOV_PRODUCTS .'.id', array(), 'inner');

                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));

                if(isset($ssFilter['filter_categoryId']) && $ssFilter['filter_categoryId'] != '') {
                    $select->where->equalTo(TABLE_KOV_PRODUCTS.'.categoryId', $ssFilter['filter_categoryId']);
                }

                if(isset($ssFilter['filter_keyword']) && trim($ssFilter['filter_keyword']) != '') {
                    $select -> where -> NEST
                        ->like(TABLE_KOV_PRODUCTS.'.fullName', '%'.$ssFilter['filter_keyword'].'%')
                        ->Or
                        ->like(TABLE_KOV_PRODUCTS.'.code', '%'.$ssFilter['filter_keyword'].'%')
                        -> UNNEST;

                }

                if(isset($ssFilter['filter_branches']) && $ssFilter['filter_branches'] != '') {
                    $select->where->equalTo(TABLE_KOV_PRODUCT_BRANCH.'.branchId', $ssFilter['filter_branches']);
                }
                if(isset($ssFilter['filter_evaluate']) && $ssFilter['filter_evaluate'] != '') {
                    $select->where->equalTo(TABLE_KOV_PRODUCTS.'.evaluate', $ssFilter['filter_evaluate']);
                }
                if(isset($ssFilter['filter_tailors']) && $ssFilter['filter_tailors'] != '') {
                    $select->where->equalTo(TABLE_KOV_PRODUCTS.'.product_type', $ssFilter['filter_tailors']);
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
                $date = new \ZendX\Functions\Date();
                $paginator = $arrParam['paginator'];
                $ssFilter  = $arrParam['ssFilter'];

                $select -> join(TABLE_KOV_PRODUCT_BRANCH, TABLE_KOV_PRODUCT_BRANCH .'.productId = '. TABLE_KOV_PRODUCTS .'.id',
                    array(
                        'branch_id' => 'branchId',
                        'branch_cost' => 'cost',
                        'branch_cost_new' => 'cost_new',
                        'branch_fee' => 'fee',
                        'branch_onHand' => 'onHand',
                        'branch_reserved' => 'reserved',
                    ), 'inner');

                $select -> limit($paginator['itemCountPerPage'])
                    -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);


                if(isset($ssFilter['filter_categoryId']) && $ssFilter['filter_categoryId'] != '') {
                    $select->where->equalTo(TABLE_KOV_PRODUCTS.'.categoryId', $ssFilter['filter_categoryId']);
                }

                if(isset($ssFilter['filter_keyword']) && trim($ssFilter['filter_keyword']) != '') {
                    $select -> where -> NEST
                        ->like(TABLE_KOV_PRODUCTS.'.fullName', '%'.$ssFilter['filter_keyword'].'%')
                        ->Or
                        ->like(TABLE_KOV_PRODUCTS.'.code', '%'.$ssFilter['filter_keyword'].'%')
                        -> UNNEST;
                }

                if(isset($ssFilter['filter_branches']) && $ssFilter['filter_branches'] != '') {
                    $select->where->equalTo(TABLE_KOV_PRODUCT_BRANCH.'.branchId', $ssFilter['filter_branches']);
                }
                if(isset($ssFilter['filter_evaluate']) && $ssFilter['filter_evaluate'] != '') {
                    $select->where->equalTo(TABLE_KOV_PRODUCTS.'.evaluate', $ssFilter['filter_evaluate']);
                }
                if(isset($ssFilter['filter_tailors']) && $ssFilter['filter_tailors'] != '') {
                    $select->where->equalTo(TABLE_KOV_PRODUCTS.'.product_type', $ssFilter['filter_tailors']);
                }
                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select->where->equalTo(TABLE_KOV_PRODUCTS.'.status', $ssFilter['filter_status']);
                }
            });
        }

        if($options['task'] == 'list-all') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $date = new \ZendX\Functions\Date();
                $paginator = $arrParam['paginator'];
                $ssFilter  = $arrParam['ssFilter'];
            });
        }

        if($options['task'] == 'list-export-template') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){

                $select -> join(TABLE_KOV_PRODUCT_BRANCH, TABLE_KOV_PRODUCT_BRANCH .'.productId = '. TABLE_KOV_PRODUCTS .'.id',
                    array(
                        'branch_cost' => 'cost',
                        'branch_cost_new' => 'cost_new',
                        'branch_fee' => 'fee',
                        'branch_name' => 'branchName',
                        'branch_id' => 'branchId',
                    ), 'inner');

                $select -> order(array(TABLE_KOV_PRODUCTS.'.id'));
                if(!empty($arrParam['ids'])) {
                    $select->where->in(TABLE_KOV_PRODUCTS.'.id', $arrParam['ids']);
                }
                if(!empty($arrParam['branches'])) {
                    $select->where->equalTo(TABLE_KOV_PRODUCT_BRANCH.'.branchId', $arrParam['branches']);
                }
            });
        }

        if($options['task'] == 'cache') {
            $cache = $this->getServiceLocator()->get('cache');
            $cache_key = 'KovProducts'. $arrParam['type'];
            $result = $cache->getItem($cache_key);

            if (empty($result)) {
                $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
                    $select -> order(array('fullName' => 'ASC'));
                });
                $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));

                $cache->setItem($cache_key, $result);
            }
        }

        return $result;
    }

    public function getItem($arrParam = null, $options = null){

        if($options['task'] == 'by-name') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
                $select->where->equalTo('name', $arrParam['name']);
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


        if($options['task'] == 'edit-item') {
            $data = array(
                'product_type' => $arrData['product_type'],
                'evaluate'     => $arrData['evaluate'],
            );

            $this->tableGateway->update($data, array('id' => $arrData['id']));
            return $arrData['id'];
        }

        if($options['task'] == 'add') {
            $data = array(
                'id'                => $arrData['id'],
                'code'              => $arrData['code'],
                'name'              => $arrData['name'],
                'fullName'          => $arrData['fullName'],
                'categoryId'        => $arrData['categoryId'],
                'basePrice'         => $arrData['basePrice'],
                'images'            => serialize($arrData['images']),
            );

            $this->tableGateway->insert($data);
            return $arrData['id'];
        }

        if($options['task'] == 'update') {
            $data = array(
                'id'                => $arrData['id'],
                'code'              => $arrData['code'],
                'name'              => $arrData['name'],
                'fullName'          => $arrData['fullName'],
                'categoryId'        => $arrData['categoryId'],
                'basePrice'         => $arrData['basePrice'],
                'images'            => serialize($arrData['images']),
            );

            $this->tableGateway->update($data, array('id' => $arrData['id']));
            return $arrData['id'];
        }

        if($options['task'] == 'change-available') {
            if(count($arrData['cid']) > 0) {
                $data = array(
                    'product_type'            => $arrData['product_type'],
                );
                $where = new Where();
                $where->in('id', $arrData['cid']);
                $this->tableGateway->update($data, $where);
            }

            return count($arrData['cid']);
        }

        if($options['task'] == 'change-tailors') {
            if(count($arrData['cid']) > 0) {
                $data = array(
                    'evaluate'            => $arrData['evaluate'],
                );
                $where = new Where();
                $where->in('id', $arrData['cid']);
                $this->tableGateway->update($data, $where);
            }

            return count($arrData['cid']);
        }
    }

    public function changeStatus($arrParam = null, $options = null){
        if($options['task'] == 'change-status') {
            $result = $this->defaultStatus($arrParam, null);
        }

        return $result;
    }
}