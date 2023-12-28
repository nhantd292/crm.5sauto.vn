<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class LogsTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
                $number     = new \ZendX\Functions\Number();
                
                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
                
	            if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    			    $filter_keyword = trim($ssFilter['filter_keyword']);
    			    if(strlen($number->formatToPhone($filter_keyword)) == 10) {
                        $select -> where -> NEST
                            -> equalTo('phone', $number->formatToPhone($filter_keyword))
                            ->OR
                            -> like('title', '%'. $filter_keyword .'%')
                            -> UNNSET;
    			    } elseif(strlen($filter_keyword) == 22) {
    			        $select -> where -> NEST
    			                         -> like('options', '%'. $filter_keyword .'%')
    			                         ->OR
    			                         -> like('contact_id', '%'. $filter_keyword .'%')
    			                         ->OR
    			                         -> like('contract_id', '%'. $filter_keyword .'%')
                                        ->OR
                                        -> like('title', '%'. $filter_keyword .'%')
    			                         -> UNNSET;
    			    } else {
    			        $select -> where -> NEST
                    			            -> like('name', '%'. $filter_keyword .'%')
                                            ->OR
                                            -> like('title', '%'. $filter_keyword .'%')
                    			            -> UNNEST;
    			    }
    			}
            })->current();
	    }
	    
	    if($options['task'] == 'list-ajax-parent') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
	            $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
	            $select -> where -> equalTo('action', 'Chuyển quản lý');
		        $select -> where -> NEST
		                         -> equalTo(TABLE_LOGS .'.contact_id', $arrParam['data']['contact_id'])
		                         ->OR
		                         -> like(TABLE_LOGS .'.options', '%'. $arrParam['data']['contact_id'] .'%')
		                         -> UNNEST;
		        
		        if(!empty($arrParam['data']['contract_id'])) {
		            $select -> where -> equalTo(TABLE_LOGS .'.contract_id', $arrParam['data']['contract_id']);
		        }
            })->current();
	    }
	    
	    if($options['task'] == 'list-ajax') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
	            $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
		        $select -> where -> equalTo(TABLE_LOGS .'.contact_id', $arrParam['data']['contact_id']);
		        
		        if(!empty($arrParam['data']['contract_id'])) {
		            $select -> where -> equalTo(TABLE_LOGS .'.contract_id', $arrParam['data']['contract_id']);
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
                $date       = new \ZendX\Functions\Date();
                $number     = new \ZendX\Functions\Number();
                
    			$select -> limit($paginator['itemCountPerPage'])
    			        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
    			
    			if(!empty($ssFilter['order_by']) && !empty($ssFilter['order'])) {
    			    $select -> order(array($ssFilter['order_by'] .' '. strtoupper($ssFilter['order'])));
    			}
    			
    			if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    			    $filter_keyword = trim($ssFilter['filter_keyword']);
    			    if(strlen($number->formatToPhone($filter_keyword)) == 10) {
    			        $select -> where -> NEST
                                        -> equalTo('phone', $number->formatToPhone($filter_keyword))
                                        ->OR
                                        -> like('title', '%'. $filter_keyword .'%')
                                        -> UNNSET;
    			    } elseif(strlen($filter_keyword) == 22) {
    			        $select -> where -> NEST
    			                         -> like('options', '%'. $filter_keyword .'%')
    			                         ->OR
    			                         -> like('contact_id', '%'. $filter_keyword .'%')
    			                         ->OR
    			                         -> like('contract_id', '%'. $filter_keyword .'%')
                                        ->OR
                                        -> like('title', '%'. $filter_keyword .'%')
    			                         -> UNNSET;
    			    } else {
    			        $select -> where -> NEST
                    			            -> like('name', '%'. $filter_keyword .'%')
                                            ->OR
                                            -> like('title', '%'. $filter_keyword .'%')
                    			            -> UNNEST;
    			    }
    			}
    		});
		}
		
		if($options['task'] == 'list-ajax-parent') {
		    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
		        $paginator = $arrParam['paginator'];
		        
		        $select -> limit($paginator['itemCountPerPage'])
		                -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
		        
		        $select -> order(array(TABLE_LOGS .'.created' => 'DESC'));
		        $select -> where -> equalTo('action', 'Chuyển quản lý');
		        $select -> where -> NEST
                		         -> equalTo(TABLE_LOGS .'.contact_id', $arrParam['data']['contact_id'])
                		         -> OR
                		         -> like(TABLE_LOGS .'.options', '%'. $arrParam['data']['contact_id'] .'%')
                		         -> UNNEST;
		        
		        if(!empty($arrParam['data']['contract_id'])) {
		            $select -> where -> equalTo(TABLE_LOGS .'.contract_id', $arrParam['data']['contract_id']);
		        }
		    });
		}
		
		if($options['task'] == 'list-ajax') {
		    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
		        $paginator = $arrParam['paginator'];
		        
		        $select -> limit($paginator['itemCountPerPage'])
		                -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
		        
		        $select -> order(array(TABLE_LOGS .'.created' => 'DESC'));
		        $select -> where -> equalTo(TABLE_LOGS .'.contact_id', $arrParam['data']['contact_id']);
		        
		        if(!empty($arrParam['data']['contract_id'])) {
		            $select -> where -> equalTo(TABLE_LOGS .'.contract_id', $arrParam['data']['contract_id']);
		        }
		    });
		}
		
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
	
		if($options == null) {
			$result	= $this->defaultGet($arrParam, array('by' => 'id'));
		}
	
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];
	    
	    $filter   = new \ZendX\Filter\Purifier();
	    $gid      = new \ZendX\Functions\Gid();
	    
		if($options['task'] == 'add-item') {
			$id = $gid->getId();
			$data	= array(
				'id'                => $id,
				'title'             => $arrData['title'],
		        'phone'             => $arrData['phone'],
		        'name'              => $arrData['name'],
		        'action'            => $arrData['action'],
		        'options'           => $arrData['options'] ? serialize($arrData['options']) : null,
		        'contact_id'        => $arrData['contact_id'],
		        'contract_id'       => $arrData['contract_id'] ? $arrData['contract_id'] : null,
				'created'           => date('Y-m-d H:i:s'),
				'created_by'        => $this->userInfo->getUserInfo('id'),
			);
			
			$this->tableGateway->insert($data);
			return $id;
		}
	}
	
	public function deleteItem($arrParam = null, $options = null){
	    if($options['task'] == 'delete-item') {
	        $result = $this->defaultDelete($arrParam, null);
	    }
	
	    if($options['task'] == 'contract-delete') {
	        $where = new Where(); 
	        $where->equalTo('contract_id', $arrParam['contract_id']);
	        $this->tableGateway->delete($where);
	         
	        $result = $arrParam['contract_id'];
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