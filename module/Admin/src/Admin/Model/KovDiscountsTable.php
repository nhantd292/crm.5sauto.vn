<?php
namespace Admin\Model;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use ZendX\System\UserInfo;

class KovDiscountsTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){

        if($options['task'] == 'list-item') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $date = new \ZendX\Functions\Date();
                $ssFilter  = $arrParam['ssFilter'];

                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));

                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select->where->equalTo(TABLE_KOV_DISCOUNTS.'.status', $ssFilter['filter_status']);
                }

                if(isset($ssFilter['filter_keyword']) && trim($ssFilter['filter_keyword']) != '') {
                    $select->where->like(TABLE_KOV_DISCOUNTS.'.name', '%'.$ssFilter['filter_keyword'].'%');
                }

                if(isset($ssFilter['filter_discounts_type']) && trim($ssFilter['filter_discounts_type']) != '') {
                    $select->where->equalTo(TABLE_KOV_DISCOUNTS.'.discounts_type', $ssFilter['filter_discounts_type']);
                }
                if(isset($ssFilter['filter_discounts_option']) && trim($ssFilter['filter_discounts_option']) != '') {
                    $select->where->equalTo(TABLE_KOV_DISCOUNTS.'.discounts_option', $ssFilter['filter_discounts_option']);
                }

                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo(TABLE_KOV_DISCOUNTS.'.'.$ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_begin']))
                        ->AND
                        -> lessThanOrEqualTo(TABLE_KOV_DISCOUNTS.'.'.$ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select -> where -> greaterThanOrEqualTo(TABLE_KOV_DISCOUNTS.'.'.$ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select -> where -> lessThanOrEqualTo(TABLE_KOV_DISCOUNTS.'.'.$ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
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
                    $select->where->equalTo(TABLE_KOV_DISCOUNTS.'.status', $ssFilter['filter_status']);
                }

                if(isset($ssFilter['filter_keyword']) && trim($ssFilter['filter_keyword']) != '') {
                    $select->where->like(TABLE_KOV_DISCOUNTS.'.name', '%'.$ssFilter['filter_keyword'].'%');
                }

                if(isset($ssFilter['filter_discounts_type']) && trim($ssFilter['filter_discounts_type']) != '') {
                    $select->where->equalTo(TABLE_KOV_DISCOUNTS.'.discounts_type', $ssFilter['filter_discounts_type']);
                }
                if(isset($ssFilter['filter_discounts_option']) && trim($ssFilter['filter_discounts_option']) != '') {
                    $select->where->equalTo(TABLE_KOV_DISCOUNTS.'.discounts_option', $ssFilter['filter_discounts_option']);
                }

                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo(TABLE_KOV_DISCOUNTS.'.'.$ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_begin']))
                        ->AND
                        -> lessThanOrEqualTo(TABLE_KOV_DISCOUNTS.'.'.$ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select -> where -> greaterThanOrEqualTo(TABLE_KOV_DISCOUNTS.'.'.$ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select -> where -> lessThanOrEqualTo(TABLE_KOV_DISCOUNTS.'.'.$ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                }
            });
        }

        if($options['task'] == 'list-all') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter  = $arrParam['ssFilter'];

                $select -> order('name ASC');

                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select->where->equalTo(TABLE_KOV_DISCOUNTS.'.status', $ssFilter['filter_status']);
                }

            });
        }

        if($options['task'] == 'list-check') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){

                $select->where->equalTo(TABLE_KOV_DISCOUNTS.'.status', 1);

                $select -> where -> NEST
                    -> greaterThanOrEqualTo(TABLE_KOV_DISCOUNTS.'.date_end', date('Y-m-d'))
                    ->AND
                    -> lessThanOrEqualTo(TABLE_KOV_DISCOUNTS.'.date_begin', date('Y-m-d'))
                    -> UNNEST;

                $select -> where -> NEST
                    -> equalTo(TABLE_KOV_DISCOUNTS.'.discounts_range_branchs', 'all')
                    ->OR
                    -> like(TABLE_KOV_DISCOUNTS.'.discounts_range_branchs_detail', '%'.$arrParam['range_branch'].'%')
                    -> UNNEST;

                $select -> where -> NEST
                    -> equalTo(TABLE_KOV_DISCOUNTS.'.discounts_range_sales', 'all')
                    ->OR
                    -> like(TABLE_KOV_DISCOUNTS.'.discounts_range_sales_detail', '%'.$arrParam['range_sales'].'%')
                    -> UNNEST;

                $select -> where -> NEST
                    -> equalTo(TABLE_KOV_DISCOUNTS.'.discounts_range_customers', 'all')
                    ->OR
                    -> like(TABLE_KOV_DISCOUNTS.'.discounts_range_customers_detail', '%'.$arrParam['range_contact'].'%')
                    -> UNNEST;

                if(isset($arrParam['contract_value_min']) && $arrParam['contract_value_min'] != '') {
                    $select->where->lessThanOrEqualTo(TABLE_KOV_DISCOUNTS.'.contract_value_total_min', $arrParam['contract_value_min']);
                }

                $select -> order('name ASC');
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
                $select->where->equalTo('id', $arrParam['id']);
//                $select->where->equalTo('status', 1);
            })->current();
        }
        return $result;
    }

    public function saveItem($arrParam = null, $options = null){
        $arrData  = $arrParam['data'];
        $date     = new \ZendX\Functions\Date();
        $number   = new \ZendX\Functions\Number();
        $gid      = new \ZendX\Functions\Gid();

        if($options['task'] == 'add-item') {
            $id = $gid->getId();
            $detail_discounts = $arrData['detail_discounts'];
            $discount_options = array();
            if($arrData['discounts_type'] == 'hoa-don'){
                $contract_value_total_min = 10000000000;
                foreach($detail_discounts['contract_total'] as $k){
                    $contract_value_total_min = $number->formatToData($k) < $contract_value_total_min ? $number->formatToData($k) : $contract_value_total_min;
                }

                if($arrData['discounts_option'] == 'giam-gia-hoa-don'){
                    for($i = 0; $i < count($detail_discounts['contract_total']); $i++){
                        if(!empty($detail_discounts['contract_total'][$i])) {
                            $discount_options[$i]['contract_total']    = $detail_discounts['contract_total'][$i];
                            $discount_options[$i]['discount_value']    = $detail_discounts['discount_value'][$i];
                            $discount_options[$i]['unit_type']         = $detail_discounts['unit_type'][$i];
                        }
                    }
                }
                if($arrData['discounts_option'] == 'tang-hang'){
                    for($i = 0; $i < count($detail_discounts['contract_total']); $i++){
                        if(!empty($detail_discounts['contract_total'][$i])) {
                            $discount_options[$i]['contract_total']    = $detail_discounts['contract_total'][$i];
                            $discount_options[$i]['number_donate']     = $detail_discounts['number_donate'][$i];
                            $discount_options[$i]['product_donate']    = serialize($detail_discounts['product_donate'][$i]);
                        }
                    }
                }
                if($arrData['discounts_option'] == 'giam-gia-hang'){
                    for($i = 0; $i < count($detail_discounts['contract_total']); $i++){
                        if(!empty($detail_discounts['contract_total'][$i])) {
                            $discount_options[$i]['contract_total']    = $detail_discounts['contract_total'][$i];
                            $discount_options[$i]['discount_value']    = $detail_discounts['discount_value'][$i];
                            $discount_options[$i]['unit_type']         = $detail_discounts['unit_type'][$i];
                            $discount_options[$i]['number_donate']     = $detail_discounts['number_donate'][$i];
                            $discount_options[$i]['product_donate']    = serialize($detail_discounts['product_donate'][$i]);
                        }
                    }
                }
            }

            $data = array(
                'id'                                => $id,
                'code'                              => $id,
                'name'                              => $arrData['name'],
                'note'                              => $arrData['note'],
                'status'                            => $arrData['status'],
                'date_begin'                        => $date->formatToData($arrData['date_begin'], 'Y-m-d'),
                'date_end'                          => $date->formatToData($arrData['date_end'], 'Y-m-d'),
                'discounts_range_branchs'           => $arrData['discounts_range_branchs'],
                'discounts_range_sales'             => $arrData['discounts_range_sales'],
                'discounts_range_customers'         => $arrData['discounts_range_customers'],
                'discounts_range_branchs_detail'    => implode(',', $arrData['discounts_range_branchs_detail']),
                'discounts_range_sales_detail'      => implode(',', $arrData['discounts_range_sales_detail']),
                'discounts_range_customers_detail'  => implode(',', $arrData['discounts_range_customers_detail']),
                'discounts_type'                    => $arrData['discounts_type'],
                'discounts_option'                  => $arrData['discounts_option'],
                'contract_value_total_min'          => $contract_value_total_min,
                'detail'                            => serialize($discount_options),

                'created'                           => date('Y-m-d H:i:s'),
                'created_by'                        => $this->userInfo->getUserInfo('id'),
            );

            $this->tableGateway->insert($data);
            return $id;
        }

        if($options['task'] == 'edit-item') {
            $id = $arrData['id'];
            $detail_discounts = $arrData['detail_discounts'];
            $discount_options = array();

            if($arrData['discounts_type'] == 'hoa-don'){
                $contract_value_total_min = 10000000000;
                foreach($detail_discounts['contract_total'] as $k){
                    $contract_value_total_min = $number->formatToData($k) < $contract_value_total_min ? $number->formatToData($k) : $contract_value_total_min;
                }

                if($arrData['discounts_option'] == 'giam-gia-hoa-don'){
                    for($i = 0; $i < count($detail_discounts['contract_total']); $i++){
                        if(!empty($detail_discounts['contract_total'][$i])) {
                            $discount_options[$i]['contract_total']    = $detail_discounts['contract_total'][$i];
                            $discount_options[$i]['discount_value']    = $detail_discounts['discount_value'][$i];
                            $discount_options[$i]['unit_type']         = $detail_discounts['unit_type'][$i];
                        }
                    }
                }
                if($arrData['discounts_option'] == 'tang-hang'){
                    for($i = 0; $i < count($detail_discounts['contract_total']); $i++){
                        if(!empty($detail_discounts['contract_total'][$i])) {
                            $discount_options[$i]['contract_total']    = $detail_discounts['contract_total'][$i];
                            $discount_options[$i]['number_donate']     = $detail_discounts['number_donate'][$i];
                            $discount_options[$i]['product_donate']    = serialize($detail_discounts['product_donate'][$i]);
                        }
                    }
                }
                if($arrData['discounts_option'] == 'giam-gia-hang'){
                    for($i = 0; $i < count($detail_discounts['contract_total']); $i++){
                        if(!empty($detail_discounts['contract_total'][$i])) {
                            $discount_options[$i]['contract_total']    = $detail_discounts['contract_total'][$i];
                            $discount_options[$i]['discount_value']    = $detail_discounts['discount_value'][$i];
                            $discount_options[$i]['unit_type']         = $detail_discounts['unit_type'][$i];
                            $discount_options[$i]['number_donate']     = $detail_discounts['number_donate'][$i];
                            $discount_options[$i]['product_donate']    = serialize($detail_discounts['product_donate'][$i]);
                        }
                    }
                }
            }

            $data = array(
                'name'                              => $arrData['name'],
                'note'                              => $arrData['note'],
                'status'                            => $arrData['status'],
                'date_begin'                        => $date->formatToData($arrData['date_begin'], 'Y-m-d'),
                'date_end'                          => $date->formatToData($arrData['date_end'], 'Y-m-d'),
                'discounts_range_branchs'           => $arrData['discounts_range_branchs'],
                'discounts_range_sales'             => $arrData['discounts_range_sales'],
                'discounts_range_customers'         => $arrData['discounts_range_customers'],
                'discounts_range_branchs_detail'    => implode(',', $arrData['discounts_range_branchs_detail']),
                'discounts_range_sales_detail'      => implode(',', $arrData['discounts_range_sales_detail']),
                'discounts_range_customers_detail'  => implode(',', $arrData['discounts_range_customers_detail']),
                'discounts_type'                    => $arrData['discounts_type'],
                'discounts_option'                  => $arrData['discounts_option'],
                'contract_value_total_min'          => $contract_value_total_min,
                'detail'                            => serialize($discount_options),
            );

            $this->tableGateway->update($data, array('id' => $id));
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