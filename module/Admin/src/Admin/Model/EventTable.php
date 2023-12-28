<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

class EventTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter  = $arrParam['ssFilter'];
                $date      = new \ZendX\Functions\Date();
                
                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
                $select->where->equalTo('type', $options['type']);
                
                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select->where->equalTo('status', $ssFilter['filter_status']);
                }
                
                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select   ->where -> like('name', '%'. $ssFilter['filter_keyword'] . '%');
    			}
    			
    			if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> NEST
                    			     -> greaterThanOrEqualTo('public_date', $date->formatToData($ssFilter['filter_date_begin']))
                    			     -> AND
                    			     -> lessThanOrEqualTo('public_date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                    			     -> UNNEST;
    			} elseif (!empty($ssFilter['filter_date_begin'])) {
    			    $select -> where -> greaterThanOrEqualTo('public_date', $date->formatToData($ssFilter['filter_date_begin']));
    			} elseif (!empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> lessThanOrEqualTo('public_date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
    			}
    			
    			if(!empty($ssFilter['filter_sale_branch'])) {
    			    $select->where->equalTo('sale_branch_id', $ssFilter['filter_sale_branch']);
    			}
            })->current();
	    }
	    
	    return $result->count;
	}
	
	public function listItem($arrParam = null, $options = null){
		if($options['task'] == 'list-item') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $paginator = $arrParam['paginator'];
                $ssFilter  = $arrParam['ssFilter'];
                $date      = new \ZendX\Functions\Date();
                
                $select -> where -> equalTo('type', $options['type']);
                
                if(!isset($options['paginator']) || $options['paginator'] == true) {
                    $select -> limit($paginator['itemCountPerPage'])
                            -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }
    			
    			if(!empty($ssFilter['order_by']) && !empty($ssFilter['order'])) {
    			    $select -> order(array($ssFilter['order_by'] .' '. strtoupper($ssFilter['order'])));
    			}
    			
    			if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
    			    $select->where->equalTo('status', $ssFilter['filter_status']);
    			}
    			
			    if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select   ->where -> like('name', '%'. $ssFilter['filter_keyword'] . '%');
    			}
    			
    			if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> NEST
                    			     -> greaterThanOrEqualTo('public_date', $date->formatToData($ssFilter['filter_date_begin']))
                    			     -> AND
                    			     -> lessThanOrEqualTo('public_date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                    			     -> UNNEST;
    			} elseif (!empty($ssFilter['filter_date_begin'])) {
    			    $select -> where -> greaterThanOrEqualTo('public_date', $date->formatToData($ssFilter['filter_date_begin']));
    			} elseif (!empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> lessThanOrEqualTo('public_date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
    			}
    			
    			if(!empty($ssFilter['filter_sale_branch'])) {
    			    $select->where->equalTo('sale_branch_id', $ssFilter['filter_sale_branch']);
    			}
    		});
		}
		
		if($options['task'] == 'list-all') {
			$items	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
    			if(!empty($arrParam['type'])) {
        			$select -> where -> equalTo(TABLE_EVENT .'.type', $arrParam['type']);
    			}
    			
    			if(isset($arrParam['status']) && $arrParam['status'] != '') {
    			    $select -> where -> equalTo(TABLE_EVENT .'.status', $arrParam['status']);
    			}
    			
    			if(!empty($arrParam['public']) && $arrParam['public'] == true) {
    			    $select -> where -> greaterThanOrEqualTo(TABLE_EVENT .'.public_date', date('Y-m-d') . ' 00:00:00');
    			}
    			
    			if(!empty($arrParam['sale_branch_id'])) {
    			    $select->where->equalTo(TABLE_EVENT .'.sale_branch_id', $arrParam['sale_branch_id']);
    			}
    		});
			
		    $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));
		}
		
	    if($options['task'] == 'cache') {
	        $cache = $this->getServiceLocator()->get('cache');
	        $cache_key = 'AdminEvent' . $arrParam['type'];
	        $result = $cache->getItem($cache_key);
	         
	        if (empty($result)) {
	            $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	                $select->order(array('public_date' => 'DESC', 'name' => 'ASC'));
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
			    $select -> where -> equalTo('id', $arrParam['id']);
    		})->current();
		}
	
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];
	    
	    $image    = new \ZendX\Functions\Thumbnail($arrData['image']);
	    $date     = new \ZendX\Functions\Date();
	    $number   = new \ZendX\Functions\Number();
	    $filter   = new \ZendX\Filter\Purifier();
	    $gid      = new \ZendX\Functions\Gid();
	    
		if($options['task'] == 'add-item') {
			$id = $gid->getId();
			$data	= array(
				'id'                => $id,
				'name'              => $arrData['name'],
				'type'              => $arrData['type'],
				'public_date'       => $arrData['public_date'] ? $date->formatToData($arrData['public_date']) : null,
				'sale_branch_id'    => $arrData['sale_branch_id'],
				'teacher_ids'       => $arrData['teacher_ids'] ? serialize($arrData['teacher_ids']) : null,
				'coach_ids'         => $arrData['coach_ids'] ? serialize($arrData['coach_ids']) : null,
				'created'           => date('Y-m-d H:i:s'),
				'created_by'        => $this->userInfo->getUserInfo('id'),
			);
			
			$this->tableGateway->insert($data);
			return $id;
		}
		
		if($options['task'] == 'edit-item') {
		    $id = $arrData['id'];
			$data	= array(
				'name'              => $arrData['name'],
				'public_date'       => $arrData['public_date'] ? $date->formatToData($arrData['public_date']) : null,
				'sale_branch_id'    => $arrData['sale_branch_id'],
				'teacher_ids'       => $arrData['teacher_ids'] ? serialize($arrData['teacher_ids']) : null,
				'coach_ids'        	=> $arrData['coach_ids'] ? serialize($arrData['coach_ids']) : null,
			);
			
			$this->tableGateway->update($data, array('id' => $id));
			return $id;
		}
		
		if($options['task'] == 'update') {
		    $id = $arrData['id'];
			$data	= array();
			if(!empty($arrData['contact_total'])) {
			    $data['contact_total'] = $arrData['contact_total'];
			}
			
			$this->tableGateway->update($data, array('id' => $id));
			return $id;
		}
		
		if($options['task'] == 'update-total') {
		    $id = $arrData['id'];
			$data	= array(
				'contact_total'	=> new Expression('(`contact_total` + ?)', array(1)),
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