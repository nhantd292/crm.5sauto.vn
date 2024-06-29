<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;

class ContractTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
				$number     = new \ZendX\Functions\Number();
				$userInfo = new \ZendX\System\UserInfo();
				$permission = $userInfo->getPermissionOfUser();
				$permissions = explode(',', $permission['permission_ids']);
				$userInfo = $userInfo->getUserInfo();
                
                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
                $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_CONTRACT .'.contact_id',
                    array(
                        'contact_phone' => 'phone',
                        'contact_name' => 'name',
                        'contact_date' => 'date',
                        'contact_email' => 'email',
                        'contact_sex' => 'sex',
                        'contact_contract_number' => 'contract_number',
                        'contact_birthday' => 'birthday',
                        'contact_birthday_year' => 'birthday_year',
                        'contact_location_city_id' => 'location_city_id',
                        'contact_location_district_id' => 'location_district_id',
                        'contact_options' => 'options',
                    ), 'inner');
                $select -> order(array(TABLE_CONTRACT .'.date' => 'DESC'));

                // Đơn hàng chưa xóa có trạng thái = 0
                $select -> where -> equalTo(TABLE_CONTRACT .'.delete', 0);

                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $filter_keyword = trim($ssFilter['filter_keyword']);
                    $select -> where -> NEST
                        -> like(TABLE_CONTRACT. '.name', '%'. $filter_keyword .'%')
                        ->Or
                        -> like(TABLE_CONTRACT. '.phone', '%'. $filter_keyword .'%')
                        ->Or
                        -> equalTo(TABLE_CONTRACT. '.code', $filter_keyword) // mã đơn
                        ->Or
                        -> like(TABLE_CONTRACT. '.ghtk_code', '%'. $filter_keyword .'%') // mã đơn ghtk
                        -> UNNEST;

                }
                $date_type = 'date';
                if(!empty($ssFilter['filter_date_type'])) {
                    $date_type = $ssFilter['filter_date_type'];
                }
                if($date_type == 'not_shipped'){
                    $select -> where -> NEST
                        -> isNull(TABLE_CONTRACT .'.shipped_date')
                        ->OR
                        -> equalTo(TABLE_CONTRACT .'.shipped_date', '')
                        -> UNNEST;
                }
                else {
                    if (!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                        $select->where->NEST
                            ->greaterThanOrEqualTo(TABLE_CONTRACT . '.' . $date_type, $date->formatToData($ssFilter['filter_date_begin']))
                            ->AND
                            ->lessThanOrEqualTo(TABLE_CONTRACT . '.' . $date_type, $date->formatToData($ssFilter['filter_date_end'] . ' 23:59:59'))
                            ->UNNEST;
                    } elseif (!empty($ssFilter['filter_date_begin'])) {
                        $select->where->greaterThanOrEqualTo(TABLE_CONTRACT . '.' . $date_type, $date->formatToData($ssFilter['filter_date_begin']));
                    } elseif (!empty($ssFilter['filter_date_end'])) {
                        $select->where->lessThanOrEqualTo(TABLE_CONTRACT . '.' . $date_type, $date->formatToData($ssFilter['filter_date_end'] . ' 23:59:59'));
                    }
                }

                if(!empty($ssFilter['filter_sale_branch'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.sale_branch_id', $ssFilter['filter_sale_branch']);
                }


                if(!empty($ssFilter['filter_sale_group'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.sale_group_id', $ssFilter['filter_sale_group']);
                }

                if(!empty($ssFilter['filter_marketer_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.marketer_id', $ssFilter['filter_marketer_id']);
                }

                if(!empty($ssFilter['filter_user'])) {
//                    $select -> where -> equalTo(TABLE_CONTRACT .'.user_id', $ssFilter['filter_user']);
                    $select -> where -> NEST
                        -> equalTo(TABLE_CONTRACT .'.user_id', $ssFilter['filter_user'])
                        ->Or
                        -> equalTo(TABLE_CONTRACT .'.created_by', $ssFilter['filter_user'])
                        ->Or
                        -> equalTo(TABLE_CONTRACT .'.care_id', $ssFilter['filter_user'])
                        -> UNNEST;
                }

                if(!empty($ssFilter['filter_delivery_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.delivery_id', $ssFilter['filter_delivery_id']);
                }

                if(!empty($ssFilter['filter_gd_ids'])) {
                    $select -> where -> in(TABLE_CONTRACT .'.delivery_id', $ssFilter['filter_gd_ids']);
                }

                if(!empty($ssFilter['filter_status_type'])) {
                    if(!empty($ssFilter['filter_status'])) {
                        $select -> where -> equalTo(TABLE_CONTRACT .'.'.$ssFilter['filter_status_type'], $ssFilter['filter_status']);
                    }
                }

                if(!empty($ssFilter['filter_unit_transport'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.unit_transport', $ssFilter['filter_unit_transport']);
                    $select -> where -> isNotNull(TABLE_CONTRACT .'.ghtk_code');
                }
                if(!empty($ssFilter['filter_production_type_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.production_type_id', $ssFilter['filter_production_type_id']);
                }

                if(!empty($ssFilter['filter_product'])) {
                    foreach($ssFilter['filter_product'] as $key => $value){
                        $select -> where -> like(TABLE_CONTRACT .'.options', '%'.$value.'%');
                    }
//                    $select -> where -> equalTo(TABLE_CONTRACT .'.total_product', count($ssFilter['filter_product']));
                }

                if(!empty($ssFilter['filter_category'])) {
                    $select -> where -> like(TABLE_CONTRACT .'.options', '%'.$ssFilter['filter_category'].'%');
                }

                if (!empty($ssFilter['filter_send_ghtk'])) {
                    if($ssFilter['filter_send_ghtk'] == -1) {
                        $select -> where -> NEST
                            -> isNull(TABLE_CONTRACT .'.ghtk_code')
                            ->OR
                            -> equalTo(TABLE_CONTRACT .'.ghtk_code', '')
                            -> UNNEST;
                        $select -> where -> notEqualTo(TABLE_CONTRACT .'.status_id', HUY_SALES);
                    } else {
                        $select -> where -> isNotNull(TABLE_CONTRACT .'.ghtk_code');
                    }
                }

                if (!empty($ssFilter['filter_care_status'])) {
                    if($ssFilter['filter_care_status'] == -1) {
                        $select -> where -> NEST
                            -> isNull(TABLE_CONTRACT .'.care_id')
                            ->OR
                            -> equalTo(TABLE_CONTRACT .'.care_id', '')
                            -> UNNEST;
                    } else {
                        $select -> where -> isNotNull(TABLE_CONTRACT .'.care_id');
                    }
                }

                if (!empty($ssFilter['filter_marketer_status'])) {
                    if($ssFilter['filter_marketer_status'] == -1) {
                        $select -> where -> NEST
                            -> isNull(TABLE_CONTRACT .'.marketer_id')
                            ->OR
                            -> equalTo(TABLE_CONTRACT .'.marketer_id', '')
                            -> UNNEST;
                    } else {
                        $select -> where -> isNotNull(TABLE_CONTRACT .'.marketer_id');
                    }
                }

                if (!empty($ssFilter['filter_update_kov_false'])) {
                    if($ssFilter['filter_update_kov_false'] == -1) {
                        $select -> where -> NEST
                            -> equalTo(TABLE_CONTRACT .'.shipped', 1)
                            ->AND
                            -> in(TABLE_CONTRACT .'.ghtk_status', [3,4,5,6,9,10,11,13,20,21,123,45,49,410])
                            -> UNNEST;
                    } else {
                        $select -> where -> NEST
                            -> equalTo(TABLE_CONTRACT .'.shipped', 0)
                            ->AND
                            -> in(TABLE_CONTRACT .'.ghtk_status', [3,4,5,6,9,10,11,13,20,21,123,45,49,410])
                            -> UNNEST;
                    }
                }

                if (!empty($ssFilter['filter_coincider'])) {
                    if($ssFilter['filter_coincider'] == -1) {
                        $select -> where -> NEST
                            -> isNull(TABLE_CONTRACT .'.coincider_code')
                            ->OR
                            -> equalTo(TABLE_CONTRACT .'.coincider_code', '')
                            -> UNNEST;
                    } else {
                        $select -> where -> isNotNull(TABLE_CONTRACT .'.coincider_code');
                    }
                }

                if (!empty($ssFilter['filter_returned'])) {
                    if($ssFilter['filter_returned'] == -1) {
                        $select -> where -> equalTo(TABLE_CONTRACT .'.returned', 0);
                    } else {
                        $select -> where -> equalTo(TABLE_CONTRACT .'.returned', 1);
                    }
                }

                if(!empty($ssFilter['filter_shipper_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.ghtk_code', $ssFilter['filter_shipper_id']);
                }
            })->current();
	    }

	    if($options['task'] == 'list-all') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
				$number     = new \ZendX\Functions\Number();
				$userInfo = new \ZendX\System\UserInfo();
				$permission = $userInfo->getPermissionOfUser();
				$permissions = explode(',', $permission['permission_ids']);
				$userInfo = $userInfo->getUserInfo();

                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
                $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_CONTRACT .'.contact_id',
                    array(
                        'contact_phone' => 'phone',
                        'contact_name' => 'name',
                        'contact_date' => 'date',
                        'contact_email' => 'email',
                        'contact_sex' => 'sex',
                        'contact_contract_number' => 'contract_number',
                        'contact_birthday' => 'birthday',
                        'contact_birthday_year' => 'birthday_year',
                        'contact_location_city_id' => 'location_city_id',
                        'contact_location_district_id' => 'location_district_id',
                        'contact_options' => 'options',
                    ), 'inner');
                $select -> order(array(TABLE_CONTRACT .'.date' => 'DESC'));

                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $filter_keyword = trim($ssFilter['filter_keyword']);
                    $select -> where -> NEST
                        -> like(TABLE_CONTRACT. '.name', '%'. $filter_keyword .'%')
                        ->Or
                        -> like(TABLE_CONTRACT. '.phone', '%'. $filter_keyword .'%')
                        ->Or
                        -> equalTo(TABLE_CONTRACT. '.code', $filter_keyword) // mã đơn
                        ->Or
                        -> like(TABLE_CONTRACT. '.ghtk_code', '%'. $filter_keyword .'%') // mã đơn ghtk
                        -> UNNEST;

                }
                $date_type = 'date';
                if(!empty($ssFilter['filter_date_type'])) {
                    $date_type = $ssFilter['filter_date_type'];
                }
                if($date_type == 'not_shipped'){
                    $select -> where -> NEST
                        -> isNull(TABLE_CONTRACT .'.shipped_date')
                        ->OR
                        -> equalTo(TABLE_CONTRACT .'.shipped_date', '')
                        -> UNNEST;
                }
                else {
                    if (!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                        $select->where->NEST
                            ->greaterThanOrEqualTo(TABLE_CONTRACT . '.' . $date_type, $date->formatToData($ssFilter['filter_date_begin']))
                            ->AND
                            ->lessThanOrEqualTo(TABLE_CONTRACT . '.' . $date_type, $date->formatToData($ssFilter['filter_date_end'] . ' 23:59:59'))
                            ->UNNEST;
                    } elseif (!empty($ssFilter['filter_date_begin'])) {
                        $select->where->greaterThanOrEqualTo(TABLE_CONTRACT . '.' . $date_type, $date->formatToData($ssFilter['filter_date_begin']));
                    } elseif (!empty($ssFilter['filter_date_end'])) {
                        $select->where->lessThanOrEqualTo(TABLE_CONTRACT . '.' . $date_type, $date->formatToData($ssFilter['filter_date_end'] . ' 23:59:59'));
                    }
                }

                if(!empty($ssFilter['filter_delete'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.delete', 1);
                }

                if(!empty($ssFilter['filter_sale_branch'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.sale_branch_id', $ssFilter['filter_sale_branch']);
                }


                if(!empty($ssFilter['filter_sale_group'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.sale_group_id', $ssFilter['filter_sale_group']);
                }

                if(!empty($ssFilter['filter_marketer_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.marketer_id', $ssFilter['filter_marketer_id']);
                }

                if(!empty($ssFilter['filter_user'])) {
//                    $select -> where -> equalTo(TABLE_CONTRACT .'.user_id', $ssFilter['filter_user']);
                    $select -> where -> NEST
                        -> equalTo(TABLE_CONTRACT .'.user_id', $ssFilter['filter_user'])
                        ->Or
                        -> equalTo(TABLE_CONTRACT .'.created_by', $ssFilter['filter_user'])
                        ->Or
                        -> equalTo(TABLE_CONTRACT .'.care_id', $ssFilter['filter_user'])
                        -> UNNEST;
                }

                if(!empty($ssFilter['filter_delivery_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.delivery_id', $ssFilter['filter_delivery_id']);
                }

                if(!empty($ssFilter['filter_gd_ids'])) {
                    $select -> where -> in(TABLE_CONTRACT .'.delivery_id', $ssFilter['filter_gd_ids']);
                }

                if(!empty($ssFilter['filter_status_type'])) {
                    if(!empty($ssFilter['filter_status'])) {
                        $select -> where -> equalTo(TABLE_CONTRACT .'.'.$ssFilter['filter_status_type'], $ssFilter['filter_status']);
                    }
                }

                if(!empty($ssFilter['filter_unit_transport'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.unit_transport', $ssFilter['filter_unit_transport']);
                    $select -> where -> isNotNull(TABLE_CONTRACT .'.ghtk_code');
                }
                if(!empty($ssFilter['filter_production_type_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.production_type_id', $ssFilter['filter_production_type_id']);
                }

                if(!empty($ssFilter['filter_product'])) {
                    foreach($ssFilter['filter_product'] as $key => $value){
                        $select -> where -> like(TABLE_CONTRACT .'.options', '%'.$value.'%');
                    }
                    $select -> where -> equalTo(TABLE_CONTRACT .'.total_product', count($ssFilter['filter_product']));
                }

                if(!empty($ssFilter['filter_category'])) {
                    $select -> where -> like(TABLE_CONTRACT .'.options', '%'.$ssFilter['filter_category'].'%');
                }

                if (!empty($ssFilter['filter_send_ghtk'])) {
                    if($ssFilter['filter_send_ghtk'] == -1) {
                        $select -> where -> NEST
                            -> isNull(TABLE_CONTRACT .'.ghtk_code')
                            ->OR
                            -> equalTo(TABLE_CONTRACT .'.ghtk_code', '')
                            -> UNNEST;
                        $select -> where -> notEqualTo(TABLE_CONTRACT .'.status_id', HUY_SALES);
                    } else {
                        $select -> where -> isNotNull(TABLE_CONTRACT .'.ghtk_code');
                    }
                }

                if (!empty($ssFilter['filter_care_status'])) {
                    if($ssFilter['filter_care_status'] == -1) {
                        $select -> where -> NEST
                            -> isNull(TABLE_CONTRACT .'.care_id')
                            ->OR
                            -> equalTo(TABLE_CONTRACT .'.care_id', '')
                            -> UNNEST;
                    } else {
                        $select -> where -> isNotNull(TABLE_CONTRACT .'.care_id');
                    }
                }

                if (!empty($ssFilter['filter_marketer_status'])) {
                    if($ssFilter['filter_marketer_status'] == -1) {
                        $select -> where -> NEST
                            -> isNull(TABLE_CONTRACT .'.marketer_id')
                            ->OR
                            -> equalTo(TABLE_CONTRACT .'.marketer_id', '')
                            -> UNNEST;
                    } else {
                        $select -> where -> isNotNull(TABLE_CONTRACT .'.marketer_id');
                    }
                }

                if (!empty($ssFilter['filter_update_kov_false'])) {
                    if($ssFilter['filter_update_kov_false'] == -1) {
                        $select -> where -> NEST
                            -> equalTo(TABLE_CONTRACT .'.shipped', 1)
                            ->AND
                            -> in(TABLE_CONTRACT .'.ghtk_status', [3,4,5,6,9,10,11,13,20,21,123,45,49,410])
                            -> UNNEST;
                    } else {
                        $select -> where -> NEST
                            -> equalTo(TABLE_CONTRACT .'.shipped', 0)
                            ->AND
                            -> in(TABLE_CONTRACT .'.ghtk_status', [3,4,5,6,9,10,11,13,20,21,123,45,49,410])
                            -> UNNEST;
                    }
                }

                if (!empty($ssFilter['filter_coincider'])) {
                    if($ssFilter['filter_coincider'] == -1) {
                        $select -> where -> NEST
                            -> isNull(TABLE_CONTRACT .'.coincider_code')
                            ->OR
                            -> equalTo(TABLE_CONTRACT .'.coincider_code', '')
                            -> UNNEST;
                    } else {
                        $select -> where -> isNotNull(TABLE_CONTRACT .'.coincider_code');
                    }
                }

                if (!empty($ssFilter['filter_returned'])) {
                    if($ssFilter['filter_returned'] == -1) {
                        $select -> where -> equalTo(TABLE_CONTRACT .'.returned', 0);
                    } else {
                        $select -> where -> equalTo(TABLE_CONTRACT .'.returned', 1);
                    }
                }

                if(!empty($ssFilter['filter_shipper_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.ghtk_code', $ssFilter['filter_shipper_id']);
                }
            })->current();
	    }

        if($options['task'] == 'list-production-item') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $paginator  = $arrParam['paginator'];
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
                $number     = new \ZendX\Functions\Number();
                $userInfo = new \ZendX\System\UserInfo();
                $permission = $userInfo->getPermissionOfUser();
                $permissions = explode(',', $permission['permission_ids']);
                $userInfo = $userInfo->getUserInfo();

                $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_CONTRACT .'.contact_id',
                    array(
                        'contact_phone' => 'phone',
                        'contact_name' => 'name',
                        'contact_email' => 'email',
                        'contact_sex' => 'sex',
                        'contact_birthday' => 'birthday',
                        'contact_birthday_year' => 'birthday_year',
                        'contact_location_city_id' => 'location_city_id',
                        'contact_location_district_id' => 'location_district_id',
                        'contact_options' => 'options',
                    ), 'inner');
                $select -> order(array(TABLE_CONTRACT .'.date' => 'DESC'));

                $select -> where-> NEST
                    -> notEqualTo(TABLE_CONTRACT .'.status_id', HUY_SALES)
                    ->Or
                    -> isNull( TABLE_CONTRACT .'.status_id')
                    -> UNNEST;

                if(!isset($options['paginator']) || $options['paginator'] == true) {
                    $select -> limit($paginator['itemCountPerPage'])
                        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }

                // Đơn hàng chưa xóa có trạng thái = 0
                $select -> where -> equalTo(TABLE_CONTRACT .'.delete', 0);

                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $filter_keyword = trim($ssFilter['filter_keyword']);
                    if(strlen($number->formatToPhone($filter_keyword)) >= 10) {
                        $select -> where -> equalTo(TABLE_CONTACT. '.phone', $number->formatToPhone($filter_keyword));
                    } elseif (filter_var($filter_keyword, FILTER_VALIDATE_EMAIL)) {
                        $select -> where -> equalTo(TABLE_CONTACT. '.email', $filter_keyword);
                    } else {
                        $select -> where -> NEST
                            -> like(TABLE_CONTACT. '.name', '%'. $filter_keyword .'%')
                            ->Or
                            -> equalTo(TABLE_CONTRACT. '.code', $filter_keyword) // mã đơn
                            ->Or
                            -> equalTo(TABLE_CONTRACT. '.bill_code', $filter_keyword) // mã vận đơn
                            ->Or
                            -> like(TABLE_CONTRACT. '.options', '%'. $filter_keyword .'%')
                            -> UNNEST;
                    }
                }

                if( $ssFilter['filter_date_type'] == 'date_debt') {
                    if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                        $select -> join(TABLE_BILL, TABLE_BILL .'.contract_id = '. TABLE_CONTRACT .'.id', array( 'bill_date' => new \Zend\Db\Sql\Expression('GROUP_CONCAT('. TABLE_BILL .'.date)')), 'inner');
                        $select -> group(TABLE_CONTRACT .'.id');
                        $select -> where -> NEST
                            -> greaterThanOrEqualTo(TABLE_BILL .'.date', $date->formatToData($ssFilter['filter_date_begin']))
                            ->AND
                            -> lessThanOrEqualTo(TABLE_BILL .'.date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                            ->AND
                            -> lessThan(TABLE_CONTRACT .'.date', $date->formatToData($ssFilter['filter_date_begin']) . ' 00:00:00')
                            -> UNNEST;
                    }
                } else {
                    if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                        $select -> where -> NEST
                            -> greaterThanOrEqualTo(TABLE_CONTRACT .'.'.$ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_begin']))
                            ->AND
                            -> lessThanOrEqualTo(TABLE_CONTRACT .'.'.$ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                            -> UNNEST;
                    } elseif (!empty($ssFilter['filter_date_begin'])) {
                        $select->where->greaterThanOrEqualTo(TABLE_CONTRACT .'.'.$ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_begin']));
                    } elseif (!empty($ssFilter['filter_date_end'])) {
                        $select->where->lessThanOrEqualTo(TABLE_CONTRACT .'.'.$ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                    }
                }

                if(!empty($ssFilter['filter_sale_branch'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.sale_branch_id', $ssFilter['filter_sale_branch']);
                }

                if(!empty($ssFilter['filter_sale_group'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.sale_group_id', $ssFilter['filter_sale_group']);
                }

                if(!empty($ssFilter['filter_user'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.user_id', $ssFilter['filter_user']);
                }

                if(!empty($ssFilter['filter_shipper_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.shipper_id', $ssFilter['filter_shipper_id']);
                }

                if(!empty($ssFilter['filter_coincider']) && $ssFilter['filter_coincider'] == 'yes') {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.coincider_status', $ssFilter['filter_coincider']);
                }
                if(!empty($ssFilter['filter_coincider']) && $ssFilter['filter_coincider'] == 'no') {
                    $select -> where -> notEqualTo(TABLE_CONTRACT .'.coincider_status', 'yes');
                }

                if(isset($ssFilter['filter_status_store']) && $ssFilter['filter_status_store'] != '') {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.status_store', $ssFilter['filter_status_store']);
                }

                if(isset($ssFilter['filter_status_shipped']) && $ssFilter['filter_status_shipped'] != '') {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.shipped', $ssFilter['filter_status_shipped']);
                }

                if(!empty($ssFilter['filter_status_type'])) {
                    if(!empty($ssFilter['filter_status'])) {
                        $select -> where -> equalTo(TABLE_CONTRACT .'.'.$ssFilter['filter_status_type'], $ssFilter['filter_status']);
                    }
                }

                if(!empty($ssFilter['filter_production_type_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.production_type_id', $ssFilter['filter_production_type_id']);
                }
                if(isset($ssFilter['filter_status_guarantee_id']) && $ssFilter['filter_status_guarantee_id'] != '') {
                    if ($ssFilter['filter_status_guarantee_id'] == 1){
                        $select -> where -> equalTo(TABLE_CONTRACT .'.status_guarantee_id', 1);
                    }
                    else{
                        $select -> where -> notEqualTo(TABLE_CONTRACT .'.status_guarantee_id', 1);
                    }
                }

                if(!empty($ssFilter['filter_product_type'])) {
                    $select -> join(TABLE_PRODUCT, TABLE_PRODUCT .'.id='. TABLE_CONTRACT .'.product_id', array('product_type' => 'type'), 'inner');
                    $select -> where -> equalTo(TABLE_PRODUCT .'.type', $ssFilter['filter_product_type']);
                }

                if(!empty($ssFilter['filter_product'])) {
                    $select -> where -> like(TABLE_CONTRACT .'.options', '%'.$ssFilter['filter_product'].'%');
                }

                if(!empty($ssFilter['filter_carpet_color'])) {
                    $select -> where -> like(TABLE_CONTRACT .'.options', '%'.$ssFilter['filter_carpet_color'].'%');
                }

                if(!empty($ssFilter['filter_tangled_color'])) {
                    $select -> where -> like(TABLE_CONTRACT .'.options', '%'.$ssFilter['filter_tangled_color'].'%');
                }

                if(!empty($ssFilter['filter_flooring'])) {
                    $select -> where -> like(TABLE_CONTRACT .'.options', '%'.$ssFilter['filter_flooring'].'%');
                }

                if(!empty($ssFilter['filter_technical_id'])) {
                    $select -> where -> like(TABLE_CONTRACT .'.options', '%'.$ssFilter['filter_technical_id'].'%');
                }

                if(!empty($ssFilter['filter_tailors_id'])) {
                    $select -> where -> like(TABLE_CONTRACT .'.options', '%'.$ssFilter['filter_tailors_id'].'%');
                }

                if (!empty($ssFilter['filter_bill_code'])) {
                    if($ssFilter['filter_bill_code'] == 1) {
                        $select -> where -> isNull(TABLE_CONTRACT .'.bill_code');
                    } else {
                        $select -> where -> isNotNull(TABLE_CONTRACT .'.bill_code');
                    }
                }

                if (!empty($ssFilter['filter_guarantee']) AND $ssFilter['filter_guarantee'] == 1) {
                    $select -> where -> isNull(TABLE_CONTRACT .'.guarantee_date');
                } elseif (!empty($ssFilter['filter_guarantee']) AND $ssFilter['filter_guarantee'] == 2){
                    $select -> where -> isNotNull(TABLE_CONTRACT .'.guarantee_date');
                }

                if(!empty($ssFilter['filter_debt'])) {
                    if($ssFilter['filter_debt'] == 'debt_on') {
                        $select -> where -> greaterThan(TABLE_CONTRACT .'.price_owed', 0);
                    } elseif ($ssFilter['filter_debt'] == 'debt_off') {
                        $select -> where -> equalTo(TABLE_CONTRACT .'.price_owed', 0);
                    } elseif ($ssFilter['filter_debt'] == 'debt_old') {
                        $select -> where -> NEST
                            -> greaterThan(TABLE_CONTRACT .'.price_owed', 0)
                            ->AND
                            -> lessThan(TABLE_CONTRACT .'.date', date('01/m/Y'))
                            -> UNNEST;
                    } elseif ($ssFilter['filter_debt'] == 'debt_new') {
                        $select -> where -> NEST
                            -> greaterThan(TABLE_CONTRACT .'.price_owed', 0)
                            ->AND
                            -> greaterThanOrEqualTo(TABLE_CONTRACT .'.date', date('01/m/Y'))
                            -> UNNEST;
                    }
                }
            })->current();
        }

        if($options['task'] == 'count-contract-by-contact') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
                $select -> where -> equalTo('contact_id', $arrParam['contact_id']);
            })->current();
        }

	    return $result->count;
	}
	
	public function listItem($arrParam = null, $options = null){
		if($options['task'] == 'list-item') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $paginator  = $arrParam['paginator'];
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
				$number     = new \ZendX\Functions\Number();
				$userInfo = new \ZendX\System\UserInfo();
				$permission = $userInfo->getPermissionOfUser();

                if(!isset($options['paginator']) || $options['paginator'] == true) {
                    $select -> limit($paginator['itemCountPerPage'])
                        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }
                
    			$select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_CONTRACT .'.contact_id', 
    			             array(
    			                 'contact_phone' => 'phone',
    			                 'contact_name' => 'name',
    			                 'contact_date' => 'date',
    			                 'contact_email' => 'email',
    			                 'contact_contract_first_date' => 'contract_first_date',
    			                 'contact_sex' => 'sex',
    			                 'contact_contract_number' => 'contract_number',
    			                 'contact_birthday' => 'birthday',
    			                 'contact_birthday_year' => 'birthday_year',
    			                 'contact_location_city_id' => 'location_city_id',
    			                 'contact_location_district_id' => 'location_district_id',
    			                 'contact_options' => 'options',
    			             ), 'inner');
    			$select -> order(array(TABLE_CONTRACT .'.index' => 'DESC'));

    			// Đơn hàng chưa xóa có trạng thái = 0
                $select -> where -> equalTo(TABLE_CONTRACT .'.delete', 0);

                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $filter_keyword = trim($ssFilter['filter_keyword']);
                    $select -> where -> NEST
                        -> like(TABLE_CONTRACT. '.name', '%'. $filter_keyword .'%')
                        ->Or
                        -> like(TABLE_CONTRACT. '.phone', '%'. $filter_keyword .'%')
                        ->Or
                        -> equalTo(TABLE_CONTRACT. '.code', $filter_keyword) // mã đơn
                        ->Or
                        -> like(TABLE_CONTRACT. '.ghtk_code', '%'. $filter_keyword .'%') // mã đơn ghtk
                        -> UNNEST;

                }

                $date_type = 'date';
                if(!empty($ssFilter['filter_date_type'])) {
                    $date_type = $ssFilter['filter_date_type'];
                }
                if($date_type == 'not_shipped'){
                    $select -> where -> NEST
                        -> isNull(TABLE_CONTRACT .'.shipped_date')
                        ->OR
                        -> equalTo(TABLE_CONTRACT .'.shipped_date', '')
                        -> UNNEST;
                }
                else{
                    if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                        $select -> where -> NEST
                            -> greaterThanOrEqualTo(TABLE_CONTRACT .'.'.$date_type, $date->formatToData($ssFilter['filter_date_begin']))
                            ->AND
                            -> lessThanOrEqualTo(TABLE_CONTRACT .'.'.$date_type, $date->formatToData($ssFilter['filter_date_end']. ' 23:59:59') )
                            -> UNNEST;
                    } elseif (!empty($ssFilter['filter_date_begin'])) {
                        $select->where->greaterThanOrEqualTo(TABLE_CONTRACT .'.'.$date_type, $date->formatToData($ssFilter['filter_date_begin']));
                    } elseif (!empty($ssFilter['filter_date_end'])) {
                        $select->where->lessThanOrEqualTo(TABLE_CONTRACT .'.'.$date_type, $date->formatToData($ssFilter['filter_date_end']. ' 23:59:59') );
                    }
                }

                if(!empty($ssFilter['filter_marketer_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.marketer_id', $ssFilter['filter_marketer_id']);
                }
    			
	            if(!empty($ssFilter['filter_sale_branch'])) {
    			    $select -> where -> equalTo(TABLE_CONTRACT .'.sale_branch_id', $ssFilter['filter_sale_branch']);
    			}
    			
    			if(!empty($ssFilter['filter_sale_group'])) {
    			    $select -> where -> equalTo(TABLE_CONTRACT .'.sale_group_id', $ssFilter['filter_sale_group']);
    			}
    			if(!empty($ssFilter['filter_production_type_id'])) {
    			    $select -> where -> equalTo(TABLE_CONTRACT .'.production_type_id', $ssFilter['filter_production_type_id']);
    			}

                if(!empty($ssFilter['filter_user'])) {
//                    $select -> where -> equalTo(TABLE_CONTRACT .'.user_id', $ssFilter['filter_user']);
                    $select -> where -> NEST
                        -> equalTo(TABLE_CONTRACT .'.user_id', $ssFilter['filter_user'])
                        ->Or
                        -> equalTo(TABLE_CONTRACT .'.created_by', $ssFilter['filter_user'])
                        ->Or
                        -> equalTo(TABLE_CONTRACT .'.care_id', $ssFilter['filter_user'])
                        -> UNNEST;
                }

                if(!empty($ssFilter['filter_delivery_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.delivery_id', $ssFilter['filter_delivery_id']);
                }

                if(!empty($ssFilter['filter_gd_ids'])) {
                    $select -> where -> in(TABLE_CONTRACT .'.delivery_id', $ssFilter['filter_gd_ids']);
                }

                if(!empty($ssFilter['filter_status_type'])) {
                    if(!empty($ssFilter['filter_status'])) {
                        $select -> where -> equalTo(TABLE_CONTRACT .'.'.$ssFilter['filter_status_type'], $ssFilter['filter_status']);
                    }
                }

                if(!empty($ssFilter['filter_unit_transport'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.unit_transport', $ssFilter['filter_unit_transport']);
                    $select -> where -> isNotNull(TABLE_CONTRACT .'.ghtk_code');
                }

                if(!empty($ssFilter['filter_category'])) {
                    $select -> where -> like(TABLE_CONTRACT .'.options', '%'.$ssFilter['filter_category'].'%');
                }

                if(!empty($ssFilter['filter_product'])) {
                    foreach($ssFilter['filter_product'] as $key => $value){
                        $select -> where -> like(TABLE_CONTRACT .'.options', '%'.$value.'%');
                    }
//                    $select -> where -> equalTo(TABLE_CONTRACT .'.total_product', count($ssFilter['filter_product']));
                }

                if (!empty($ssFilter['filter_send_ghtk'])) {
                    if($ssFilter['filter_send_ghtk'] == -1) {
                        $select -> where -> NEST
                            -> isNull(TABLE_CONTRACT .'.ghtk_code')
                        ->OR
                            -> equalTo(TABLE_CONTRACT .'.ghtk_code', '')
                        -> UNNEST;
                        $select -> where -> notEqualTo(TABLE_CONTRACT .'.status_id', HUY_SALES);
                    } else {
                        $select -> where -> isNotNull(TABLE_CONTRACT .'.ghtk_code');
                    }
                }

                if (!empty($ssFilter['filter_care_status'])) {
                    if($ssFilter['filter_care_status'] == -1) {
                        $select -> where -> NEST
                            -> isNull(TABLE_CONTRACT .'.care_id')
                            ->OR
                            -> equalTo(TABLE_CONTRACT .'.care_id', '')
                            -> UNNEST;
                    } else {
                        $select -> where -> isNotNull(TABLE_CONTRACT .'.care_id');
                    }
                }

                if (!empty($ssFilter['filter_marketer_status'])) {
                    if($ssFilter['filter_marketer_status'] == -1) {
                        $select -> where -> NEST
                            -> isNull(TABLE_CONTRACT .'.marketer_id')
                            ->OR
                            -> equalTo(TABLE_CONTRACT .'.marketer_id', '')
                            -> UNNEST;
                    } else {
                        $select -> where -> isNotNull(TABLE_CONTRACT .'.marketer_id');
                    }
                }

                if (!empty($ssFilter['filter_update_kov_false'])) {
                    if($ssFilter['filter_update_kov_false'] == -1) {
                        $select -> where -> NEST
                            -> equalTo(TABLE_CONTRACT .'.shipped', 1)
                        ->AND
                            -> in(TABLE_CONTRACT .'.ghtk_status', [3,4,5,6,9,10,11,13,20,21,123,45,49,410])
                        -> UNNEST;
                    } else {
                        $select -> where -> NEST
                            -> equalTo(TABLE_CONTRACT .'.shipped', 0)
                            ->AND
                            -> in(TABLE_CONTRACT .'.ghtk_status', [3,4,5,6,9,10,11,13,20,21,123,45,49,410])
                            -> UNNEST;
                    }
                }

                if (!empty($ssFilter['filter_coincider'])) {
                    if($ssFilter['filter_coincider'] == -1) {
                        $select -> where -> NEST
                            -> isNull(TABLE_CONTRACT .'.coincider_code')
                            ->OR
                            -> equalTo(TABLE_CONTRACT .'.coincider_code', '')
                            -> UNNEST;
                    } else {
                        $select -> where -> isNotNull(TABLE_CONTRACT .'.coincider_code');
                    }
                }

                if (!empty($ssFilter['filter_returned'])) {
                    if($ssFilter['filter_returned'] == -1) {
                        $select -> where -> equalTo(TABLE_CONTRACT .'.returned', 0);
                    } else {
                        $select -> where -> equalTo(TABLE_CONTRACT .'.returned', 1);
                    }
                }

                if(!empty($ssFilter['filter_shipper_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.ghtk_code', $ssFilter['filter_shipper_id']);
                }
//                echo "<pre>";
//                print_r($select->getSqlString());
//                echo "</pre>";
    		});
		}

        if($options['task'] == 'list-params') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){

                $select -> order(array(TABLE_CONTRACT .'.date' => 'DESC'));

                $select -> where -> equalTo(TABLE_CONTRACT .'.delete', 0);

                if(!empty($arrParam['phone'])) {
                    $select -> where -> equalTo('phone', $arrParam['phone']);
                }
                if(!empty($arrParam['ghtk_status_not_success'])) {
                    $select -> where ->NEST -> notIn('ghtk_status', [5,6]) ->OR -> isNull('ghtk_status') ->OR -> equalTo('ghtk_status', '') -> UNNEST ;
                }
                if(!empty($arrParam['not_id'])) {
                    $select -> where -> notEqualTo('id', $arrParam['not_id']);
                }
            });
        }

        if($options['task'] == 'list-query') {
            $result = $this->tableGateway->getAdapter()->driver->getConnection()->execute($arrParam['query']);
        }

        if($options['task'] == 'list-ajax') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_CONTRACT .'.contact_id',
                    array(
                        'contact_phone' => 'phone',
                        'contact_name' => 'name',
                        'contact_email' => 'email',
                        'contact_birthday_' => 'birthday',
                        'contact_birthday_year' => 'birthday_year',
                        'contact_location_city_id' => 'location_city_id',
                        'contact_location_district_id' => 'location_district_id',
                        'contact_options' => 'options',
                    ), 'inner');

                if(!empty($arrParam['data']['contrach_success'])) {
                    $select -> order(array(TABLE_CONTRACT .'.created' => 'ASC'));
                }
                else{
                    $select -> order(array(TABLE_CONTRACT .'.date' => 'DESC'));
                }

                // Đơn hàng chưa xóa có trạng thái = 0
                $select -> where -> equalTo(TABLE_CONTRACT .'.delete', 0);

                if(!empty($arrParam['data']['contact_id'])) {
                    $select->where->equalTo(TABLE_CONTRACT .'.contact_id', $arrParam['data']['contact_id']);
                }

                if(!empty($arrParam['data']['phone'])) {
                    $select->where->equalTo(TABLE_CONTACT .'.phone', $arrParam['data']['phone']);
                }

                if(!empty($arrParam['data']['contract_id'])) {
                    $select->where->notEqualTo(TABLE_CONTRACT .'.id', $arrParam['data']['contract_id']);
                }
            });
        }

        if($options['task'] == 'list-production-item') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $paginator  = $arrParam['paginator'];
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
                $number     = new \ZendX\Functions\Number();
                $userInfo = new \ZendX\System\UserInfo();
                $permission = $userInfo->getPermissionOfUser();
                $permissions = explode(',', $permission['permission_ids']);
                $userInfo = $userInfo->getUserInfo();

                $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_CONTRACT .'.contact_id',
                    array(
                        'contact_phone' => 'phone',
                        'contact_name' => 'name',
                        'contact_email' => 'email',
                        'contact_sex' => 'sex',
                        'contact_birthday' => 'birthday',
                        'contact_birthday_year' => 'birthday_year',
                        'contact_location_city_id' => 'location_city_id',
                        'contact_location_district_id' => 'location_district_id',
                        'contact_options' => 'options',
                    ), 'inner');
                $select -> order(array(TABLE_CONTRACT .'.date' => 'DESC'));

                $select -> where-> NEST
                    -> notEqualTo(TABLE_CONTRACT .'.status_id', HUY_SALES)
                    ->Or
                    -> isNull( TABLE_CONTRACT .'.status_id')
                    -> UNNEST;

                if(!isset($options['paginator']) || $options['paginator'] == true) {
                    $select -> limit($paginator['itemCountPerPage'])
                        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }

                // Đơn hàng chưa xóa có trạng thái = 0
                $select -> where -> equalTo(TABLE_CONTRACT .'.delete', 0);

                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $filter_keyword = trim($ssFilter['filter_keyword']);
                    if(strlen($number->formatToPhone($filter_keyword)) >= 10) {
                        $select -> where -> equalTo(TABLE_CONTACT. '.phone', $number->formatToPhone($filter_keyword));
                    } elseif (filter_var($filter_keyword, FILTER_VALIDATE_EMAIL)) {
                        $select -> where -> equalTo(TABLE_CONTACT. '.email', $filter_keyword);
                    } else {
                        $select -> where -> NEST
                            -> like(TABLE_CONTACT. '.name', '%'. $filter_keyword .'%')
                            ->Or
                            -> equalTo(TABLE_CONTRACT. '.code', $filter_keyword) // mã đơn
                            ->Or
                            -> equalTo(TABLE_CONTRACT. '.bill_code', $filter_keyword) // mã vận đơn
                            ->Or
                            -> like(TABLE_CONTRACT. '.options', '%'. $filter_keyword .'%')
                            -> UNNEST;
                    }
                }

                if( $ssFilter['filter_date_type'] == 'date_debt') {
                    if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                        $select -> join(TABLE_BILL, TABLE_BILL .'.contract_id = '. TABLE_CONTRACT .'.id', array( 'bill_date' => new \Zend\Db\Sql\Expression('GROUP_CONCAT('. TABLE_BILL .'.date)')), 'inner');
                        $select -> group(TABLE_CONTRACT .'.id');
                        $select -> where -> NEST
                            -> greaterThanOrEqualTo(TABLE_BILL .'.date', $date->formatToData($ssFilter['filter_date_begin']))
                            ->AND
                            -> lessThanOrEqualTo(TABLE_BILL .'.date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                            ->AND
                            -> lessThan(TABLE_CONTRACT .'.date', $date->formatToData($ssFilter['filter_date_begin']) . ' 00:00:00')
                            -> UNNEST;
                    }
                } else {
                    if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                        $select -> where -> NEST
                            -> greaterThanOrEqualTo(TABLE_CONTRACT .'.'.$ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_begin']))
                            ->AND
                            -> lessThanOrEqualTo(TABLE_CONTRACT .'.'.$ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                            -> UNNEST;
                    } elseif (!empty($ssFilter['filter_date_begin'])) {
                        $select->where->greaterThanOrEqualTo(TABLE_CONTRACT .'.'.$ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_begin']));
                    } elseif (!empty($ssFilter['filter_date_end'])) {
                        $select->where->lessThanOrEqualTo(TABLE_CONTRACT .'.'.$ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                    }
                }

                if(!empty($ssFilter['filter_sale_branch'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.sale_branch_id', $ssFilter['filter_sale_branch']);
                }

                if(!empty($ssFilter['filter_sale_group'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.sale_group_id', $ssFilter['filter_sale_group']);
                }

                if(!empty($ssFilter['filter_user'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.user_id', $ssFilter['filter_user']);
                }

                if(!empty($ssFilter['filter_shipper_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.shipper_id', $ssFilter['filter_shipper_id']);
                }

                if(!empty($ssFilter['filter_coincider']) && $ssFilter['filter_coincider'] == 'yes') {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.coincider_status', $ssFilter['filter_coincider']);
                }
                if(!empty($ssFilter['filter_coincider']) && $ssFilter['filter_coincider'] == 'no') {
                    $select -> where -> notEqualTo(TABLE_CONTRACT .'.coincider_status', 'yes');
                }

                if(isset($ssFilter['filter_status_store']) && $ssFilter['filter_status_store'] != '') {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.status_store', $ssFilter['filter_status_store']);
                }

                if(isset($ssFilter['filter_status_shipped']) && $ssFilter['filter_status_shipped'] != '') {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.shipped', $ssFilter['filter_status_shipped']);
                }

                if(!empty($ssFilter['filter_status_type'])) {
                    if(!empty($ssFilter['filter_status'])) {
                        $select -> where -> equalTo(TABLE_CONTRACT .'.'.$ssFilter['filter_status_type'], $ssFilter['filter_status']);
                    }
                }

                if(!empty($ssFilter['filter_production_type_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.production_type_id', $ssFilter['filter_production_type_id']);
                }
                if(isset($ssFilter['filter_status_guarantee_id']) && $ssFilter['filter_status_guarantee_id'] != '') {
                    if ($ssFilter['filter_status_guarantee_id'] == 1){
                        $select -> where -> equalTo(TABLE_CONTRACT .'.status_guarantee_id', 1);
                    }
                    else{
                        $select -> where -> notEqualTo(TABLE_CONTRACT .'.status_guarantee_id', 1);
                    }
                }

                if(!empty($ssFilter['filter_product_type'])) {
                    $select -> join(TABLE_PRODUCT, TABLE_PRODUCT .'.id='. TABLE_CONTRACT .'.product_id', array('product_type' => 'type'), 'inner');
                    $select -> where -> equalTo(TABLE_PRODUCT .'.type', $ssFilter['filter_product_type']);
                }

                if(!empty($ssFilter['filter_product'])) {
                    $select -> where -> like(TABLE_CONTRACT .'.options', '%'.$ssFilter['filter_product'].'%');
                }

                if(!empty($ssFilter['filter_carpet_color'])) {
                    $select -> where -> like(TABLE_CONTRACT .'.options', '%'.$ssFilter['filter_carpet_color'].'%');
                }

                if(!empty($ssFilter['filter_tangled_color'])) {
                    $select -> where -> like(TABLE_CONTRACT .'.options', '%'.$ssFilter['filter_tangled_color'].'%');
                }

                if(!empty($ssFilter['filter_flooring'])) {
                    $select -> where -> like(TABLE_CONTRACT .'.options', '%'.$ssFilter['filter_flooring'].'%');
                }

                if(!empty($ssFilter['filter_technical_id'])) {
                    $select -> where -> like(TABLE_CONTRACT .'.options', '%'.$ssFilter['filter_technical_id'].'%');
                }

                if(!empty($ssFilter['filter_tailors_id'])) {
                    $select -> where -> like(TABLE_CONTRACT .'.options', '%'.$ssFilter['filter_tailors_id'].'%');
                }

                if (!empty($ssFilter['filter_bill_code'])) {
                    if($ssFilter['filter_bill_code'] == 1) {
                        $select -> where -> isNull(TABLE_CONTRACT .'.bill_code');
                    } else {
                        $select -> where -> isNotNull(TABLE_CONTRACT .'.bill_code');
                    }
                }

                if (!empty($ssFilter['filter_guarantee']) AND $ssFilter['filter_guarantee'] == 1) {
                    $select -> where -> isNull(TABLE_CONTRACT .'.guarantee_date');
                } elseif (!empty($ssFilter['filter_guarantee']) AND $ssFilter['filter_guarantee'] == 2){
                    $select -> where -> isNotNull(TABLE_CONTRACT .'.guarantee_date');
                }

                if(!empty($ssFilter['filter_debt'])) {
                    if($ssFilter['filter_debt'] == 'debt_on') {
                        $select -> where -> greaterThan(TABLE_CONTRACT .'.price_owed', 0);
                    } elseif ($ssFilter['filter_debt'] == 'debt_off') {
                        $select -> where -> equalTo(TABLE_CONTRACT .'.price_owed', 0);
                    } elseif ($ssFilter['filter_debt'] == 'debt_old') {
                        $select -> where -> NEST
                            -> greaterThan(TABLE_CONTRACT .'.price_owed', 0)
                            ->AND
                            -> lessThan(TABLE_CONTRACT .'.date', date('01/m/Y'))
                            -> UNNEST;
                    } elseif ($ssFilter['filter_debt'] == 'debt_new') {
                        $select -> where -> NEST
                            -> greaterThan(TABLE_CONTRACT .'.price_owed', 0)
                            ->AND
                            -> greaterThanOrEqualTo(TABLE_CONTRACT .'.date', date('01/m/Y'))
                            -> UNNEST;
                    }
                }

            });
        }

        if($options['task'] == 'list-print-multi') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_CONTRACT .'.contact_id',
                    array(
                        'contact_phone' => 'phone',
                        'contact_name' => 'name',
                        'contact_email' => 'email',
                        'contact_birthday_' => 'birthday',
                        'contact_birthday_year' => 'birthday_year',
                        'contact_location_city_id' => 'location_city_id',
                        'contact_location_district_id' => 'location_district_id',
                        'contact_options' => 'options',
                        'contact_license_plate' => 'license_plate',
                    ), 'inner');

                $select -> where -> in(TABLE_CONTRACT .'.id', $arrParam['ids']);

                // Đơn hàng chưa xóa có trạng thái = 0
                $select -> where -> equalTo(TABLE_CONTRACT .'.delete', 0);
            });
        }

        // Lấy ra danh sách đơn hàng cần cập nhật lại giá vốn khi giá vốn trên kiotviet thay đổi
        if($options['task'] == 'list-item-update-cost') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter   = $arrParam['ssFilter'];

                $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_CONTRACT .'.contact_id',
                    array(
                        'contact_phone' => 'phone',
                        'contact_name' => 'name',
                        'contact_date' => 'date',
                        'contact_email' => 'email',
                        'contact_sex' => 'sex',
                        'contact_contract_number' => 'contract_number',
                        'contact_birthday' => 'birthday',
                        'contact_birthday_year' => 'birthday_year',
                        'contact_location_city_id' => 'location_city_id',
                        'contact_location_district_id' => 'location_district_id',
                        'contact_options' => 'options',
                    ), 'inner');
                $select -> order(array(TABLE_CONTRACT .'.date' => 'DESC'));
                // Đơn hàng chưa xóa có trạng thái = 0
                $select -> where -> equalTo(TABLE_CONTRACT .'.delete', 0);

                if(!empty($ssFilter['filter_product_id'])) {
                    $select -> where -> like(TABLE_CONTRACT .'.options', '%'.$ssFilter['filter_product_id'].'%');
                }
                if(!empty($ssFilter['filter_sale_branch'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.sale_branch_id', $ssFilter['filter_sale_branch']);
                }
//                $select -> where -> equalTo(TABLE_CONTRACT .'.status_id', 'da-chot');
                $select -> where-> NEST
                    -> EqualTo(TABLE_CONTRACT .'.shipped_date', '')
                    ->Or
                    -> isNull( TABLE_CONTRACT .'.shipped_date')
                    -> UNNEST;
            });
        }

        if($options['task'] == 'list-all') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $paginator  = $arrParam['paginator'];
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
                $number     = new \ZendX\Functions\Number();
                $userInfo = new \ZendX\System\UserInfo();
                $permission = $userInfo->getPermissionOfUser();

                if(!isset($options['paginator']) || $options['paginator'] == true) {
                    $select -> limit($paginator['itemCountPerPage'])
                        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }

                $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_CONTRACT .'.contact_id',
                    array(
                        'contact_phone' => 'phone',
                        'contact_name' => 'name',
                        'contact_date' => 'date',
                        'contact_email' => 'email',
                        'contact_contract_first_date' => 'contract_first_date',
                        'contact_sex' => 'sex',
                        'contact_contract_number' => 'contract_number',
                        'contact_birthday' => 'birthday',
                        'contact_birthday_year' => 'birthday_year',
                        'contact_location_city_id' => 'location_city_id',
                        'contact_location_district_id' => 'location_district_id',
                        'contact_options' => 'options',
                    ), 'inner');
                $select -> order(array(TABLE_CONTRACT .'.index' => 'DESC'));

                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $filter_keyword = trim($ssFilter['filter_keyword']);
                    $select -> where -> NEST
                        -> like(TABLE_CONTRACT. '.name', '%'. $filter_keyword .'%')
                        ->Or
                        -> like(TABLE_CONTRACT. '.phone', '%'. $filter_keyword .'%')
                        ->Or
                        -> equalTo(TABLE_CONTRACT. '.code', $filter_keyword) // mã đơn
                        ->Or
                        -> like(TABLE_CONTRACT. '.ghtk_code', '%'. $filter_keyword .'%') // mã đơn ghtk
                        -> UNNEST;

                }

                $date_type = 'date';
                if(!empty($ssFilter['filter_date_type'])) {
                    $date_type = $ssFilter['filter_date_type'];
                }
                if($date_type == 'not_shipped'){
                    $select -> where -> NEST
                        -> isNull(TABLE_CONTRACT .'.shipped_date')
                        ->OR
                        -> equalTo(TABLE_CONTRACT .'.shipped_date', '')
                        -> UNNEST;
                }
                else{
                    if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                        $select -> where -> NEST
                            -> greaterThanOrEqualTo(TABLE_CONTRACT .'.'.$date_type, $date->formatToData($ssFilter['filter_date_begin']))
                            ->AND
                            -> lessThanOrEqualTo(TABLE_CONTRACT .'.'.$date_type, $date->formatToData($ssFilter['filter_date_end']. ' 23:59:59') )
                            -> UNNEST;
                    } elseif (!empty($ssFilter['filter_date_begin'])) {
                        $select->where->greaterThanOrEqualTo(TABLE_CONTRACT .'.'.$date_type, $date->formatToData($ssFilter['filter_date_begin']));
                    } elseif (!empty($ssFilter['filter_date_end'])) {
                        $select->where->lessThanOrEqualTo(TABLE_CONTRACT .'.'.$date_type, $date->formatToData($ssFilter['filter_date_end']. ' 23:59:59') );
                    }
                }

                if(!empty($ssFilter['filter_delete'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.delete', 1);
                }

                if(!empty($ssFilter['filter_marketer_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.marketer_id', $ssFilter['filter_marketer_id']);
                }

                if(!empty($ssFilter['filter_sale_branch'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.sale_branch_id', $ssFilter['filter_sale_branch']);
                }

                if(!empty($ssFilter['filter_sale_group'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.sale_group_id', $ssFilter['filter_sale_group']);
                }
                if(!empty($ssFilter['filter_production_type_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.production_type_id', $ssFilter['filter_production_type_id']);
                }

                if(!empty($ssFilter['filter_user'])) {
//                    $select -> where -> equalTo(TABLE_CONTRACT .'.user_id', $ssFilter['filter_user']);
                    $select -> where -> NEST
                        -> equalTo(TABLE_CONTRACT .'.user_id', $ssFilter['filter_user'])
                        ->Or
                        -> equalTo(TABLE_CONTRACT .'.created_by', $ssFilter['filter_user'])
                        ->Or
                        -> equalTo(TABLE_CONTRACT .'.care_id', $ssFilter['filter_user'])
                        -> UNNEST;
                }

                if(!empty($ssFilter['filter_delivery_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.delivery_id', $ssFilter['filter_delivery_id']);
                }

                if(!empty($ssFilter['filter_gd_ids'])) {
                    $select -> where -> in(TABLE_CONTRACT .'.delivery_id', $ssFilter['filter_gd_ids']);
                }

                if(!empty($ssFilter['filter_status_type'])) {
                    if(!empty($ssFilter['filter_status'])) {
                        $select -> where -> equalTo(TABLE_CONTRACT .'.'.$ssFilter['filter_status_type'], $ssFilter['filter_status']);
                    }
                }

                if(!empty($ssFilter['filter_unit_transport'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.unit_transport', $ssFilter['filter_unit_transport']);
                    $select -> where -> isNotNull(TABLE_CONTRACT .'.ghtk_code');
                }

                if(!empty($ssFilter['filter_category'])) {
                    $select -> where -> like(TABLE_CONTRACT .'.options', '%'.$ssFilter['filter_category'].'%');
                }

                if(!empty($ssFilter['filter_product'])) {
                    foreach($ssFilter['filter_product'] as $key => $value){
                        $select -> where -> like(TABLE_CONTRACT .'.options', '%'.$value.'%');
                    }
                    $select -> where -> equalTo(TABLE_CONTRACT .'.total_product', count($ssFilter['filter_product']));
                }

                if (!empty($ssFilter['filter_send_ghtk'])) {
                    if($ssFilter['filter_send_ghtk'] == -1) {
                        $select -> where -> NEST
                            -> isNull(TABLE_CONTRACT .'.ghtk_code')
                            ->OR
                            -> equalTo(TABLE_CONTRACT .'.ghtk_code', '')
                            -> UNNEST;
                        $select -> where -> notEqualTo(TABLE_CONTRACT .'.status_id', HUY_SALES);
                    } else {
                        $select -> where -> isNotNull(TABLE_CONTRACT .'.ghtk_code');
                    }
                }

                if (!empty($ssFilter['filter_care_status'])) {
                    if($ssFilter['filter_care_status'] == -1) {
                        $select -> where -> NEST
                            -> isNull(TABLE_CONTRACT .'.care_id')
                            ->OR
                            -> equalTo(TABLE_CONTRACT .'.care_id', '')
                            -> UNNEST;
                    } else {
                        $select -> where -> isNotNull(TABLE_CONTRACT .'.care_id');
                    }
                }

                if (!empty($ssFilter['filter_marketer_status'])) {
                    if($ssFilter['filter_marketer_status'] == -1) {
                        $select -> where -> NEST
                            -> isNull(TABLE_CONTRACT .'.marketer_id')
                            ->OR
                            -> equalTo(TABLE_CONTRACT .'.marketer_id', '')
                            -> UNNEST;
                    } else {
                        $select -> where -> isNotNull(TABLE_CONTRACT .'.marketer_id');
                    }
                }

                if (!empty($ssFilter['filter_update_kov_false'])) {
                    if($ssFilter['filter_update_kov_false'] == -1) {
                        $select -> where -> NEST
                            -> equalTo(TABLE_CONTRACT .'.shipped', 1)
                            ->AND
                            -> in(TABLE_CONTRACT .'.ghtk_status', [3,4,5,6,9,10,11,13,20,21,123,45,49,410])
                            -> UNNEST;
                    } else {
                        $select -> where -> NEST
                            -> equalTo(TABLE_CONTRACT .'.shipped', 0)
                            ->AND
                            -> in(TABLE_CONTRACT .'.ghtk_status', [3,4,5,6,9,10,11,13,20,21,123,45,49,410])
                            -> UNNEST;
                    }
                }

                if (!empty($ssFilter['filter_coincider'])) {
                    if($ssFilter['filter_coincider'] == -1) {
                        $select -> where -> NEST
                            -> isNull(TABLE_CONTRACT .'.coincider_code')
                            ->OR
                            -> equalTo(TABLE_CONTRACT .'.coincider_code', '')
                            -> UNNEST;
                    } else {
                        $select -> where -> isNotNull(TABLE_CONTRACT .'.coincider_code');
                    }
                }

                if (!empty($ssFilter['filter_returned'])) {
                    if($ssFilter['filter_returned'] == -1) {
                        $select -> where -> equalTo(TABLE_CONTRACT .'.returned', 0);
                    } else {
                        $select -> where -> equalTo(TABLE_CONTRACT .'.returned', 1);
                    }
                }

                if(!empty($ssFilter['filter_shipper_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.ghtk_code', $ssFilter['filter_shipper_id']);
                }
//                echo "<pre>";
//                print_r($select->getSqlString());
//                echo "</pre>";
            });
        }

        return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
	
		if($options == null) {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
			    $select -> where -> equalTo('id', $arrParam['id']);
    		})->toArray();
		}
	
		if($options['task'] == 'by-code') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
				$select -> where -> equalTo('code', $arrParam['code']);
//				$select -> where -> equalTo('hidden', 0);
				if (!empty($arrParam['status_acounting_id'])) {
					$select -> where -> equalTo('status_acounting_id', $arrParam['status_acounting_id']);
				}
    		})->toArray();
		}

		if($options['task'] == 'by-code-all') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
				$select -> where -> equalTo('code', $arrParam['code']);
    		})->toArray();
		}

		if($options['task'] == 'by-bill-code') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
			    $select -> where -> equalTo('bill_code', $arrParam['bill_code']);
			})->toArray();
		}

		if($options['task'] == 'ghtk-code') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
			    $select -> where -> equalTo('ghtk_code', $arrParam['ghtk_code']);
			})->toArray();
		}
			
		return current($result);
	}
	
	public function saveItem($arrParam = null, $options = null){
        $arrData = $arrParam['data'];
        $arrItem = $arrParam['item'];
        $sales_manager = $arrParam['sales_manager'];

	    $date     = new \ZendX\Functions\Date();
	    $number   = new \ZendX\Functions\Number();
	    $gid      = new \ZendX\Functions\Gid();
		
		if ($options['task'] == 'update-options') {
            $id = $arrData['id'];
            $data['options'] = serialize($arrData['options']);
            $this->tableGateway->update($data, array('id' => $id));
            return true;
		}

//		if ($options['task'] == 'update-code') {
//            $id = $arrData;
//            $result = $this->getItem(array('id' => $id));
//            $index = $result['index'];
//
//            if (strlen($index) <= 6) {
//                $i = 8 - strlen($index);
//                $data['code'] = substr_replace("DH000000",$index, $i);
//                $this->tableGateway->update($data, array('id' => $id));
//            }else{
//                $data['code'] = substr_replace("DH000000",$index, 2);
//                $this->tableGateway->update($data, array('id' => $id));
//
//            }
//            return true;
//		}
        if ($options['task'] == 'update-code') {
            $id = $arrData;
            $result = $this->getItem(array('id' => $id));
            $productionType = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-type')), array('task' => 'cache')), array('key' => 'id', 'value' => 'object'));
            $index = $result['index'];
            $data = [];

            if (strlen($index) <= 6) {
                $i = 8 - strlen($index);
                if ($productionType[$result['production_type_id']]['alias'] == DON_TINH) {
                    $data['code'] = substr_replace("T-000000",$index, $i);
                } elseif ($productionType[$result['production_type_id']]['alias'] == DON_HA_NOI) {
                    $data['code'] = substr_replace("H-000000",$index, $i);
                }
            }else{

                if ($productionType[$result['production_type_id']]['alias'] == DON_TINH) {
                    $data['code'] = substr_replace("T-000000",$index, 2);
                } elseif ($productionType[$result['production_type_id']]['alias'] == DON_HA_NOI) {
                    $data['code'] = substr_replace("H-000000",$index, 2);
                }
            }
            $branch = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(array('id' => $result['sale_branch_id']));
            $data['code'] = $branch['alias'].'-'.$data['code'];
            if ($options['split-contract']){
                $data['code'] = $data['code'] .'-H';
            }

            $this->tableGateway->update($data, array('id' => $id));
            return true;
        }

		if ($options['task'] == 'update-status-technical') {
            $id = $arrData;
            $contract   = $this->getItem(array('id' => $id));
            $products   = unserialize($contract['options'])['product'];
            $status_technical = false;
            $status_tailors = false;
            foreach($products as $key => $value){
                if(!empty($value['technical_id'])){
                    $status_technical = true;
                }
                if(!empty($value['tailors_id'])){
                    $status_tailors = true;
                }
            }
			$data = array(
			    'status_technical' => $status_technical,
			    'status_tailors'   => $status_tailors,
            );
            $this->tableGateway->update($data, array('id' => $id));
            return true;
		}

		if ($options['task'] == 'update-code-warehouse') {
            $id = $arrData;
            $result = $this->getItem(array('id' => $id));
			$index = $result['index'];

            if (strlen($index) <= 6) {
				$i = 8 - strlen($index);
				$data['code'] = substr_replace("T-000000",$index, $i);
                $this->tableGateway->update($data, array('id' => $id));
            }else{
				$data['code'] = substr_replace("T-000000",$index, 2);
            }
            $branch = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem(array('id' => $result['sale_branch_id']));
            $data['code'] = $branch['alias'].'-'.$data['code'];
            $this->tableGateway->update($data, array('id' => $id));
            return true;
		}

        // Lần thứ bao nhiêu khách hàng mua hàng.
		if ($options['task'] == 'update-index-number') {
            $id = $arrData;
            $result = $this->getItem(array('id' => $id));
            $contract_number = $this->countItem($result, array('task' => 'count-contract-by-contact'));
            $data['index_number'] = $contract_number;

            $this->tableGateway->update($data, array('id' => $id));
            return true;
		}

        // Cập nhật thông tin đơn hàng thành công
		if ($options['task'] == 'update-contract-succes') {
            $id = $arrData['id'];
            $date_success = !empty($arrData['date_success']) ? $arrData['date_success'] : date('Y-m-d H:i:s');
            $data['date_success'] = $date_success;
            $this->tableGateway->update($data, array('id' => $id));

            $contract = $this->getItem(array('id' => $id));
            $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem(array('contact_id' => $contract['contact_id'],'date_success' => $date_success), array('task' => 'update-contract-time-success'));
            return true;
		}

        // Cập nhật bản ghi
		if ($options['task'] == 'update-item') {
            $id = $arrData['id'];
            
            if($arrData['shipped_date']){
                $data['shipped_date'] = $arrData['shipped_date'];
            }
            if($arrData['ghtk_status']){
                $data['ghtk_status'] = $arrData['ghtk_status'];
            }
            if($arrData['options']){
                $data['options'] = serialize($arrData['options']);
            }
            if($arrData['send_zalo_notifi_care']){
                $data['send_zalo_notifi_care'] = $arrData['send_zalo_notifi_care'];
            }

            $this->tableGateway->update($data, array('id' => $id));
            return true;
		}

		// Cập nhật lịch sử chăm sóc đơn hàng
		if ($options['task'] == 'update-history-contract') {
            $id      = $arrParam['data']['id'];
            $arrData = $arrParam['data'];
            $arrItem = $arrParam['item'];

            $history_data = array(
                'created'       => date('Y-m-d H:i:s'),
                'created_by'    => $this->userInfo->getUserInfo('id'),
                'content'       => $arrData['history_content'],
            );
            $history_option = !empty($arrItem['history_contract']) ? unserialize($arrItem['history_contract']) : [];
            $history_option[] = $history_data;

            $data['history_contract'] = serialize($history_option);

            $this->tableGateway->update($data, array('id' => $id));
            return true;
		}

        // Cập nhật thông tin hoàn đơn
        if ($options['task'] == 'update-refund-contract') {
            $id      = $arrParam['data']['id'];
            $arrData = $arrParam['data'];
            $arrItem = $arrParam['item'];
            $options = unserialize($arrItem['options'])?:[];
            $products = $options['product']?:[];
            $optionsNew = $options;

            $products_old_x = [];
            $products_new_x = [];
            foreach($products as $key=>$product_info){
                if(!empty($arrData['contract_product']['refund'][$key])){
                    if($arrData['contract_product']['refund'][$key] < $product_info['numbers']){
                        $new_x = $old_x = $product_info;
                        $old_x['refund']  = $arrData['contract_product']['refund'][$key];
                        $old_x['numbers'] -= $arrData['contract_product']['refund'][$key];
                        $factor = $old_x['numbers'] / $product_info['numbers'];
                        $old_x['price'] *= $factor;
                        $old_x['listed_price'] *= $factor;
                        $old_x['capital_default'] *= $factor;
                        $old_x['sale_price'] *= $factor;
                        $old_x['vat'] *= $factor;
                        $old_x['total'] *= $factor;
                        $old_x['sales_new'] *= $factor;
                        $old_x['sales_care'] *= $factor;
                        $old_x['sales_old'] *= $factor;
                        $old_x['sales_cross'] *= $factor;
                        $old_x['abcccc'] *= $factor;
                        $products_old_x[] = $old_x;

                        $new_x['numbers'] = $arrData['contract_product']['refund'][$key];
                        $factor2 = $old_x['refund'] / $product_info['numbers'];
                        $new_x['price'] *= $factor2;
                        $new_x['listed_price'] *= $factor2;
                        $new_x['capital_default'] *= $factor2;
                        $new_x['sale_price'] *= $factor2;
                        $new_x['vat'] *= $factor2;
                        $new_x['total'] *= $factor2;
                        $new_x['sales_new'] *= $factor2;
                        $new_x['sales_care'] *= $factor2;
                        $new_x['sales_old'] *= $factor2;
                        $new_x['sales_cross'] *= $factor2;
                        $new_x['abcccc'] *= $factor2;
                        $products_new_x[] = $new_x;
                    }
                    else{
                        $products_new_x[] = $product_info;
                    }
                }
                else{
                    $products_old_x[] = $product_info;
                }
            }
            if(empty($products_old_x)){
                return 'er1';// Trả toàn bộ sản phẩm;
            }
            if(empty($products_new_x)){
                return 'er2';// không trả sản phẩm nào;
            }

            $data_old = [];
            foreach($products_old_x as $key => $value){
                $data_old['price_total'] += $value['total'];
                $data_old['total_number_product'] += $value['numbers'];
                $data_old['sales_new'] += $value['sales_new'];
                $data_old['sales_care'] += $value['sales_care'];
                $data_old['sales_old'] += $value['sales_old'];
                $data_old['sales_cross'] += $value['sales_cross'];
            }

            $data_new = [];
            foreach($products_new_x as $key => $value){
                $data_new['price_total'] += $value['total'];
                $data_new['total_number_product'] += $value['numbers'];
                $data_new['sales_new'] += $value['sales_new'];
                $data_new['sales_care'] += $value['sales_care'];
                $data_new['sales_old'] += $value['sales_old'];
                $data_new['sales_cross'] += $value['sales_cross'];
            }

            if (strlen($arrData['history_content'])) {
                $options['refund_note'] = $arrData['history_content'];
            }
            $options['product'] = $products_old_x;
            $data_old['options'] = serialize($options);
            $data_old['status_refund'] = 2;
            $data_old['status_check_id'] = 'thanh-cong';
            $data_old['date_success'] = date('Y-m-d H:i:s');
            $data_old['price_owed'] = $data_old['price_total'] - $arrItem['price_paid'];
            if(!empty($arrItem['mkt_sales_new']))
                $data_old['mkt_sales_new'] = $data_old['price_total'];
            if(!empty($arrItem['mkt_sales_care']))
                $data_old['mkt_sales_care'] = $data_old['price_total'];

            $this->tableGateway->update($data_old, array('id' => $id));
            // cập nhật lại ngày thành công của đơn hàng đầu tiên
//            $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem(array('contact_id' => $arrItem['contact_id']), array('task' => 'update-contract-time-success'));

            $dataNew = $this->getItem(['id'=>$id]);
            $dataNew['created'] = date('Y-m-d H:i:s');
            $dataNew['date'] = date('Y-m-d');
            $dataNew['total_number_product'] = $data_new['total_number_product'];
            $dataNew['price_total'] = $data_new['price_total'];
            $dataNew['sales_new'] = $data_new['sales_new'];
            $dataNew['sales_care'] = $data_new['sales_care'];
            $dataNew['sales_old'] = $data_new['sales_old'];
            $dataNew['sales_cross'] = $data_new['sales_cross'];
            if(!empty($dataNew['mkt_sales_new']))
                $dataNew['mkt_sales_new'] = $dataNew['price_total'];
            if(!empty($dataNew['mkt_sales_care']))
                $dataNew['mkt_sales_care'] = $dataNew['price_total'];

            $dataNew['price_paid'] = 0;
            $dataNew['price_owed'] = $dataNew['price_total'];

            $dataNew['production_department_type'] = $arrItem['production_department_type'];
            $dataNew['status_refund'] = 0;
            $dataNew['status_id'] = null;
            $dataNew['status_check_id'] = 'hoan';
            $dataNew['date_success'] = null;
            $dataNew['status_acounting_id'] = null;
            $dataNew['code'] = $arrItem['code'].'-H';
            $optionsNew['product'] = $products_new_x;
            $dataNew['options'] = serialize($optionsNew);
            $dataNew['id'] = $gid->getId();
            $this->tableGateway->insert($dataNew);
            return $id;
        }

        // Duyệt thông tin hoàn đơn + sinh đơn
        if ($options['task'] == 'approve-refund-contract') {
            $contact = $arrParam['contact'];
            $contract = $arrParam['contract'];
            $data = $arrParam['data']?:[];
            $product = array_map(function($item){
                $factor = $item['listed_price']/$item['numbers'];
                $item['listed_price'] = ($item['numbers']-$item['refund'])*$factor;
                $item['price'] = ($item['numbers']-$item['refund'])*$factor;
                $item['total'] = ($item['numbers']-$item['refund'])*$factor;
                $item['sales_cross'] = ($item['numbers']-$item['refund'])*$factor;
                $item['numbers'] -= $item['refund'];
                unset($item['refund']);
                return $item;
            },$contract['product']);
            $options = unserialize($contract['options']);
            $options['product'] = $product;
            $this->tableGateway->update([
                'status_check_id' => 'thanh-cong',
                'sales_cross'=>array_sum(array_column($product,'total')),
                'price'=>array_sum(array_column($product,'total')),
                'price_total'=>array_sum(array_column($product,'total')),
                'total_number_product'=>array_sum(array_column($product,'numbers')),'status_refund'=>2,
                'options'=>serialize($options?:[]),
            ],['id' => $contract['id']]);

            $contact_same_phones = $this->getServiceLocator()->get('Admin\Model\ContactTable')->listItem(['ssFilter'=>['filter_keyword'=>$contact['phone']]],['task'=>'search'])->toArray();
            $contract_coincider = $this->getServiceLocator()->get('Admin\Model\ContractTable')->listItem(array('ssFilter' => array('contact_id' => array_column($contact_same_phones,'id'), 'date' => date('d/m/Y'))), array('task' => 'contract-coincider'));
            if(!empty($contract_coincider)){
//                $data['coincider_status']  = 'yes';
                $data['coincider_code']    = $contract_coincider['code'];
            }

            $product = array_map(function($item){
                $item['total'] = $item['refund']*($item['listed_price']/$item['numbers']);
                $item['sales_cross'] = $item['refund']*($item['listed_price']/$item['numbers']);
                $item['listed_price'] = $item['refund']*($item['listed_price']/$item['numbers']);
                $item['price'] = $item['refund']*($item['listed_price']/$item['numbers']);
                $item['numbers'] = $item['refund'];
                unset($item['refund']);
                return $item;
            },$contract['product']);
            foreach($product as $key=>$val){
                $data['contract_product']['product_id'][] = $val['product_id'];
                $data['contract_product']['product_name'][] = $val['product_name'];
                $data['contract_product']['carpet_color_id'][] = $val['carpet_color_id'];
                $data['contract_product']['tangled_color_id'][] = $val['tangled_color_id'];
                $data['contract_product']['flooring_id'][] = $val['flooring_id'];
                $data['contract_product']['numbers'][] = $val['numbers'];
                $data['contract_product']['listed_price'][] = $val['listed_price'];
                $data['contract_product']['price'][] = $val['price'];
                $data['contract_product']['sale_price'][] = 0;
                $data['contract_product']['total'][] = $val['total'];
                $data['price_total'] += $val['total'];
                $data['contract_product']['vat'][] = 0;
            }
            $contact_options = unserialize($contact['options']);
            foreach($contact_options['contact_received'] as $key=>$val) {
                $data[$key.'_received'] = $val;
            }
            $data['production_type_id'] = $contract['production_type_id'];
            $data['transport_id'] = $contract['transport_id'];
            $new_id = $this->saveItem(['item'=>$contact,'data'=>$data],['task'=>'add-item','split-contract'=>true]);
            $this->tableGateway->update(['status_check_id'=>'hoan'],['id'=>$new_id]);
            return $contract['id'];
        }

		// cập nhật bảo hành
		if ($options['task'] == 'update-guarantee') {
			$id = $arrData['id'];
			$data = [
				'guarantee_date' => $date->formatToData($arrData['guarantee_date']),
				'guarantee_note' => $arrData['guarantee_note'],
			];
            // Cập nhật đơn hàng
			$this->tableGateway->update($data, array('id' => $id));
			return $id;
		}

		// cập nhật bảo hành
		if ($options['task'] == 'update-evaluate') {
			$id = $arrData['contract_id'];
			$data = [
				'evaluate' => 1,
			];
            // Cập nhật đơn hàng
			$this->tableGateway->update($data, array('id' => $id));
			return $id;
		}

        // import thợ kỹ thuật và thợ may vào đơn hàng,
        if($options['task'] == 'import-technical') {
            $id = $arrData['id'];
            $data	= array(
                'options'           => $arrData['options'],
            );

            $this->tableGateway->update($data, array('id' => $id));
            // Cập nhật trạng thái đơn đã nhập thợ kỹ thuật, thợ may chưa
            $this->saveItem(array('data' => $id), array('task' => 'update-status-technical'));
            return $id;
        }

		// cập nhật giảm trừ doanh thu
		if ($options['task'] == 'update-reduce') {
			$id = $arrData['id'];
            $data = array();
			$data['price_reduce_sale'] = $number->formatToData($arrData['price_reduce_sale']);
            $data['note_accounting'] = $arrData['note_accounting'];

			$this->tableGateway->update($data, array('id' => $id));
			return $id;
		}

		// cập nhật giảm trừ doanh thu
		if ($options['task'] == 'update-price') {
			$id = $arrData['id'];
            $data = array();
            if($arrData['price_paid']){
                $data['price_paid'] = $number->formatToData($arrData['price_paid']);
                $data['price_owed'] = $arrItem['price_total'] - $arrItem['price_deposits'] - $number->formatToData($arrData['price_paid']);
            }
            if($arrData['status_acounting_id']){
                $data['status_acounting_id'] = $arrData['status_acounting_id'];
            }

			$this->tableGateway->update($data, array('id' => $id));
			return $id;
		}

		// cập nhật phụ phí vận chuyển
		if ($options['task'] == 'update-ship-ext') {
			$id = $arrData['id'];
            $data = array();
            $data['ship_ext'] = $arrItem['ship_ext'] + $number->formatToData($arrData['fee']);


			$this->tableGateway->update($data, array('id' => $id));
			return $id;
		}

		// cập nhật giảm trừ doanh thu
		if ($options['task'] == 'update-shipping-fee') {
			$id = $arrData['id'];
            $data = array();
			$data['shipping_fee'] = $number->formatToData($arrData['shipping_fee']);
            $data['returned'] = 1;

			$this->tableGateway->update($data, array('id' => $id));
			return $id;
		}

        if($options['task'] == 'add-item') {
            // Tham số liên hệ
            $arrParamContact = $arrParam;

            // Xóa phân tử không cần update
            unset($arrParamContact['data']['date']);
            unset($arrParamContact['data']['product_id']);

            if(!empty($arrItem)) {
                $arrParamContact['item']                      		= $arrItem;
                $arrParamContact['data']['id']                		= $arrItem['id'];
                $arrParamContact['data']['contract_total']    		= $arrItem['contract_total'] + 1;
                $arrParamContact['data']['contract_number']   		= $arrItem['contract_number'] + 1;
                $arrParamContact['data']['contract_price_total']    = $arrItem['contract_price_total'] + $number->formatToData($arrData['price_total']);

                $item_options = !empty($arrItem['options']) ? unserialize($arrItem['options']) : array();
                if(!empty($item_options['product_ids'])) {
                    $product_ids = explode(',', $item_options['product_ids']);
                    if(!in_array($arrData['product_id'], $product_ids)) {
                        $product_ids[] = $arrData['product_id'];
                        $arrParamContact['data']['product_ids'] = implode(',', $product_ids);
                    }
                } else {
                    $arrParamContact['data']['product_ids'] = $arrData['product_id'];
                }

                $arrParamContact['data']['contact_received'] = [
                    'name' 		=> $arrData['name_received'],
                    'phone' 	=> $arrData['phone_received'],
                    'address' 	=> $arrData['address_received'],
                ];

                // Cập nhật liên hệ
                $contact_id = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($arrParamContact, array('task' => 'edit-item'));
            }
            else {
                // Thêm mới liên hệ
                $arrParamContact['data']['contract_total']       = 1;
                $arrParamContact['data']['contract_number']      = 1;
                $arrParamContact['data']['contract_price_total'] = $number->formatToData($arrData['price_total']);
                $arrParamContact['data']['user_id']              = $this->userInfo->getUserInfo('id');
                $arrParamContact['data']['sale_group_id']        = $this->userInfo->getUserInfo('sale_group_id');
                $arrParamContact['data']['sale_branch_id']       = $this->userInfo->getUserInfo('sale_branch_id');
                $arrParamContact['data']['product_ids']          = $arrData['product_id'];

                $arrParamContact['data']['contact_received'] = [
                    'name' 		=> $arrData['name_received'],
                    'phone' 	=> $arrData['phone_received'],
                    'address' 	=> $arrData['address_received'],
                ];

                $contact_id = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($arrParamContact, array('task' => 'add-item'));
            }

            // Thêm đơn hàng
            if(!empty($contact_id)) {
                $id = $gid->getId();
                $contract_product = $arrData['contract_product'];
                $contract_options = array();
                $contract_options['contact_type']                 = $arrItem['type'];
                $contract_options['contact_source_group_id']      = $arrItem['source_group_id'] ? $arrItem['source_group_id'] : '';
                $contract_options['contact_source_known_id']      = $arrItem['source_known_id'] ? $arrItem['source_known_id'] : '';
                $contract_options['contact_history_created']      = $arrItem['history_created'];
                $contract_options['contact_store']                = $arrItem['store'];
                $contract_options['sale_note']                    = $arrData['sale_note']; // ghi chus sale
                $contract_options['production_note']              = $arrData['production_note']; // ghi chú sản xuất
                $contract_options['contract_received'] 			  = [
                    'name' 		=> $arrData['name_received'],
                    'phone' 	=> $arrData['phone_received'],
                    'address' 	=> $arrData['address_received'],
                ];
                $contract_options['product']  = array();
                for($i = 0; $i < count($contract_product['product_id']); $i++){
                    if(!empty($contract_product['product_id'][$i])) {
                        $contract_options['product'][$i]['key_id']           = !empty($contract_product['key_id'][$i]) ? $contract_product['key_id'][$i] : $gid->getId(); // Tạo mã đối chiếu trong trường hợp bán lại hàng có sẵn.
                        $contract_options['product'][$i]['product_id']       = !empty($contract_product['product_id'][$i]) ? $contract_product['product_id'][$i] : null; // sản phẩm
                        $contract_options['product'][$i]['product_name']     = !empty($contract_product['product_name'][$i]) ? $contract_product['product_name'][$i] : null; // tên xe - năm sản xuất
                        $contract_options['product'][$i]['stock']            = !empty($contract_product['stock'][$i]) ? $contract_product['stock'][$i] : null; // Hàng có sẵn
                        $contract_options['product'][$i]['carpet_color_id']  = !empty($contract_product['carpet_color_id'][$i]) ? $contract_product['carpet_color_id'][$i] : null; // màu thảm
                        $contract_options['product'][$i]['tangled_color_id'] = !empty($contract_product['tangled_color_id'][$i]) ? $contract_product['tangled_color_id'][$i] : null; // màu rối
                        $contract_options['product'][$i]['flooring_id']      = !empty($contract_product['flooring_id'][$i]) ? $contract_product['flooring_id'][$i] : null; // loại sản phẩm
                        $contract_options['product'][$i]['numbers']          = !empty($contract_product['numbers'][$i]) ? $contract_product['numbers'][$i] : 1; // số lượng của đơn hàng
                        $contract_options['product'][$i]['price']            = !empty($contract_product['price'][$i]) ? $number->formatToNumber($contract_product['price'][$i]) : null; // giá bán
                        $contract_options['product'][$i]['listed_price']     = !empty($contract_product['listed_price'][$i]) ? $number->formatToNumber($contract_product['listed_price'][$i]) : null; // giá niêm yết
                        $contract_options['product'][$i]['capital_default']  = !empty($contract_product['capital_default'][$i]) ? $number->formatToNumber($contract_product['capital_default'][$i]) : null; // giá vốn mặc định
                        $contract_options['product'][$i]['sale_price']       = !empty($contract_product['sale_price'][$i]) ? $number->formatToNumber($contract_product['sale_price'][$i]) : null; // giảm giá
                        $contract_options['product'][$i]['vat']              = !empty($contract_product['vat'][$i]) ? $number->formatToNumber($contract_product['vat'][$i]) : null; // vat
                        $contract_options['product'][$i]['total']            = !empty($contract_product['total'][$i]) ? $number->formatToNumber($contract_product['total'][$i]) : null; // tổng tiền (chính là cột thành tiền)
                        $contract_options['product'][$i]['type']             = !empty($contract_product['type'][$i]) ? $contract_product['type'][$i] : null;
                        $contract_options['product'][$i]['sale_branch_id']   = !empty($contract_product['sale_branch_id'][$i]) ? $contract_product['sale_branch_id'][$i] : null;

                        $product                                             = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'list-item'));
                        $contract_options['product'][$i]['product_alias']    = $product[$contract_product['product_id'][$i]]['code']; // alias của sản phẩm
                        $contract_options['product'][$i]['product_group_id'] = $product[$contract_product['product_id'][$i]]['product_group_id']; // nhóm sản phẩm

                        if (!empty($contract_product['stock'][$i])) {
                            $checkCode = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('code' => $contract_product['stock'][$i]), array('task' => 'by-code'));
                        }
                        if ($this->userInfo->getUserInfo('sale_branch_id') == $checkCode['sale_branch_id']) {
                            $contract_options['product'][$i]['total_production'] = 0;
                        } else {
                            $contract_options['product'][$i]['total_production'] = !empty($contract_product['total_production'][$i]) ? $number->formatToNumber($contract_product['total_production'][$i] / 2) : 0; // Tổng tiền giá vốn
                        }

                        $contract_options['product'][$i]['number_production'] = !empty($contract_product['number_production'][$i]) || $contract_product['number_production'][$i] == 0 ? $contract_product['number_production'][$i] : null; // số lượng sx của sản phẩm (CM sản phẩm)
                        $contract_options['product'][$i]['number_carpet']     = !empty($contract_product['number_carpet'][$i]) || $contract_product['number_carpet'][$i] == 0 ? $contract_product['number_carpet'][$i] : null; // số lượng sản xuất màu thảm (CM thảm)
                        $contract_options['product'][$i]['number_tangled']    = !empty($contract_product['number_tangled'][$i]) || $contract_product['number_tangled'][$i] == 0 ? $contract_product['number_tangled'][$i] : null; //số lượng sản xuất màu rối (CM rối)
                        $contract_options['product'][$i]['price_production']  = !empty($contract_product['price_production'][$i]) ? $number->formatToNumber($contract_product['price_production'][$i]) : 0; // Giá vốn
                        $contract_options['product'][$i]['keyUpdate']         = isset($contract_product['keyUpdate'][$i]) ? $contract_product['keyUpdate'][$i] : null;

                        // Trừ số lượng sản phẩm của đơn hàng có sẵn khi lên đơn
                        if (!empty($contract_product['stock'][$i])) {
                            $param_check = array(
                                'code'      => $contract_product['stock'][$i],
                                'key_id'    => $contract_product['key_id'][$i],
                            );
                            $this->saveItem($param_check, array('task' => 'update-number-product', 'type_action' => 'yes'));
                        }
                    }
                }

                $price_deposits = '';
                if (!empty($arrData['bill_paid_type_id']) AND $arrData['bill_paid_type_id'] == 'dat-coc') {
                    $price_deposits = $arrData['bill_paid_price'];
                }
                $data = array(
                    'id'                      => $id,
                    'date'                    => !empty($arrData['date']) ? $date->formatToData($arrData['date']) : date('Y-m-d'),
                    'price'                   => $number->formatToData($arrData['price']),
                    'price_promotion'         => $number->formatToData($arrData['price_promotion']),
                    'price_total'             => $number->formatToData($arrData['price_total']),
                    'price_paid'              => 0,
                    'price_accrued'           => 0,
                    'price_owed'              => $number->formatToData($arrData['price_total']),
                    'price_surcharge'         => 0,
                    'price_deposits'          => $number->formatToData($price_deposits),
                    'contact_id'              => $contact_id,
                    'code_old'                => $arrData['code_old'],
//                    'coincider_status'        => $arrData['coincider_status'],
                    'coincider_code'          => $arrData['coincider_code'],
                    'stock'			  		  => $arrData['stock'],
                    'transport_id'			  => $arrData['transport_id'],
                    'status_guarantee_id'	  => $arrData['status_guarantee_id'],
                    'status_id'		          => $arrData['status_id'],
                    'status_store'		      => $arrData['status_store'],
                    'production_type_id'	  => $arrData['production_type_id'],
                    'production_department_type' => $arrData['production_department_type'],
                    'price_carpet'		      => $number->formatToData($arrData['price_carpet']),
                    'price_nano'		      => $number->formatToData($arrData['price_nano']),
                    'vat'		      		  => $number->formatToData($arrData['vat']),
                    'marketer_id'             => $arrItem['marketer_id'],
                    'product_group_id'        => $arrItem['product_group_id'],
                    'status_id'               => DA_CHOT,

//    		        'user_id'                 => $this->userInfo->getUserInfo('id'),
//    		        'sale_group_id'           => $this->userInfo->getUserInfo('sale_group_id'),
//    		        'sale_branch_id'          => $this->userInfo->getUserInfo('sale_branch_id'),
                    'user_id'                 => $sales_manager['id'],
                    'sale_group_id'           => $sales_manager['sale_group_id'],
                    'sale_branch_id'          => $sales_manager['sale_branch_id'],

                    'created'                 => date('Y-m-d H:i:s'),
                    'created_by'              => $this->userInfo->getUserInfo('id'),
                    'options'                 => serialize($contract_options)
                );

                // cập nhật doanh thu cho Marketer
                if ($arrData['production_department_type'] == STATUS_CONTRACT_PRODUCT_PRODUCTED) {
                    $this->getServiceLocator()->get('Admin\Model\MarketingReportTable')->saveItem(array('data'=> array('contract_id' => $id)), array('task' => 'update-sales-finish')); # bỏ
                }

                $data['price'] = $data['price_total'] + $data['price_promotion'];

                if($number->formatToData($arrData['price_total']) > 0) {
                    $data['price_paid']   = $number->formatToData($arrData['bill_paid_price']);
                    $data['price_owed']   = $number->formatToData($arrData['price_total']) - $number->formatToData($arrData['bill_paid_price']);
                } else {
                    $data['price_paid']   = 0;
                    $data['price_owed']   = 0;
                }

                $this->tableGateway->insert($data); // Thực hiện lưu database

                // Lưu mã hoá đơn
                $this->saveItem(array('data' => $id), array('task' => 'update-code','split-contract'=>isset($options['split-contract'])&&$options['split-contract']==true));
                // Lưu đơn thứ bao nhiêu của khách hàng
                $this->saveItem(array('data' => $id), array('task' => 'update-index-number'));
                // Cập nhật các loại doanh số sau khi tạo đơn hàng
                $this->saveItem(array('data' => $id), array('task' => 'update-sales'));
                // Cập nhật tổng số lượng sản phẩm trong đơn hàng
                $this->saveItem(array('data' => $id), array('task' => 'update-number-product-total'));
                // Cập nhật thông tin đơn hàng đầu tiên cho khách hàng
                $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem(array('contract_id' => $id), array('task' => 'update-contract-first'));

                // Lưu thông tin hóa đơn
                if(!empty($arrData['bill_type_id'])) {
                    $arrParamBill = $arrParam;
                    $arrParamBill['data'] = array();
                    $arrParamBill['data']['contract_id']              = $id;
                    $arrParamBill['data']['contract_date']   		  = $arrData['date_register'];
                    $arrParamBill['data']['date']                     = $arrData['bill_date'] ? $date->formatToData($arrData['bill_date']) : null;
                    $arrParamBill['data']['paid_number']              = $arrData['bill_paid_number'] ? $arrData['bill_paid_number'] : $arrData['phone'];
                    $arrParamBill['data']['paid_price']               = $arrData['bill_paid_price'];
                    $arrParamBill['data']['paid_type_id']             = $arrData['bill_paid_type_id'];
                    $arrParamBill['data']['bill_type_id']             = $arrData['bill_type_id'];
                    $arrParamBill['data']['bill_bank_id']             = ($arrData['bill_type_id'] == 'chuyen-khoan') ? $arrData['bill_bank_id'] : null;
                    $arrParamBill['data']['type']                     = 'paid';
                    $arrParamBill['data']['contact_id']               = $contact_id;
                    $arrParamBill['data']['user_id']                  = $arrParamContact['item']['user_id'];
                    $arrParamBill['data']['sale_group_id']         	  = $arrParamContact['item']['sale_group_id'];
                    $arrParamBill['data']['sale_branch_id']           = $arrParamContact['item']['sale_branch_id'];
                    $arrParamBill['data']['branch_id']           	  = $arrParamContact['item']['branch_id'];

                    $bill = $this->getServiceLocator()->get('Admin\Model\BillTable')->saveItem($arrParamBill, array('task' => 'contract-add-bill'));
                }

                // Thêm lịch sử hệ thống
                $arrParamLogs = array(
                    'data' => array(
                        'title'          => 'đơn hàng',
                        'phone'          => $arrData['phone'],
                        'name'           => $arrData['name'],
                        'action'         => 'Thêm mới',
                        'contact_id'     => $contact_id,
                        'contract_id'    => $id,
                        'options'        => array(
                            'date'                    => $arrData['date'],
                            'product_name'			  => $arrData['product_name'],
                            'price_total'             => $arrData['price_total'],
                            'user_id'                 => $data['user_id'],
                            'sale_branch_id'          => $data['sale_branch_id'],
                            'sale_group_id'           => $data['sale_group_id'],
                        )
                    )
                );
                $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));

                return $id;
            }
        }
		
		if($options['task'] == 'edit-item') {
			$arrContact = $arrParam['contact'];
		    $id = $arrData['id'];
			$data = array();
			$arrParamContact = array();
			$contract_product = $arrData['contract_product'];

			// cập nhật contact
			if(!empty($arrContact)) {
				$arrParamContact['data']['id'] = $arrContact['id'];
				$arrParamContact['data']['contact_received'] = [
					'name' 		=> $arrData['name_received'],
					'phone' 	=> $arrData['phone_received'],
					'address' 	=> $arrData['address_received'],
				];
		        
		        // Cập nhật liên hệ
		        $contact_id = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($arrParamContact, array('task' => 'edit-item'));
		    }
			
			$contract_options = !empty($arrItem['options']) ? unserialize($arrItem['options']) : array();

			$contract_options['sale_note']                    = $arrData['sale_note'];
			$contract_options['production_note']              = $arrData['production_note'];
			$contract_options['product_name']              	  = $arrData['product_name'];
			$contract_options['product_return'] 			  = $arrData['product_return'];
			$contract_options['contract_received'] = [
				'name' 		=> $arrData['name_received'],
				'phone' 	=> $arrData['phone_received'],
				'address' 	=> $arrData['address_received'],
			];

            // Danh sách sản phẩm trước khi sửa của đơn hàng.
            $code_stock_old = [];
            foreach ($contract_options['product'] as $key => $value){
                if(!empty($value['stock'])){
                    $code_stock_old[] = $value['stock'].','.$value['key_id'];
                }
            }

			$contract_options['product']  = array();
		    for($i = 0; $i < count($contract_product['product_id']); $i++){
                if(!empty($contract_product['product_id'][$i])) {
                    $contract_options['product'][$i]['key_id']           = !empty($contract_product['key_id'][$i]) ? $contract_product['key_id'][$i] : $gid->getId();;
                    $contract_options['product'][$i]['product_id']       = !empty($contract_product['product_id'][$i]) ? $contract_product['product_id'][$i] : null;
                    $contract_options['product'][$i]['product_name']     = !empty($contract_product['product_name'][$i]) ? $contract_product['product_name'][$i] : null;
                    $contract_options['product'][$i]['stock']            = !empty($contract_product['stock'][$i]) ? $contract_product['stock'][$i] : null;
                    $contract_options['product'][$i]['carpet_color_id']  = !empty($contract_product['carpet_color_id'][$i]) ? $contract_product['carpet_color_id'][$i] : null;
                    $contract_options['product'][$i]['tangled_color_id'] = !empty($contract_product['tangled_color_id'][$i]) ? $contract_product['tangled_color_id'][$i] : null;
                    $contract_options['product'][$i]['flooring_id']      = !empty($contract_product['flooring_id'][$i]) ? $contract_product['flooring_id'][$i] : null;
                    $contract_options['product'][$i]['numbers']          = !empty($contract_product['numbers'][$i]) ? $contract_product['numbers'][$i] : null;
                    $contract_options['product'][$i]['price']            = !empty($contract_product['price'][$i]) ? $number->formatToNumber($contract_product['price'][$i]) : null;
                    $contract_options['product'][$i]['listed_price']     = !empty($contract_product['listed_price'][$i]) ? $number->formatToNumber($contract_product['listed_price'][$i]) : null;
                    $contract_options['product'][$i]['capital_default']  = !empty($contract_product['capital_default'][$i]) ? $number->formatToNumber($contract_product['capital_default'][$i]) : null;
                    $contract_options['product'][$i]['sale_price']       = !empty($contract_product['sale_price'][$i]) ? $number->formatToNumber($contract_product['sale_price'][$i]) : null;
                    $contract_options['product'][$i]['vat']              = !empty($contract_product['vat'][$i]) ? $number->formatToNumber($contract_product['vat'][$i]) : null;
                    $contract_options['product'][$i]['total']            = !empty($contract_product['total'][$i]) ? $number->formatToNumber($contract_product['total'][$i]) : null;

                    $contract_options['product'][$i]['type']              = !empty($contract_product['type'][$i]) ? $contract_product['type'][$i] : null;
                    $contract_options['product'][$i]['sale_branch_id']    = !empty($contract_product['sale_branch_id'][$i]) ? $contract_product['sale_branch_id'][$i] : null;
                    $contract_options['product'][$i]['total_production']  = !empty($contract_product['total_production'][$i]) ? $number->formatToNumber($contract_product['total_production'][$i]) : 0;
                    $contract_options['product'][$i]['number_production'] = !empty($contract_product['number_production'][$i]) || $contract_product['number_production'][$i] == 0 ? $contract_product['number_production'][$i] : null;
                    $contract_options['product'][$i]['number_carpet']     = !empty($contract_product['number_carpet'][$i]) || $contract_product['number_carpet'][$i] == 0 ? $contract_product['number_carpet'][$i] : null;
                    $contract_options['product'][$i]['number_tangled']    = !empty($contract_product['number_tangled'][$i]) || $contract_product['number_tangled'][$i] == 0 ? $contract_product['number_tangled'][$i] : null;
                    $contract_options['product'][$i]['price_production']  = !empty($contract_product['price_production'][$i]) ? $number->formatToNumber($contract_product['price_production'][$i]) : 0;
                    $contract_options['product'][$i]['keyUpdate']         = !empty($contract_product['keyUpdate'][$i]) ? $contract_product['keyUpdate'][$i] : 0;

                    $product                                             = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'list-item'));
                    $contract_options['product'][$i]['product_alias']    = $product[$contract_product['product_id'][$i]]['code']; // alias của sản phẩm
                    $contract_options['product'][$i]['product_group_id'] = $product[$contract_product['product_id'][$i]]['product_group_id']; // nhóm sản phẩm

                    // Cập nhật giá vốn của các sản phẩm có sẵn.
                    if (!empty($contract_product['stock'][$i])) {
                        $checkCode    = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('code' => $contract_product['stock'][$i]), array('task' => 'by-code'));
                        $options      = unserialize($checkCode['options']);
                        $dataUpdateDB = !empty($options) ? unserialize($checkCode['options']) : array();
                        if (!empty($checkCode)) {
                            foreach ($dataUpdateDB['product'] as $key => $item) {
                                if ($item['key_id'] == $contract_product['key_id'][$i]) {
                                    if ($this->userInfo->getUserInfo('sale_branch_id') == $checkCode['sale_branch_id']) {
                                        $contract_options['product'][$i]['total_production'] = 0;
                                    } else {
                                        $contract_options['product'][$i]['total_production'] = !empty($item['total_production']) ? $number->formatToNumber($item['total_production'] / 2) : 0; // Tổng tiền giá vốn
                                    }
                                }
                            }
                        }
                    }

                    // Trừ số lượng sản phẩm của đơn hàng có sẵn khi lên đơn
                    if (!empty($contract_product['stock'][$i])) {
                        $param_check = array(
                            'code'      => $contract_product['stock'][$i],
                            'key_id'    => $contract_product['key_id'][$i],
                        );
                        $this->saveItem($param_check, array('task' => 'update-number-product', 'type_action' => 'yes'));
                    }
                }
		    }

            // Nếu khi sửa đơn xóa sản phẩm có sẵn cần cập nhật lại số lượng sản phẩm của đơn có sẵn đã bán
            // Danh sách sản phẩm sau khi sửa của đơn hàng.
            $code_stock_new = [];
            foreach ($contract_options['product'] as $key => $value){
                if(!empty($value['stock'])){
                    $code_stock_new[] = $value['stock'].','.$value['key_id'];
                }
            }
            // Những sản phẩm có sẵn bị xóa khi sửa đơn
            $code_delete = array_diff($code_stock_old,$code_stock_new);
            if(!empty($code_delete)){
                foreach ($code_delete as $key => $value){
                    $value = explode(',', $value);
                    // Cập nhật lại số lượng sản phẩm của đơn hàng có sẵn (+1)
                    $param_check = array(
                        'code'      => $value[0],
                        'key_id'    => $value[1],
                    );
                    $this->saveItem($param_check, array('task' => 'update-number-product'));
                }
            }
			
			if(isset($arrData['contract_note'])) {
			    $contract_options['contract_note'] = $arrData['contract_note'];
			}

			if(!empty($contract_options)) {
			    $data['options'] = serialize($contract_options);
			}
			
			if(!empty($arrData['date'])) {
			    $data['date'] = $date->formatToData($arrData['date']);
			    
			    // Ngày mới khác ngày cũ thì cập nhật lại hóa đơn theo ngày mới
			    if($arrData['date'] != $arrItem['date']) {
			        $bill = $this->getServiceLocator()->get('Admin\Model\BillTable')->saveItem(array('data' => array('contract_date' => $arrData['date'], 'contract_id' => $arrItem['id'])), array('task' => 'update-by-contract'));
			    }
			}
			if(isset($arrData['price'])) {
			    $data['price'] = $number->formatToNumber($arrData['price']);
			}
			if(isset($arrData['price_promotion'])) {
				$data['price_promotion'] = $number->formatToNumber($arrData['price_promotion']);
				$data['price'] = $number->formatToNumber($arrData['price_total']) + $number->formatToNumber($arrData['price_promotion']);
			}
			if(isset($arrData['price_total'])) {
				$data['price_total'] = $number->formatToNumber($arrData['price_total']);
			}
			if(isset($arrData['price_paid'])) {
			    $data['price_paid'] = $number->formatToNumber($arrData['price_paid']);
			}
			if(isset($arrData['price_accrued'])) {
			    $data['price_accrued'] = $number->formatToNumber($arrData['price_accrued']);
			}
			if(isset($arrData['price_surcharge'])) {
			    $data['price_surcharge'] = $number->formatToNumber($arrData['price_surcharge']);
			}
			if(!empty($arrData['contact_id'])) {
			    $data['contact_id'] = $arrData['contact_id'];
			}
			if(!empty($arrData['user_id'])) {
			    $data['user_id'] = $arrData['user_id'];
			}
			if(!empty($arrData['sale_branch_id'])) {
			    $data['sale_branch_id'] = $arrData['sale_branch_id'];
			}
			if(!empty($arrData['sale_group_id'])) {
			    $data['sale_group_id'] = $arrData['sale_group_id'];
			}
			if(!empty($arrData['created'])) {
			    $data['created'] = $date->formatToData($arrData['created'], 'Y-m-d H:i:s');
			}
			if(!empty($arrData['created_by'])) {
			    $data['created_by'] = $arrData['created_by'];
			}
			if(!empty($arrData['stock'])) {
			    $data['stock'] = $arrData['stock'];
			}
			if(!empty($arrData['transport_id'])) {
			    $data['transport_id'] = $arrData['transport_id'];
			}
			if(!empty($arrData['status_id'])) {
			    $data['status_id'] = $arrData['status_id'];
			}
			if(!empty($arrData['status_check_id'])) {
			    $data['status_check_id'] = $arrData['status_check_id'];
			}
			if(!empty($arrData['status_acounting_id'])) {
			    $data['status_acounting_id'] = $arrData['status_acounting_id'];
			}
			if(!empty($arrData['production_type_id'])) {
			    $data['production_type_id'] = $arrData['production_type_id'];
			}
			if(!empty($arrData['production_department_type'])) {
				$data['production_department_type'] = $arrData['production_department_type'];
				// cập nhật doanh thu cho Marketer
				if ($arrItem['production_department_type'] != STATUS_CONTRACT_PRODUCT_PRODUCTED && $arrData['production_department_type'] == STATUS_CONTRACT_PRODUCT_PRODUCTED) {
					$this->getServiceLocator()->get('Admin\Model\MarketingReportTable')->saveItem(array('data'=> array('contract_id' => $arrItem['id'])), array('task' => 'update-sales-finish')); # bỏ
				}
			}
			if(!empty($arrData['price_carpet'])) {
			    $data['price_carpet'] = $number->formatToNumber($arrData['price_carpet']);
			}
			if(!empty($arrData['price_nano'])) {
			    $data['price_nano'] = $number->formatToNumber($arrData['price_nano']);
			}
			if(!empty($arrData['vat'])) {
			    $data['vat'] = $number->formatToNumber($arrData['vat']);
			}

			// Cập nhật đơn hàng
			$this->tableGateway->update($data, array('id' => $id));
            // Cập nhật các loại doanh số sau khi sửa đơn hàng
            $this->saveItem(array('data' => $id), array('task' => 'update-sales'));
            // Cập nhật tổng số lượng sản phẩm trong đơn hàng
            $this->saveItem(array('data' => $id), array('task' => 'update-number-product-total'));
			
			// Thêm lịch sử hệ thống
			if(!empty($id)) {
			    $arrCheckLogs = array('date', 'price_carpet', 'price_promotion', 'price_nano', 'vat', 'price_promotion', 'contact_id', 'user_id', 'sale_branch_id', 'sale_group_id');
			    $arrCheckResult = array();
			    foreach ($arrCheckLogs AS $field) {
		            if(isset($data[$field])) {
		                $check = $data[$field];
		                if($field == 'date') {
		                    $check = $date->formatToView($data[$field]);
		                }
			            if($check != $arrItem[$field]) {
			                $arrCheckResult[$field] = $data[$field];
			            }
			        }
			    }
			    
			    if(!empty($arrData['note_log'])) {
			        $arrCheckResult['note_log'] = $arrData['note_log'];
			    }
			
			    if(!empty($arrCheckResult)) {
			        $arrParamLogs = array(
			            'data' => array(
			                'title'          => 'đơn hàng',
			                'phone'          => $arrContact['phone'],
			                'name'           => $arrContact['name'],
			                'action'         => 'Sửa',
			                'contact_id'     => $arrContact['id'],
			                'contract_id'    => $id,
			                'options'        => $arrCheckResult
			            )
			        );
			        $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
			    }
			}
			
			return $id;
		}

        if($options['task'] == 'add-kov-item') {
            // Tham số liên hệ
            $arrParamContact = $arrParam;

            // Xóa phân tử không cần update
            unset($arrParamContact['data']['date']);
            unset($arrParamContact['data']['product_id']);

            if(!empty($arrItem)) {
                $arrParamContact['item']                      		= $arrItem;
                $arrParamContact['data']['id']                		= $arrItem['id'];
                $arrParamContact['data']['contract_total']    		= $arrItem['contract_total'] + 1;
                $arrParamContact['data']['contract_number']   		= $arrItem['contract_number'] + 1;
                $arrParamContact['data']['contract_price_total']    = $arrItem['contract_price_total'] + $number->formatToData($arrData['price_total']);
                // Cập nhật liên hệ
                $contact_id = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($arrParamContact, array('task' => 'edit-item'));
            }

            // Thêm đơn hàng
            if(!empty($contact_id)) {
                $id = $gid->getId();
                $contract_product = $arrData['contract_product'];
                $contract_options = array();
                $contract_options['product']  = array();

//                $product_type_contract =  \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "production-type" )), array('task' => 'cache')), array('key' => 'id', 'value' => 'name'));
                $contract_product['unit_type'] = array_values($contract_product['unit_type']);

                for($i = 0; $i < count($contract_product['product_id']); $i++){
                    if(!empty($contract_product['product_id'][$i])) {
                        $contract_options['product'][$i]['full_name']        = $contract_product['full_name'][$i]; // Tên đầy đủ
                        $contract_options['product'][$i]['product_id']       = $contract_product['product_id'][$i]; // id sản phẩm
                        $contract_options['product'][$i]['code']             = $contract_product['code'][$i];// mã sản phẩm
                        $contract_options['product'][$i]['numbers']          = $number->formatToData($contract_product['numbers'][$i]); // số lượng của đơn hàng
                        $contract_options['product'][$i]['price']            = $number->formatToData($contract_product['price'][$i]); // giá bán
                        $contract_options['product'][$i]['total']            = $number->formatToData($contract_product['total'][$i]); // tổng tiền (chính là cột thành tiền)
                        $contract_options['product'][$i]['cost']             = $contract_product['cost'][$i]; // giá vốn kov
                        $contract_options['product'][$i]['weight']           = $contract_product['weight'][$i]; // Khối lượng 1 gói hàng (gram)
                        $contract_options['product'][$i]['categoryId']       = $contract_product['categoryId'][$i]; // Khối lượng 1 gói hàng (gram)
                        $contract_options['product'][$i]['categoryName']     = $contract_product['categoryName'][$i]; // Khối lượng 1 gói hàng (gram)
                        $contract_options['product'][$i]['car_year']         = $contract_product['car_year'][$i]; // Tên xe năm sản xuất
                        $contract_options['product'][$i]['length']           = $contract_product['length'][$i]; // Chiều dài
                        $contract_options['product'][$i]['width']            = $contract_product['width'][$i]; // Chiều rộng
                        $contract_options['product'][$i]['height']           = $contract_product['height'][$i]; // Chiều cao

                        $item_inven = $this->getServiceLocator()->get('Admin\Model\KovProductBranchTable')->getItem(array('productId' => $contract_product['product_id'][$i], 'branchId' => $this->userInfo->getUserInfo('sale_branch_id')));
                        if($item_inven){
                            $capital_default = (int)($contract_product['cost'][$i] + $item_inven['cost_new']) * $number->formatToData($contract_product['numbers'][$i]);

                            $contract_options['product'][$i]['capital_default'] = (int)$capital_default; // Giá vốn mới
                            $contract_options['product'][$i]['cost_new']        = $item_inven['cost_new']; // giá vốn thăng theo chi nhánh
                            $contract_options['product'][$i]['fee']             = $item_inven['fee']; // phụ phí
                        }
                    }
                }

                $data = array(
                    'id'                      => $id,
                    'date'                    => !empty($arrData['date']) ? $date->formatToData($arrData['date']) : date('Y-m-d'),
                    'name'                    => $arrData['name'],
                    'phone'                   => $arrData['phone'],
                    'location_city_id'        => $arrData['location_city_id'],
                    'location_district_id'    => $arrData['location_district_id'],
                    'location_town_id'        => $arrData['location_town_id'],
                    'address'                 => $arrData['address'],
                    'price_deposits'          => $number->formatToData($arrData['price_deposits']),
                    'price_paid'              => 0,
                    'price_owed'              => $number->formatToData($arrData['price_owed']),
                    'total_price_product'     => $number->formatToData($arrData['total_contract_product']),
                    'contact_id'              => $contact_id,
                    'id_kov'                  => $arrData['id_kov'],
                    'kov_code'                => $arrData['kov_code'],
                    'coincider_code'          => $arrData['coincider_code'],
                    'vat'		      		  => $number->formatToData($arrData['total_contract_vat']),
                    'fee_other'		      	  => $number->formatToData($arrData['fee_other']),
                    'marketer_id'             => $arrItem['marketer_id'],
                    'price_total'             => $number->formatToData($arrData['total_contract_product'])+$number->formatToData($arrData['total_contract_vat'])+$number->formatToData($arrData['fee_other']),

                    'status_id'               => DA_CHOT,
                    'paid_cost'               => 'f',
                    'sale_note'               => $arrData['sale_note'],
                    'ghtk_note'               => $arrData['ghtk_note'],

                    'groupaddressId'          => $arrData['groupaddressId'],
                    'size_product_id'         => $arrData['size_product_id'],
                    'ORDER_SERVICE'           => $arrData['ORDER_SERVICE'],
                    'pick_work_shift'         => $arrData['pick_work_shift'],
                    'deliver_work_shift'      => $arrData['deliver_work_shift'],
                    'production_type_id'      => $arrData['production_type_id'],

                    'user_id'                 => $this->userInfo->getUserInfo('id'),
                    'sale_branch_id'          => $this->userInfo->getUserInfo('sale_branch_id'),
                    'sale_group_id'           => $this->userInfo->getUserInfo('sale_group_id'),

                    'created'                 => date('Y-m-d H:i:s'),
                    'created_by'              => $this->userInfo->getUserInfo('id'),
                    'options'                 => serialize($contract_options)
                );

                $this->tableGateway->insert($data); // Thực hiện lưu database

                // Lưu mã hoá đơn
                $this->saveItem(array('data' => $id), array('task' => 'update-code'));
                // Cập nhật tổng số lượng sản phẩm trong đơn hàng
                $this->saveItem(array('data' => $id), array('task' => 'update-number-product-total'));
                // Lưu đơn thứ bao nhiêu của khách hàng
                $this->saveItem(array('data' => $id), array('task' => 'update-index-number'));
                // Cập nhật thông tin đơn hàng đầu tiên cho khách hàng
                $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem(array('contract_id' => $id), array('task' => 'update-contract-first'));
                // Thêm chi tiết sản phẩm đơn hàng
//                foreach($contract_options['product'] as $arraydata){
//                    $this->getServiceLocator()->get('Admin\Model\ContractDetailTable')->saveItem(array('data' => $arraydata, 'contract_id' => $id), array('task' => 'add-item'));
//                }

                // Thêm lịch sử hệ thống
                $locations     = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache'));
                $arrParamLogs = array(
                    'data' => array(
                        'title'          => 'đơn hàng',
                        'phone'          => $arrData['phone'],
                        'name'           => $arrData['name'],
                        'action'         => 'Thêm mới',
                        'contact_id'     => $contact_id,
                        'contract_id'    => $id,
                        'options'        => array(
                            'date'                      => $arrData['date'],
                            'price_total'               => $arrData['price_total'],
                            'user_id'                   => $data['user_id'],
                            'sale_branch_id'            => $data['sale_branch_id'],
                            'sale_group_id'             => $data['sale_group_id'],
                            'products'                  => $data['options'],
                            'name'                      => $data['name'],
                            'phone'                     => $data['phone'],
                            'address'                   => $data['address'],
                            'location_city_id'          => $locations[$data['location_city_id']]->name,
                            'location_district_id'      => $locations[$data['location_district_id']]->name,
                            'location_town_id'          => $locations[$data['location_town_id']]->name,
                            'marketer_id'               => $data['marketer_id'],
                        )
                    )
                );
                $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));

                return $id;
            }
        }

		if($options['task'] == 'edit-kov-item') {

			$contact_old = $arrParam['contact_old'];
			$contact_new = $arrParam['contact_new'];
            if($contact_new['id'] != $contact_old['id'] ) {
                $arrParamContact['item']                      		= $contact_new;
                $arrParamContact['data']['id']                		= $contact_new['id'];
                $arrParamContact['data']['contract_total']    		= $contact_new['contract_total'] + 1;
                $arrParamContact['data']['contract_number']   		= $contact_new['contract_number'] + 1;
                $arrParamContact['data']['contract_price_total']    = $contact_new['contract_price_total'] + $number->formatToData($arrData['price_total']);
                $contact_id_new = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($arrParamContact, array('task' => 'edit-item'));

                $arrParamContact['item']                      		= $contact_old;
                $arrParamContact['data']['id']                		= $contact_old['id'];
                $arrParamContact['data']['contract_total']    		= $contact_old['contract_total'] - 1;
                $arrParamContact['data']['contract_number']   		= $contact_old['contract_number'] - 1;
                $arrParamContact['data']['contract_price_total']    = $contact_old['contract_price_total'] - $number->formatToData($arrData['price_total']);
                $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($arrParamContact, array('task' => 'edit-item'));
            }
            else{
                $arrParamContact['item']                      		= $contact_new;
                $arrParamContact['data']['id']                		= $contact_new['id'];
                $arrParamContact['data']['contract_price_total']    = $contact_new['contract_price_total'] - $number->formatToData($arrItem['price_total']) + $number->formatToData($arrData['price_total']);
                $contact_id_new = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($arrParamContact, array('task' => 'edit-item'));
            }



		    $id = $arrData['id'];
			$contract_product = $arrData['contract_product'];

            $contract_options['product']  = array();
            for($i = 0; $i < count($contract_product['product_id']); $i++){
                if(!empty($contract_product['product_id'][$i])) {
                    $contract_options['product'][$i]['full_name']        = $contract_product['full_name'][$i]; // Tên đầy đủ
                    $contract_options['product'][$i]['product_id']       = $contract_product['product_id'][$i]; // id sản phẩm
                    $contract_options['product'][$i]['code']             = $contract_product['code'][$i];// mã sản phẩm
                    $contract_options['product'][$i]['numbers']          = $number->formatToData($contract_product['numbers'][$i]); // số lượng của đơn hàng
                    $contract_options['product'][$i]['price']            = $number->formatToData($contract_product['price'][$i]); // giá bán
                    $contract_options['product'][$i]['total']            = $number->formatToData($contract_product['total'][$i]); // tổng tiền (chính là cột thành tiền)
                    $contract_options['product'][$i]['cost']             = $contract_product['cost'][$i]; // giá vốn kov
                    $contract_options['product'][$i]['weight']           = $contract_product['weight'][$i]; // Khối lượng 1 gói hàng (gram)
                    $contract_options['product'][$i]['categoryId']       = $contract_product['categoryId'][$i]; // Khối lượng 1 gói hàng (gram)
                    $contract_options['product'][$i]['categoryName']     = $contract_product['categoryName'][$i]; // Khối lượng 1 gói hàng (gram)
                    $contract_options['product'][$i]['car_year']         = $contract_product['car_year'][$i]; // Tên xe năm sản xuất
                    $contract_options['product'][$i]['length']           = $contract_product['length'][$i]; // Chiều dài
                    $contract_options['product'][$i]['width']            = $contract_product['width'][$i]; // Chiều rộng
                    $contract_options['product'][$i]['height']           = $contract_product['height'][$i]; // Chiều cao

                    $item_inven = $this->getServiceLocator()->get('Admin\Model\KovProductBranchTable')->getItem(array('productId' => $contract_product['product_id'][$i], 'branchId' => $arrItem['sale_branch_id']));
                    if($item_inven){
                        $capital_default = (int)($contract_product['cost'][$i] + $item_inven['cost_new']) * $number->formatToData($contract_product['numbers'][$i]);

                        $contract_options['product'][$i]['capital_default'] = (int)$capital_default; // Giá vốn mới
                        $contract_options['product'][$i]['cost_new']        = $item_inven['cost_new']; // giá vốn thăng theo chi nhánh
                        $contract_options['product'][$i]['fee']             = $item_inven['fee']; // phụ phí
                    }
                }
            }

            $data = array(
                'name'                    => $arrData['name'],
                'phone'                   => $arrData['phone'],
                'location_city_id'        => $arrData['location_city_id'],
                'location_district_id'    => $arrData['location_district_id'],
                'location_town_id'        => $arrData['location_town_id'],
                'address'                 => $arrData['address'],
                'price_deposits'          => $number->formatToData($arrData['price_deposits']),
                'price_paid'              => 0,
                'price_owed'              => $number->formatToData($arrData['price_owed']),
                'total_price_product'     => $number->formatToData($arrData['total_contract_product']),
                'contact_id'              => $contact_id_new,
                'coincider_code'          => $arrData['coincider_code'],
                'vat'		      		  => $number->formatToData($arrData['total_contract_vat']),
                'fee_other'		      	  => $number->formatToData($arrData['fee_other']),
                'price_total'             => $number->formatToData($arrData['total_contract_product'])+$number->formatToData($arrData['total_contract_vat'])+$number->formatToData($arrData['fee_other']),
                'marketer_id'             => $arrItem['marketer_id'],
                'sale_note'               => $arrData['sale_note'],
                'ghtk_note'               => $arrData['ghtk_note'],
                'groupaddressId'          => $arrData['groupaddressId'],
                'size_product_id'         => $arrData['size_product_id'],
                'ORDER_SERVICE'           => $arrData['ORDER_SERVICE'],
                'options'                 => serialize($contract_options),
                'pick_work_shift'         => $arrData['pick_work_shift'],
                'deliver_work_shift'      => $arrData['deliver_work_shift'],
                'production_type_id'      => $arrData['production_type_id'],
            );

			// Cập nhật đơn hàng
			$this->tableGateway->update($data, array('id' => $id));
            // Cập nhật tổng số lượng sản phẩm trong đơn hàng
            $this->saveItem(array('data' => $id), array('task' => 'update-number-product-total'));
            // Xóa sản phẩm cũ của đơn hàng
//            $this->getServiceLocator()->get('Admin\Model\ContractDetailTable')->saveItem(array('contract_id' => $id), array('task' => 'delete_product_by_contract_id'));
//            // Thêm chi tiết sản phẩm đơn hàng
//            foreach($contract_options['product'] as $arraydata){
//                $this->getServiceLocator()->get('Admin\Model\ContractDetailTable')->saveItem(array('data' => $arraydata, 'contract_id' => $id), array('task' => 'add-item'));
//            }

			// Thêm lịch sử hệ thống
			if(!empty($id)) {
			    $arrCheckLogs = array('name', 'phone', 'location_city_id', 'location_district_id', 'location_town_id', 'address', 'marketer_id', 'options');
			    $arrCheckResult = array();
                $locations     = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache'));
			    foreach ($arrCheckLogs AS $field) {
		            if(isset($data[$field])) {
		                $check = $data[$field];
		                if($field == 'date') {
		                    $check = $date->formatToView($data[$field]);
		                }
			            if($check != $arrItem[$field]) {
			                $value_change = $data[$field];
                            if($field == 'location_city_id' || $field == 'location_district_id' || $field == 'location_town_id')
                                $value_change = $locations[$data[$field]]->name;

			                $arrCheckResult[$field] = $value_change;
			            }
			        }
			    }

			    if(!empty($arrCheckResult)) {
			        $arrParamLogs = array(
			            'data' => array(
			                'title'          => 'đơn hàng',
			                'phone'          => $contact_new['phone'],
			                'name'           => $contact_new['name'],
			                'action'         => 'Sửa',
			                'contact_id'     => $contact_new['id'],
			                'contract_id'    => $id,
			                'options'        => $arrCheckResult
			            )
			        );
			        $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
			    }
			}

			return $id;
		}

        if($options['task'] == 'edit-product-price') {
            $id = $arrData['id'];
            $contract_product = $arrData['contract_product'];
            $contact_new = $arrParam['contact_new'];

            $contract_options['product']  = array();
            for($i = 0; $i < count($contract_product['product_id']); $i++){
                if(!empty($contract_product['product_id'][$i])) {
                    $contract_options['product'][$i]['full_name']        = $contract_product['full_name'][$i]; // Tên đầy đủ
                    $contract_options['product'][$i]['product_id']       = $contract_product['product_id'][$i]; // id sản phẩm
                    $contract_options['product'][$i]['code']             = $contract_product['code'][$i];// mã sản phẩm
                    $contract_options['product'][$i]['numbers']          = $number->formatToData($contract_product['numbers'][$i]); // số lượng của đơn hàng
                    $contract_options['product'][$i]['price']            = $number->formatToData($contract_product['price'][$i]); // giá bán
                    $contract_options['product'][$i]['total']            = $number->formatToData($contract_product['total'][$i]); // tổng tiền (chính là cột thành tiền)
                    $contract_options['product'][$i]['cost']             = $contract_product['cost'][$i]; // giá vốn kov
                    $contract_options['product'][$i]['weight']           = $contract_product['weight'][$i]; // Khối lượng 1 gói hàng (gram)
                    $contract_options['product'][$i]['categoryId']       = $contract_product['categoryId'][$i]; // Khối lượng 1 gói hàng (gram)
                    $contract_options['product'][$i]['categoryName']     = $contract_product['categoryName'][$i]; // Khối lượng 1 gói hàng (gram)
                    $contract_options['product'][$i]['car_year']         = $contract_product['car_year'][$i]; // Tên xe năm sản xuất
                    $contract_options['product'][$i]['length']           = $contract_product['length'][$i]; // Chiều dài
                    $contract_options['product'][$i]['width']            = $contract_product['width'][$i]; // Chiều rộng
                    $contract_options['product'][$i]['height']           = $contract_product['height'][$i]; // Chiều cao

                    $item_inven = $this->getServiceLocator()->get('Admin\Model\KovProductBranchTable')->getItem(array('productId' => $contract_product['product_id'][$i], 'branchId' => $arrItem['sale_branch_id']));
                    if($item_inven){
                        $capital_default = (int)($contract_product['cost'][$i] + $item_inven['cost_new']) * $number->formatToData($contract_product['numbers'][$i]);

                        $contract_options['product'][$i]['capital_default'] = (int)$capital_default; // Giá vốn mới
                        $contract_options['product'][$i]['cost_new']        = $item_inven['cost_new']; // giá vốn thăng theo chi nhánh
                        $contract_options['product'][$i]['fee']             = $item_inven['fee']; // phụ phí
                    }
                }
            }

            $data = array(
                'price_deposits'          => $number->formatToData($arrData['price_deposits']),
                'price_paid'              => 0,
                'price_owed'              => $number->formatToData($arrData['price_owed']),
                'total_price_product'     => $number->formatToData($arrData['total_contract_product']),
                'vat'		      		  => $number->formatToData($arrData['total_contract_vat']),
                'fee_other'		      	  => $number->formatToData($arrData['fee_other']),
                'price_total'             => $number->formatToData($arrData['total_contract_product'])+$number->formatToData($arrData['total_contract_vat'])+$number->formatToData($arrData['fee_other']),
                'options'                 => serialize($contract_options)
            );

            // Cập nhật đơn hàng
            $this->tableGateway->update($data, array('id' => $id));
            // Cập nhật tổng số lượng sản phẩm trong đơn hàng
            $this->saveItem(array('data' => $id), array('task' => 'update-number-product-total'));
//            // Xóa sản phẩm cũ của đơn hàng
//            $this->getServiceLocator()->get('Admin\Model\ContractDetailTable')->saveItem(array('contract_id' => $id), array('task' => 'delete_product_by_contract_id'));
//            // Thêm chi tiết sản phẩm đơn hàng
//            foreach($contract_options['product'] as $arraydata){
//                $this->getServiceLocator()->get('Admin\Model\ContractDetailTable')->saveItem(array('data' => $arraydata, 'contract_id' => $id), array('task' => 'add-item'));
//            }

            // Thêm lịch sử hệ thống
            if(!empty($id)) {
                $arrCheckLogs = array('name', 'phone', 'location_city_id', 'location_district_id', 'location_town_id', 'address', 'marketer_id', 'options');
                $arrCheckResult = array();
                $locations     = $this->getServiceLocator()->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache'));
                foreach ($arrCheckLogs AS $field) {
                    if(isset($data[$field])) {
                        $check = $data[$field];
                        if($field == 'date') {
                            $check = $date->formatToView($data[$field]);
                        }
                        if($check != $arrItem[$field]) {
                            $value_change = $data[$field];
                            if($field == 'location_city_id' || $field == 'location_district_id' || $field == 'location_town_id')
                                $value_change = $locations[$data[$field]]->name;

                            $arrCheckResult[$field] = $value_change;
                        }
                    }
                }

                if(!empty($arrCheckResult)) {
                    $arrParamLogs = array(
                        'data' => array(
                            'title'          => 'đơn hàng',
                            'phone'          => $contact_new['phone'],
                            'name'           => $contact_new['name'],
                            'action'         => 'Sửa',
                            'contact_id'     => $contact_new['id'],
                            'contract_id'    => $id,
                            'options'        => $arrCheckResult
                        )
                    );
                    $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
                }
            }

            return $id;
        }

        // Import đơn hàng có sẵn
        if($options['task'] == 'import-contract-old') {
            // Thêm đơn hàng
            $id = $gid->getId();
            $contract_options = array();

            $contract_options['sale_note']       = $arrData['sale_note'];
            $contract_options['production_note'] = $arrData['production_note'];
            $contract_options['contact_type']    = $arrData['contact_type'];

            $contract_options['product'][0]['key_id']        = $gid->getId();
            $contract_options['product'][0]['product_id']        = $arrData['product_id'];
            $contract_options['product'][0]['number_production'] = $arrData['number_production'];
            $contract_options['product'][0]['product_name']      = $arrData['product_name'];
            $contract_options['product'][0]['stock']             = $arrData['stock'];
            $contract_options['product'][0]['carpet_color_id']   = $arrData['carpet_color_id'];
            $contract_options['product'][0]['number_carpet']     = $arrData['number_carpet'];
            $contract_options['product'][0]['tangled_color_id']  = $arrData['tangled_color_id'];
            $contract_options['product'][0]['number_tangled']    = $arrData['number_tangled'];
            $contract_options['product'][0]['flooring_id']       = $arrData['flooring_id'];
            $contract_options['product'][0]['listed_price']      = $arrData['listed_price'];
            $contract_options['product'][0]['capital_default']   = $arrData['capital_default'];
            $contract_options['product'][0]['price']             = $arrData['price'];
            $contract_options['product'][0]['vat']               = $arrData['vat'];
            $contract_options['product'][0]['price_production']  = $arrData['price_production'];
            $contract_options['product'][0]['numbers']           = 1;
            $contract_options['product'][0]['sale_price']        = $arrData['listed_price'] - $arrData['price'];
            $contract_options['product'][0]['total']             = $arrData['price'] - $arrData['vat'];
            $contract_options['product'][0]['product_alias']     = $arrData['product_alias'];
            $contract_options['product'][0]['product_group_id']  = $arrData['product_group_id'];
            $contract_options['product'][0]['sale_branch_id']    = $arrData['sale_branch_id'];

            $data = array(
                'id'                        => $id,
                'date'                      => $date->formatToData($arrData['date']),
                'bill_code'                 => $arrData['bill_code'],
                'code'                      => $arrData['code'],
                'price_total'               => $number->formatToData($arrData['price_total']),
                'price_deposits'            => $number->formatToData($arrData['price_deposits']),
                'price_surcharge'           => $number->formatToData($arrData['price_surcharge']),
                'price_paid'                => $number->formatToData($arrData['price_paid']),
                'price_owed'                => $number->formatToData($arrData['price_owed']),
                'contact_id'                => $arrData['contact_id'],
                'marketer_id'               => $arrData['marketer_id'],
                'user_id'                   => $arrData['user_id'],
                'product_group_id'          => $arrData['product_group_id'],
                'production_department_type'=> $arrData['production_department_type'],
                'status_check_id'           => $arrData['status_check_id'],
                'status_acounting_id'       => $arrData['status_acounting_id'],
                'production_type_id'        => $arrData['production_type_id'],
                'shipper_id'                => $arrData['shipper_id'],
                'transport_id'              => $arrData['transport_id'],
                'sale_branch_id'            => $arrData['sale_branch_id'],
                'sale_group_id'             => $arrData['sale_group_id'],
                'status_guarantee_id'       => $arrData['status_guarantee_id'],
                'code_old'                  => $arrData['code_old'],
                'guarantee_date'            => $date->formatToData($arrData['guarantee_date']),
                'guarantee_note'            => $arrData['guarantee_note'],

                'created'                   => date('Y-m-d H:i:s'),
                'created_by'                => !empty($arrData['user_id']) ? $arrData['user_id'] : $this->userInfo->getUserInfo('id'),
                'options'                   => serialize($contract_options)
            );

            $this->tableGateway->insert($data); // Thực hiện lưu database

            // Lưu đơn thứ bao nhiêu của khách hàng
            $this->saveItem(array('data' => $id), array('task' => 'update-index-number'));
            // Cập nhật các loại doanh số sau khi tạo đơn hàng
            $this->saveItem(array('data' => $id), array('task' => 'update-sales'));
            // Cập nhật thông tin đơn hàng đầu tiên cho khách hàng
            $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem(array('contract_id' => $id), array('task' => 'update-contract-first'));

            return $id;
        }

		// cập nhật sản xuất
		if ($options['task'] == 'update-production') {
			$id = $arrData['id'];
            $arrItem = empty($arrItem) ? $this->getItem(array('id' => $id)) : $arrItem;

			$data = array();
			$contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $id));
			$options = unserialize($contract['options']);
			if(!empty($arrData['production_department_type'])) {
				$data['production_department_type'] = $arrData['production_department_type'];
				// cập nhật doanh thu cho Marketer
				if ($arrItem['production_department_type'] != STATUS_CONTRACT_PRODUCT_PRODUCTED && $arrData['production_department_type'] == STATUS_CONTRACT_PRODUCT_PRODUCTED) {
					$this->getServiceLocator()->get('Admin\Model\MarketingReportTable')->saveItem(array('data'=> array('contract_id' => $arrItem['id'])), array('task' => 'update-sales-finish')); # bỏ
				}
				// lưu ngày chuyển trạng thái đã giao hàng
				if ($arrItem['production_department_type'] != STATUS_CONTRACT_PRODUCT_POST && $arrData['production_department_type'] == STATUS_CONTRACT_PRODUCT_POST) {
					$data['production_date_send'] = date('Y-m-d');
				}

				// lưu ngày chuyển trạng thái đã sản xuất, hoặc trong trường hợp nhân viên chuyển trực tiếp lên trạng thái đã giao hàng
				if (empty($arrItem['production_date']) && ($arrData['production_department_type'] == STATUS_CONTRACT_PRODUCT_PRODUCTED || $arrData['production_department_type'] == STATUS_CONTRACT_PRODUCT_POST)) {
                    $data['production_date'] = date('Y-m-d');
				}
			}

			$contract_product = $arrData['contract_product'];
			if (isset($arrData['contract_product'])) 
		    for($i = 0; $i < count($options['product']); $i++){
				$totalProduction = 0;
				$carpetColor = null;
				$tangledColor = null;
				if (!empty($options['product'][$i]['carpet_color_id'])) {
                    $carpetColor = $this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->getItem(array('id' => $options['product'][$i]['carpet_color_id']), null);
				}
				if (!empty($options['product'][$i]['tangled_color_id'])) {
                    $tangledColor = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->getItem(array('id' => $options['product'][$i]['tangled_color_id']), null);
				}
				
				$product = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'list-item'));
				$colorGroup = $this->getServiceLocator()->get('Admin\Model\ColorGroupTable')->listItem(null, array('task' => 'list-item'));
				
				if (!empty($carpetColor)) {
					$priceCarpet = $colorGroup[$carpetColor['parent']]['price'];
				}
				if (!empty($tangledColor)) {
					$priceTangled = $colorGroup[$tangledColor['parent']]['price'];
				}

				if (!empty($contract_product['number_production'][$i])) {
					$priceProduct = $product[$options['product'][$i]['product_id']]['price'];
					$totalProduction = $contract_product['number_production'][$i] * $priceProduct;
				}
				else {
					$totalProduction = $priceCarpet*$contract_product['number_carpet'][$i] + $priceTangled*$contract_product['number_tangled'][$i];
				}

                $options['product'][$i]['number_production'] = !empty($contract_product['number_production'][$i]) ? $contract_product['number_production'][$i] : 0;
                $options['product'][$i]['number_carpet']     = !empty($contract_product['number_carpet'][$i]) ? $contract_product['number_carpet'][$i] : 0;
                $options['product'][$i]['number_tangled']    = !empty($contract_product['number_tangled'][$i]) ? $contract_product['number_tangled'][$i] : 0;
                $options['product'][$i]['price_production']  = !empty($contract_product['price_production'][$i]) ? $number->formatToNumber($contract_product['price_production'][$i]) : 0;


                if(isset($contract_product['technical_id'][$i])){
                    $options['product'][$i]['technical_id']      = !empty($contract_product['technical_id'][$i]) ? implode(',', $contract_product['technical_id'][$i]) : '';
                }
                if(isset($contract_product['tailors_id'][$i])){
                    $options['product'][$i]['tailors_id']        = !empty($contract_product['tailors_id'][$i]) ? $contract_product['tailors_id'][$i] : '';
                }

                if(empty($options['product'][$i]['stock'])){
                    $options['product'][$i]['total_production']  = !empty($totalProduction) ? $number->formatToNumber($totalProduction) : 0;
                }
			}

			if(!empty($arrData['production_note'])) {
				$options['production_note'] = $arrData['production_note'];
			}

			if(!empty($arrData['shipper_id'])) {
				$data['shipper_id'] = $arrData['shipper_id'];
			}

            $data['options'] = serialize($options);

            // Cập nhật lại số lượng sản phẩm của đơn hàng có sẵn khi hủy sản xuất
            if($arrData['production_department_type'] == STATUS_CONTRACT_PRODUCT_CANCEL) {
                $data['lock'] = 1;

                $contract_options = unserialize($contract['options']);
                if (!empty($contract_options)) {
                    $products = $contract_options['product'];
                    if (!empty($products)) {
                        foreach ($products as $key => $product) {
                            if(!empty($product['product_return_id'])){
                                $item_upd = $this->getServiceLocator()->get('Admin\Model\ProductReturnTable')->getItem(array('id' => $product['product_return_id']));
                                $data_upd['item'] = $item_upd;
                                $data_upd['data']['quantity'] = $item_upd['quantity'] + $product['numbers'];
                                $this->getServiceLocator()->get('Admin\Model\ProductReturnTable')->saveItem($data_upd, array('task' => 'edit-item'));
                            }
                        }
                    }
                }
            }

			// Cập nhật đơn hàng
			$this->tableGateway->update($data, array('id' => $id));

            // Ghi log
            if(!empty($arrData['production_department_type']) && $arrItem['production_department_type'] != $arrData['production_department_type']){
                $status_product    = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-department')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
                $arrParamLogs = array(
                    'data' => array(
                        'title'          => 'Cập nhật đơn hàng: '.$arrItem['code'],
                        'action'         => 'Cập nhật',
                        'contact_id'     => $arrItem['contact_id'],
                        'contract_id'    => $arrItem['id'],
                        'options'        => array('production_department_type' => $status_product[$arrItem['production_department_type']]['name'].' => '.$status_product[$arrData['production_department_type']]['name']),
                    )
                );
                $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
            }

            return $id;
		}

		// cập nhật sản xuất thợ kỹ thuật - thợ may
		if ($options['task'] == 'update-production-technical') {
			$id = $arrData['id'];
			$data = array();
			$contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $id));
			$options = unserialize($contract['options']);
			if(!empty($arrData['production_department_type'])) {
				$data['production_department_type'] = $arrData['production_department_type'];
				// cập nhật doanh thu cho Marketer
				if ($arrItem['production_department_type'] != STATUS_CONTRACT_PRODUCT_PRODUCTED && $arrData['production_department_type'] == STATUS_CONTRACT_PRODUCT_PRODUCTED) {
					$this->getServiceLocator()->get('Admin\Model\MarketingReportTable')->saveItem(array('data'=> array('contract_id' => $arrItem['id'])), array('task' => 'update-sales-finish')); # bỏ
				}
			}

			$contract_product = $arrData['contract_product'];
			if (isset($arrData['contract_product']))
		    for($i = 0; $i < count($options['product']); $i++){
				$totalProduction = 0;
				$carpetColor = null;
				$tangledColor = null;
				if (!empty($options['product'][$i]['carpet_color_id'])) {
                    $carpetColor = $this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->getItem(array('id' => $options['product'][$i]['carpet_color_id']), null);
				}
				if (!empty($options['product'][$i]['tangled_color_id'])) {
                    $tangledColor = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->getItem(array('id' => $options['product'][$i]['tangled_color_id']), null);
				}

				$product = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'list-item'));
				$colorGroup = $this->getServiceLocator()->get('Admin\Model\ColorGroupTable')->listItem(null, array('task' => 'list-item'));

				if (!empty($carpetColor)) {
					$priceCarpet = $colorGroup[$carpetColor['parent']]['price'];
				}
				if (!empty($tangledColor)) {
					$priceTangled = $colorGroup[$tangledColor['parent']]['price'];
				}

				if (!empty($contract_product['number_production'][$i])) {
					$priceProduct = $product[$options['product'][$i]['product_id']]['price'];
					$totalProduction = $contract_product['number_production'][$i] * $priceProduct;
				} else {
					$totalProduction = $priceCarpet*$contract_product['number_carpet'][$i] + $priceTangled*$contract_product['number_tangled'][$i];
				}

                $options['product'][$i]['number_production'] = !empty($contract_product['number_production'][$i]) ? $contract_product['number_production'][$i] : 0;
                $options['product'][$i]['number_carpet']     = !empty($contract_product['number_carpet'][$i]) ? $contract_product['number_carpet'][$i] : 0;
                $options['product'][$i]['number_tangled']    = !empty($contract_product['number_tangled'][$i]) ? $contract_product['number_tangled'][$i] : 0;
                $options['product'][$i]['price_production']  = !empty($contract_product['price_production'][$i]) ? $number->formatToNumber($contract_product['price_production'][$i]) : 0;
                $options['product'][$i]['technical_id']      = !empty($contract_product['technical_id'][$i]) ? implode(',', $contract_product['technical_id'][$i]) : '';
                $options['product'][$i]['tailors_id']        = !empty($contract_product['tailors_id'][$i]) ? implode(',', $contract_product['tailors_id'][$i]) : '';


                if(empty($options['product'][$i]['stock'])){
                    $options['product'][$i]['total_production']  = !empty($totalProduction) ? $number->formatToNumber($totalProduction) : 0;
                }
			}

			if(!empty($arrData['production_note'])) {
				$options['production_note'] = $arrData['production_note'];
			}

            $data['options'] = serialize($options);

			// Cập nhật đơn hàng
			$this->tableGateway->update($data, array('id' => $id));

            // Cập nhật lại số lượng sản phẩm của đơn hàng có sẵn khi hủy sản xuất
            if($arrData['production_department_type'] == STATUS_CONTRACT_PRODUCT_CANCEL) {
                $contract_options = unserialize($contract['options']);
                if (!empty($contract_options)) {
                    $products = $contract_options['product'];
                    if (!empty($products)) {
                        foreach ($products as $key => $product) {
                            if (!empty($product['stock'])) {
                                $param_check = array(
                                    'code'      => $product['stock'],
                                    'key_id'    => $product['key_id'],
                                );
                                $this->saveItem($param_check, array('task' => 'update-number-product'));
                            }
                        }
                    }
                }
            }

            // Ghi log
            if(!empty($arrData['production_department_type']) && $arrItem['production_department_type'] != $arrData['production_department_type']){
                $status_product    = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-department')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
                $arrParamLogs = array(
                    'data' => array(
                        'title'          => 'Cập nhật đơn hàng: '.$arrItem['code'],
                        'action'         => 'Cập nhật',
                        'contact_id'     => $arrItem['contact_id'],
                        'contract_id'    => $arrItem['id'],
                        'options'        => array('production_department_type' => $status_product[$arrItem['production_department_type']]['name'].' => '.$status_product[$arrData['production_department_type']]['name']),
                    )
                );
                $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
            }

            // Cập nhật trạng thái đơn đã nhập thợ kỹ thuật, thợ may chưa
            $this->saveItem(array('data' => $id), array('task' => 'update-status-technical'));
            return $id;
		}

		// cập nhật giá vốn đơn hàng có sản phẩm có sẵn
		if ($options['task'] == 'update-cost-new') {
            $id         = $arrData['id'];
            $options    = $arrData['options'];
			$data = array();
            $data['options'] = serialize($options);
			// Cập nhật đơn hàng
			$this->tableGateway->update($data, array('id' => $id));
		}

		// cập nhật giá vốn đơn hàng có sản phẩm có sẵn
		if ($options['task'] == 'update-capital') {
            $id             = $arrData['id'];
            $options_update = $arrData['options_update'];

			$data = array();
			$contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $id));
			$options = unserialize($contract['options']);

		    for($i = 0; $i < count($options['product']); $i++){
		        $options['product'][$i]['total_production']  = $options_update[$i]['total_production'];
			}
            $data['options'] = serialize($options);

			// Cập nhật đơn hàng
			$this->tableGateway->update($data, array('id' => $id));
		}

		// Đơn hàng có sẵn - Sửa tên xe năm sản xuất
		if ($options['task'] == 'edit-warehouse') {
			$id = $arrData['id'];
			$data = array();
			$contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $id));
			$options = unserialize($contract['options']);
			$contract_product = $arrData['contract_product'];

		    for($i = 0; $i < count($options['product']); $i++){
                $options['product'][$i]['product_name'] = $contract_product['product_name'][$i] ;
			}
            $data['options'] = serialize($options);

			// Cập nhật đơn hàng
			$this->tableGateway->update($data, array('id' => $id));
		}

		// Đơn hàng có sẵn - Cập nhật giá vốn mặc định
		if ($options['task'] == 'edit-capital-default') {
			$id = $arrData['id'];
			$data = array();
			$contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $id));
			$options = unserialize($contract['options']);

		    for($i = 0; $i < count($options['product']); $i++){
				if ($options['product'][$i]['capital_default']['stock'])
                $options['product'][$i]['capital_default'] = $arrData['capital_default'];
			}
            $data['options'] = serialize($options);

			$this->tableGateway->update($data, array('id' => $id));
			return $id;
		}

		// Đơn hàng có sẵn - xóa sản phẩm.
		if ($options['task'] == 'del-product-warehouse') {
			$id = $arrData['id'];
			$data = array();
			$contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $id));
			$options = unserialize($contract['options']);
			$key_ids = $arrData['contract_product']['key_id'];
			$products_new = [];
            if(!empty($key_ids)) {
                foreach ($options['product'] as $key => $value) {
                    if (in_array($value['key_id'], $key_ids)) {
                        $products_new[] = $value;
                    }
                }
            }

            $options['product'] = $products_new;
            $data['options'] = serialize($options);

			// Cập nhật đơn hàng
			$this->tableGateway->update($data, array('id' => $id));
		}

		// cập nhật các trạng thái
		if ($options['task'] == 'update-status') {
			$id = $arrData['id'];
            $arrItem = $this->getItem(array('id' => $id));
			$data = array();

            if(!empty($arrData['status_check_id'])) {
                $data['status_check_id'] = $arrData['status_check_id'];
            }
            if(!empty($arrData['status_acounting_id'])) {
                $data['status_acounting_id'] = $arrData['status_acounting_id'];
            }
            if(strlen($arrData['price_transport'])) {
                $data['price_transport'] = preg_replace('/,/','',$arrData['price_transport'])*1;
            }

			// Cập nhật đơn hàng
			$this->tableGateway->update($data, array('id' => $id));

            // Ghi log
            $check_status_change = false;
            if(!empty($arrData['status_check_id']) && $arrItem['status_check_id'] != $arrData['status_check_id']){
                $status_check      = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-check')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
                $check_status_change = true;
                $option_log = array('status_check_id' => $status_check[$arrItem['status_check_id']]['name'].' => '.$status_check[$arrData['status_check_id']]['name']);
            }
            if(!empty($arrData['status_acounting_id']) && $arrItem['status_acounting_id'] != $arrData['status_acounting_id']){
                $check_status_change = true;
                $status_accounting = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-acounting')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
                $option_log = array('status_id' => $status_accounting[$arrItem['status_acounting_id']]['name'].' => '.$status_accounting[$arrData['status_acounting_id']]['name']);
            }
            if($check_status_change){
                $arrParamLogs = array(
                    'data' => array(
                        'title'          => 'Cập nhật đơn hàng: '.$arrItem['code'],
                        'action'         => 'Cập nhật',
                        'contact_id'     => $arrItem['contact_id'],
                        'contract_id'    => $arrItem['id'],
                        'options'        => $option_log,
                    )
                );
                $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
            }
		}

		// cập nhật các tiền thanh toán khi thêm hóa đơn phụ phí
		if ($options['task'] == 'update-bill-add') {
			$id = $arrData['id'];
			$data = array();

            if(isset($arrData['price_paid'])) {
                $data['price_paid'] = $number->formatToNumber($arrData['price_paid']);
            }
            if(isset($arrData['price_accrued'])) {
                $data['price_accrued'] = $number->formatToNumber($arrData['price_accrued']);
            }
            if(isset($arrData['price_owed'])) {
                $data['price_owed'] = $number->formatToNumber($arrData['price_owed']);
            }

            if(isset($arrData['price_surcharge'])) {
                $data['price_surcharge'] = $number->formatToNumber($arrData['price_surcharge']);
            }

			// Cập nhật đơn hàng
			$this->tableGateway->update($data, array('id' => $id));
		}

		// cập nhật các trạng thái
		if ($options['task'] == 'update-note') {
			$id = $arrData['id'];
			$options = !empty($arrItem['options']) ? unserialize($arrItem['options']) : array();
			$data = array();

			$options['sale_note']       = $arrData['sale_note'];
//			$options['production_note'] = $arrData['production_note'];

			if(!empty($options)){
			    $data['options'] = serialize($options);
            }

			// Cập nhật đơn hàng
			$this->tableGateway->update($data, array('id' => $id));
		}

		// chuyển thành đơn lẻ
		if ($options['task'] == 'convert-order') {
			$id = $arrData['id'];
			$options = !empty($arrItem['options']) ? unserialize($arrItem['options']) : array();
			$data = array();

			$options['sale_note']       = $arrData['sale_note'];

			if(!empty($options)){
                $data['unit_transport'] = $arrData['unit_transport'];
			    $data['ghtk_code'] = $arrData['unit_transport'] != '5sauto' ? $arrData['ghtk_code'] : $arrData['shipper_id'];
			    $data['ghtk_status'] = $arrData['ghtk_status'];
			    $data['price_transport'] = $number->formatToData($arrData['price_transport']);
            }

			// Cập nhật đơn hàng
			$this->tableGateway->update($data, array('id' => $id));
		}

		// Cập nhật các loại doanh số: Doanh số mới, Doanh số cũ, Doanh số chăm sóc, Doanh số bán chéo.
		if ($options['task'] == 'update-sales') {
		    $id = $arrData;
		    // Lấy ra đơn hàng cần cập nhật doanh số
			$item = $this->getItem(array('id' => $id));
			$options = unserialize($item['options']);
			$products = $options['product'];
            $contract_create = substr($item['created'], 0, 10);

            // Lấy ra liên hệ vừa lên đơn hàng
            $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $item['contact_id']));
            $contact_create  = substr($contact['created'], 0, 10);

            $sales_new   = 0;
            $sales_old   = 0;
            $sales_care  = 0;
            $sales_cross = 0;

            foreach ($products as $key => $product){
				//Điều kiện ghi nhận Doanh số mới
				if($contact_create == $contract_create && ($product['product_group_id'] == $item['product_group_id'] || empty($item['product_group_id']))){					
					$sales_new += $product['total'];
					$options['product'][$key]['sales_new']   = $product['total'];
				} else {
					$options['product'][$key]['sales_new']   = 0;
				}
				//Điều kiện ghi nhận Doanh số chăm sóc
				if($item['index_number'] != 1 && $contact_create !== $contract_create && ($product['product_group_id'] == $item['product_group_id'] || empty($item['product_group_id']))){					
					$sales_care += $product['total'];
					$options['product'][$key]['sales_care']   = $product['total'];
				} else {
					$options['product'][$key]['sales_care']   = 0;
				}
				//Điều kiện ghi nhận Doanh số cũ
				if($item['index_number'] == 1 && $contact_create !== $contract_create && ($product['product_group_id'] == $item['product_group_id'] || empty($item['product_group_id']))){					
					$sales_old += $product['total'];
					$options['product'][$key]['sales_old']   = $product['total'];
				} else {
					$options['product'][$key]['sales_old']   = 0;
				}
                // Đơn hàng lần đầu
                if($item['index_number'] == 1){
                    // Nếu nhóm sản phẩm mua lần đầu trùng với nhóm sản phẩm quan tâm
                    // (hoặc nhóm sản phẩm quan tâm bị rỗng) thì tính doanh số mới và cũ
                    if($product['product_group_id'] == $item['product_group_id'] || empty($item['product_group_id'])){
                        if($contact_create == $contract_create){ 
                            $options['product'][$key]['sales_cross'] = 0;
                        }
                        else{// Ngày lên đơn khác ngày ra contact => doanh số cũ
                            // sản phẩm này thuộc loại doanh số nào
                            $options['product'][$key]['sales_cross'] = 0;
                        }
                    }
                    // Nếu khác sản phẩm quan tâm thì tính doanh số chéo luôn từ lần mua đầu tiên
                    else{
                        $sales_cross += $product['total'];
                        // sản phẩm này thuộc loại doanh số nào
                        $options['product'][$key]['sales_cross'] = $product['total'];
                    }
                }
                // Đơn hàng thứ 2 trở đi
                else{
					if($product['product_group_id'] == $item['product_group_id']){
                        // sản phẩm này thuộc loại doanh số nào
                        $options['product'][$key]['sales_cross'] = 0;
                    }
                    else{
                        $sales_cross += $product['total'];
                        // sản phẩm này thuộc loại doanh số nào
                        $options['product'][$key]['sales_cross'] = $product['total'];
                    }
				}
				$options['product'][$key]['sales_new'] = $options['product'][$key]['sales_new']?:0;
				$options['product'][$key]['sales_old'] = $options['product'][$key]['sales_old']?:0;
				$options['product'][$key]['sales_care'] = $options['product'][$key]['sales_care']?:0;
				$options['product'][$key]['sales_cross'] = $options['product'][$key]['sales_cross']?:0;
            }

            $data = array(
                'sales_new'     => $sales_new,
                'sales_old'     => $sales_old,
                'sales_care'    => $sales_care,
                'sales_cross'   => $sales_cross,
                'options'       => serialize($options),
            );

            if(empty($contact['contract_first_code'])){
                $data['mkt_sales_new'] = $item['price_total'];
            }
            else{
                $sale_time = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-time')), array('task' => 'list-all')),array('key' => 'alias', 'value' => 'content'));
                if(isset($sale_time['sale-time-marketing'])){
                    $day_begin  = strtotime($contact['contract_first_date']);
                    $day        = date('Y-m-d H:i:s', $day_begin + $sale_time['sale-time-marketing']*3600);
                    if($day > $item['created']){
                        $data['mkt_sales_new'] = $item['price_total'];
                    }
                    else{
                        $data['mkt_sales_care'] = $item['price_total'];
                    }
                }
            }

			// Cập nhật đơn hàng
			$this->tableGateway->update($data, array('id' => $id));
		}

		// Cập nhật tổng số lượng sản phẩm của đơn hàng.
        if ($options['task'] == 'update-number-product-total') {
            $id = $arrData;
            $contract = $this->getItem(array('id' => $id));

            $options = !empty($contract['options']) ? unserialize($contract['options']) : array();
            $products = $options['product'];
            $number_product = 0;

            foreach ($products as $key => $product){
                $number_product += $product['numbers'];
            }
            $data['total_number_product'] = $number_product;
            $data['total_product'] = count($products);

            $this->tableGateway->update($data, array('id' => $id));
            return true;
        }

        // Cập nhật số sản phẩm trong đơn hàng có sẵn .
        if ($options['task'] == 'update-number-product') {
            $type_action   = $options['type_action'];
            $contract_code = $arrParam['code'];
            $key_id        = $arrParam['key_id'];

            $contract = $this->getItem( array('code' => $contract_code), array('task' => 'by-code'));
            $id = $contract['id'];

            if(!empty($contract)){
                $options = !empty($contract['options']) ? unserialize($contract['options']) : array();
                $products = $options['product'];
                if(!empty($products)){
                    foreach ($products as $key => $product){
                        if($product['key_id'] == $key_id){
                            // Trừ số lượng sản phẩm khi xóa hoặc sửa một đơn hàng chứa sản phẩm có sẵn
                            if(!empty($type_action)){
                                $products[$key]['numbers'] = 0;
                            }
                            // Cộng số lượng sản phẩm khi thêm hoặc sửa một đơn hàng chứa sản phẩm có sẵn
                            else{
                                $products[$key]['numbers'] = 1;
                            }
                            break; // khi cập nhật được một sản phẩm thì không cập nhật nữa
                        }
                    }
                }

                $options['product'] = $products;
                $data['options'] = serialize($options);

                $this->tableGateway->update($data, array('id' => $id));
                // Cập nhật tổng số lượng sản phẩm còn lại trong đơn hàng có sẵn.
                $this->saveItem(array('data' => $id), array('task' => 'update-number-product-total'));
                return true;
            }
        }

		// Import - Cập nhật mã vận đơn cho đơn hàng
		if($options['task'] == 'import-update') {				
			$data	= array(
			    'bill_code'             => $arrData['bill_code'],
			);
			$this->tableGateway->update($data, ['id' => $arrData['id']]);
			
			return $id;
		}

		// Đối soát
		if($options['task'] == 'compare-order') {
			if(!empty($arrData['status_acounting_id'])){
			    $data['status_acounting_id'] = $arrData['status_acounting_id'];
            }
			if(!empty($arrData['price_paid'])){
			    $data['price_paid'] = $arrData['price_paid'];
                $data['price_owed'] = $arrItem['price_total'] - $arrItem['price_deposits'] - $arrData['price_paid'];
            }
			$this->tableGateway->update($data, ['id' => $arrData['id']]);

			return $id;
		}

        // Ẩn đơn hàng
        if($options['task'] == 'hidden') {
            if(count($arrData['cid']) > 0) {
                $data = array(
                    'hidden'            => 1,
                );
                $where = new Where();
                $where->in('id', $arrData['cid']);
                $this->tableGateway->update($data, $where);
            }

            return count($arrData['cid']);
        }

        // Hiện đơn hàng đơn hàng
        if($options['task'] == 'show') {
            if(count($arrData['cid']) > 0) {
                $data = array(
                    'hidden'            => 0,
                );
                $where = new Where();
                $where->in('id', $arrData['cid']);
                $this->tableGateway->update($data, $where);
            }

            return count($arrData['cid']);
        }

        // Hiện đơn hàng đơn hàng
        if($options['task'] == 'show-delete') {
            $arrItem = $arrParam['item'];
            $data = array(
                'delete'            => 0,
            );
            $where = new Where();
            $where->equalTo('id', $arrData['id']);
            $this->tableGateway->update($data, $where);

            // cập nhật lại ngày thành công của đơn hàng đầu tiên
//            $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem(array('contact_id' => $arrItem['contact_id']), array('task' => 'update-contract-time-success'));

            return $arrData['id'];
        }

        // Xác nhận hoàn đơn hàng
        if($options['task'] == 'returned') {
            if(count($arrData['cid']) > 0) {
                $data = array(
                    'returned'            => 1,
                );
                $where = new Where();
                $where->in('id', $arrData['cid']);
                $this->tableGateway->update($data, $where);
            }

            return count($arrData['cid']);
        }

        // Khóa đơn
        if($options['task'] == 'lock') {
            if(count($arrData['cid']) > 0) {
                $data = array(
                    'lock'            => 1,
                );
                $where = new Where();
                $where->in('id', $arrData['cid']);
                $this->tableGateway->update($data, $where);
            }
            return count($arrData['cid']);
        }

        // Mở khóa
        if($options['task'] == 'unlock') {
            if(count($arrData['cid']) > 0) {
                $data = array(
                    'lock'            => 0,
                );
                $where = new Where();
                $where->in('id', $arrData['cid']);
                $this->tableGateway->update($data, $where);
            }
            return count($arrData['cid']);
        }

        // ĐÃ thanh toán giá vốn
        if($options['task'] == 'paidcost') {
            if(count($arrData['cid']) > 0) {
                $data = array(
                    'paid_cost'            => 't',
                );
                $where = new Where();
                $where->in('id', $arrData['cid']);
                $this->tableGateway->update($data, $where);
            }
            return count($arrData['cid']);
        }

        // Chưa thanh toán giá vốn
        if($options['task'] == 'nopaidcost') {
            if(count($arrData['cid']) > 0) {
                $data = array(
                    'paid_cost'            => 'f',
                );
                $where = new Where();
                $where->in('id', $arrData['cid']);
                $this->tableGateway->update($data, $where);
            }
            return count($arrData['cid']);
        }

        if($options['task'] == 'update-shipped') {
            $data = array(
                'shipped' => 1,
                'shipped_date' => date('Y-m-d H:i:s'),
            );
            $this->tableGateway->update($data, array('id' => $arrData['id']));
            return $arrData['id'];
        }

        if($options['task'] == 'update-kov-code') {
            $data = array(
                'kov_code' => $arrData['kov_code'],
            );
            $this->tableGateway->update($data, array('id' => $arrData['id']));
            return $arrData['id'];
        }

        if($options['task'] == 'update-product-cost-auto') {
            $data = array(
                'options' => serialize($arrData['options']),
            );
            $this->tableGateway->update($data, array('id' => $arrData['id']));
            return $arrData['id'];
        }

        if($options['task'] == 'update-cost-ads') {
            $cost_ads    = $arrParam['cost_ads'];
            $date        = $arrParam['date'];

            $sql_update = "UPDATE ".TABLE_CONTRACT." SET cost_ads = ".$cost_ads." WHERE `delete` = 0 AND date >= '".$date."' AND date <= '".$date." 23:59:59'";
            $this->tableGateway->getAdapter()->driver->getConnection()->execute($sql_update);
        }

        if($options['task'] == 'change-delivery') {
            $arrUser = $arrParam['user'];

            $contract_ids = explode(',', $arrData['contract_ids']);
            if(count($contract_ids) > 0) {
                $data = array(
                    'delivery_id'         => $arrUser['id'],
                );
                $where = new Where();
                $where->in('id', $contract_ids);
                $this->tableGateway->update($data, $where);

                // Thêm lịch sử hệ thống
                $arrCheckResult = array(
                    'contact_ids'     => $contract_ids,
                    'user_id'         => $arrUser['id'],
                );
                $arrParamLogs = array(
                    'data' => array(
                        'title'          => 'Đơn hàng',
                        'phone'          => null,
                        'name'           => null,
                        'action'         => 'Thêm giục đơn',
                        'contact_id'     => null,
                        'options'        => $arrCheckResult
                    )
                );
                $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
            }

            return count($contract_ids);
        }

        if($options['task'] == 'change-care') {
            $arrUser = $arrParam['user'];

            $contract_ids = explode(',', $arrData['contract_ids']);
            if(count($contract_ids) > 0) {
                $data = array(
                    'care_id'         => $arrUser['id'],
                );
                $where = new Where();
                $where->isNull('care_id');
                $where->in('id', $contract_ids);
                $result = $this->tableGateway->update($data, $where);

                // Thêm lịch sử hệ thống
                $arrCheckResult = array(
                    'contact_ids'     => $contract_ids,
                    'user_id'         => $arrUser['id'],
                );
                $arrParamLogs = array(
                    'data' => array(
                        'title'          => 'Đơn hàng',
                        'phone'          => null,
                        'name'           => null,
                        'action'         => 'Thêm giục chăm sóc',
                        'contact_id'     => null,
                        'options'        => $arrCheckResult
                    )
                );
                $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
            }

            return $result;
        }
	}

    public function updateItem($arrParam = null, $options = null) {
        $arrData  = $arrParam['data'];
        $arrItem  = $arrParam['item'];

        // Cập nhật trạng thái đơn hàng
        if ($options['task'] == 'update-item-status') {
            $arr_id = $arrData['cid'];
            $field_status_name  = $arrData['field_status_name'];
            $field_status_value = $arrData['field_status_value'];

            $data = array(
                $field_status_name => $field_status_value,
            );

            // Ghi log
            foreach($arr_id as $vid){
                $arrItem = $this->getItem(array('id' => $vid));
                if($arrItem[$field_status_name] != $field_status_value){
                    if($field_status_name == 'production_department_type')
                        $status    = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-department')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
                    if($field_status_name == 'status_acounting_id')
                        $status    = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-acounting')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
                    $arrParamLogs = array(
                        'data' => array(
                            'title'          => 'Cập nhật đơn hàng: '.$arrItem['code'],
                            'action'         => 'Cập nhật',
                            'contact_id'     => $arrItem['contact_id'],
                            'contract_id'    => $arrItem['id'],
                            'options'        => array($field_status_name => $status[$arrItem[$field_status_name]]['name'].' => '.$status[$field_status_value]['name']),
                        )
                    );
                    $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
                }
            }

            $where = new Where();
            $where -> in('id', $arr_id);
            $where -> notEqualTo('lock', 1);
            $result = $this -> tableGateway -> update($data, $where);

            return $result;
        }
        if ($options['task'] == 'update-options') {
            $id = $arrData['id'];
            $data = array(
				'options' => serialize($arrData['options'])
			);
            

            // Cập nhật đơn hàng
			$this->tableGateway->update($data, array('id' => $id));
			return $id;
        }
        // cập nhật các trạng thái
        if ($options['task'] == 'update-status') {
            $id = $arrData['id'];
            $arrItem = $this->getItem(array('id' => $id));

            $data = array();
            if(!empty($arrData['status_acounting_id'])) {
                $data['status_acounting_id'] = $arrData['status_acounting_id'];
            }
            if(!empty($arrData['status_id'])) {
                $data['status_id'] = $arrData['status_id'];
            }
            if(!empty($arrData['price_transport'])) {
                $data['price_transport'] = preg_replace('/,/','',$arrData['price_transport'])*1;
            }

            // cập nhật lại số lượng đơn hàng lấy ra bán sẵn
            if($arrItem['status_id'] == DA_CHOT && $arrData['status_id'] == HUY_SALES){
                $data['lock'] = 1; // khóa đơn hàng
                $pro_items = unserialize($arrItem['options'])['product'];
                if(!empty($pro_items)){
                    foreach($pro_items as $key => $value){
                        if(!empty($value['product_return_id'])){
                            $item_upd = $this->getServiceLocator()->get('Admin\Model\ProductReturnTable')->getItem(array('id' => $value['product_return_id']));
                            $data_upd['item'] = $item_upd;
                            $data_upd['data']['quantity'] = $item_upd['quantity'] + $value['numbers'];
                            $this->getServiceLocator()->get('Admin\Model\ProductReturnTable')->saveItem($data_upd, array('task' => 'edit-item'));
                        }
                    }
                }
            }

            // Cập nhật đơn hàng
            $this->tableGateway->update($data, array('id' => $id));

            // Ghi log
            $check_status_change = false;
            if($arrItem['status_id'] != $arrData['status_id']){
                $status_sales      = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
                $check_status_change = true;
                $option_log = array('status_id' => $status_sales[$arrItem['status_id']]['name'].' => '.$status_sales[$arrData['status_id']]['name']);
            }
            if($arrItem['status_acounting_id'] != $arrData['status_acounting_id']){
                $check_status_change = true;
                $status_accounting = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-acounting')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
                $option_log = array('status_id' => $status_accounting[$arrItem['status_acounting_id']]['name'].' => '.$status_accounting[$arrData['status_acounting_id']]['name']);
            }
            if($check_status_change){
                $arrParamLogs = array(
                    'data' => array(
                        'title'          => 'Cập nhật đơn hàng: '.$arrItem['code'],
                        'action'         => 'Cập nhật',
                        'contact_id'     => $arrItem['contact_id'],
                        'contract_id'    => $arrItem['id'],
                        'options'        => $option_log,
                    )
                );
                $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
            }
        }

        // Cập nhật khi đẩy đơn sang ghtk
        if ($options['task'] == 'update-ghtk') {
            $id = $arrData['id'];

            if(!empty($arrData['ghtk_code'])){
                $data['ghtk_code'] = $arrData['ghtk_code'];
            }
            if(!empty($arrData['ghtk_result'])){
                $data['ghtk_result'] = serialize($arrData['ghtk_result']);
            }
            if(!empty($arrData['ghtk_status'])){
                $data['ghtk_status'] = $arrData['ghtk_status'];
            }
            if(!empty($arrData['price_transport'])){
                $data['price_transport'] = $arrData['price_transport'];
            }
            if(!empty($arrData['ORDER_NUMBER'])){
                $data['ORDER_NUMBER'] = $arrData['ORDER_NUMBER'];
            }
            if(!empty($arrData['unit_transport'])){
                $data['unit_transport'] = $arrData['unit_transport'];
            }
            if(!empty($arrData['token'])){
                $data['token'] = $arrData['token'];
            }

            // Cập nhật đơn hàng
            $this->tableGateway->update($data, array('id' => $id));
            return $id;
        }
        // Cập nhật webhook ghtk
        if ($options['task'] == 'update-webhook-status') {
            $id = $arrData['id'];
            $status_history = unserialize($arrItem['status_history']);

            if(!empty($arrData['ghtk_status'])){
                $data['ghtk_status'] = $arrData['ghtk_status'];
            }
            if(!empty($arrData['ghtk_code'])){
                $data['ghtk_code'] = $arrData['ghtk_code'];
            }
            if(!empty($arrData['viettel_status'])){
                $data['viettel_status'] = $arrData['viettel_status'];
            }
            if(!empty($arrData['price_transport'])){
                $data['price_transport'] = $arrData['price_transport'];
            }
            if(!empty($arrData['status_history'])){
                $arrData['status_history']['created'] = date('Y-m-d H:i:s');
                $status_history[] = $arrData['status_history'];
                $data['status_history'] = serialize($status_history);
            }
            // Cập nhật đơn hàng
            $this->tableGateway->update($data, array('id' => $id));

            return $id;
        }
    }

	public function importItem($arrParam = null, $options = null){
        $arrData  = $arrParam['data'];
        $gid      = new \ZendX\Functions\Gid();

        $date     = new \ZendX\Functions\Date();
        $number   = new \ZendX\Functions\Number();

		// Import đơn hàng có sẵn
		if($options['task'] == 'import-contract-old') {
		    // Thêm đơn hàng
            $id = $gid->getId();
            $contract_options = array();

            $contract_options['sale_note']       = $arrData['sale_note'];
            $contract_options['production_note'] = $arrData['production_note'];
            $contract_options['contact_type']    = $arrData['contact_type'];

            $contract_options['product'][0]['key_id']            = $gid->getId();
            $contract_options['product'][0]['product_id']        = $arrData['product_id'];
            $contract_options['product'][0]['number_production'] = $arrData['number_production'];
            $contract_options['product'][0]['product_name']      = $arrData['product_name'];
            $contract_options['product'][0]['stock']             = $arrData['stock'];
            $contract_options['product'][0]['carpet_color_id']   = $arrData['carpet_color_id'];
            $contract_options['product'][0]['number_carpet']     = $arrData['number_carpet'];
            $contract_options['product'][0]['tangled_color_id']  = $arrData['tangled_color_id'];
            $contract_options['product'][0]['number_tangled']    = $arrData['number_tangled'];
            $contract_options['product'][0]['flooring_id']       = $arrData['flooring_id'];
            $contract_options['product'][0]['listed_price']      = $arrData['listed_price'];
            $contract_options['product'][0]['capital_default']   = $arrData['capital_default'];
            $contract_options['product'][0]['price']             = $arrData['price'];
            $contract_options['product'][0]['vat']               = $arrData['vat'];
//            $contract_options['product'][0]['price_production']  = $arrData['price_production'];
            $contract_options['product'][0]['total_production']  = $arrData['total_production'];
            $contract_options['product'][0]['numbers']           = 1;
            $contract_options['product'][0]['sale_price']        = $arrData['listed_price'] - $arrData['price'];
            $contract_options['product'][0]['total']             = $arrData['price'] - $arrData['vat'];
            $contract_options['product'][0]['product_alias']     = $arrData['product_alias'];
            $contract_options['product'][0]['product_group_id']  = $arrData['product_group_id'];
            $contract_options['product'][0]['sale_branch_id']    = $arrData['sale_branch_id'];

            $data = array(
                'id'                        => $id,
                'date'                      => $date->formatToData($arrData['date']),
                'bill_code'                 => $arrData['bill_code'],
                'code'                      => $arrData['code'],
                'price_total'               => $number->formatToData($arrData['price_total']),
                'price_deposits'            => $number->formatToData($arrData['price_deposits']),
                'price_surcharge'           => $number->formatToData($arrData['price_surcharge']),
                'price_paid'                => $number->formatToData($arrData['price_paid']),
                'price_owed'                => $number->formatToData($arrData['price_owed']),
                'contact_id'                => $arrData['contact_id'],
                'marketer_id'               => $arrData['marketer_id'],
                'user_id'                   => $arrData['user_id'],
                'product_group_id'          => $arrData['product_group_id'],
                'production_department_type'=> $arrData['production_department_type'],
                'status_check_id'           => $arrData['status_check_id'],
                'status_acounting_id'       => $arrData['status_acounting_id'],
                'production_type_id'        => $arrData['production_type_id'],
                'shipper_id'                => $arrData['shipper_id'],
                'transport_id'              => $arrData['transport_id'],
                'sale_branch_id'            => $arrData['sale_branch_id'],
                'sale_group_id'             => $arrData['sale_group_id'],
                'status_guarantee_id'       => $arrData['status_guarantee_id'],
                'code_old'                  => $arrData['code_old'],
                'guarantee_date'            => $date->formatToData($arrData['guarantee_date']),
                'guarantee_note'            => $arrData['guarantee_note'],
                'total_number_product'      => 1,

                'created'                   => date('Y-m-d H:i:s'),
                'created_by'                => !empty($arrData['user_id']) ? $arrData['user_id'] : $this->userInfo->getUserInfo('id'),
                'options'                   => serialize($contract_options)
            );
            $this->tableGateway->insert($data); // Thực hiện lưu database

            // Lưu mã hoá đơn
            $this->saveItem(array('data' => $id), array('task' => 'update-code'));
            // Lưu đơn thứ bao nhiêu của khách hàng
            $this->saveItem(array('data' => $id), array('task' => 'update-index-number'));
            // Cập nhật các loại doanh số sau khi tạo đơn hàng
            $this->saveItem(array('data' => $id), array('task' => 'update-sales'));

            // Cập nhật thông tin đơn hàng đầu tiên cho khách hàng
            $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem(array('contract_id' => $id), array('task' => 'update-contract-first'));

            return $id;
		}

		// Import đơn hàng tồn kho
		if($options['task'] == 'import-contract-warehouse') {
		    // Thêm đơn hàng
            $id = $gid->getId();
            $contract_options = array();

            $contract_options['sale_note']       = $arrData['sale_note'];
            $contract_options['production_note'] = $arrData['production_note'];
            $contract_options['contact_type']    = $arrData['contact_type'];

            $contract_options['product'][0]['key_id']            = $gid->getId();
            $contract_options['product'][0]['product_id']        = $arrData['product_id'];
            $contract_options['product'][0]['number_production'] = $arrData['number_production'];
            $contract_options['product'][0]['product_name']      = $arrData['product_name'];
            $contract_options['product'][0]['stock']             = $arrData['stock'];
            $contract_options['product'][0]['carpet_color_id']   = $arrData['carpet_color_id'];
            $contract_options['product'][0]['number_carpet']     = $arrData['number_carpet'];
            $contract_options['product'][0]['tangled_color_id']  = $arrData['tangled_color_id'];
            $contract_options['product'][0]['number_tangled']    = $arrData['number_tangled'];
            $contract_options['product'][0]['flooring_id']       = $arrData['flooring_id'];
            $contract_options['product'][0]['listed_price']      = $arrData['listed_price'];
            $contract_options['product'][0]['capital_default']   = $arrData['capital_default'];
            $contract_options['product'][0]['price']             = $arrData['price'];
            $contract_options['product'][0]['total_production']  = $arrData['total_production'];
            $contract_options['product'][0]['numbers']           = 1;
            $contract_options['product'][0]['sale_price']        = $arrData['listed_price'] - $arrData['price'];
            $contract_options['product'][0]['total']             = $arrData['price'] - $arrData['vat'];
            $contract_options['product'][0]['product_alias']     = $arrData['product_alias'];
            $contract_options['product'][0]['product_group_id']  = $arrData['product_group_id'];
            $contract_options['product'][0]['sale_branch_id']    = $arrData['sale_branch_id'];

            $data = array(
                'id'                        => $id,
                'date'                      => $date->formatToData($arrData['date']),
                'price_total'               => $number->formatToData($arrData['price_total']),
                'price_deposits'            => $number->formatToData($arrData['price_deposits']),
                'price_surcharge'           => $number->formatToData($arrData['price_surcharge']),
                'price_paid'                => $number->formatToData($arrData['price_paid']),
                'price_owed'                => $number->formatToData($arrData['price_owed']),
                'contact_id'                => $arrData['contact_id'],
                'marketer_id'               => !empty($arrData['user_id']) ? $arrData['user_id'] : $this->userInfo->getUserInfo('id'),
                'user_id'                   => !empty($arrData['user_id']) ? $arrData['user_id'] : $this->userInfo->getUserInfo('id'),
                'product_group_id'          => $arrData['product_group_id'],
                'production_department_type'=> $arrData['production_department_type'],
                'status_check_id'           => $arrData['status_check_id'],
                'status_acounting_id'       => $arrData['status_acounting_id'],
                'production_type_id'        => $arrData['production_type_id'],
                'shipper_id'                => $arrData['shipper_id'],
                'transport_id'              => $arrData['transport_id'],
                'sale_branch_id'            => !empty($arrData['sale_branch_id']) ? $arrData['sale_branch_id'] : $this->userInfo->getUserInfo('sale_branch_id'),
                'sale_group_id'             => !empty($arrData['sale_group_id']) ? $arrData['sale_group_id'] : $this->userInfo->getUserInfo('sale_group_id'),
                'status_guarantee_id'       => $arrData['status_guarantee_id'],
                'code_old'                  => $arrData['code_old'],
                'guarantee_date'            => $date->formatToData($arrData['guarantee_date']),
                'guarantee_note'            => $arrData['guarantee_note'],
                'total_number_product'      => 1,

                'created'                   => date('Y-m-d H:i:s'),
                'created_by'                => !empty($arrData['user_id']) ? $arrData['user_id'] : $this->userInfo->getUserInfo('id'),
                'options'                   => serialize($contract_options)
            );

            $this->tableGateway->insert($data);
            // Lưu mã hoá đơn
            $this->saveItem(array('data' => $id), array('task' => 'update-code-warehouse'));

            return $id;
		}
	}
	
	public function deleteItem($arrParam = null, $options = null){
	    if($options['task'] == 'delete-item') {
	        $arrData  = $arrParam['data'];
    	    $arrRoute = $arrParam['route'];
    	    $arrItem  = $arrParam['item'];

    	    $contract_options = !empty($arrItem['options']) ? unserialize($arrItem['options']) : array();

    	    // Cập nhật lại số lượng sản phẩm của đơn hàng có sẵn khi xóa đơn hàng có chứa sản phẩm có sẵn
    	    if(!empty($contract_options)){
                $products = $contract_options['product'];
                if(!empty($products)){
                    foreach ($products as $key => $product){
                        if(!empty($product['stock'])){
                            $param_check = array(
                                'code'      => $product['stock'],
                                'key_id'    => $product['key_id'],
                            );
                            $this->saveItem($param_check, array('task' => 'update-number-product'));
                        }
                    }
                }
    	    }
    	    
    	    // Xóa đơn hàng
            $where = new Where();
            $where -> equalTo('id', $arrItem['id']);
            $this -> tableGateway -> delete($where);

            // Xóa toàn bộ hóa đơn của đơn hàng
            $bill_delete = $this->getServiceLocator()->get('Admin\Model\BillTable')->deleteItem(array('contract_id' => $arrItem['id']), array('task' => 'contract-delete'));
            
            // Cập nhật lại số đơn hàng của liên hệ
            $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $arrItem['contact_id']));
            $contract_total = intval($contact['contract_total']) - 1;
            $contract_number = intval($contact['contract_number']) - 1;
            $contact_data = array(
                'id' => $contact['id'],
                'contract_total' => $contract_total,
                'contract_number' => $contract_number,
            );
            if($contract_total <= 0) {
                $contact_data['contract_total'] = 0;
            }
            if($contract_number <= 0) {
                $contact_data['contract_number'] = 0;
            }
            $contact_update = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem(array('data' => $contact_data, 'item' => $contact), array('task' => 'edit-item'));
            
            // Thêm lịch sử xóa đơn hàng
            $arrParamLogs = array(
                'data' => array(
                    'title'          => 'đơn hàng',
                    'phone'          => $contact['phone'],
                    'name'           => $contact['name'],
                    'action'         => 'Xóa',
                    'contact_id'     => $contact['id'],
                    'contract_id'    => $arrItem['id'],
                    'options'        => array(
                        'date'                    => $arrItem['date'],
                        'price'                   => $arrItem['price'],
                        'price_promotion'         => $arrItem['price_promotion'],
                        'price_promotion_percent' => $arrItem['price_promotion_percent'],
                        'price_promotion_price'   => $arrItem['price_promotion_price'],
                        'promotion_content'       => $contract_options['promotion_content'],
                        'price_total'             => $arrItem['price_total'],
                        'product_id'              => $arrItem['product_id'],
                        'user_id'                 => $arrItem['user_id'],
                        'sale_branch_id'          => $arrItem['sale_branch_id'],
                        'sale_group_id'           => $arrItem['sale_group_id'],
                    )
                )
            );
            $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
            
            $result = 1;
	    }

        // Ẩn đơn hàng
        if($options['task'] == 'delete-hidden') {
            $arrItem = $arrParam['item'];

            // Xóa đơn hàng tạm thời
            $data = array(
                'delete' => 1,
            );
            $where = new Where();
            $where->equalTo('id', $arrItem['id']);
            $this->tableGateway->update($data, $where);

            // cập nhật lại ngày thành công của đơn hàng đầu tiên
//            $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem(array('contact_id' => $arrItem['contact_id']), array('task' => 'update-contract-time-success'));

            // Thêm lịch sử xóa đơn hàng
            $arrParamLogs = array(
                'data' => array(
                    'title'          => 'Xóa đơn hàng: '.$arrItem['code'],
                    'phone'          => $contact['phone'],
                    'name'           => $contact['name'],
                    'action'         => 'Xóa',
                    'contact_id'     => $arrItem['contact_id'],
                    'contract_id'    => $arrItem['id'],
                    'options'        => array(
                        'date'                    => $arrItem['date'],
                        'price'                   => $arrItem['price'],
                        'price_promotion'         => $arrItem['price_promotion'],
                        'price_promotion_percent' => $arrItem['price_promotion_percent'],
                        'price_promotion_price'   => $arrItem['price_promotion_price'],
                        'promotion_content'       => $contract_options['promotion_content'],
                        'price_total'             => $arrItem['price_total'],
                        'product_id'              => $arrItem['product_id'],
                        'user_id'                 => $arrItem['user_id'],
                        'sale_branch_id'          => $arrItem['sale_branch_id'],
                        'sale_group_id'           => $arrItem['sale_group_id'],
                        'note'                    => "Xóa đơn hàng",
                    )
                )
            );
            $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));

            $result = $arrItem['id'];
        }

        // Hủy sale
        if($options['task'] == 'cancel') {
            $arrItem = $arrParam['item'];

            // Xóa đơn hàng tạm thời
            $data = array(
                'status_id' => HUY_SALES,
            );
            $where = new Where();
            $where->equalTo('id', $arrItem['id']);
            $this->tableGateway->update($data, $where);

            // Thêm lịch sử HỦY SALE
            $arrParamLogs = array(
                'data' => array(
                    'title'          => 'Hủy sale: '.$arrItem['code'],
                    'phone'          => $contact['phone'],
                    'name'           => $contact['name'],
                    'action'         => 'Xóa',
                    'contact_id'     => $arrItem['contact_id'],
                    'contract_id'    => $arrItem['id'],
                    'options'        => array(
                        'date'                    => $arrItem['date'],
                        'price'                   => $arrItem['price'],
                        'price_promotion'         => $arrItem['price_promotion'],
                        'price_promotion_percent' => $arrItem['price_promotion_percent'],
                        'price_promotion_price'   => $arrItem['price_promotion_price'],
                        'promotion_content'       => $contract_options['promotion_content'],
                        'price_total'             => $arrItem['price_total'],
                        'product_id'              => $arrItem['product_id'],
                        'user_id'                 => $arrItem['user_id'],
                        'sale_branch_id'          => $arrItem['sale_branch_id'],
                        'sale_group_id'           => $arrItem['sale_group_id'],
                        'note'                    => "Xóa đơn hàng",
                    )
                )
            );
            $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));

            $result = $arrItem['id'];
        }
	
	    return $result;
	}
	
	public function changeStatus($arrParam = null, $options = null){
	    if($options['task'] == 'change-status') {
	        $result = $this->defaultStatus($arrParam, null);
	    }
	    
	    if($options['task'] == 'update-status') {
	        $arrData = $arrParam['data'];
            if(!empty($arrData['id'])) {
    	        $data	= array( $arrData['fields'] => ($arrData['value'] == 1) ? 0 : 1 );
    			$this->tableGateway->update($data, array('id' => $arrData['id']));
    			return true;
            }
    	    
    	    return false;
	    }
	     
	    return $result;
	}
	
	public function changeOrdering($arrParam = null, $options = null){
	    if($options['task'] == 'change-ordering') {
	        $result = $this->defaultOrdering($arrParam, null);
	    }
	    return $result;
	}
	
	public function report($arrParam = null, $options = null){
	    if($options['task'] == 'date') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];
	            $dateFormat = new \ZendX\Functions\Date();
	            
	            $columns = array('date', 'price_total', 'price_paid', 'price_accrued', 'user_id', 'sale_branch_id', 'sale_group_id','production_department_type');
	            if(!empty($options['columns'])) {
	                $columns = array_merge($columns, $options['columns']);
	            }
	            $select -> columns($columns)
	                    -> where -> greaterThanOrEqualTo('date', $dateFormat->formatToData($arrData['date_begin']) .' 00:00:00')
	                             -> lessThanOrEqualTo('date', $dateFormat->formatToData($arrData['date_end']) .' 23:59:59');
	            
                if(!empty($arrData['sale_branch_id'])) {
                    $select -> where -> equalTo('sale_branch_id', $arrData['sale_branch_id']);
                }
                if(!empty($arrData['sale_group_id'])) {
                    $select -> where -> equalTo('sale_group_id', $arrData['sale_group_id']);
                }
                if(!empty($arrData['user_id'])) {
                    $select -> where -> equalTo('user_id', $arrData['user_id']);
                }

                // Đơn hàng chưa xóa có trạng thái = 0
                $select -> where -> equalTo(TABLE_CONTRACT .'.delete', 0);
	        });
	    }
	    
	    if($options['task'] == 'join-date') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];
	            $dateFormat = new \ZendX\Functions\Date();
	            
	            $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_CONTRACT .'.contact_id',
        	                array(
        	                    'contact_type' => 'type', 
        	                    'contact_sex' => 'sex', 
        	                    'contact_location_city_id' => 'location_city_id', 
        	                    'contact_location_district_id' => 'location_district_id', 
        	                    'contact_source_group_id' => 'source_group_id', 
        	                    'contact_source_known_id' => 'source_known_id', 
        	                    'contact_birthday_year' => 'birthday_year', 
        	                    'contact_product_id' => 'product_id', 
        	                    'contact_options' => 'options'
        	                ), 'inner');
	            $select -> columns(array('date', 'options'))
                        -> where -> greaterThanOrEqualTo(TABLE_CONTRACT .' .date', $dateFormat->formatToData($arrData['date_begin']) .' 00:00:00')
	                             -> lessThanOrEqualTo(TABLE_CONTRACT .' .date', $dateFormat->formatToData($arrData['date_end']) .' 23:59:59');
	            
                 if(!empty($arrData['sale_branch_id'])) {
                     $select -> where -> equalTo(TABLE_CONTRACT.' .sale_branch_id', $arrData['sale_branch_id']);
                 }
                 if(!empty($arrData['sale_group_id'])) {
                     $select -> where -> equalTo(TABLE_CONTRACT.' .sale_group_id', $arrData['sale_group_id']);
                 }
                 if(!empty($arrData['user_id'])) {
                     $select -> where -> equalTo(TABLE_CONTRACT.' .user_id', $arrData['user_id']);
                 }
                 if(!empty($arrData['location_city_id'])) {
                     $select -> where -> equalTo(TABLE_CONTACT.' .location_city_id', $arrData['location_city_id']);
                 }

                // Đơn hàng chưa xóa có trạng thái = 0
                $select -> where -> equalTo(TABLE_CONTRACT .'.delete', 0);
	        });
	    }

	    if($options['task'] == 'join-contact') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
                $ssFilter  = $arrParam['ssFilter'];
	            $dateFormat = new \ZendX\Functions\Date();
	            $date_type = 'date';
                if(!empty($ssFilter['date_type'])) {
                    $date_type = $ssFilter['date_type'];
                }

	            $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_CONTRACT .'.contact_id',
        	                array(
        	                    'contact_date' => 'date',
        	                    'contact_created' => 'created',
        	                    'contact_marketer_id' => 'marketer_id',
        	                    'contact_type' => 'type',
        	                    'contact_cost_ads' => 'cost_ads',
        	                    'contact_contract_first_date' => 'contract_first_date',
        	                    'contact_contract_time_success' => 'contract_time_success',
        	                ), 'inner');

                if(!empty($ssFilter['order'])) {
                    $select -> order(array(TABLE_CONTRACT .'.'.$ssFilter['order'] => 'ASC'));
                }

                // Đơn hàng chưa xóa có trạng thái = 0
                $select -> where -> equalTo(TABLE_CONTRACT .'.delete', 0);

                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select -> where -> NEST
                            -> greaterThanOrEqualTo(TABLE_CONTRACT .'.'.$date_type, $dateFormat->formatToSearch($ssFilter['filter_date_begin']))
                            ->AND
                            -> lessThanOrEqualTo(TABLE_CONTRACT .'.'.$date_type, $dateFormat->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59')
                            -> UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select->where->greaterThanOrEqualTo(TABLE_CONTRACT .'.'.$date_type, $dateFormat->formatToSearch($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select->where->lessThanOrEqualTo(TABLE_CONTRACT .'.'.$date_type, $dateFormat->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59');
                }

                if(!empty($ssFilter['sale_branch_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.sale_branch_id', $ssFilter['sale_branch_id']);
                }
                if(!empty($ssFilter['sale_group_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.sale_group_id', $ssFilter['sale_group_id']);
                }
                if(!empty($ssFilter['sale_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.user_id', $ssFilter['sale_id']);
                }
                if(!empty($ssFilter['delivery_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.delivery_id', $ssFilter['delivery_id']);
                }
                if(!empty($ssFilter['production_type_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.production_type_id', $ssFilter['production_type_id']);
                }
                if(!empty($ssFilter['product_cat_id'])) {
                    $select -> where -> like(TABLE_CONTRACT .'.options','%'.$ssFilter['product_cat_id'].'%');
                }
                if(!empty($ssFilter['code'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.code', $ssFilter['code']);
                }
                if(isset($ssFilter['status_store']) && $ssFilter['status_store'] != '') {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.status_store', $ssFilter['status_store']);
                }
                if(isset($ssFilter['paid_cost']) && $ssFilter['paid_cost'] != '') {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.paid_cost', $ssFilter['paid_cost']);
                }
                if(isset($ssFilter['filter_status_sale']) && $ssFilter['filter_status_sale'] != '') {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.status_id', $ssFilter['filter_status_sale']);
                }
                if(isset($ssFilter['filter_status_check']) && $ssFilter['filter_status_check'] != '') {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.ghtk_status', $ssFilter['filter_status_check']);
                }
                if(isset($ssFilter['filter_status_accounting']) && $ssFilter['filter_status_accounting'] != '') {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.status_acounting_id', $ssFilter['filter_status_accounting']);
                }

                if(isset($ssFilter['contract_type_bh']) && $ssFilter['contract_type_bh'] != '') {
                    if ($ssFilter['contract_type_bh'] == 1){
                        $select -> where -> equalTo(TABLE_CONTRACT .'.status_guarantee_id', 1);
                    }
                    else{
                        $select -> where -> notEqualTo(TABLE_CONTRACT .'.status_guarantee_id', 1);
                    }
                }

                // Chỉ lấy những đơn có trạng thái sales khác hủy sale
                if(!empty($ssFilter['filter_sales_status_id'])) {
                    $select -> where-> NEST
                        -> notEqualTo(TABLE_CONTRACT .'.status_id', HUY_SALES)
                        ->Or
                        -> isNull( TABLE_CONTRACT .'.status_id')
                        -> UNNEST;
                }

                // Chỉ lấy những đơn có trạng thái giục đơn khác hủy hoàn
                if(!empty($ssFilter['filter_check_status_id'])) {
                    $select -> where-> NEST
                        -> notEqualTo(TABLE_CONTRACT .'.status_check_id', STATUS_CONTRACT_CHECK_RETURN)
                        ->Or
                        -> isNull( TABLE_CONTRACT .'.status_check_id')
                        -> UNNEST;
                }

                // Nếu đơn hàng ở trạng thái đã sản xuất, hoặc đã giao hàng (trạng thái sản xuất) thì sẽ tính danh thu cho mkt
                if(!empty($ssFilter['filter_status_type'])) {
                    if(!empty($ssFilter['filter_status'])) {
                        $select -> where->NEST
                                        ->equalTo(TABLE_CONTRACT .'.'.$ssFilter['filter_status_type'], STATUS_CONTRACT_PRODUCT_PRODUCTED)
                                        ->OR
                                        ->equalTo(TABLE_CONTRACT .'.'.$ssFilter['filter_status_type'], STATUS_CONTRACT_PRODUCT_POST)
                                        ->UNNEST;
                    }
				}

                if(!empty($ssFilter['filter_product_group_id'])) {
                    $select -> where -> equalTo(TABLE_CONTACT .'.product_group_id', $ssFilter['filter_product_group_id']);
                }
	        })->toArray();
	    }

	    if($options['task'] == 'join-user') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];
	            $dateFormat = new \ZendX\Functions\Date();

	            $select -> join(TABLE_USER, TABLE_USER .'.id = '. TABLE_CONTRACT .'.user_id',
        	                array(
        	                    'user_sale_branch_id' => 'sale_branch_id',
        	                    'user_sale_group_id' => 'sale_group_id',
        	                ), 'inner');

	            $select -> columns(array('date', 'options', 'production_date'))
                        -> where -> greaterThanOrEqualTo(TABLE_CONTRACT .'.'.$arrData['date_type'], $dateFormat->formatToSearch($arrData['date_begin']) .' 00:00:00')
	                             -> lessThanOrEqualTo(TABLE_CONTRACT .'.'.$arrData['date_type'], $dateFormat->formatToSearch($arrData['date_end']) .' 23:59:59');

                if(!empty($arrData['sale_branch_id'])) {
                    $select -> where -> equalTo(TABLE_USER.' .sale_branch_id', $arrData['sale_branch_id']);
                }
                if(!empty($arrData['sale_group_id'])) {
                    $select -> where -> equalTo(TABLE_USER.' .sale_group_id', $arrData['sale_group_id']);
                }
                if(!empty($arrData['user_id'])) {
                    $select -> where -> equalTo(TABLE_USER.' .user_id', $arrData['user_id']);
                }

                // Đơn hàng chưa xóa có trạng thái = 0
                $select -> where -> equalTo(TABLE_CONTRACT .'.delete', 0);
	        });
	    }

        if($options['task'] == 'sum-contract-date'){
            $result = $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $columns = array('date', 'number_total_box' => new Expression('SUM('.TABLE_CONTRACT .'.number_total)'));

                $select -> columns($columns);
                $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_CONTRACT .'.contact_id',
                    array(), 'inner')
                    -> join(TABLE_USER, TABLE_USER .'.id = '. TABLE_CONTRACT .'.user_id',
                        array(), 'inner')
                    -> join(TABLE_DOCUMENT, TABLE_DOCUMENT .'.id = '. TABLE_USER .'.sale_group_id',
                        array(), 'inner');

                $select -> group(TABLE_CONTRACT .'.date');
            });
        }

        if($options['task'] == 'join-contact-producted') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
                $ssFilter  = $arrParam['ssFilter'];
                $dateFormat = new \ZendX\Functions\Date();
                $date_type = 'date';
                if(!empty($ssFilter['date_type'])) {
                    $date_type = $ssFilter['date_type'];
                }

                $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_CONTRACT .'.contact_id',
                    array(
                        'contact_date' => 'date',
                        'contact_created' => 'created',
                        'contact_marketer_id' => 'marketer_id',
                        'contact_type' => 'type',
                        'contact_contract_first_date' => 'contract_first_date',
                        'contact_contract_time_success' => 'contract_time_success',
                    ), 'inner');

                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo(TABLE_CONTRACT .'.'.$date_type, $dateFormat->formatToSearch($ssFilter['filter_date_begin']))
                        ->AND
                        -> lessThanOrEqualTo(TABLE_CONTRACT .'.'.$date_type, $dateFormat->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select->where->greaterThanOrEqualTo(TABLE_CONTRACT .'.'.$date_type, $dateFormat->formatToSearch($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select->where->lessThanOrEqualTo(TABLE_CONTRACT .'.'.$date_type, $dateFormat->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59');
                }

                if(!empty($ssFilter['sale_branch_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.sale_branch_id', $ssFilter['sale_branch_id']);
                }
                if(!empty($ssFilter['sale_group_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.sale_group_id', $ssFilter['sale_group_id']);
                }
                if(!empty($ssFilter['sale_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.user_id', $ssFilter['sale_id']);
                }
                if(!empty($ssFilter['production_type_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.production_type_id', $ssFilter['production_type_id']);
                }
                if(!empty($ssFilter['product_cat_id'])) {
                    $select -> where -> like(TABLE_CONTRACT .'.options','%'.$ssFilter['product_cat_id'].'%');
                }
                if(!empty($ssFilter['code'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.code', $ssFilter['code']);
                }
                if(isset($ssFilter['status_store']) && $ssFilter['status_store'] != '') {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.status_store', $ssFilter['status_store']);
                }
                if(!empty($ssFilter['filter_status_acounting_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.status_acounting_id', $ssFilter['filter_status_acounting_id']);
                }

                if(!empty($ssFilter['filter_product_group_id'])) {
                    $select -> where -> equalTo(TABLE_CONTACT .'.product_group_id', $ssFilter['filter_product_group_id']);
                }

                if(isset($ssFilter['contract_type_bh']) && $ssFilter['contract_type_bh'] != '') {
                    if ($ssFilter['contract_type_bh'] == 1){
                        $select -> where -> equalTo(TABLE_CONTRACT .'.status_guarantee_id', 1);
                    }
                    else{
                        $select -> where -> notEqualTo(TABLE_CONTRACT .'.status_guarantee_id', 1);
                    }
                }

                // Chỉ lấy những đơn có trạng thái sales khác hủy sale
                if(!empty($ssFilter['filter_sales_status_id'])) {
                    $select -> where-> NEST
                        -> notEqualTo(TABLE_CONTRACT .'.status_id', HUY_SALES)
                        ->Or
                        -> isNull( TABLE_CONTRACT .'.status_id')
                        -> UNNEST;
                }

                // Nếu đơn hàng ở trạng thái đã sản xuất, hoặc đã giao hàng (trạng thái sản xuất) thì sẽ tính danh thu cho mkt
                if(!empty($ssFilter['filter_status_type'])) {
                    if(!empty($ssFilter['filter_status'])) {
                        $select -> where->NEST
                            ->equalTo(TABLE_CONTRACT .'.'.$ssFilter['filter_status_type'], STATUS_CONTRACT_PRODUCT_PRODUCTED)
                            ->OR
                            ->equalTo(TABLE_CONTRACT .'.'.$ssFilter['filter_status_type'], STATUS_CONTRACT_PRODUCT_POST)
                            ->UNNEST;

                    }
                }

                // Đơn hàng chưa xóa có trạng thái = 0
                $select -> where -> equalTo(TABLE_CONTRACT .'.delete', 0);
            })->toArray();
        }

	    return $result;
	}
}





