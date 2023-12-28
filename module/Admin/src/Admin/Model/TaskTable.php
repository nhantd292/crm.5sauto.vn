<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

class TaskTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
            $date       = new \ZendX\Functions\Date();
            $ssFilter   = $arrParam['ssFilter'];
             
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
			
            if(!empty($ssFilter['filter_user'])) {
	           $select->where->equalTo('user_id', $ssFilter['filter_user']);
			}
			
            if(!empty($ssFilter['filter_task_stauts'])) {
	           $select->where->equalTo('task_stauts_id', $ssFilter['filter_task_stauts']);
			}
			
            if(!empty($ssFilter['filter_task_category'])) {
	           $select->where->equalTo('task_category_id', $ssFilter['filter_task_category']);
			}
			
	        if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
    			    $select->where->NEST
                    			  ->greaterThanOrEqualTo('created', $date->formatToData($ssFilter['filter_date_begin']))
                    			  ->and
                    			  ->lessThanOrEqualTo('created', $date->formatToData($ssFilter['filter_date_end']))
                    			  ->UNNEST;
    	   }
        })->count();
        
	    return $result;
	}
	
	public function listItem($arrParam = null, $options = null){
		if($options['task'] == 'list-item') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
			    $date     = new \ZendX\Functions\Date();
                $paginator	= $arrParam['paginator'];
                $ssFilter	= $arrParam['ssFilter'];
                
    			$select ->columns (array('*',
    							'date_begin' => new Expression("DATE_FORMAT(date_begin, '%d/%m/%Y')"),
                                'date_end' => new Expression("DATE_FORMAT(date_end, '%d/%m/%Y')"),
    						));
    			
    			if($ssFilter['filter_type_list'] == 'list') {
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
    		        $select->where->NEST
                			      ->like('name', '%'. $ssFilter['filter_keyword'] . '%')
                			      ->or
                			      ->equalTo('id', $ssFilter['filter_keyword'])
                			      ->UNNEST;
    			}
    			
    			if(!empty($ssFilter['filter_user'])) {
    			    $select->where->equalTo('user_id', $ssFilter['filter_user']);
    			}
    			
    			if(!empty($ssFilter['filter_task_stauts'])) {
    	           $select->where->equalTo('task_stauts_id', $ssFilter['filter_task_stauts']);
    			}
    			
    			if(!empty($ssFilter['filter_task_category'])) {
    			    $select->where->equalTo('task_category_id', $ssFilter['filter_task_category']);
    			}
    			
    			if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
    			    $select->where->NEST
                    			  ->greaterThanOrEqualTo('created', $date->formatToData($ssFilter['filter_date_begin']))
                    			  ->and
                    			  ->lessThanOrEqualTo('created', $date->formatToData($ssFilter['filter_date_end']))
                    			  ->UNNEST;
    			}
            });
		}
		
	    if($options['task'] == 'cache') {
	        $cache = $this->getServiceLocator()->get('cache');
	        $cache_key = 'AdminTask';
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
	
	public function getItem($arrParam = null, $options = null){
	
		if($options == null) {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
			    $select -> columns(array('*', 'date_begin' => new Expression("DATE_FORMAT(date_begin, '%d/%m/%Y')"), 'date_end' => new Expression("DATE_FORMAT(date_end, '%d/%m/%Y')")))
                        -> where -> equalTo('id', $arrParam['id']);
    		})->current();
		}
	
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];
	    
	    $image    = new \ZendX\Functions\Thumbnail($arrData['image']);
	    $date     = new \ZendX\Functions\Date();
	    $filter   = new \ZendX\Filter\Purifier();
	    $gid      = new \ZendX\Functions\Gid();
	    
		if($options['task'] == 'add-item') {
			$id = $gid->getId();
			$data	= array(
				'id'                => $id,
				'name'              => $arrData['name'],
				'task_category_id'  => $arrData['task_category_id'],
				'task_project_id'   => $arrData['task_project_id'],
				'main_hour'         => $arrData['main_hour'],
				'task_status_id'    => $arrData['task_status_id'],
				'date_begin'        => $arrData['date_begin'] ? $date->formatToData($arrData['date_begin']) : null,
				'date_end'          => $arrData['date_end'] ? $date->formatToData($arrData['date_end']) : null,
				'content'           => $arrData['content'],
				'user_id'           => $this->userInfo->getUserInfo('id'),
				'ordering'          => $arrData['ordering'],
				'status'            => $arrData['status'],
				'created'           => date('Y-m-d H:i:s'),
				'created_by'        => $this->userInfo->getUserInfo('id'),
				'modified'          => date('Y-m-d H:i:s'),
				'modified_by'       => $this->userInfo->getUserInfo('id'),
			);
			
			$this->tableGateway->insert($data);
			return $id;
		}
		if($options['task'] == 'edit-item') {
		    $id = $arrData['id'];
			$data	= array(
				'name'              => $arrData['name'],
				'task_category_id'  => $arrData['task_category_id'],
				'task_project_id'   => $arrData['task_project_id'],
				'main_hour'         => $arrData['main_hour'],
				'task_status_id'    => $arrData['task_status_id'],
				'date_begin'        => $arrData['date_begin'] ? $date->formatToData($arrData['date_begin']) : null,
				'date_end'          => $arrData['date_end'] ? $date->formatToData($arrData['date_end']) : null,
				'content'           => $arrData['content'],
			    'user_id'           => $this->userInfo->getUserInfo('id'),
			    'ordering'          => $arrData['ordering'],
				'status'            => $arrData['status'],
				'created'           => date('Y-m-d H:i:s'),
				'created_by'        => $this->userInfo->getUserInfo('id'),
				'modified'          => date('Y-m-d H:i:s'),
				'modified_by'       => $this->userInfo->getUserInfo('id'),
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