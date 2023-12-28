<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class ContractOwedTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
                $number     = new \ZendX\Functions\Number();
                
                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
                $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_CONTRACT .'.contact_id', array(), 'inner');

                $select -> where-> NEST
                    -> notEqualTo(TABLE_CONTRACT .'.status_id', HUY_SALES)
                    ->Or
                    -> isNull( TABLE_CONTRACT .'.status_id')
                    -> UNNEST;
                
	            if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
			        $filter_keyword = trim($ssFilter['filter_keyword']);
			        if(0&&strlen($number->formatToPhone($filter_keyword)) >= 10) {
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

    			if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> NEST
                    			     -> greaterThanOrEqualTo(TABLE_CONTRACT .'.date', $date->formatToSearch($ssFilter['filter_date_begin']))
                    			     ->AND
                    			     -> lessThanOrEqualTo(TABLE_CONTRACT .'.date', $date->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59')
                    			     -> UNNEST;
    			} elseif (!empty($ssFilter['filter_date_begin'])) {
    			    $select->where->greaterThanOrEqualTo(TABLE_CONTRACT .'.date', $date->formatToSearch($ssFilter['filter_date_begin']));
    			} elseif (!empty($ssFilter['filter_date_end'])) {
    			    $select->where->lessThanOrEqualTo(TABLE_CONTRACT .'.date', $date->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59');
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

                if(!empty($ssFilter['filter_production_type_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.production_type_id', $ssFilter['filter_production_type_id']);
                }

                if(!empty($ssFilter['filter_status_type'])) {
                    if(!empty($ssFilter['filter_status'])) {
                        $select -> where -> equalTo(TABLE_CONTRACT .'.'.$ssFilter['filter_status_type'], $ssFilter['filter_status']);
                    }
                }
    			 
    			if(!empty($ssFilter['filter_product_type'])) {
    			    $select -> join(TABLE_PRODUCT, TABLE_PRODUCT .'.id='. TABLE_CONTRACT .'.product_id', array(), 'inner');
    			    $select -> where -> equalTo(TABLE_PRODUCT .'.type', $ssFilter['filter_product_type']);
    			}
    			
    			if(!empty($ssFilter['filter_product'])) {
    				$select -> where -> equalTo(TABLE_CONTRACT .'.product_id', $ssFilter['filter_product']);
    			}
    			 
    			if(!empty($ssFilter['filter_edu_class'])) {
    				$select -> where -> equalTo(TABLE_CONTRACT .'.edu_class_id', $ssFilter['filter_edu_class']);
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

                if(!empty($ssFilter['filter_owed'])){
                    if($ssFilter['filter_owed'] == 'yes') {
                        $select -> where -> greaterThan(TABLE_CONTRACT .'.price_owed', 0);
                    }
                    if($ssFilter['filter_owed'] == 'no') {
                        $select -> where -> lessThanOrEqualTo(TABLE_CONTRACT .'.price_owed', 0);
                    }
                }

                // Không lấy những đỡn ở trạng thái đã nhận hoàn
                $select -> where -> NEST
                        -> notEqualTo(TABLE_CONTRACT .'.status_acounting_id', STATUS_CONTRACT_ACOUNTING_RETURN)
                        ->OR
                        -> isNull(TABLE_CONTRACT .'.status_acounting_id')
                        -> UNNEST;

                // Đơn hàng chưa xóa có trạng thái = 0
                $select -> where -> equalTo(TABLE_CONTRACT .'.delete', 0);
            })->current();
	    }

        if($options['task'] == 'list-item-warehouse-cancel') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
                $number     = new \ZendX\Functions\Number();
                $userInfo = new \ZendX\System\UserInfo();
                $permission = $userInfo->getPermissionOfUser();
                $permissions = explode(',', $permission['permission_ids']);
                $userInfo = $userInfo->getUserInfo();

                $select -> where -> equalTo(TABLE_CONTRACT .'.status_acounting_id', STATUS_CONTRACT_ACOUNTING_CANCEL_RETURN);

                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
                $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_CONTRACT .'.contact_id', array(), 'inner');

                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $filter_keyword = trim($ssFilter['filter_keyword']);
                    if(0&&strlen($number->formatToPhone($filter_keyword)) >= 10) {
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

                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo(TABLE_CONTRACT .'.date', $date->formatToData($ssFilter['filter_date_begin']))
                        ->AND
                        -> lessThanOrEqualTo(TABLE_CONTRACT .'.date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select->where->greaterThanOrEqualTo(TABLE_CONTRACT .'.date', $date->formatToData($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select->where->lessThanOrEqualTo(TABLE_CONTRACT .'.date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
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
                // Đơn hàng chưa xóa có trạng thái = 0
                $select -> where -> equalTo(TABLE_CONTRACT .'.delete', 0);

            })->current();
        }

        if($options['task'] == 'list-item-warehouse-sold') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
                $number     = new \ZendX\Functions\Number();
                $userInfo = new \ZendX\System\UserInfo();
                $permission = $userInfo->getPermissionOfUser();
                $permissions = explode(',', $permission['permission_ids']);
                $userInfo = $userInfo->getUserInfo();

                $select -> where -> equalTo(TABLE_CONTRACT .'.status_acounting_id', STATUS_CONTRACT_ACOUNTING_RETURN);
                $select -> where -> lessThan(TABLE_CONTRACT .'.total_number_product', 1);

                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
                $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_CONTRACT .'.contact_id', array(), 'left');

                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $filter_keyword = trim($ssFilter['filter_keyword']);
                    if(0&&strlen($number->formatToPhone($filter_keyword)) >= 10) {
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

                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo(TABLE_CONTRACT .'.date', $date->formatToData($ssFilter['filter_date_begin']))
                        ->AND
                        -> lessThanOrEqualTo(TABLE_CONTRACT .'.date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select->where->greaterThanOrEqualTo(TABLE_CONTRACT .'.date', $date->formatToData($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select->where->lessThanOrEqualTo(TABLE_CONTRACT .'.date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
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
                // Đơn hàng chưa xóa có trạng thái = 0
                $select -> where -> equalTo(TABLE_CONTRACT .'.delete', 0);

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
    			
			    if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
			        $filter_keyword = trim($ssFilter['filter_keyword']);
			        if(0&&strlen($number->formatToPhone($filter_keyword)) >= 10) {
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

				if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
					$select -> where -> NEST
										-> greaterThanOrEqualTo(TABLE_CONTRACT .'.date', $date->formatToSearch($ssFilter['filter_date_begin']))
										->AND
										-> lessThanOrEqualTo(TABLE_CONTRACT .'.date', $date->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59')
										-> UNNEST;
				} elseif (!empty($ssFilter['filter_date_begin'])) {
					$select->where->greaterThanOrEqualTo(TABLE_CONTRACT .'.date', $date->formatToSearch($ssFilter['filter_date_begin']));
				} elseif (!empty($ssFilter['filter_date_end'])) {
					$select->where->lessThanOrEqualTo(TABLE_CONTRACT .'.date', $date->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59');
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

                if(!empty($ssFilter['filter_production_type_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT .'.production_type_id', $ssFilter['filter_production_type_id']);
                }

                if(!empty($ssFilter['filter_status_type'])) {
                    if(!empty($ssFilter['filter_status'])) {
                        $select -> where -> equalTo(TABLE_CONTRACT .'.'.$ssFilter['filter_status_type'], $ssFilter['filter_status']);
                    }
                }

                if(!empty($ssFilter['filter_owed'])){
                    if($ssFilter['filter_owed'] == 'yes') {
                        $select -> where -> greaterThan(TABLE_CONTRACT .'.price_owed', 0);
                    }
                    if($ssFilter['filter_owed'] == 'no') {
                        $select -> where -> lessThanOrEqualTo(TABLE_CONTRACT .'.price_owed', 0);
                    }
                }
                // Không lấy những đỡn ở trạng thái đã nhận hoàn
                $select -> where -> NEST
                        -> notEqualTo(TABLE_CONTRACT .'.status_acounting_id', STATUS_CONTRACT_ACOUNTING_RETURN)
                        ->OR
                        -> isNull(TABLE_CONTRACT .'.status_acounting_id')
                        -> UNNEST;

                // Đơn hàng chưa xóa có trạng thái = 0
                $select -> where -> equalTo(TABLE_CONTRACT .'.delete', 0);
    		});
		}

        if($options['task'] == 'list-item-warehouse-cancel') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $paginator  = $arrParam['paginator'];
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
                $number     = new \ZendX\Functions\Number();

                // Chỉ lấy ra danh sách những đơn hàng có trạng thái giục đơn là hoàn và trạng thái kế toán là Hủy hàng có sẵn.
                $select -> where -> equalTo(TABLE_CONTRACT .'.status_acounting_id', STATUS_CONTRACT_ACOUNTING_CANCEL_RETURN);

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
                    ), 'left');
                $select -> order(array(TABLE_CONTRACT .'.date' => 'DESC'));

                if(!isset($options['paginator']) || $options['paginator'] == true) {
                    $select -> limit($paginator['itemCountPerPage'])
                        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }

                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $filter_keyword = trim($ssFilter['filter_keyword']);
                    if(0&&strlen($number->formatToPhone($filter_keyword)) >= 10) {
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
                            -> greaterThanOrEqualTo(TABLE_CONTRACT .'.date', $date->formatToData($ssFilter['filter_date_begin']))
                            ->AND
                            -> lessThanOrEqualTo(TABLE_CONTRACT .'.date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                            -> UNNEST;
                    } elseif (!empty($ssFilter['filter_date_begin'])) {
                        $select->where->greaterThanOrEqualTo(TABLE_CONTRACT .'.date', $date->formatToData($ssFilter['filter_date_begin']));
                    } elseif (!empty($ssFilter['filter_date_end'])) {
                        $select->where->lessThanOrEqualTo(TABLE_CONTRACT .'.date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
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
                // Đơn hàng chưa xóa có trạng thái = 0
                $select -> where -> equalTo(TABLE_CONTRACT .'.delete', 0);
            });
        }

        if($options['task'] == 'list-item-warehouse-sold') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $paginator  = $arrParam['paginator'];
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
                $number     = new \ZendX\Functions\Number();

                // Chỉ lấy ra danh sách những đơn hàng có trạng thái giục đơn là hoàn và trạng thái kế toán là Hủy hàng có sẵn.
                $select -> where -> equalTo(TABLE_CONTRACT .'.status_acounting_id', STATUS_CONTRACT_ACOUNTING_RETURN);
                $select -> where -> lessThan(TABLE_CONTRACT .'.total_number_product', 1);

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
                    ), 'left');
                $select -> order(array(TABLE_CONTRACT .'.date' => 'DESC'));

                if(!isset($options['paginator']) || $options['paginator'] == true) {
                    $select -> limit($paginator['itemCountPerPage'])
                        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }

                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $filter_keyword = trim($ssFilter['filter_keyword']);
                    if(0&&strlen($number->formatToPhone($filter_keyword)) >= 10) {
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
                            -> greaterThanOrEqualTo(TABLE_CONTRACT .'.date', $date->formatToData($ssFilter['filter_date_begin']))
                            ->AND
                            -> lessThanOrEqualTo(TABLE_CONTRACT .'.date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                            -> UNNEST;
                    } elseif (!empty($ssFilter['filter_date_begin'])) {
                        $select->where->greaterThanOrEqualTo(TABLE_CONTRACT .'.date', $date->formatToData($ssFilter['filter_date_begin']));
                    } elseif (!empty($ssFilter['filter_date_end'])) {
                        $select->where->lessThanOrEqualTo(TABLE_CONTRACT .'.date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
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
                // Đơn hàng chưa xóa có trạng thái = 0
                $select -> where -> equalTo(TABLE_CONTRACT .'.delete', 0);
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
    		})->toArray();
		}

		if($options['task'] == 'by-bill-code') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
			    $select -> where -> equalTo('bill_code', $arrParam['bill_code']);
    		})->toArray();
		}
		
		return current($result);
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrItem  = $arrParam['item'];
	    $arrRoute = $arrParam['route'];
	    
	    $date     = new \ZendX\Functions\Date();
	    $number   = new \ZendX\Functions\Number();
	    $filter   = new \ZendX\Filter\Purifier();
	    $gid      = new \ZendX\Functions\Gid();
		
		if ($options['task'] == 'update-code') {
            $id = $arrParam;
            $result = $this->getItem(array('id' => $id));
            $index = $result['index'];

            if (strlen($index) <= 6) {
                $i = 8 - strlen($index);
                $data['code'] = substr_replace("DH000000",$index, $i);
                $this->tableGateway->update($data, array('id' => $id));
            }else{
                $data['code'] = substr_replace("DH000000",$index, 2);
                $this->tableGateway->update($data, array('id' => $id));
            }
            return true;
		}
		
		if($options['task'] == 'add-item') {
		    // Tham số liên hệ
		    $arrParamContact  = $arrParam;
		    
		    // Xóa phân tử không cần update
		    unset($arrParamContact['data']['date']);
		    unset($arrParamContact['data']['product_id']);
		    
		    if(!empty($arrItem)) {
		        // Nếu khách hàng không phải kho
		        $arrParamContact['item']                      		= $arrItem;
		        $arrParamContact['data']['id']                		= $arrItem['id'];
		        $arrParamContact['data']['contract_total']    		= $arrItem['contract_total'] + 1;
		        $arrParamContact['data']['contract_number']   		= $arrItem['contract_number'] + 1;
		        $arrParamContact['data']['contract_price_total']    = $arrItem['contract_price_total'] + $number->formatToData($arrData['price_total']);
		        $arrParamContact['data']['type']              		= 'ok';
		        
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
		        
		        // Nếu là khách hàng kho. Chuyển về cho người nhập đơn hàng quản lý
		        if(!empty($arrItem['store'])) {
    		        $arrParamContact['data']['user_id']           = $this->userInfo->getUserInfo('id');
    		        $arrParamContact['data']['sale_group_id']     = $this->userInfo->getUserInfo('sale_group_id');
    		        $arrParamContact['data']['sale_branch_id']    = $this->userInfo->getUserInfo('sale_branch_id');
    		        $arrParamContact['data']['store']             = 'null';
				}
				$arrParamContact['data']['contact_received'] = [
					'name' 		=> $arrData['name_received'],
					'phone' 	=> $arrData['phone_received'],
					'address' 	=> $arrData['address_received'],
				];
		        
		        // Cập nhật liên hệ
		        $contact_id = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($arrParamContact, array('task' => 'edit-item'));
		    } else {
		        // Thêm mới liên hệ
		        $arrParamContact['data']['contract_total']    = 1;
		        $arrParamContact['data']['contract_number']    = 1;
		        $arrParamContact['data']['contract_price_total'] = $number->formatToData($arrData['price_total']);
		        $arrParamContact['data']['type']              = 'ok';
		        $arrParamContact['data']['user_id']           = $this->userInfo->getUserInfo('id');
		        $arrParamContact['data']['sale_group_id']     = $this->userInfo->getUserInfo('sale_group_id');
		        $arrParamContact['data']['sale_branch_id']    = $this->userInfo->getUserInfo('sale_branch_id');
				$arrParamContact['data']['product_ids']       = $arrData['product_id'];
				
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
    		    $contract_options = array();
    		    $contract_options['contact_type']                 = $arrItem['type'];
    		    $contract_options['contact_source_group_id']      = $arrItem['source_group_id'] ? $arrItem['source_group_id'] : $arrData['source_group_id'];
    		    $contract_options['contact_source_known_id']      = $arrItem['source_known_id'] ? $arrItem['source_known_id'] : $arrData['source_known_id'];
    		    $contract_options['contact_history_created']      = $arrItem['history_created'];
    		    $contract_options['contact_store']                = $arrItem['store'];
    		    $contract_options['sale_note']                    = $arrData['sale_note'];
				$contract_options['production_note']              = $arrData['production_note'];
				$contract_options['product_name'] 				  = $arrData['product_name'];
				$contract_options['product_return'] 			  = $arrData['product_return'];
				$contract_options['contract_received'] 			  = [
					'name' 		=> $arrData['name_received'],
					'phone' 	=> $arrData['phone_received'],
					'address' 	=> $arrData['address_received'],
				];
				
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
					'contact_id'              => $contact_id,
					'stock'			  		  => $arrData['stock'],
					'transport_id'			  => $arrData['transport_id'],
					'type_of_carpet_id'		  => $arrData['type_of_carpet_id'],
					'carpet_color_id'		  => $arrData['carpet_color_id'],
					'tangled_color_id'		  => $arrData['tangled_color_id'],
					'row_seats_id'		  	  => $arrData['row_seats_id'],
					'flooring_id'		      => $arrData['flooring_id'],
					'status_id'		          => $arrData['status_id'],
					'production_type_id'	  => $arrData['production_type_id'],
					'production_department_type' => $arrData['production_department_type'],
					'price_carpet'		      => $number->formatToData($arrData['price_carpet']),
					'price_nano'		      => $number->formatToData($arrData['price_nano']),
					'vat'		      		  => $number->formatToData($arrData['vat']),
    		        'user_id'                 => $arrParamContact['data']['user_id'] ? $arrParamContact['data']['user_id'] : $arrItem['user_id'],
    		        'sale_group_id'           => $arrParamContact['data']['sale_group_id'] ? $arrParamContact['data']['sale_group_id'] : $arrItem['sale_group_id'],
    		        'sale_branch_id'          => $arrParamContact['data']['sale_branch_id'] ? $arrParamContact['data']['sale_branch_id'] : $arrItem['sale_branch_id'],
    		        'created'                 => date('Y-m-d H:i:s'),
    		        'created_by'              => $this->userInfo->getUserInfo('id'),
    		        'options'                 => serialize($contract_options)
				);

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
				$this->saveItem($id, array('task' => 'update-code'));
				
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
    		                // 'price'                   => $arrData['price'],
    		                // 'price_promotion'         => $arrData['price_promotion'],
    		                // 'price_promotion_percent' => $arrData['price_promotion_percent'],
    		                // 'price_promotion_price'   => $arrData['price_promotion_price'],
    		                // 'promotion_content'       => $arrData['promotion_content'],
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
			
			$contract_options = !empty($arrItem['options']) ? unserialize($arrItem['options']) : null;
			$contract_options['sale_note']                    = $arrData['sale_note'];
			$contract_options['production_note']              = $arrData['production_note'];
			$contract_options['product_name']              	  = $arrData['product_name'];
			$contract_options['product_return'] 			  = $arrData['product_return'];
			$contract_options['contract_received'] = [
				'name' 		=> $arrData['name_received'],
				'phone' 	=> $arrData['phone_received'],
				'address' 	=> $arrData['address_received'],
			];
			
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
				$data['price_owed'] = $number->formatToNumber($arrData['price_total']) - $arrItem['price_paid'];
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
			if(isset($arrData['price_owed'])) {
			    $data['price_owed'] = $number->formatToNumber($arrData['price_owed']);
			}
			if(!empty($arrData['contact_id'])) {
			    $data['contact_id'] = $arrData['contact_id'];
			}
			// if(!empty($arrData['product_id'])) {
			//     $data['product_id'] = $arrData['product_id'];
			// }
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
			if(!empty($arrData['type_of_carpet_id'])) {
			    $data['type_of_carpet_id'] = $arrData['type_of_carpet_id'];
			}
			if(!empty($arrData['carpet_color_id'])) {
			    $data['carpet_color_id'] = $arrData['carpet_color_id'];
			}
			if(!empty($arrData['tangled_color_id'])) {
			    $data['tangled_color_id'] = $arrData['tangled_color_id'];
			}
			if(!empty($arrData['row_seats_id'])) {
			    $data['row_seats_id'] = $arrData['row_seats_id'];
			}
			if(!empty($arrData['flooring_id'])) {
			    $data['flooring_id'] = $arrData['flooring_id'];
			}
			if(!empty($arrData['status_id'])) {
			    $data['status_id'] = $arrData['status_id'];
			}
			if(!empty($arrData['production_type_id'])) {
			    $data['production_type_id'] = $arrData['production_type_id'];
			}
			if(!empty($arrData['production_department_type'])) {
			    $data['production_department_type'] = $arrData['production_department_type'];
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

		if($options['task'] == 'edit-code-price') {

		    $id = $arrData['id'];
			$data = array();

			$contract_options = !empty($arrItem['options']) ? unserialize($arrItem['options']) : null;
            foreach($arrData['contract_product']['capital_default'] as $key => $value){
                if ($contract_options['product'][$key]['capital_default'] != $number->formatToData($value)) {
                    $contract_options['product'][$key]['capital_default'] = $number->formatToData($value);
                }
            }

			if(!empty($contract_options)) {
			    $data['options'] = serialize($contract_options);
			}
			// Cập nhật đơn hàng
			$this->tableGateway->update($data, array('id' => $id));
			return $id;
		}

		if($options['task'] == 'update-price') {
			$arrContact = $arrParam['contact'];
		    $id = $arrData['id'];
			$data = array();

			if(isset($arrData['price_paid'])) {
				$data['price_paid'] = $number->formatToNumber($arrData['price_paid']);
			}
            if(isset($arrData['price_owed'])) {
                $data['price_owed'] = $number->formatToNumber($arrData['price_owed']);
            }
			if(isset($arrData['price_reduce_sale'])) {
			    $data['price_reduce_sale'] = $number->formatToNumber($arrData['price_reduce_sale']);
			}

			// Cập nhật đơn hàng
			$this->tableGateway->update($data, array('id' => $id));

			// Thêm lịch sử hệ thống
			if(!empty($id)) {
			    $arrCheckLogs = array('price_paid', 'price_owed', 'price_reduce_sale');
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

		// Import - Insert
		if($options['task'] == 'import-update') {				
			$data	= array(
			    'bill_code'             => $arrData['bill_code'],
			);
			$this->tableGateway->update($data, ['id' => $arrData['id']]);
			
			return $id;
		}
		
		// Cập nhật vật phẩm
		if($options['task'] == 'add-matter') {
		    $arrContact = $arrParam['contact'];
		    $arrContract = $arrParam['contract'];
		    
		    $id = $arrContract['id'];
		    $matter_ids = !empty($arrContract['matter_ids']) ? unserialize($arrContract['matter_ids']) : array();
		    $matter_add = array();
		    if(!empty($arrData['matter_ids'])) {
		        foreach ($arrData['matter_ids'] AS $key => $val) {
		            $matter_ids[$val] = array(
		                'date' => $arrData['date'],
		                'created_by' => $this->userInfo->getUserInfo('id')
		            );
		            $matter_add[] = $val;
		        }
		    }
		    $data = array(
		        'matter_ids' => serialize($matter_ids)
		    );
		    
		    $this->tableGateway->update($data, array('id' => $id));
		    
		    // Thêm lịch sử hệ thống
		    if(!empty($id)) {
	            $arrParamLogs = array(
	                'data' => array(
	                    'title'          => 'Vật phẩm',
	                    'phone'          => $arrContact['phone'],
	                    'name'           => $arrContact['name'],
	                    'action'         => 'Thêm mới',
	                    'contact_id'     => $arrContact['id'],
	                    'contract_id'    => $id,
	                    'options'        => array(
	                        'date' => $arrData['date'],
	                        'created_by' => $this->userInfo->getUserInfo('id'),
	                        'matter_ids' => implode(',', $matter_add)
	                    )
	                )
	            );
	            $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
		    }
		    return $id;
		}
		
		// Xóa vật phẩm
		if($options['task'] == 'delete-matter') {
		    $arrContract = $arrParam['contract'];
		    $arrContact = $arrParam['contact'];
		    $arrItem = $arrParam['item'];
		    
		    $id = $arrContract['id'];
		    $matter_ids = !empty($arrContract['matter_ids']) ? unserialize($arrContract['matter_ids']) : array();
		    unset($matter_ids[$arrItem['matter_id']]);
		    $data = array(
		        'matter_ids' => !empty($matter_ids) ? serialize($matter_ids) : null
		    );
		    $this->tableGateway->update($data, array('id' => $id));
		    
		    // Thêm lịch sử hệ thống
		    if(!empty($id)) {
	            $arrParamLogs = array(
	                'data' => array(
	                    'title'          => 'Vật phẩm',
	                    'phone'          => $arrContact['phone'],
	                    'name'           => $arrContact['name'],
	                    'action'         => 'Xóa',
	                    'contact_id'     => $arrContact['id'],
	                    'contract_id'    => $id,
	                    'options'        => array(
	                        'matter_ids' => $arrItem['matter_id'],
	                        'note_log' => $arrData['note_log'],
	                    )
	                )
	            );
	            $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
		    }
		    return $id;
		}
		
		// Chuyển người quản lý
		if($options['task'] == 'change-user') {
		    $arrUser      = $arrParam['user'];
		    $arrContract  = $arrParam['contract'];
		    $arrContact   = $arrParam['contact'];
		    $arrBill      = $arrParam['bill'];
		
		    // Cập nhật quản lý đơn hàng
		    $id = $arrContract['id'];
	        $data = array(
	            'user_id'            => $arrUser['id'],
	            'sale_branch_id'     => $arrUser['sale_branch_id'],
	            'sale_group_id'      => $arrUser['sale_group_id'],
	        );
	        $where = new Where();
	        $where->equalTo('id', $id);
	        $this->tableGateway->update($data, $where);
	        
	        // Kiểm tra xem có được chuyển toàn bộ hóa đơn
	        if($arrData['transfer_bill'] == 'yes') {
	            $arrParamBill = $arrParam;
	            $arrParamBill['data']['contract_id'] = $arrContract['id'];
	            $arrParamBill['data']['user_id'] = $arrUser['id'];
	            $arrParamBill['data']['sale_branch_id'] = $arrUser['sale_branch_id'];
	            $arrParamBill['data']['sale_group_id'] = $arrUser['sale_group_id'];
	            $bill = $this->getServiceLocator()->get('Admin\Model\BillTable')->saveItem($arrParamBill, array('task' => 'contract-change-user'));
	        }
	        
	        // Kiểm tra xem có được chuyển quản lý liên hệ
	        if($arrData['transfer_contact'] == 'yes') {
	            $arrParamContact = $arrParam;
	            $arrParamContact['data']['id'] = $arrContact['id'];
	            $arrParamContact['data']['user_id'] = $arrUser['id'];
	            $arrParamContact['data']['sale_branch_id'] = $arrUser['sale_branch_id'];
	            $arrParamContact['data']['sale_group_id'] = $arrUser['sale_group_id'];
	            $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($arrParamContact, array('task' => 'contract-change-user'));
	        }
	
		    // Thêm lịch sử hệ thống
			if(!empty($id)) {
			    $arrCheckLogs = array('date', 'price', 'price_promotion', 'contact_id', 'product_id', 'edu_class_id', 'user_id', 'sale_branch_id', 'sale_group_id');
			    $arrCheckResult = array();
			    foreach ($arrCheckLogs AS $field) {
			        if($data[$field] != $arrContract[$field]) {
			            if(isset($data[$field])) {
			                $arrCheckResult[$field] = $data[$field];
			            }
			        }
			    }
			
			    if(!empty($arrCheckResult)) {
			        $arrParamLogs = array(
			            'data' => array(
			                'title'          => 'đơn hàng',
			                'phone'          => $arrContact['phone'],
			                'name'           => $arrContact['name'],
			                'action'         => 'Chuyển quản lý',
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
	}

    public function updateItem($arrParam = null, $options = null) {
        $arrData  = $arrParam['data'];

        // Cập nhật trạng thái đơn hàng
        if ($options['task'] == 'update-item-status') {
            $arr_id = $arrData['cid'];
            $field_status_name  = $arrData['field_status_name'];
            $field_status_value = $arrData['field_status_value'];

            $data = array(
                $field_status_name => $field_status_value,
            );

            $where = new Where();
            $where -> isNull($field_status_name);
            $where -> in('id', $arr_id);
            $where -> notEqualTo('lock', 1);

            $result = $this -> tableGateway -> update($data, $where);
            return $result;
        }


        // cập nhật các trạng thái
        if ($options['task'] == 'update-status') {
            $id = $arrData['id'];
            $data = array();
            if(!empty($arrData['status_acounting_id'])) {
                $data['status_acounting_id'] = $arrData['status_acounting_id'];
            }

            // Cập nhật đơn hàng
            $this->tableGateway->update($data, array('id' => $id));
        }
    }
	
//	public function deleteItem($arrParam = null, $options = null){
//	    if($options['task'] == 'delete-item') {
//	        $arrData  = $arrParam['data'];
//    	    $arrRoute = $arrParam['route'];
//    	    $arrItem  = $arrParam['item'];
//
//    	    $contract_options = !empty($arrItem['options']) ? unserialize($arrItem['options']) : array();
//
//    	    // Xóa đơn hàng
//            $where = new Where();
//            $where -> equalTo('id', $arrItem['id']);
//            $this -> tableGateway -> delete($where);
//
//            // Xóa toàn bộ hóa đơn của đơn hàng
//            $bill_delete = $this->getServiceLocator()->get('Admin\Model\BillTable')->deleteItem(array('contract_id' => $arrItem['id']), array('task' => 'contract-delete'));
//
//            // Cập nhật lại số đơn hàng của liên hệ
//            $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $arrItem['contact_id']));
//            $contract_total = intval($contact['contract_total']) - 1;
//            $contact_data = array('id' => $contact['id'], 'contract_total' => $contract_total);
//            if($contract_total <= 0) {
//                $contact_data['contract_total'] = 0;
//            }
//            $contact_update = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem(array('data' => $contact_data, 'item' => $contact), array('task' => 'edit-item'));
//
//            // Cập nhật lại sĩ số lớp học
//            if(!empty($arrItem['edu_class_id'])) {
//                $edu_class_id = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->saveItem(array('data' => array('id' => $arrItem['edu_class_id'])), array('task' => 'update-student', 'type' => 'down'));
//            }
//
//            // Thêm lịch sử xóa đơn hàng
//            $arrParamLogs = array(
//                'data' => array(
//                    'title'          => 'đơn hàng',
//                    'phone'          => $contact['phone'],
//                    'name'           => $contact['name'],
//                    'action'         => 'Xóa',
//                    'contact_id'     => $contact['id'],
//                    'contract_id'    => $arrItem['id'],
//                    'options'        => array(
//                        'date'                    => $arrItem['date'],
//                        'price'                   => $arrItem['price'],
//                        'price_promotion'         => $arrItem['price_promotion'],
//                        'price_promotion_percent' => $arrItem['price_promotion_percent'],
//                        'price_promotion_price'   => $arrItem['price_promotion_price'],
//                        'promotion_content'       => $contract_options['promotion_content'],
//                        'price_total'             => $arrItem['price_total'],
//                        'product_id'              => $arrItem['product_id'],
//                        'edu_class_id'            => $arrItem['edu_class_id'],
//                        'user_id'                 => $arrItem['user_id'],
//                        'sale_branch_id'          => $arrItem['sale_branch_id'],
//                        'sale_group_id'           => $arrItem['sale_group_id'],
//                    )
//                )
//            );
//            $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
//
//            $result = 1;
//	    }
//
//	    return $result;
//	}
	
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
	            
	            $columns = array('date', 'price_total', 'price_paid', 'price_accrued', 'user_id', 'sale_branch_id', 'sale_group_id');
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
	    return $result;
	}
}





