<?php
namespace Admin\Model;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use ZendX\System\UserInfo;

class ProductReturnKovTable extends DefaultTable {

    public function listItem($arrParam = null, $options = null){
        if($options['task'] == 'list-item') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $dateFormat = new \ZendX\Functions\Date();
                $paginator = $arrParam['paginator'];
                $ssFilter  = $arrParam['ssFilter'];

                $date_type = 'created';
                if(!empty($ssFilter['date_type'])) {
                    $date_type = $ssFilter['date_type'];
                }

                $select -> join(TABLE_CONTRACT, TABLE_CONTRACT .'.id = '. TABLE_PRODUCT_RETURN_KOV .'.contract_id',
                    array(
                        'code' => 'code',
                        'sale_branch_id' => 'sale_branch_id',
                        'user_id' => 'user_id',
                    ), 'left');

                if(!isset($options['paginator']) || $options['paginator'] == true) {
                    $select -> limit($paginator['itemCountPerPage'])
                        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }

                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo(TABLE_PRODUCT_RETURN_KOV .'.'.$date_type, $dateFormat->formatToSearch($ssFilter['filter_date_begin']))
                        ->AND
                        -> lessThanOrEqualTo(TABLE_PRODUCT_RETURN_KOV .'.'.$date_type, $dateFormat->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select->where->greaterThanOrEqualTo(TABLE_PRODUCT_RETURN_KOV .'.'.$date_type, $dateFormat->formatToSearch($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select->where->lessThanOrEqualTo(TABLE_PRODUCT_RETURN_KOV .'.'.$date_type, $dateFormat->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59');
                }

                if (!empty($ssFilter['order_by']) && !empty($ssFilter['order'])) {
                    $select->order(array($ssFilter['order_by'] . ' ' . strtoupper($ssFilter['order'])));
                }

                if(isset($ssFilter['filter_product_id']) && trim($ssFilter['filter_product_id']) != '') {
                    $select -> where -> NEST
                        ->like(TABLE_PRODUCT_RETURN_KOV.'.products', '%'.$ssFilter['filter_product_id'].'%')
                        -> UNNEST;
                }

                if(isset($ssFilter['filter_contract_code']) && $ssFilter['filter_contract_code'] != '') {
                    $select->where->equalTo(TABLE_CONTRACT.'.code', $ssFilter['filter_contract_code']);
                }

                if(isset($ssFilter['filter_sale_branch_id']) && $ssFilter['filter_sale_branch_id'] != '') {
                    $select->where->equalTo(TABLE_CONTRACT.'.sale_branch_id', $ssFilter['filter_sale_branch_id']);
                }

                if(isset($ssFilter['filter_user_id']) && $ssFilter['filter_user_id'] != '') {
                    $select->where->equalTo(TABLE_CONTRACT.'.user_id', $ssFilter['filter_user_id']);
                }

                if(isset($ssFilter['filter_type']) && $ssFilter['filter_type'] != '') {
                    $select->where->equalTo(TABLE_PRODUCT_RETURN_KOV.'.type', $ssFilter['filter_type']);
                }
            });
        }
        return $result;
    }

    public function saveItem($arrParam = null, $options = null){
        $arrData  = $arrParam['data'];
        $gid      = new \ZendX\Functions\Gid();

        if($options['task'] == 'add-item') {
            $id = $gid->getId();
            $data = array(
                'id' => $id,
                'contract_id'   => $arrData['contract_id'],
                'type'          => $arrData['type'],
                'products'      => serialize($arrData['products']),
                'created'       => date('Y-m-d H:i:s'),
                'created_by'    => $this->userInfo->getUserInfo('id'),

            );

            $this->tableGateway->insert($data);
            return $arrData['id'];
        }
    }
}