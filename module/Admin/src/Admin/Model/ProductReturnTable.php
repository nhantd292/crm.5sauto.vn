<?php
namespace Admin\Model;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use ZendX\System\UserInfo;

class ProductReturnTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){

        if($options['task'] == 'list-item') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $date = new \ZendX\Functions\Date();
                $ssFilter  = $arrParam['ssFilter'];
                $select -> join(TABLE_KOV_PRODUCTS, TABLE_KOV_PRODUCTS .'.id = '. TABLE_PRODUCT_RETURN .'.productId',
                    array(
                        'code' => 'code',
                        'name' => 'name',
                        'fullName' => 'fullName',
                        'categoryId' => 'categoryId',
                        'basePrice' => 'basePrice',
                        'images' => 'images',
                    ), 'inner')
                    -> join(TABLE_KOV_PRODUCT_BRANCH, TABLE_KOV_PRODUCT_BRANCH .'.branchId = '. TABLE_PRODUCT_RETURN .'.branchId and '.  TABLE_KOV_PRODUCT_BRANCH .'.productId = '. TABLE_PRODUCT_RETURN .'.productId',
                        array(
                            'branch_id' => 'branchId',
                            'branch_cost' => 'cost',
                            'branch_cost_new' => 'cost_new',
                            'branch_fee' => 'fee',
                            'branch_onHand' => 'onHand',
                            'branch_reserved' => 'reserved',
                        )
                        , 'inner');
                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));


                if(isset($ssFilter['filter_categoryId']) && $ssFilter['filter_categoryId'] != '') {
                    $select->where->equalTo(TABLE_KOV_PRODUCTS.'.categoryId', $ssFilter['filter_categoryId']);
                }

                if(isset($ssFilter['filter_keyword']) && trim($ssFilter['filter_keyword']) != '') {
                    $select -> where -> NEST
                        ->like(TABLE_KOV_PRODUCTS.'.fullName', '%'.$ssFilter['filter_keyword'].'%')
                        ->Or
                        ->like(TABLE_KOV_PRODUCTS.'.code', '%'.$ssFilter['filter_keyword'].'%')
                        ->Or
                        ->like(TABLE_PRODUCT_RETURN.'.name_year', '%'.$ssFilter['filter_keyword'].'%')
                        ->Or
                        ->like(TABLE_PRODUCT_RETURN.'.contract_code', '%'.$ssFilter['filter_keyword'].'%')
                        -> UNNEST;
                }

                if(isset($ssFilter['filter_branches']) && $ssFilter['filter_branches'] != '') {
                    $select->where->equalTo(TABLE_PRODUCT_RETURN.'.branchId', $ssFilter['filter_branches']);
                }
                if(isset($ssFilter['filter_name_year']) && $ssFilter['filter_name_year'] != '') {
                    $select->where->like(TABLE_PRODUCT_RETURN.'.name_year', '%'.$ssFilter['filter_name_year'].'%');
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

                $select -> join(TABLE_KOV_PRODUCTS, TABLE_KOV_PRODUCTS .'.id = '. TABLE_PRODUCT_RETURN .'.productId',
                    array(
                        'code' => 'code',
                        'name' => 'name',
                        'fullName' => 'fullName',
                        'categoryId' => 'categoryId',
                        'basePrice' => 'basePrice',
                        'images' => 'images',
                    ), 'inner')
                    -> join(TABLE_KOV_PRODUCT_BRANCH, TABLE_KOV_PRODUCT_BRANCH .'.branchId = '. TABLE_PRODUCT_RETURN .'.branchId and '.  TABLE_KOV_PRODUCT_BRANCH .'.productId = '. TABLE_PRODUCT_RETURN .'.productId',
                        array(
                            'branch_id' => 'branchId',
                            'branch_cost' => 'cost',
                            'branch_cost_new' => 'cost_new',
                            'branch_fee' => 'fee',
                            'branch_onHand' => 'onHand',
                            'branch_reserved' => 'reserved',
                        )
                        , 'inner');

                $select -> limit($paginator['itemCountPerPage'])
                    -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);


                if (!empty($ssFilter['order_by']) && !empty($ssFilter['order'])) {
                    $select->order(array($ssFilter['order_by'] . ' ' . strtoupper($ssFilter['order'])));
                }


                if(isset($ssFilter['filter_categoryId']) && $ssFilter['filter_categoryId'] != '') {
                    $select->where->equalTo(TABLE_KOV_PRODUCTS.'.categoryId', $ssFilter['filter_categoryId']);
                }

                if(isset($ssFilter['filter_keyword']) && trim($ssFilter['filter_keyword']) != '') {
                    $select -> where -> NEST
                        ->like(TABLE_KOV_PRODUCTS.'.fullName', '%'.$ssFilter['filter_keyword'].'%')
                        ->Or
                        ->like(TABLE_KOV_PRODUCTS.'.code', '%'.$ssFilter['filter_keyword'].'%')
                        ->Or
                        ->like(TABLE_PRODUCT_RETURN.'.name_year', '%'.$ssFilter['filter_keyword'].'%')
                        ->Or
                        ->like(TABLE_PRODUCT_RETURN.'.contract_code', '%'.$ssFilter['filter_keyword'].'%')
                        -> UNNEST;
                }

                if(isset($ssFilter['filter_branches']) && $ssFilter['filter_branches'] != '') {
                    $select->where->equalTo(TABLE_PRODUCT_RETURN.'.branchId', $ssFilter['filter_branches']);
                }
                if(isset($ssFilter['filter_name_year']) && $ssFilter['filter_name_year'] != '') {
                    $select->where->like(TABLE_PRODUCT_RETURN.'.name_year', '%'.$ssFilter['filter_name_year'].'%');
                }
                if(isset($ssFilter['filter_quantity']) && $ssFilter['filter_quantity'] != '') {
                    $select->where->greaterThan(TABLE_PRODUCT_RETURN.'.quantity', 0);
                }
            });
        }

        if($options['task'] == 'cache') {
            $cache = $this->getServiceLocator()->get('cache');
            $cache_key = 'ProductReturn'. $arrParam['type'];
            $result = $cache->getItem($cache_key);

            if (empty($result)) {
                $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
                    $select -> order(array('productId' => 'ASC'));
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
                $select -> join(TABLE_KOV_PRODUCTS, TABLE_KOV_PRODUCTS .'.id = '. TABLE_PRODUCT_RETURN .'.productId',
                    array(
                        'fullName' => 'fullName',
                        'code' => 'code',
                        'categoryId' => 'categoryId',
                        'basePrice' => 'basePrice',
                    ), 'inner');

                if(isset($arrParam['id']) and $arrParam['id'] != ''){
                    $select->where->equalTo(TABLE_PRODUCT_RETURN.'.id', $arrParam['id']);
                }
                if(isset($arrParam['branchId']) and $arrParam['branchId'] != ''){
                    $select->where->equalTo(TABLE_PRODUCT_RETURN.'.branchId', $arrParam['branchId']);
                }
                if(isset($arrParam['productId']) and $arrParam['productId'] != ''){
                    $select->where->equalTo(TABLE_PRODUCT_RETURN.'.productId', $arrParam['productId']);
                }
                if(isset($arrParam['name_year']) and $arrParam['name_year'] != ''){
                    $select->where->equalTo(TABLE_PRODUCT_RETURN.'.name_year', $arrParam['name_year']);
                }
                if(isset($arrParam['sale_branch_id']) and $arrParam['sale_branch_id'] != ''){
                    $select->where->equalTo(TABLE_PRODUCT_RETURN.'.sale_branch_id', $arrParam['sale_branch_id']);
                }
                if(isset($arrParam['contract_code']) and $arrParam['contract_code'] != ''){
                    $select->where->equalTo(TABLE_PRODUCT_RETURN.'.contract_code', $arrParam['contract_code']);
                }
            })->current();
        }
        return $result;
    }

    public function saveItem($arrParam = null, $options = null){
        $arrData  = $arrParam['data'];
        $arrItem  = $arrParam['item'];
        $gid      = new \ZendX\Functions\Gid();

        if($options['task'] == 'edit-item') {
            $data = [];

            $data['quantity'] = $arrData['quantity'];
            if(!empty($arrData['name_year'])){
                $data['name_year'] = $arrData['name_year'];
            }

            $this->tableGateway->update($data, array('id' => $arrItem['id']));
            return $arrItem['id'];
        }

        if($options['task'] == 'add-item') {
            $id = $gid->getId();
            $data = array(
                'id' => $id,
                'branchId'       => $arrData['branch_id'],
                'productId'      => $arrData['product_id'],
                'name_year'      => $arrData['product_name'],
                'quantity'       => $arrData['numbers'],
                'sale_branch_id' => $arrData['sale_branch_id'],
                'contract_code'  => $arrData['contract_code'],
                'contract_id'    => $arrData['contract_id'],
            );

            $this->tableGateway->insert($data);
            return $arrData['id'];
        }

        if($options['task'] == 'import-item') {
            $id = $gid->getId();
            $data = array(
                'id' => $id,
                'branchId'       => $arrData['branchId'],
                'productId'      => $arrData['productId'],
                'sale_branch_id' => $arrData['sale_branch_id'],
                'contract_code'  => $arrData['contract_code'],
                'contract_id'    => $arrData['contract_id'],
                'name_year'      => $arrData['name_year'],
                'quantity'       => $arrData['quantity'],
            );

            $this->tableGateway->insert($data);
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