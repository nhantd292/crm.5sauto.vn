<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class TargetTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter  = $arrParam['ssFilter']; 
                 
                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select->where->equalTo('status', $ssFilter['filter_status']);
                }
                
                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select->where->NEST
                			      ->like('name', '%'. $ssFilter['filter_keyword'] . '%')
                			      ->or
                			      ->equalTo('id', $ssFilter['filter_keyword'])
                			      ->UNNEST;
    			}
            })->count();
	    }
	    if($options['task'] == 'list-item-type') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $date = new \ZendX\Functions\Date();
                $ssFilter  = $arrParam['ssFilter'];

                $select -> join(TABLE_USER, TABLE_USER .'.ID = '. TABLE_TARGET .'.user_id',
                    array('user_name' => 'name', 'user_sale_branch_id' => 'sale_branch_id', 'user_sale_group_id' => 'sale_group_id',
                    ), 'inner');

                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select->where->equalTo('status', $ssFilter['filter_status']);
                }

                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo('date', $date->formatToData($ssFilter['filter_date_begin']))
                        ->AND
                        -> lessThanOrEqualTo('date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select -> where -> greaterThanOrEqualTo('date', $date->formatToData($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select -> where -> lessThanOrEqualTo('date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                }

                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $select->where->like(TABLE_USER.'.name', '%'. $ssFilter['filter_keyword'] . '%');
                }

                if(isset($ssFilter['filter_sale_branch']) && $ssFilter['filter_keyword'] != '') {
                    $select->where->like(TABLE_USER.'.sale_branch_id', '%'. $ssFilter['filter_sale_branch'] . '%');
                }

                if(isset($ssFilter['filter_sale_group']) && $ssFilter['filter_keyword'] != '') {
                    $select->where->like(TABLE_USER.'.sale_group_id', '%'. $ssFilter['filter_sale_group'] . '%');
                }

                $select->where->equalTo('type', $ssFilter['filter_type']);
            })->count();
	    }
	    
	    return $result;
	}
	
	public function listItem($arrParam = null, $options = null){

		if($options['task'] == 'list-item') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $paginator = $arrParam['paginator'];
                $ssFilter  = $arrParam['ssFilter'];
                
    			$select -> limit($paginator['itemCountPerPage'])
    			        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
    			
    			$select -> order(array('year' => 'DESC', 'month' => 'DESC'));
    			
    			if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
    			    $select->where->equalTo('status', $ssFilter['filter_status']);
    			}
    			
    			if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select->where->NEST
                			      ->like('name', '%'. $ssFilter['filter_keyword'] . '%')
                			      ->or
                			      ->equalTo('id', $ssFilter['filter_keyword'])
                			      ->UNNEST;
    			}
    		});
		}

        // Lấy danh sách theo kiểu taget
		if($options['task'] == 'list-item-type') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $date = new \ZendX\Functions\Date();
                $paginator = $arrParam['paginator'];
                $ssFilter  = $arrParam['ssFilter'];

                $select -> join(TABLE_USER, TABLE_USER .'.ID = '. TABLE_TARGET .'.user_id',
                    array('user_name' => 'name', 'user_sale_branch_id' => 'sale_branch_id', 'user_sale_group_id' => 'sale_group_id',
                    ), 'inner');

                if(!isset($options['paginator']) || $options['paginator'] == true) {
                    $select -> limit($paginator['itemCountPerPage'])
                        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }

    			$select -> order(array('date' => 'ASC', 'user_name' => 'ASC' ));

                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo('date', $date->formatToData($ssFilter['filter_date_begin']))
                        ->AND
                        -> lessThanOrEqualTo('date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select -> where -> greaterThanOrEqualTo('date', $date->formatToData($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select -> where -> lessThanOrEqualTo('date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                }

                if(!empty($ssFilter['filter_sale_branch'])) {
                    $select->where->equalTo(TABLE_USER.'.sale_branch_id', $ssFilter['filter_sale_branch']);
                }

                if(!empty($ssFilter['filter_sale_group'])) {
                    $select->where->equalTo(TABLE_USER.'.sale_group_id', $ssFilter['filter_sale_group']);
                }

    			if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select->where->like(TABLE_USER.'.name', '%'. $ssFilter['filter_keyword'] . '%');
    			}

                if(!empty($ssFilter['filter_user_id'])) {
                    $select->where->equalTo(TABLE_TARGET.'.user_id', $ssFilter['filter_user_id']);
                }

                $select->where->equalTo('type', $ssFilter['filter_type']);
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
	    
	    if($options['task'] == 'add-all') {
	        $id = $gid->getId();
	        
	        $data	= array(
	            'id'            => $id,
	            'date'          => $date->formatToData($arrData['date']),
	            'month'         => $arrData['month'],
	            'user_id'       => $arrData['user_id'],
	            'year'          => $arrData['year'],
	            'type'          => $arrData['type'],
	            'created'       => date('Y-m-d H:i:s'),
	            'created_by'    => $this->userInfo->getUserInfo('id'),
	        );
	        	
	        $this->tableGateway->insert($data);
	        return $id;
	    }

	    if($options['task'] == 'add-item') {
	        $id = $gid->getId();

	        $data	= array(
	            'id'            => $id,
	            'date'          => $date->formatToData($arrData['date']),
	            'day'           => $arrData['day'],
	            'month'         => $arrData['month'],
	            'year'          => $arrData['year'],
	            'type'          => $arrData['type'],
	            'user_id'   => $arrData['user_id'],
	            'params'        => !empty($arrData['params']) ? serialize($arrData['params']): '',
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