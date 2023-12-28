<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class EvaluateTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item-sales') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $date = new \ZendX\Functions\Date();
                $ssFilter  = $arrParam['ssFilter'];

                $select -> join(TABLE_CONTRACT, TABLE_CONTRACT .'.id = '. TABLE_EVALUATE .'.contract_id', array(
                    'contract_code' => 'code',
                    'contract_price' => 'price_total',
                ), 'inner');

                if(isset($ssFilter['filter_type']) && $ssFilter['filter_type'] != '') {
                    $select->where->equalTo('type', $ssFilter['filter_type']);
                }

                if(isset($ssFilter['filter_user_id']) && $ssFilter['filter_user_id'] != '') {
                    $select->where->equalTo(TABLE_EVALUATE.'.user_id', $ssFilter['filter_user_id']);
                }

                if(isset($ssFilter['filter_level_id']) && $ssFilter['filter_level_id'] != '') {
                    $select->where->equalTo('level', $ssFilter['filter_level_id']);
                }

                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo(TABLE_EVALUATE.'.created', $date->formatToData($ssFilter['filter_date_begin']))
                        ->AND
                        -> lessThanOrEqualTo(TABLE_EVALUATE.'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select -> where -> greaterThanOrEqualTo(TABLE_EVALUATE.'.created', $date->formatToData($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select -> where -> lessThanOrEqualTo(TABLE_EVALUATE.'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                }
            })->count();
	    }

	    if($options['task'] == 'list-item-technical') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $date = new \ZendX\Functions\Date();
                $ssFilter  = $arrParam['ssFilter'];

                $select -> join(TABLE_CONTRACT, TABLE_CONTRACT .'.id = '. TABLE_EVALUATE .'.contract_id', array(
                    'contract_code' => 'code',
                    'contract_price' => 'price_total',
                ), 'right');

                $select->where->equalTo(TABLE_CONTRACT.'.status_technical', 1);

                if(isset($ssFilter['filter_type']) && $ssFilter['filter_type'] != '') {
                    $select -> where -> NEST
                        ->equalTo(TABLE_EVALUATE .'.type', $ssFilter['filter_type'])
                        ->OR
                        -> isNull(TABLE_EVALUATE .'.type')
                        -> UNNEST;
                }

                if(isset($ssFilter['filter_technical_id']) && $ssFilter['filter_technical_id'] != '') {
                    $select->where->like(TABLE_CONTRACT.'.options', '%'.$ssFilter['filter_technical_id'].'%');
                }

                if(isset($ssFilter['filter_level_id']) && $ssFilter['filter_level_id'] != '') {
                    $select->where->equalTo('level', $ssFilter['filter_level_id']);
                }

                if($ssFilter['filter_date_type'] == 1){
                    if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                        $select -> where -> NEST
                            -> greaterThanOrEqualTo(TABLE_EVALUATE.'.created', $date->formatToData($ssFilter['filter_date_begin']))
                            ->AND
                            -> lessThanOrEqualTo(TABLE_EVALUATE.'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                            -> UNNEST;
                    } elseif (!empty($ssFilter['filter_date_begin'])) {
                        $select -> where -> greaterThanOrEqualTo(TABLE_EVALUATE.'.created', $date->formatToData($ssFilter['filter_date_begin']));
                    } elseif (!empty($ssFilter['filter_date_end'])) {
                        $select -> where -> lessThanOrEqualTo(TABLE_EVALUATE.'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                    }
                }
                elseif ($ssFilter['filter_date_type'] == 2){
                    if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                        $select -> where -> NEST
                            -> greaterThanOrEqualTo(TABLE_CONTRACT.'.production_date_send', $date->formatToData($ssFilter['filter_date_begin']))
                            ->AND
                            -> lessThanOrEqualTo(TABLE_CONTRACT.'.production_date_send', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                            -> UNNEST;
                    } elseif (!empty($ssFilter['filter_date_begin'])) {
                        $select -> where -> greaterThanOrEqualTo(TABLE_CONTRACT.'.production_date_send', $date->formatToData($ssFilter['filter_date_begin']));
                    } elseif (!empty($ssFilter['filter_date_end'])) {
                        $select -> where -> lessThanOrEqualTo(TABLE_CONTRACT.'.production_date_send', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                    }
                }
            })->count();
	    }

	    if($options['task'] == 'list-item-tailors') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $date = new \ZendX\Functions\Date();
                $ssFilter  = $arrParam['ssFilter'];

                $select -> join(TABLE_CONTRACT, TABLE_CONTRACT .'.id = '. TABLE_EVALUATE .'.contract_id', array(
                    'contract_code' => 'code',
                    'contract_price' => 'price_total',
                ), 'right');

                $select->where->equalTo(TABLE_CONTRACT.'.status_tailors', 1);

                if(isset($ssFilter['filter_type']) && $ssFilter['filter_type'] != '') {
                    $select -> where -> NEST
                        ->equalTo(TABLE_EVALUATE .'.type', $ssFilter['filter_type'])
                        ->OR
                        -> isNull(TABLE_EVALUATE .'.type')
                        -> UNNEST;
                }

                if(isset($ssFilter['filter_tailors_id']) && $ssFilter['filter_tailors_id'] != '') {
                    $select->where->like(TABLE_CONTRACT.'.options', '%'.$ssFilter['filter_tailors_id'].'%');
                }

                if(isset($ssFilter['filter_level_id']) && $ssFilter['filter_level_id'] != '') {
                    $select->where->equalTo('level', $ssFilter['filter_level_id']);
                }

                if($ssFilter['filter_date_type'] == 1){
                    if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                        $select -> where -> NEST
                            -> greaterThanOrEqualTo(TABLE_EVALUATE.'.created', $date->formatToData($ssFilter['filter_date_begin']))
                            ->AND
                            -> lessThanOrEqualTo(TABLE_EVALUATE.'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                            -> UNNEST;
                    } elseif (!empty($ssFilter['filter_date_begin'])) {
                        $select -> where -> greaterThanOrEqualTo(TABLE_EVALUATE.'.created', $date->formatToData($ssFilter['filter_date_begin']));
                    } elseif (!empty($ssFilter['filter_date_end'])) {
                        $select -> where -> lessThanOrEqualTo(TABLE_EVALUATE.'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                    }
                }
                elseif ($ssFilter['filter_date_type'] == 2){
                    if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                        $select -> where -> NEST
                            -> greaterThanOrEqualTo(TABLE_CONTRACT.'.production_date_send', $date->formatToData($ssFilter['filter_date_begin']))
                            ->AND
                            -> lessThanOrEqualTo(TABLE_CONTRACT.'.production_date_send', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                            -> UNNEST;
                    } elseif (!empty($ssFilter['filter_date_begin'])) {
                        $select -> where -> greaterThanOrEqualTo(TABLE_CONTRACT.'.production_date_send', $date->formatToData($ssFilter['filter_date_begin']));
                    } elseif (!empty($ssFilter['filter_date_end'])) {
                        $select -> where -> lessThanOrEqualTo(TABLE_CONTRACT.'.production_date_send', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                    }
                }
            })->count();
	    }
	    
	    return $result;
	}
	
	public function listItem($arrParam = null, $options = null){

		if($options['task'] == 'list-item-sales') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $date = new \ZendX\Functions\Date();
                $paginator = $arrParam['paginator'];
                $ssFilter  = $arrParam['ssFilter'];

                $select -> join(TABLE_CONTRACT, TABLE_CONTRACT .'.id = '. TABLE_EVALUATE .'.contract_id', array(
                    'contract_code' => 'code',
                    'contract_price' => 'price_total',
                ), 'inner');
                
    			$select -> limit($paginator['itemCountPerPage'])
    			        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
    			
    			if(isset($ssFilter['filter_type']) && $ssFilter['filter_type'] != '') {
    			    $select->where->equalTo('type', $ssFilter['filter_type']);
    			}

    			if(isset($ssFilter['filter_user_id']) && $ssFilter['filter_user_id'] != '') {
    			    $select->where->equalTo(TABLE_EVALUATE.'.user_id', $ssFilter['filter_user_id']);
    			}

    			if(isset($ssFilter['filter_level_id']) && $ssFilter['filter_level_id'] != '') {
    			    $select->where->equalTo('level', $ssFilter['filter_level_id']);
    			}

                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo(TABLE_EVALUATE.'.created', $date->formatToData($ssFilter['filter_date_begin']))
                        ->AND
                        -> lessThanOrEqualTo(TABLE_EVALUATE.'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select -> where -> greaterThanOrEqualTo(TABLE_EVALUATE.'.created', $date->formatToData($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select -> where -> lessThanOrEqualTo(TABLE_EVALUATE.'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                }
    		});
		}

		if($options['task'] == 'list-item-technical') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $date = new \ZendX\Functions\Date();
                $paginator = $arrParam['paginator'];
                $ssFilter  = $arrParam['ssFilter'];

                $select -> join(TABLE_CONTRACT, TABLE_CONTRACT .'.id = '. TABLE_EVALUATE .'.contract_id', array(
                    'contract_code' => 'code',
                    'contract_price' => 'price_total',
                    'contract_options' => 'options',
                    'contract_production_date_send' => 'production_date_send',
                ), 'right');

                $select->where->equalTo(TABLE_CONTRACT.'.status_technical', 1);

    			$select -> limit($paginator['itemCountPerPage'])
    			        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);

    			if(isset($ssFilter['filter_type']) && $ssFilter['filter_type'] != '') {
                    $select -> where -> NEST
                        ->equalTo(TABLE_EVALUATE .'.type', $ssFilter['filter_type'])
                        ->OR
                        -> isNull(TABLE_EVALUATE .'.type')
                        -> UNNEST;
    			}

    			if(isset($ssFilter['filter_technical_id']) && $ssFilter['filter_technical_id'] != '') {
    			    $select->where->like(TABLE_CONTRACT.'.options', '%'.$ssFilter['filter_technical_id'].'%');
    			}

    			if(isset($ssFilter['filter_level_id']) && $ssFilter['filter_level_id'] != '') {
    			    $select->where->equalTo('level', $ssFilter['filter_level_id']);
    			}

    			if($ssFilter['filter_date_type'] == 1){
                    if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                        $select -> where -> NEST
                            -> greaterThanOrEqualTo(TABLE_EVALUATE.'.created', $date->formatToData($ssFilter['filter_date_begin']))
                            ->AND
                            -> lessThanOrEqualTo(TABLE_EVALUATE.'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                            -> UNNEST;
                    } elseif (!empty($ssFilter['filter_date_begin'])) {
                        $select -> where -> greaterThanOrEqualTo(TABLE_EVALUATE.'.created', $date->formatToData($ssFilter['filter_date_begin']));
                    } elseif (!empty($ssFilter['filter_date_end'])) {
                        $select -> where -> lessThanOrEqualTo(TABLE_EVALUATE.'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                    }
    			}
    			elseif ($ssFilter['filter_date_type'] == 2){
                    if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                        $select -> where -> NEST
                            -> greaterThanOrEqualTo(TABLE_CONTRACT.'.production_date_send', $date->formatToData($ssFilter['filter_date_begin']))
                            ->AND
                            -> lessThanOrEqualTo(TABLE_CONTRACT.'.production_date_send', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                            -> UNNEST;
                    } elseif (!empty($ssFilter['filter_date_begin'])) {
                        $select -> where -> greaterThanOrEqualTo(TABLE_CONTRACT.'.production_date_send', $date->formatToData($ssFilter['filter_date_begin']));
                    } elseif (!empty($ssFilter['filter_date_end'])) {
                        $select -> where -> lessThanOrEqualTo(TABLE_CONTRACT.'.production_date_send', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                    }
                }
    		});
		}

		if($options['task'] == 'list-item-tailors') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $date = new \ZendX\Functions\Date();
                $paginator = $arrParam['paginator'];
                $ssFilter  = $arrParam['ssFilter'];

                $select -> join(TABLE_CONTRACT, TABLE_CONTRACT .'.id = '. TABLE_EVALUATE .'.contract_id', array(
                    'contract_code' => 'code',
                    'contract_price' => 'price_total',
                    'contract_options' => 'options',
                    'contract_production_date_send' => 'production_date_send',
                ), 'right');

    			$select -> limit($paginator['itemCountPerPage'])
    			        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);



                $select->where->equalTo(TABLE_CONTRACT.'.status_tailors', 1);

                if(isset($ssFilter['filter_type']) && $ssFilter['filter_type'] != '') {
                    $select -> where -> NEST
                        ->equalTo(TABLE_EVALUATE .'.type', $ssFilter['filter_type'])
                        ->OR
                        -> isNull(TABLE_EVALUATE .'.type')
                        -> UNNEST;
                }

    			if(isset($ssFilter['filter_tailors_id']) && $ssFilter['filter_tailors_id'] != '') {
    			    $select->where->like(TABLE_CONTRACT.'.options', '%'.$ssFilter['filter_tailors_id'].'%');
    			}

    			if(isset($ssFilter['filter_level_id']) && $ssFilter['filter_level_id'] != '') {
    			    $select->where->equalTo('level', $ssFilter['filter_level_id']);
    			}

                if($ssFilter['filter_date_type'] == 1){
                    if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                        $select -> where -> NEST
                            -> greaterThanOrEqualTo(TABLE_EVALUATE.'.created', $date->formatToData($ssFilter['filter_date_begin']))
                            ->AND
                            -> lessThanOrEqualTo(TABLE_EVALUATE.'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                            -> UNNEST;
                    } elseif (!empty($ssFilter['filter_date_begin'])) {
                        $select -> where -> greaterThanOrEqualTo(TABLE_EVALUATE.'.created', $date->formatToData($ssFilter['filter_date_begin']));
                    } elseif (!empty($ssFilter['filter_date_end'])) {
                        $select -> where -> lessThanOrEqualTo(TABLE_EVALUATE.'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                    }
                }
                elseif ($ssFilter['filter_date_type'] == 2){
                    if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                        $select -> where -> NEST
                            -> greaterThanOrEqualTo(TABLE_CONTRACT.'.production_date_send', $date->formatToData($ssFilter['filter_date_begin']))
                            ->AND
                            -> lessThanOrEqualTo(TABLE_CONTRACT.'.production_date_send', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                            -> UNNEST;
                    } elseif (!empty($ssFilter['filter_date_begin'])) {
                        $select -> where -> greaterThanOrEqualTo(TABLE_CONTRACT.'.production_date_send', $date->formatToData($ssFilter['filter_date_begin']));
                    } elseif (!empty($ssFilter['filter_date_end'])) {
                        $select -> where -> lessThanOrEqualTo(TABLE_CONTRACT.'.production_date_send', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                    }
                }
    		});
		}
		
	    if($options['task'] == 'cache') {
	        $cache = $this->getServiceLocator()->get('cache');
	        $cache_key = 'AdminSaleTarget';
	        $result = $cache->getItem($cache_key);
	         
	        if (empty($result)) {
	            $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	                $select->order(array('ordering' => 'ASC', 'name' => 'ASC'));
	            });
                $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));
                 
                $cache->setItem($cache_key, $result);
	        }
	    }
	    
		return $result;
	}

	public function report($arrParam = null, $options = null){
        // Lấy danh sách theo kiểu taget
		if($options['task'] == 'list-item-type') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $date = new \ZendX\Functions\Date();
                $ssFilter  = $arrParam['ssFilter'];

                $select -> join(TABLE_USER, TABLE_USER .'.id = '. TABLE_TARGET .'.user_id',
                    array('user_name' => 'name', 'user_sale_branch_id' => 'sale_branch_id', 'user_sale_group_id' => 'sale_group_id',
                    ), 'inner');

                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo('date', $date->formatToSearch($ssFilter['filter_date_begin']))
                        ->AND
                        -> lessThanOrEqualTo('date', $date->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select -> where -> greaterThanOrEqualTo('date', $date->formatToSearch($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select -> where -> lessThanOrEqualTo('date', $date->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59');
                }

                if(!empty($ssFilter['filter_sale_branch'])) {
                    $select->where->equalTo(TABLE_USER.'.sale_branch_id', $ssFilter['filter_sale_branch']);
                }

                if(!empty($ssFilter['filter_sale_group'])) {
                    $select->where->equalTo(TABLE_USER.'.sale_group_id', $ssFilter['filter_sale_group']);
                }

                if(!empty($ssFilter['filter_user_id'])) {
                    $select->where->equalTo(TABLE_TARGET.'.user_id', $ssFilter['filter_user_id']);
                }

                $select->where->equalTo('type', $ssFilter['filter_type']);
    		});
		}

		return $result->toArray();
	}
	
	public function getItem($arrParam = null, $options = null){
	
		if($options == null) {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
			    $select -> where -> equalTo('id', $arrParam['id']);
    		})->current();
		}
		
		if($options['task'] == 'month-year') {
		    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
		        $select -> where -> equalTo('month', $arrParam['month'])
		                         -> equalTo('year', $arrParam['year']);
		    })->current();
		}

		// check marketer đã được tạo trong ngày chưa
		if($options['task'] == 'user-date') {
		    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
		        if(!empty($arrParam['date'])){
                    $select -> where -> equalTo('date', $arrParam['date']);
                }
		        if(!empty($arrParam['user_id'])){
                    $select -> where -> equalTo('user_id', $arrParam['user_id']);
                }
		        if(!empty($arrParam['type'])){
                    $select -> where -> equalTo('type', $arrParam['type']);
                }
		    })->current();
		}
		
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrItem  = $arrParam['item'];
	    $arrRoute = $arrParam['route'];
	    
	    $date     = new \ZendX\Functions\Date();
	    $number   = new \ZendX\Functions\Number();
	    $filter   = new \ZendX\Filter\Purifier();
	    $gid      = new \ZendX\Functions\Gid();

	    if($options['task'] == 'add-item-sale') {
	        $id = $gid->getId();
	        $data	= array(
	            'id'            => $id,
	            'contract_id'   => $arrItem['id'],

	            'user_id'       => $arrItem['created_by'],
	            'revenue'       => $arrItem['price_total'],

	            'level'         => $arrData['sale_level'],
	            'note'          => $arrData['sale_note'],
	            'type'          => 'sale',
	            'created'       => date('Y-m-d H:i:s'),
	            'created_by'    => $this->userInfo->getUserInfo('id'),
	        );
	        $this->tableGateway->insert($data);
	        return $id;
	    }

	    if($options['task'] == 'add-item-technical') {
	        $id = $gid->getId();
	        $data	= array(
	            'id'            => $id,
	            'contract_id'   => $arrItem['id'],

	            'user_id'       => $arrItem['created_by'],
	            'revenue'       => $arrItem['price_total'],

	            'products'      => serialize($arrData['technical_product']),
	            'level'         => $arrData['technical_level'],
	            'note'          => $arrData['technical_note'],
	            'type'          => 'technical',
	            'created'       => date('Y-m-d H:i:s'),
	            'created_by'    => $this->userInfo->getUserInfo('id'),
	        );
	        $this->tableGateway->insert($data);
	        return $id;
	    }

	    if($options['task'] == 'add-item-tailors') {
	        $id = $gid->getId();
	        $data	= array(
	            'id'            => $id,
	            'contract_id'   => $arrItem['id'],
	            'user_id'       => $arrItem['created_by'],
	            'revenue'       => $arrItem['price_total'],

                'products'      => serialize($arrData['tailors_product']),
	            'level'         => $arrData['tailors_level'],
	            'note'          => $arrData['tailors_note'],
	            'type'          => 'tailors',
	            'created'       => date('Y-m-d H:i:s'),
	            'created_by'    => $this->userInfo->getUserInfo('id'),
	        );
	        $this->tableGateway->insert($data);
	        return $id;
	    }

	    if($options['task'] == 'edit-item') {
	        $id = $arrData['id'];
            $params = !empty($arrItem['params']) ? unserialize($arrItem['params']) : array();

            foreach ($arrData['params'] as $key => $value) {
                $params[$key] = $value;
            }

	        $data	= array(
	            'params'       => !empty($params) ? serialize($params) : '',
	        );
	        	
	        $this->tableGateway->update($data, array('id' => $id));
	        return $id;
	    }

	    if($options['task'] == 'save-ajax') {
	        $id = $arrData['id'];
            $params = !empty($arrItem['params']) ? unserialize($arrItem['params']) : array();

            foreach ($arrData['params'] as $key => $value) {
                $params[$key] = $value;
            }

	        $data	= array(
	            'params'       => !empty($params) ? serialize($params) : '',
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