<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;

class CampaignTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter  = $arrParam['ssFilter'];
                 
                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select -> where -> equalTo('status', $ssFilter['filter_status']);
                }
                
                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select -> where ->like('name', '%'. $ssFilter['filter_keyword'] . '%');
    			}
    			
    			if($arrParam['permissionListInfo']['privileges'] != 'full') {
    			    $likeKey = '(';
    			    $likeValue = array();
    			    foreach ($arrParam['groupInfo'] AS $key => $value) {
    			        if($key == 0) {
    			            $likeKey .= 'permission_ids LIKE ?';
    			        } else {
    			            $likeKey .= ' OR permission_ids LIKE ?';
    			        }
    			         
    			        $likeValue[] = '%'. $value['id'] .'%';
    			    }
    			    $likeKey .= ')';
    			    $select -> where -> expression($likeKey, $likeValue);
    			}
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
    			
    			if(!empty($ssFilter['order_by']) && !empty($ssFilter['order'])) {
    			    $select -> order(array($ssFilter['order_by'] .' '. strtoupper($ssFilter['order'])));
    			}
    			
    			if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
    			    $select -> where -> equalTo('status', $ssFilter['filter_status']);
    			}
    			
    			if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select -> where -> like('name', '%'. $ssFilter['filter_keyword'] . '%');
    			}
    			
    			if($arrParam['permissionListInfo']['privileges'] != 'full') {
    			    $likeKey = '(';
    			    $likeValue = array();
    			    foreach ($arrParam['groupInfo'] AS $key => $value) {
    			        if($key == 0) {
    			            $likeKey .= 'permission_ids LIKE ?';
    			        } else {
    			            $likeKey .= ' OR permission_ids LIKE ?';
    			        }
    			        
    			        $likeValue[] = '%'. $value['id'] .'%';
    			    }
    			    $likeKey .= ')';
    			    $select -> where -> expression($likeKey, $likeValue);
    			}
    		});
		}
		
		if($options['task'] == 'list-all') {
		    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
		
		        $select -> order(array('ordering' => 'ASC', 'created' => 'DESC'))
		                -> where -> equalTo('status', 1);
		         
		        if($arrParam['permissionListInfo']['privileges'] != 'full') {
		            $likeKey = '(';
		            $likeValue = array();
		            foreach ($arrParam['groupInfo'] AS $key => $value) {
		                if($key == 0) {
		                    $likeKey .= 'permission_ids LIKE ?';
		                } else {
		                    $likeKey .= ' OR permission_ids LIKE ?';
		                }
		                 
		                $likeValue[] = '%'. $value['id'] .'%';
		            }
		            $likeKey .= ')';
		            $select -> where -> expression($likeKey, $likeValue);
		        }
		    });
		}
		
	    if($options['task'] == 'cache') {
	        $cache = $this->getServiceLocator()->get('cache');
	        $cache_key = 'Form';
	        $result = $cache->getItem($cache_key);
	         
	        if (empty($result)) {
	            $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	                $select->order(array('ordering' => 'ASC', 'created' => 'DESC'));
	            });
                $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));
                 
                $cache->setItem($cache_key, $result);
	        }
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
	    
	    $image    = new \ZendX\Functions\Thumbnail($arrData['image']);
	    $filter   = new \ZendX\Filter\Purifier();
	    $gid      = new \ZendX\Functions\Gid();
	    
		if($options['task'] == 'add-item') {
			$id = !empty($arrData['id']) ? $arrData['id'] : $gid->getId();
			$data	= array(
				'id'                => $id,
				'name'              => $arrData['name'],
				'ordering'          => $arrData['ordering'],
				'status'            => $arrData['status'],
				'fields'            => $arrData['fields'],
				'created'           => @date('Y-m-d H:i:s'),
				'created_by'        => $this->userInfo->getUserInfo('id'),
			    'permission_ids'    => implode(',', $arrData['permission_ids']),
			);
			
			$this->tableGateway->insert($data);
			return $id;
		}
		if($options['task'] == 'edit-item') {
		    $id = $arrData['id'];
			$data	= array(
				'name'              => $arrData['name'],
				'ordering'          => $arrData['ordering'],
				'status'            => $arrData['status'],
			    'fields'            => $arrData['fields'],
			    'permission_ids'    => implode(',', $arrData['permission_ids']),
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