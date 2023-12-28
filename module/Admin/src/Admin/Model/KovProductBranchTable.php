<?php
namespace Admin\Model;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use ZendX\System\UserInfo;

class KovProductBranchTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){

        if($options['task'] == 'list-item') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $date = new \ZendX\Functions\Date();
                $ssFilter  = $arrParam['ssFilter'];

                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));

                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select->where->equalTo(TABLE_COMBO_PRODUCT.'.status', $ssFilter['filter_status']);
                }

                if(isset($ssFilter['filter_keyword']) && trim($ssFilter['filter_keyword']) != '') {
                    $select->where->like(TABLE_COMBO_PRODUCT.'.name', '%'.$ssFilter['filter_keyword'].'%');
                }

                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo(TABLE_COMBO_PRODUCT.'.created', $date->formatToData($ssFilter['filter_date_begin']))
                        ->AND
                        -> lessThanOrEqualTo(TABLE_COMBO_PRODUCT.'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select -> where -> greaterThanOrEqualTo(TABLE_COMBO_PRODUCT.'.created', $date->formatToData($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select -> where -> lessThanOrEqualTo(TABLE_COMBO_PRODUCT.'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
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

                $select -> limit($paginator['itemCountPerPage'])
                    -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);

                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select->where->equalTo(TABLE_COMBO_PRODUCT.'.status', $ssFilter['filter_status']);
                }

                if(isset($ssFilter['filter_keyword']) && trim($ssFilter['filter_keyword']) != '') {
                    $select->where->like(TABLE_COMBO_PRODUCT.'.name', '%'.$ssFilter['filter_keyword'].'%');
                }

                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo(TABLE_COMBO_PRODUCT.'.created', $date->formatToData($ssFilter['filter_date_begin']))
                        ->AND
                        -> lessThanOrEqualTo(TABLE_COMBO_PRODUCT.'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select -> where -> greaterThanOrEqualTo(TABLE_COMBO_PRODUCT.'.created', $date->formatToData($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select -> where -> lessThanOrEqualTo(TABLE_COMBO_PRODUCT.'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                }
            });
        }

        if($options['task'] == 'list-all') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter  = $arrParam['ssFilter'];

                $select -> order('name ASC');

                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select->where->equalTo(TABLE_COMBO_PRODUCT.'.status', $ssFilter['filter_status']);
                }

            });
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
                $select -> join(TABLE_KOV_PRODUCTS, TABLE_KOV_PRODUCTS .'.id = '. TABLE_KOV_PRODUCT_BRANCH .'.productId',
                    array(
                        'fullName' => 'fullName',
                        'code' => 'code',
                        'categoryId' => 'categoryId',
                        'basePrice' => 'basePrice',
                    ), 'inner');

                $select->where->equalTo(TABLE_KOV_PRODUCT_BRANCH.'.branchId', $arrParam['branchId']);
                $select->where->equalTo(TABLE_KOV_PRODUCT_BRANCH.'.productId', $arrParam['productId']);
            })->current();
        }
        return $result;
    }

    public function saveItem($arrParam = null, $options = null){
        $arrData  = $arrParam['data'];

        if($options['task'] == 'import-item') {
            $data = array(
                'cost_new' => (int)$arrData['cost_new'],
                'fee'      => (int)$arrData['fee'],
            );

            $this->tableGateway->update($data, array('branchId' => $arrData['branchId'], 'productId' => $arrData['productId']));
            return $arrData['branchId'].'_'.$arrData['productId'];
        }

        if($options['task'] == 'add') {
            $data = array(
                'branchId'          => $arrData['branchId'],
                'productId'         => $arrData['productId'],
                'branchName'        => $arrData['branchName'],
                'cost'              => $arrData['cost'],
                'onHand'            => $arrData['onHand'],
                'reserved'          => $arrData['reserved'],
            );

            $this->tableGateway->insert($data);
            return $arrData['branchId'].'_'.$arrData['branchId'];
        }

        if($options['task'] == 'update') {
            $data = array(
                'branchId'          => $arrData['branchId'],
                'productId'         => $arrData['productId'],
                'branchName'        => $arrData['branchName'],
                'cost'              => $arrData['cost'],
                'onHand'            => $arrData['onHand'],
                'reserved'          => $arrData['reserved'],
            );

            $this->tableGateway->update($data, array('branchId' => $arrData['branchId'], 'productId' => $arrData['productId']));
            return $arrData['branchId'].'_'.$arrData['productId'];
        }
    }

    public function changeStatus($arrParam = null, $options = null){
        if($options['task'] == 'change-status') {
            $result = $this->defaultStatus($arrParam, null);
        }

        return $result;
    }
}