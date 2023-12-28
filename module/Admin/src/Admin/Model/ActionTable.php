<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;

class ActionTable extends DefaultTable {
	public function searchItem($arrParam=null,$action='count',$options=null){
		if($options['task'] == 'list-item') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $action,$options){
	            $ssFilter  = $arrParam['ssFilter'];
	            if ($action=='list'){
					$paginator = $arrParam['paginator'];
					if (!isset($arrParam['paginator']) || $arrParam['paginator']==true){
						$select -> limit($paginator['itemCountPerPage']?:50)
					            -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
					}
					if(!empty($ssFilter['order_by']) && !empty($ssFilter['order'])) {
					    $select -> order(array($ssFilter['order_by'] .' '. strtoupper($ssFilter['order'])));
					}
			    }else{
			    	$select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
			    }	

			    if(isset($ssFilter['filter_company']) && $ssFilter['filter_company'] != '') {
				    $select -> where -> equalTo('company_id', $ssFilter['filter_company']);
				}			
				
				if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
				    $select -> where -> equalTo('status', $ssFilter['filter_status']);
				}
				
				if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
			        $select -> where -> like('name', '%'. $ssFilter['filter_keyword'] . '%');
				}
				if(!empty($ssFilter['filter_location_city'])) {
    			    $select -> where -> equalTo('location_city_id', $ssFilter['filter_location_city']);
    			}
    			 
    			if(!empty($ssFilter['filter_location_district'])) {
    			    $select -> where -> equalTo('location_district_id', $ssFilter['filter_location_district']);
				}
			});
		}
		return $result;
	}
    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->searchItem($arrParam,'count',$options)->current();
	    }
	    

	    return $result->count;
	}
	
	public function listItem($arrParam = null, $options = null){
		if($options['task'] == 'list-item') {
			$result	= $this->searchItem($arrParam,'list',$options);
		}
		
	    if($options['task'] == 'cache') {
	        $cache = $this->getServiceLocator()->get('cache');
	        $cache_key = 'AdminBranch';
	        $result = $cache->getItem($cache_key);
	         
	        if (empty($result)) {
	            $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	                $select -> order(array('ordering' => 'ASC', 'name' => 'ASC'));
	                if(!empty($arrParam['parent'])) {
	                    $select -> where -> equalTo('company_id', $arrParam['parent']);
	                }
	            });
                $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));
                 
                $cache->setItem($cache_key, $result);
	        }
	    }
	    if($options['task'] == 'reset-cache') {
	        $cache = $this->getServiceLocator()->get('cache');
	        $cache_key = 'AdminBranch';
	        $result = $cache->getItem($cache_key);
			$items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
				$select -> order(array('ordering' => 'ASC', 'name' => 'ASC'));
				if(!empty($arrParam['parent'])) {
					$select -> where -> equalTo('company_id', $arrParam['parent']);
				}
			});
			$result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));
			 
			$cache->setItem($cache_key, $result);
	    }

	    if($options['task'] == 'list-by-company') {
	    	if(!$arrParam['company_id']) $arrParam['company_id'] = '';
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){ 
    			$select -> where -> equalTo('company_id', $arrParam['company_id']);
			});
		}
		if($options['task'] == 'by-id') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){ 
				$select -> where -> in('id', $arrParam['id']);
			});
		}
		if($options['task'] == 'by-item') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
				if(!empty($arrParam['company_id'])){
                    $select -> where -> equalTo('company_id', $arrParam['company_id']);
                }
			});
		}
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
	
		if($options == null) {
			$result	= $this->defaultGet($arrParam, array('by' => 'id'));
		}
        if ($options['task'] == 'by-view-name') {
            $result = $this->tableGateway->select(function (Select $select) use ($arrParam, $options) {
                $select->where->equalTo('view_name', $arrParam['view_name']);
            })->toArray();
        }

		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];
	    
	    $filter   = new \ZendX\Filter\Purifier();
	    $number   = new \ZendX\Functions\Number();
	    $gid      = new \ZendX\Functions\Gid();
	    
		if($options['task'] == 'add-item') {
			$id = $gid->getId();
			$data	= array(
				'id'                  => $id,
				'view_name'           => $arrData['view_name'],
				'count'               => $arrData['count']?:1,
				'created'             => date('Y-m-d H:i:s'),
			);
			
			$this->tableGateway->insert($data);
			return $id;
		}
		if($options['task'] == 'edit-item') {
		    $id = $arrData['id'];
			$data	= array(
				'count'          => $arrData['count'],
			);
			$this->tableGateway->update($data, array('id' => $id));
			return $id;
		}
	}
	
	public function deleteItem($arrParam = null, $options = null){
	    if($options['task'] == 'delete-item') {
			$this->getServiceLocator()->get('Admin\Model\BranchTable')->listItem(null, array('task' => 'reset-cache'));
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