<?php
namespace Admin\Model;

use kcfinder\zipFolder;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class FormDataTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter  = $arrParam['ssFilter'];
                $date      = new \ZendX\Functions\Date();
                
                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));

                $select -> join(TABLE_USER, TABLE_USER .'.id = '. TABLE_FORM_DATA .'.marketer_id',array(), 'inner');
                
    			if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> NEST
                    			     -> greaterThanOrEqualTo(TABLE_FORM_DATA .'.date', $date->formatToSearch($ssFilter['filter_date_begin']))
                    			     ->AND
                    			     -> lessThanOrEqualTo(TABLE_FORM_DATA .'.date', $date->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59')
                    			     -> UNNEST;
    			} elseif (!empty($ssFilter['filter_date_begin'])) {
    			    $select -> where -> greaterThanOrEqualTo(TABLE_FORM_DATA .'.date', $date->formatToSearch($ssFilter['filter_date_begin']));
    			} elseif (!empty($ssFilter['filter_date_end'])) {
    			    $select->where->lessThanOrEqualTo(TABLE_FORM_DATA .'.date', $date->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59');
    			}
    			
    			if(!empty($ssFilter['filter_form'])) {
    				$select -> where -> equalTo(TABLE_CONTACT .'.product_id', $ssFilter['filter_form']);
    			}
                
    			if(!empty($ssFilter['filter_location_city'])) {
    			    $select -> where -> equalTo(TABLE_FORM_DATA .'.city_id', $ssFilter['filter_location_city']);
    			}

    			if(!empty($ssFilter['filter_marketer_id'])) {
    			    $select -> where -> equalTo(TABLE_FORM_DATA .'.marketer_id', $ssFilter['filter_marketer_id']);
    			}

    			if(!empty($ssFilter['filter_sales_id'])) {
    			    $select -> where -> equalTo(TABLE_FORM_DATA .'.sales_id', $ssFilter['filter_sales_id']);
    			}

                if(!empty($ssFilter['filter_product_id'])) {
                    $select -> where -> equalTo(TABLE_FORM_DATA .'.product_id', $ssFilter['filter_product_id']);
                }

                if(!empty($ssFilter['filter_product_group_id'])) {
                    $select -> where -> equalTo(TABLE_FORM_DATA .'.product_group_id', $ssFilter['filter_product_group_id']);
                }
    			
    			if($ssFilter['filter_active'] == 'active') {
    				$select -> where -> NEST
    			    				 -> isNotNull(TABLE_FORM_DATA .'.sales_id')
    			    				 ->AND
    			    				 -> notEqualTo(TABLE_FORM_DATA .'.sales_id', '');
    			    
    			} elseif($ssFilter['filter_active'] == 'unactive') {
    			    $select -> where -> NEST
    			    				 -> isNull(TABLE_FORM_DATA .'.sales_id');
    			}

                if(isset($ssFilter['filter_contact_coin']) &&  $ssFilter['filter_contact_coin'] != '') {
                    if($ssFilter['filter_contact_coin'] != 0){
                        $select -> where ->equalTo(TABLE_FORM_DATA .'.contact_coin', $ssFilter['filter_contact_coin']);
                    }
                    else{
                        $select -> where ->isNull(TABLE_FORM_DATA .'.contact_coin');
                    }
                }

                if(!empty($ssFilter['filter_sale_branch'])) {
                    $select -> where ->equalTo(TABLE_USER .'.sale_branch_id', $ssFilter['filter_sale_branch']);
                }

                if(!empty($ssFilter['filter_sale_group'])) {
                    $select -> where ->equalTo(TABLE_USER .'.sale_group_id', $ssFilter['filter_sale_group']);
                }
                
	            if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select -> where -> NEST
                			      	 -> like(TABLE_FORM_DATA .'.phone', '%'. $ssFilter['filter_keyword'] . '%')
                			      	 ->OR
                			      	 -> like(TABLE_FORM_DATA .'.name', '%'. $ssFilter['filter_keyword'] . '%')
                			       	 -> UNNEST;
    			}
            })->current();
	    }
	    
	    if($options['task'] == 'list-all') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
	            $data       = $arrParam['data'];
	            $date       = new \ZendX\Functions\Date();
	            $number     = new \ZendX\Functions\Number();
	            $ssFilter   = $arrParam['ssFilter'];
	            $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
	             
	           $select->where->NEST
	                    ->NEST
        	            ->greaterThanOrEqualTo(TABLE_FORM_DATA.'.created', $date->formatToSearch($arrParam['data']['created']) . ' 00:00:00')
        	            ->and
        	            ->lessThanOrEqualTo(TABLE_FORM_DATA.'.created', $date->formatToSearch($arrParam['data']['created']) . ' 23:59:59')
        	            ->UNNEST
        	            ->UNNEST;
	    
	            $select -> where -> notEqualTo(TABLE_FORM_DATA.'.user_action', $arrParam['data']['user_action']);
	            $select -> where -> equalTo(TABLE_FORM_DATA.'.user_id', $arrParam['data']['user_id']);
	 
	            if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
	                $select -> where -> NEST
	                -> greaterThanOrEqualTo(TABLE_FORM_DATA .'.created', $date->formatToSearch($ssFilter['filter_date_begin']))
	                ->and
	                -> lessThanOrEqualTo(TABLE_FORM_DATA .'.created', $date->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59')
	                -> UNNEST;
	            } elseif (!empty($ssFilter['filter_date_begin'])) {
	                $select -> where -> greaterThanOrEqualTo(TABLE_FORM_DATA .'.created', $date->formatToSearch($ssFilter['filter_date_begin']));
	            } elseif (!empty($ssFilter['filter_date_end'])) {
	                $select->where->lessThanOrEqualTo(TABLE_FORM_DATA .'.created', $date->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59');
	            }
	            
	            if(isset($ssFilter['filter_user_action']) && $ssFilter['filter_user_action'] != '') {
	                $select ->where->NEST
        	                ->greaterThanOrEqualTo(TABLE_FORM_DATA.'.history_return', $date->formatToSearch($arrParam['data']['history_return']) . ' 00:00:00')
        	                ->and
        	                ->lessThanOrEqualTo(TABLE_FORM_DATA.'.history_return', $date->formatToSearch($arrParam['data']['history_return']) . ' 23:59:59')
        	                ->UNNEST;
	                $select -> where -> equalTo(TABLE_FORM_DATA .'.user_action', $ssFilter['filter_user_action']);
	            }
	        })->current();
	    }

	    // Đếm số lượng data marketing cần chia cho sale
	    if($options['task'] == 'list-item-contact') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
	            $date       = new \ZendX\Functions\Date();
	            $ssFilter   = $arrParam['ssFilter'];

                $date_begin = $date->formatToSearch($ssFilter['filter_date_begin']);
                $date_end 	= $date->formatToSearch($ssFilter['filter_date_end']);

                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));

                $select -> join(TABLE_USER, TABLE_USER .'.id = '. TABLE_FORM_DATA .'.marketer_id',array(), 'inner');

                if($ssFilter['filter_active'] == 'active') {
                    $select -> where -> isNotNull(TABLE_FORM_DATA .'.sales_id');
                } elseif($ssFilter['filter_active'] == 'unactive') {
                    $select -> where ->isNull(TABLE_FORM_DATA .'.sales_id');
                }

                if(isset($ssFilter['filter_contact_coin']) &&  $ssFilter['filter_contact_coin'] != '') {
                    if($ssFilter['filter_contact_coin'] != 0){
                        $select -> where ->equalTo(TABLE_FORM_DATA .'.contact_coin', $ssFilter['filter_contact_coin']);
                    }
                    else{
                        $select -> where ->isNull(TABLE_FORM_DATA .'.contact_coin');
                    }
                }

                if(isset($ssFilter['filter_cancel_share']) &&  $ssFilter['filter_cancel_share'] != '') {
                    if($ssFilter['filter_cancel_share'] != 0){
                        $select -> where ->equalTo(TABLE_FORM_DATA .'.cancel_share', $ssFilter['filter_cancel_share']);
                    }
                    else{
                        $select -> where -> NEST
                            ->isNull(TABLE_FORM_DATA .'.cancel_share')
                            ->OR
                            ->equalTo(TABLE_FORM_DATA .'.cancel_share', $ssFilter['filter_cancel_share'])
                            ->UNNEST;
                        ;
                    }
                }

                if(!empty($date_begin) && !empty($date_end)) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo(TABLE_FORM_DATA .'.date', $date_begin)
                        ->AND
                        -> lessThanOrEqualTo(TABLE_FORM_DATA .'.date', $date_end . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($date_begin)) {
                    $select -> where -> greaterThanOrEqualTo(TABLE_FORM_DATA .'.date', $date_begin);
                } elseif (!empty($date_end)) {
                    $select->where->lessThanOrEqualTo(TABLE_FORM_DATA .'.date', $date_end . ' 23:59:59');
                }

                if(!empty($ssFilter['filter_location_city'])) {
                    $select -> where -> equalTo(TABLE_FORM_DATA .'.city_id', $ssFilter['filter_location_city']);
                }

                if(!empty($ssFilter['filter_sale_branch'])) {
                    $select -> where ->equalTo(TABLE_USER .'.sale_branch_id', $ssFilter['filter_sale_branch']);
                }

                if(!empty($ssFilter['filter_sale_group'])) {
                    $select -> where ->equalTo(TABLE_USER .'.sale_group_id', $ssFilter['filter_sale_group']);
                }

                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $select -> where ->NEST
                        ->like(TABLE_FORM_DATA .'.phone', '%'. $ssFilter['filter_keyword'] . '%')
                        ->OR
                        ->like(TABLE_FORM_DATA .'.name', '%'. $ssFilter['filter_keyword'] . '%')
                        ->UNNEST;
                }
	        })->current();
	    }

        // Đếm số data trùng sđt trùng ngày và khác marketer
        if($options['task'] == 'list-data-coin') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
                $date      	= new \ZendX\Functions\Date();

                $select -> where -> equalTo(TABLE_FORM_DATA.'.phone', $arrParam['phone']);
                $select -> where -> notEqualTo(TABLE_FORM_DATA.'.marketer_id', $arrParam['marketer_id']);
                $select -> where -> like(TABLE_FORM_DATA.'.date', '%'.$arrParam['date'].'%');
            })->current();
        }

        // lấy data_item theo các điều kiện.
        if($options['task'] == 'by-condition') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));

                if(!empty($arrParam['phone'])){
                    $select -> where -> equalTo(TABLE_FORM_DATA.'.phone', $arrParam['phone']);
                }
                if(!empty($arrParam['marketer_id'])){
                    $select -> where -> equalTo(TABLE_FORM_DATA.'.marketer_id', $arrParam['marketer_id']);
                }
                if(!empty($arrParam['date'])){
                    $select -> where -> like(TABLE_FORM_DATA.'.date', '%'.$arrParam['date'].'%');
                }
            })->current();
        }
	    
	    return $result->count;
	}
	
	public function listItem($arrParam = null, $options = null){
		if($options['task'] == 'list-item') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $paginator 	= $arrParam['paginator'];
                $ssFilter  	= $arrParam['ssFilter'];
                $date      	= new \ZendX\Functions\Date();
                
		        $date_begin = $date->formatToSearch($ssFilter['filter_date_begin']);
		        $date_end 	= $date->formatToSearch($ssFilter['filter_date_end']);
    			
    			if(!isset($options['paginator']) || $options['paginator'] == true) {
    			    $select -> limit($paginator['itemCountPerPage'])
    			            -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
    			}

				if(!empty($ssFilter['order_by']) && !empty($ssFilter['order'])) {
					$select ->ORder(array($ssFilter['order_by'] => strtoupper($ssFilter['order'])));
				}

				$select -> join(TABLE_USER, TABLE_USER .'.id = '. TABLE_FORM_DATA .'.marketer_id',
                    array(
                        'marketer_branch_id' => 'sale_branch_id',
                        'marketer_group_id'  => 'sale_group_id',
                    ), 'inner')
                    -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_FORM_DATA .'.contact_id',
                    array(
                        'contact_user_id' => 'user_id',
                        'contact_sales_expected'  => 'sales_expected',
                        'contact_options'  => 'options',
                    ), 'left');
    			
			    if(!empty($date_begin) && !empty($date_end)) {
    			    $select -> where -> NEST
    			                     -> greaterThanOrEqualTo(TABLE_FORM_DATA .'.date', $date_begin)
                    			     ->AND
                    			     -> lessThanOrEqualTo(TABLE_FORM_DATA .'.date', $date_end . ' 23:59:59')
                    			     -> UNNEST;
    			} elseif (!empty($date_begin)) {
    			    $select -> where -> greaterThanOrEqualTo(TABLE_FORM_DATA .'.date', $date_begin);
    			} elseif (!empty($date_end)) {
    			    $select->where->lessThanOrEqualTo(TABLE_FORM_DATA .'.date', $date_end . ' 23:59:59');
    			}
                
    			if(!empty($ssFilter['filter_location_city'])) {
    			    $select -> where -> equalTo(TABLE_FORM_DATA .'.city_id', $ssFilter['filter_location_city']);
    			}

                if(!empty($ssFilter['filter_marketer_id'])) {
                    $select -> where -> equalTo(TABLE_FORM_DATA .'.marketer_id', $ssFilter['filter_marketer_id']);
                }

                if(!empty($ssFilter['filter_sales_id'])) {
                    $select -> where -> equalTo(TABLE_FORM_DATA .'.sales_id', $ssFilter['filter_sales_id']);
                }

                if(!empty($ssFilter['filter_product_id'])) {
                    $select -> where -> equalTo(TABLE_FORM_DATA .'.product_id', $ssFilter['filter_product_id']);
                }

                if(!empty($ssFilter['filter_product_group_id'])) {
                    $select -> where -> equalTo(TABLE_FORM_DATA .'.product_group_id', $ssFilter['filter_product_group_id']);
                }
    			
    			if($ssFilter['filter_active'] == 'active') {
    				$select -> where -> NEST
    			    				 -> isNotNull(TABLE_FORM_DATA .'.sales_id')
    			    				 ->AND
    			    				 -> notEqualTo(TABLE_FORM_DATA .'.sales_id', '');
    			    
    			} elseif($ssFilter['filter_active'] == 'unactive') {
    			    $select -> where -> NEST
    			    				 -> isNull(TABLE_FORM_DATA .'.sales_id');
    			}
    			
	            if(isset($ssFilter['filter_contact_coin']) &&  $ssFilter['filter_contact_coin'] != '') {
	                if($ssFilter['filter_contact_coin'] != 0){
                        $select -> where ->equalTo(TABLE_FORM_DATA .'.contact_coin', $ssFilter['filter_contact_coin']);
                    }
	                else{
                        $select -> where ->isNull(TABLE_FORM_DATA .'.contact_coin');
                    }
    			}

	            if(!empty($ssFilter['filter_sale_branch'])) {
    			    $select -> where ->equalTo(TABLE_USER .'.sale_branch_id', $ssFilter['filter_sale_branch']);
    			}

	            if(!empty($ssFilter['filter_sale_group'])) {
    			    $select -> where ->equalTo(TABLE_USER .'.sale_group_id', $ssFilter['filter_sale_group']);
    			}
                
	            if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select -> where ->NEST
                			      	 ->like(TABLE_FORM_DATA .'.phone', '%'. $ssFilter['filter_keyword'] . '%')
                			      	 ->OR
                			      	 ->like(TABLE_FORM_DATA .'.name', '%'. $ssFilter['filter_keyword'] . '%')
                			       	 ->UNNEST;
				}
    		})->toArray();
		}
		
		// Trả về danh sách data đã có doanh thu khi lên đơn hàng có trạng thái là đã sản xuât.
        if($options['task'] == 'list-data-sales') {
            $result = $this->tableGateway->select(function (Select $select) use ($arrParam){
                $ssFilter  = $arrParam['ssFilter'];
                $date      = new \ZendX\Functions\Date();
				
				// $select -> columns(array('id'));
				$select -> columns(array('id', 'sum_total_price' => new \Zend\Db\Sql\Expression('SUM('.TABLE_CONTRACT.'.price_total)')));
                $select -> join(TABLE_CONTRACT, TABLE_CONTRACT .'.contact_id = '. TABLE_FORM_DATA .'.contact_id', array(), 'inner');

				$select -> where -> isNotNull(TABLE_FORM_DATA.'.sales_id');
				$select -> where -> NEST
								 -> EqualTo(TABLE_CONTRACT.'.production_department_type', STATUS_CONTRACT_PRODUCT_PRODUCTED)
								 ->OR
								 -> EqualTo(TABLE_CONTRACT.'.production_department_type', STATUS_CONTRACT_PRODUCT_POST)
								 -> UNNEST;
				$select->group(TABLE_FORM_DATA.'.id');
			});
			$result = \ZendX\Functions\CreateArray::create($result, array('key' => 'id', 'value' => 'object'));
        }

        // Trả về danh sách data marketing cần chia cho sales
        if($options['task'] == 'list-item-contact') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $paginator 	= $arrParam['paginator'];
                $ssFilter  	= $arrParam['ssFilter'];
                $date      	= new \ZendX\Functions\Date();

                $date_begin = $date->formatToSearch($ssFilter['filter_date_begin']);
                $date_end 	= $date->formatToSearch($ssFilter['filter_date_end']);

                if(!isset($options['paginator']) || $options['paginator'] == true) {
                    $select -> limit($paginator['itemCountPerPage'])
                        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }

                $select ->ORder('phone asc , date desc');

                $select -> join(TABLE_USER, TABLE_USER .'.id = '. TABLE_FORM_DATA .'.marketer_id',
                    array(
                        'marketer_branch_id' => 'sale_branch_id',
                        'marketer_group_id'  => 'sale_group_id',
                    ), 'inner');

                if($ssFilter['filter_active'] == 'active') {
                    $select -> where -> isNotNull(TABLE_FORM_DATA .'.sales_id');
                } elseif($ssFilter['filter_active'] == 'unactive') {
                    $select -> where ->isNull(TABLE_FORM_DATA .'.sales_id');
                }

                if(isset($ssFilter['filter_contact_coin']) &&  $ssFilter['filter_contact_coin'] != '') {
                    if($ssFilter['filter_contact_coin'] != 0){
                        $select -> where ->equalTo(TABLE_FORM_DATA .'.contact_coin', $ssFilter['filter_contact_coin']);
                    }
                    else{
                        $select -> where ->isNull(TABLE_FORM_DATA .'.contact_coin');
                    }
                }

                if(isset($ssFilter['filter_cancel_share']) &&  $ssFilter['filter_cancel_share'] != '') {
                    if($ssFilter['filter_cancel_share'] != 0){
                        $select -> where ->equalTo(TABLE_FORM_DATA .'.cancel_share', $ssFilter['filter_cancel_share']);
                    }
                    else{
                        $select -> where -> NEST
                            ->isNull(TABLE_FORM_DATA .'.cancel_share')
                            ->OR
                            ->equalTo(TABLE_FORM_DATA .'.cancel_share', $ssFilter['filter_cancel_share'])
                            ->UNNEST;
                        ;
                    }
                }

                // Điều kiện phân biệt data đã được chia hay chưa
//                $select -> where ->NEST
//                    ->equalTo(TABLE_FORM_DATA .'.contact_id', "")
//                    ->OR
//                    ->isNull(TABLE_FORM_DATA .'.contact_id')
//                    ->UNNEST;

                if(!empty($date_begin) && !empty($date_end)) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo(TABLE_FORM_DATA .'.date', $date_begin)
                        ->AND
                        -> lessThanOrEqualTo(TABLE_FORM_DATA .'.date', $date_end . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($date_begin)) {
                    $select -> where -> greaterThanOrEqualTo(TABLE_FORM_DATA .'.date', $date_begin);
                } elseif (!empty($date_end)) {
                    $select->where->lessThanOrEqualTo(TABLE_FORM_DATA .'.date', $date_end . ' 23:59:59');
                }

                if(!empty($ssFilter['filter_sale_branch'])) {
                    $select -> where ->equalTo(TABLE_USER .'.sale_branch_id', $ssFilter['filter_sale_branch']);
                }

                if(!empty($ssFilter['filter_sale_group'])) {
                    $select -> where ->equalTo(TABLE_USER .'.sale_group_id', $ssFilter['filter_sale_group']);
                }

                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $select -> where ->NEST
                        ->like(TABLE_FORM_DATA .'.phone', '%'. $ssFilter['filter_keyword'] . '%')
                        ->OR
                        ->like(TABLE_FORM_DATA .'.name', '%'. $ssFilter['filter_keyword'] . '%')
                        ->UNNEST;
                }
            })->toArray();
        }

        // Trả về danh sách data để chia tự động
        if($options['task'] == 'list-all-item') {
            $result = $this->tableGateway->select(function (Select $select) use ($arrParam){
                $ssFilter  = $arrParam['ssFilter'];
                $date      = new \ZendX\Functions\Date();

                $select -> limit($ssFilter['filter_limit']);
                $select -> ORder('date asc');

                if(!empty($ssFilter['filter_sale_branch'])) {
                    $select -> where ->equalTo(TABLE_FORM_DATA .'.branch_id', $ssFilter['filter_sale_branch']);
                }

                $select -> where ->NEST
                    ->equalTo(TABLE_FORM_DATA .'.contact_id', "")
                    ->OR
                    ->isNull(TABLE_FORM_DATA .'.contact_id')
                    ->UNNEST;
            });
        }

        // Trả về data trùng về trước cuối cùng
        if($options['task'] == 'list-item-coincide') {
            $result = $this->tableGateway->select(function (Select $select) use ($arrParam){
                $ssFilter  = $arrParam['ssFilter'];
                $date      = new \ZendX\Functions\Date();

                $select -> limit(1);
                $select -> ORder('date desc');

                if(!empty($ssFilter['date'])) {
                    $select -> where -> lessThanOrEqualTo(TABLE_FORM_DATA .'.date', $ssFilter['date']);
                }

                if(!empty($ssFilter['phone'])) {
                    $select -> where ->equalTo(TABLE_FORM_DATA .'.phone', $ssFilter['phone']);
                }

                if(!empty($ssFilter['id'])) {
                    $select -> where ->notEqualTo(TABLE_FORM_DATA .'.id', $ssFilter['id']);
                }
            })->current();
        }
		
	    if($options['task'] == 'cache') {
	        $cache = $this->getServiceLocator()->get('cache');
	        $cache_key = 'FormData';
	        $result = $cache->getItem($cache_key);
	         
	        if (empty($result)) {
	            $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	                $ssFilter  = $arrParam['ssFilter'];
	                $date      = new \ZendX\Functions\Date();
					// $select->order(array('ordering' => 'ASC', 'name' => 'ASC'));
	                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
	                    $select -> where -> NEST
	                    -> greaterThanOrEqualTo(TABLE_FORM_DATA .'.created', $date->formatToSearch($ssFilter['filter_date_begin']))
	                    ->AND
	                    -> lessThanOrEqualTo(TABLE_FORM_DATA .'.created', $date->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59')
	                    -> UNNEST;
	                } elseif (!empty($ssFilter['filter_date_begin'])) {
	                    $select -> where -> greaterThanOrEqualTo(TABLE_FORM_DATA .'.created', $date->formatToSearch($ssFilter['filter_date_begin']));
	                } elseif (!empty($ssFilter['filter_date_end'])) {
	                    $select->where->lessThanOrEqualTo(TABLE_FORM_DATA .'.created', $date->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59');
	                }
	            });
                $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));
                 
                $cache->setItem($cache_key, $result);
	        }
	    }

		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
	
		if($options == null) {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
				$select -> where -> equalTo(TABLE_FORM_DATA .'.id', $arrParam['id']);
			})->current();
		}

        if($options['task'] == 'by-phone') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $select -> where -> equalTo(TABLE_FORM_DATA.'.phone', $arrParam['phone']);
            })->current();
        }

        // lấy data_item cùng ngày cùng sđt so sánh khi import.
        if($options['task'] == 'by-phone-date') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $select -> where -> equalTo(TABLE_FORM_DATA.'.phone', $arrParam['phone']);
                $select -> where -> equalTo(TABLE_FORM_DATA.'.date', $arrParam['date']);
            })->current();
        }

        // lấy data_item theo các điều kiện.
        if($options['task'] == 'by-condition') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                if(!empty($arrParam['phone'])){
                    $select -> where -> equalTo(TABLE_FORM_DATA.'.phone', $arrParam['phone']);
                }
                if(!empty($arrParam['marketer_id'])){
                    $select -> where -> equalTo(TABLE_FORM_DATA.'.marketer_id', $arrParam['marketer_id']);
                }
                if(!empty($arrParam['date'])){
                    $select -> where -> like(TABLE_FORM_DATA.'.date', '%'.$arrParam['date'].'%');
                }
            })->current();
        }

		if($options['task'] == 'by-contact') {
		    $result	= $this->defaultGet($arrParam, array('by' => 'contact_id'));
		}
		
		if($options['task'] == 'form-contact') {
		    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
		        $select -> where -> equalTo('form_id', $arrParam['form_id'])
		                         -> equalTo('contact_id', $arrParam['contact_id']);
		    })->current();
		}
		
		if($options['task'] == 'get-contact') {
		    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
		        $select -> where -> equalTo('contact_id', $arrParam['data']['contact_id']);
		    })->current();
		}
	
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];
	    
	    $image         = new \ZendX\Functions\Thumbnail($arrData['image']);
	    $filter        = new \ZendX\Filter\Purifier(array( array('HTML.AllowedElements', '') ));
	    $number        = new \ZendX\Functions\Number();
	    $gid           = new \ZendX\Functions\Gid();
	    $dateFormat    = new \ZendX\Functions\Date();

	    // Thêm mới thủ công.
	    if($options['task'] == 'add-item') {
	        $id = $gid->getId();
	        $data = array(
	            'id' => $id,
                'date'                  => date('Y-m-d H:i:s'),
	            'name'                  => $arrData['name'],
	            'phone'                 => $arrData['phone'],
	            'note'                  => $arrData['note'],
	            'content'               => $arrData['content'],
	            'sex'                   => $arrData['sex'],
	            'product_id'            => $arrData['product_id'],
	            'product_group_id'      => $arrData['product_group_id'],
	            'city_id'               => $arrData['city_id'],
	            'district_id'           => $arrData['district_id'],
	            'address'               => $arrData['address'],
	            'marketing_channel_id'  => $arrData['marketing_channel_id'],
	            'job'                   => $arrData['job'],
	            'contact_coin'          => $arrData['contact_coin'],
	            'user_action'           => 'new',
                'contact_id'            => !empty($arrData['contact_id']) ? $arrData['contact_id'] : null,
	            'created'               => date('Y-m-d H:i:s'),
	            'marketer_id'           => $this->userInfo->getUserInfo('id'),
	            'branch_id'             => $this->userInfo->getUserInfo('sale_branch_id'),
	            'group_id'              => $this->userInfo->getUserInfo('sale_group_id'),
	        );
	        $this->tableGateway->insert($data);

            // Cập nhật số điện thoại marketing cho marketer.
            $param['data']["marketer_id"] = $this->userInfo->getUserInfo('id');
            $param['data']["date"]        = date('Y-m-d H:i:s');
            $this->getServiceLocator()->get('Admin\Model\MarketingReportTable')->saveItem($param, array('task' => 'update-number-phone'));

	        return $id;
	    }     

        // Thêm mới từ landing page.
        if($options['task'] == 'add-data-landing') {
            $id = $gid->getId();
            $data = array(
                'id' => $id,
                'date'                  => date('Y-m-d H:i:s'),
                'name'                  => $arrData['name'],
                'phone'                 => $arrData['phone'],
				'note'                  => $arrData['message'],
				
                'marketer_id'           => $arrData['marketer_id'],
                'product_id'            => $arrData['product_id'],
                'product_group_id'      => $arrData['product_group_id'],
	            'marketing_channel_id'  => $arrData['marketing_channel_id'],
	            'branch_id'             => $arrData['sale_branch_id'],
				'group_id'              => $arrData['sale_group_id'],
                'contact_coin'          => !empty($arrData['contact_coin']) ? $arrData['contact_coin'] : null,
                'contact_id'            => !empty($arrData['contact_id']) ? $arrData['contact_id'] : null,

                'created'               => date('Y-m-d H:i:s'),
            );
            $this->tableGateway->insert($data);

            // Cập nhật số điện thoại marketing cho marketer.
            $param['data']["marketer_id"] = $arrData['marketer_id'];
            $param['data']["date"]        = date('Y-m-d H:i:s');
            $this->getServiceLocator()->get('Admin\Model\MarketingReportTable')->saveItem($param, array('task' => 'update-number-phone'));

            return $id;
        }

        // Import dữ liệu mới
        if($options['task'] == 'import-insert') {
            $id = $gid->getId();

            $item_options['crated_by'] = $this->userInfo->getUserInfo('id');
            $item_options['type'] = 'import';

            $data = array(
                'id' => $id,
                'date'                  => $dateFormat->formatToData($arrData['date']),
                'name'                  => $arrData['name'],
                'phone'                 => $arrData['phone'],
                'note'                  => $arrData['note'],
                'content'               => $arrData['content'],
                'address'               => $arrData['address'],
                'job'                   => $arrData['job'],
                'marketer_id'           => $arrData['marketer_id'],
                'product_id'            => $arrData['product_id'],
                'product_group_id'      => $arrData['product_group_id'],
                'marketing_channel_id'  => $arrData['marketing_channel_id'],
                'branch_id'             => $arrData['branch_id'],
                'group_id'              => $arrData['group_id'],
                'contact_coin'          => $arrData['contact_coin'],
                'contact_id'            => $arrData['contact_id'],
                'created'               => date('Y-m-d H:i:s'),
                'options'               => serialize($item_options),
            );
            $this->tableGateway->insert($data);

            // Cập nhật số điện thoại marketing cho marketer.
            $param['data']["marketer_id"] = $arrData['marketer_id'];
            $param['data']["date"]        = $dateFormat->formatToData($arrData['date']);
            $this->getServiceLocator()->get('Admin\Model\MarketingReportTable')->saveItem($param, array('task' => 'update-number-phone'));

            return $id;
        }

        // Import dữ liệu có sẵn
        if($options['task'] == 'import-insert-old') {
            $id = $gid->getId();

            $item_options['crated_by'] = $this->userInfo->getUserInfo('id');
            $item_options['type'] = 'import';

            $data = array(
                'id' => $id,
                'date'                  => $dateFormat->formatToData($arrData['date']),
                'name'                  => $arrData['name'],
                'phone'                 => $arrData['phone'],
                'note'                  => $arrData['note'],
                'content'               => $arrData['content'],
                'marketer_id'           => $arrData['marketer_id'],
                'sales_id'              => $arrData['sales_id'],
                'marketing_channel_id'  => $arrData['marketing_channel_id'],
                'branch_id'             => $arrData['branch_id'],
                'group_id'              => $arrData['group_id'],
                'contact_coin'          => $arrData['contact_coin'],
                'contact_id'            => $arrData['contact_id'],
                'created'               => date('Y-m-d H:i:s'),
                'options'               => serialize($item_options),
            );
            $this->tableGateway->insert($data);

            // Cập nhật số điện thoại marketing cho marketer.
            $param['data']["marketer_id"] = $arrData['marketer_id'];
            $param['data']["date"]        = $dateFormat->formatToData($arrData['date']);
            $this->getServiceLocator()->get('Admin\Model\MarketingReportTable')->saveItem($param, array('task' => 'update-number-phone'));

            return $id;
        }

        if($options['task'] == 'edit-item') {
            $id = $arrData['id'];
            $data = array(
                'name'                  => $arrData['name'],
                'phone'                 => $arrData['phone'],
                'product_id'            => $arrData['product_id'],
                'product_group_id'      => $arrData['product_group_id'],
                'note'                  => $arrData['note'],
                'content'               => $arrData['content'],
                'sex'                   => $arrData['sex'],
                'city_id'               => $arrData['city_id'],
                'district_id'           => $arrData['district_id'],
                'address'               => $arrData['address'],
                'job'                   => $arrData['job'],
            );
            if($arrData['contact_coin']){
                $data['contact_coin'] = $arrData['contact_coin'];
            }

            $this->tableGateway->update($data, array('id' => $id));
            return $id;
        }

        // cập nhật lại data khi có data trùng
        if($options['task'] == 'update-contact-coin') {
            $phone = $arrParam['phone'];
            $data	= array(
                'contact_coin'    => $arrParam['contact_coin'],
            );
            $result = $this->tableGateway->update($data, array('phone' => $phone));
            return $result;
        }

        // cập nhật lại chi phí cho từng data
        if($options['task'] == 'update-cost-ads') {
            $cost_ads    = $arrParam['cost_ads'];
            $marketer_id = $arrParam['marketer_id'];
            $date        = $arrParam['date'];

            $sql_update = "UPDATE ".TABLE_FORM_DATA." SET cost_ads = ".$cost_ads." WHERE marketer_id = '".$marketer_id."' AND date >= '".$date."' AND date <= '".$date." 23:59:59'";
            $this->tableGateway->getAdapter()->driver->getConnection()->execute($sql_update);
        }

        // cập nhật contact_id, sale_id cho những data share thành công.
        if($options['task'] == 'update-data-share') {
            $id = $arrParam['id'];
            $data	= array(
                'contact_id'  => $arrParam['contact_id'],
                'sales_id'    => $arrParam['sales_id'],
            );
            $result = $this->tableGateway->update($data, array('id' => $id));
            return $result;
        }
		
		if($options['task'] == 'add-history') {
		    $arrContact   = $arrParam['item'];
		    $id           = $arrContact['id'];
		    
		    $contract_product       = $arrParam['data']['contract_product'];
		    $contract_products['product']  = array();
		    for($i = 0; $i < count($contract_product['code']); $i++){
		        $contract_products['product'][$i]['product_id']            = $contract_product['product_id'][$i];
		        $contract_products['product'][$i]['numbers']               = $contract_product['numbers'][$i];
		    }
		    foreach ($contract_products['product'] as $key => $product) {
		        $user_action = 'success';
		        if(empty($product['product_id']) || empty($product['numbers'])){
		            unset($contract_products['product'][$key]);
		            $user_action = 'follow';
		        }
		    }
		    
		    // Thêm lịch sử chăm sóc
		    $data	= array(
		        'user_action'    => $user_action,
		        'call_status'    => 1,
		        'history_return' => !empty($arrParam['data']['history_return']) ? $dateFormat->formatToData($arrParam['data']['history_return']) : null,
		    );
		    
		    $this->tableGateway->update($data, array('contact_id' => $id));
		     
		    return $id;
		}
		
		if($options['task'] == 'update-history') {
		    $arrContact   = $arrParam['item'];
		    $id           = $arrContact['id'];
		
		    $contract_product       = $arrParam['data']['contract_product'];
		    $contract_products['product']  = array();
		    for($i = 0; $i < count($contract_product['code']); $i++){
		        $contract_products['product'][$i]['product_id']            = $contract_product['product_id'][$i];
		        $contract_products['product'][$i]['numbers']               = $contract_product['numbers'][$i];
		    }
		    foreach ($contract_products['product'] as $key => $product) {
		        $user_action = 'success';
		        if(empty($product['product_id']) || empty($product['numbers'])){
		            unset($contract_products['product'][$key]);
		            $user_action = 'follow';
		        }
		    }
		
		    // Thêm lịch sử chăm sóc
		    $data	= array(
		        'user_action'    => $user_action,
		    );
		
		    $this->tableGateway->update($data, array('contact_id' => $id));
		     
		    return $id;
		}

        // Cập nhật trạng chia data
        if ($options['task'] == 'update-cancel-share') {
            $arr_id = $arrData['cid'];
            $data = array(
                'cancel_share' => $arrData['cancel_share']
            );

            $where = new Where();
            $where -> in('id', $arr_id);
            if($arrData['cancel_share'] == 1){// Nếu những data đã được chia thì không được chuyển về trạng thái hủy không chia
                $where -> isNull('sales_id');
            }
            $result = $this -> tableGateway -> update($data, $where);
            return $result;
        }
	}

	public function shareData($arrParam = null, $options = null){
		$items 		= $arrParam['data']['items'];
		$users 		= $arrParam['data']['user_id'];

		$index_user = 0;
        foreach ($items as $i => $item){
            $data_item     = $this->getItem(array('id' => $item['id']));
            $sales_user    = $this->getServiceLocator()->get('Admin\Model\UserTable')->getItem(array('id' => $users[$index_user % count($users)]));
            $check_contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('phone' => $data_item['phone'], 'marketer_id' => $data_item['marketer_id'], 'user_id' => $sales_user['id']), array('task' => 'check-share-data'));
            if(empty($data_item['sales_id']) && $data_item['cancel_share'] != 1 && empty($check_contact)){
                $data_contact = array(
                    'name'             => $data_item['name'],
                    'phone'            => $data_item['phone'],
                    'sex'              => $data_item['sex'],
                    'city_id'          => $data_item['city_id'],
                    'district_id'      => $data_item['district_id'],
                    'address'          => $data_item['address'],
                    'note'             => $data_item['note'],
                    'content'          => $data_item['content'],
                    'job'              => $data_item['job'],
                    'marketer_id'      => $data_item['marketer_id'],
                    'product_id'       => $data_item['product_id'],
                    'product_group_id' => $data_item['product_group_id'],
                    'user_id'          => $sales_user['id'],
                    'sale_branch_id'   => $sales_user['sale_branch_id'],
                    'sale_group_id'    => $sales_user['sale_group_id'],
                );
                $contact_id = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem(array('data' => $data_contact), array('task' => 'add-data'));
                // Cập nhật data share thành công
                $this->saveItem(array('id' => $data_item['id'], 'contact_id' => $contact_id, 'sales_id' => $sales_user['id']), array('task' => 'update-data-share'));

                $index_user ++;
            }
        }
		return true;
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

    public function report($arrParam = null, $options = null){
        if($options['task'] == 'list-item') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter  	= $arrParam['data'];
                $date      	= new \ZendX\Functions\Date();

                $date_begin = $date->formatToSearch($ssFilter['date_begin']);
                $date_end 	= $date->formatToSearch($ssFilter['date_end']);

                if(!empty($date_begin) && !empty($date_end)) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo(TABLE_FORM_DATA .'.date', $date_begin)
                        ->AND
                        -> lessThanOrEqualTo(TABLE_FORM_DATA .'.date', $date_end . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($date_begin)) {
                    $select -> where -> greaterThanOrEqualTo(TABLE_FORM_DATA .'.date', $date_begin);
                } elseif (!empty($date_end)) {
                    $select->where->lessThanOrEqualTo(TABLE_FORM_DATA .'.date', $date_end . ' 23:59:59');
                }

                if(!empty($ssFilter['sale_branch_id'])) {
                    $select -> where -> equalTo(TABLE_FORM_DATA .'.branch_id', $ssFilter['sale_branch_id']);
                }

                if(!empty($ssFilter['marketer_id'])) {
                    $select -> where -> equalTo(TABLE_FORM_DATA .'.marketer_id', $ssFilter['marketer_id']);
                }
            })->toArray();
        }

        if($options['task'] == 'list-item-shared') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter  	= $arrParam['data'];
                $date      	= new \ZendX\Functions\Date();

                $date_begin = $date->formatToSearch($ssFilter['date_begin']);
                $date_end 	= $date->formatToSearch($ssFilter['date_end']);

                $select -> where -> isNotNull(TABLE_FORM_DATA .'.sales_id');

                if(!empty($date_begin) && !empty($date_end)) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo(TABLE_FORM_DATA .'.date', $date_begin)
                        ->AND
                        -> lessThanOrEqualTo(TABLE_FORM_DATA .'.date', $date_end . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($date_begin)) {
                    $select -> where -> greaterThanOrEqualTo(TABLE_FORM_DATA .'.date', $date_begin);
                } elseif (!empty($date_end)) {
                    $select->where->lessThanOrEqualTo(TABLE_FORM_DATA .'.date', $date_end . ' 23:59:59');
                }

                if(!empty($ssFilter['sale_branch_id'])) {
                    $select -> where -> equalTo(TABLE_FORM_DATA .'.branch_id', $ssFilter['sale_branch_id']);
                }

                if(!empty($ssFilter['sale_id'])) {
                    $select -> where -> equalTo(TABLE_FORM_DATA .'.sales_id', $ssFilter['sale_id']);
                }

                if(!empty($ssFilter['marketer_id'])) {
                    $select -> where -> equalTo(TABLE_FORM_DATA .'.marketer_id', $ssFilter['marketer_id']);
                }
            })->toArray();
        }

        if($options['task'] == 'sum-cost-ads') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
                $columns = array('contact_id', 'sum_cost_ads' => new Expression('SUM('.TABLE_FORM_DATA .'.cost_ads)'));
                $select -> columns($columns);
                $select -> group(TABLE_FORM_DATA .'.contact_id');
                $select -> where -> isNotNull(TABLE_FORM_DATA.' .cost_ads');
                $select -> where -> isNotNull(TABLE_FORM_DATA.' .contact_id');
            });
        }

        return $result;
    }
}