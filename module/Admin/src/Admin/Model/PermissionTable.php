<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PermissionTable extends AbstractTableGateway implements ServiceLocatorAwareInterface {
	
    protected $tableGateway;
	protected $userInfo;
	protected $serviceLocator;
	
	public function __construct(TableGateway $tableGateway) {
	    $this->tableGateway	= $tableGateway;
	    $this->userInfo	= new \ZendX\System\UserInfo();
	}
	
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
	    $this->serviceLocator = $serviceLocator;
	}
	
	public function getServiceLocator() {
	    return $this->serviceLocator;
	}
	
	public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $ssFilter  = $arrParam['ssFilter'];
	            
	            $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
	            
	            if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
	                $select -> where -> equalTo('status', $ssFilter['filter_status']);
	            }
	            
	            if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
			        $select -> where -> like('name', '%'. $ssFilter['filter_keyword'] .'%') ;
				}
	        })->current();
	    }
	    
	    return $result->count;
	}
	
	public function listItem($arrParam = null, $options = null){
	    
		if($options['task'] == 'list-item') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
                $paginator = $arrParam['paginator'];
                $ssFilter  = $arrParam['ssFilter'];
			    
				$select -> limit($paginator['itemCountPerPage'])
				        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
				
				if(!empty($ssFilter['order_by']) && !empty($ssFilter['order'])) {
				    $select -> order(array($ssFilter['order_by'] => strtoupper($ssFilter['order'])));
				}
				
				if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
				    $select -> where -> equalTo('status', $ssFilter['filter_status']);
				}
				
				if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
			        $select -> where -> like('name', '%'. $ssFilter['filter_keyword'] .'%');
				}
				
			});
		}
	    
		if($options['task'] == 'multi-id') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
                $select -> where -> in('id', explode(',', $arrParam['ids']));
			})->toArray();
		}
		
		if($options['task'] == 'multi-code') {
		    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
		        $select -> where -> in('code', explode(',', $arrParam['code']));
		    })->toArray();
		}
		
		if($options['task'] == 'cache') {
		    $cache = $this->getServiceLocator()->get('cache');
		    $cache_key = 'AdminPermission';
		    $result = $cache->getItem($cache_key);
		
		    if (empty($result)) {
		        $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
		            $select -> order(array('ordering' => 'ASC', 'name' => 'ASC'));
		        });
		        $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));
		         
		        $cache->setItem($cache_key, $result);
		    }
		}
		
		if($options['task'] == 'cache-code') {
		    $cache = $this->getServiceLocator()->get('cache');
		    $cache_key = 'AdminPermissionCode';
		    $result = $cache->getItem($cache_key);
		
		    if (empty($result)) {
		        $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
		            $select -> order(array('ordering' => 'ASC', 'name' => 'ASC'));
		        });
		        $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'code', 'value' => 'object'));
		         
		        $cache->setItem($cache_key, $result);
		    }
		}
		
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
	
		if($options == null) {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
				$select -> where -> equalTo('id', $arrParam['id']);
			})->current();
		}
	
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];
	    
	    $filter   = new \ZendX\Filter\Purifier();
	    $gid      = new \ZendX\Functions\Gid();
	    
	    $permission_list_ids = '';
	    if(!empty($arrData['permission_list_ids'])) {
	        if(in_array('full', $arrData['permission_list_ids'])) {
	            $permission_list_ids = 'full';
	        } else {
	            $permission_list_ids = implode(',', $arrData['permission_list_ids']);
	        }
	    }
	    
		if($options['task'] == 'add-item') {
			$id = $gid->getId();
			$data	= array(
				'id'                    => $id,
				'code'                  => $arrData['code'],
				'name'                  => $arrData['name'],
				'ordering'              => $arrData['ordering'],
				'status'                => $arrData['status'],
			    'permission_list_ids'   => $permission_list_ids
			);
			
			$this->tableGateway->insert($data);
			return $id;
		}
		
		if($options['task'] == 'edit-item') {
		    $id = $arrData['id'];
			$data	= array(
			    'code'                  => $arrData['code'],
				'name'                  => $arrData['name'],
				'ordering'              => $arrData['ordering'],
				'status'                => $arrData['status'],
			    'permission_list_ids'   => $permission_list_ids
			);
			
			$this->tableGateway->update($data, array('id' => $id));
			return $id;
		}
	}
	
	public function deleteItem($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];
	    
	    if($options['task'] == 'delete-item') {
	        $where = new Where();
	        $where->in('id', $arrData['cid']);
	        $where->notEqualTo('id', '0000000000000000000000');
	        $this->tableGateway->delete($where);
	        
	        return count($arrData['cid']);
	    }
	
	    return false;
	}
	
	public function changeStatus($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];
	    
	    if($options['task'] == 'change-status') {
	        if(!empty($arrData['cid'])) {
    	        $data	= array( 'status'	=> ($arrData['status'] == 1) ? 0 : 1 );
    			$this->tableGateway->update($data, array("id IN('". implode("','", $arrData['cid']) ."')"));
	        }
	        return true;
	    }
	    
	    return false;
	}
	
	public function changeOrdering($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];
	    
	    if($options['task'] == 'change-ordering') {
            foreach ($arrData['cid'] AS $id) {
                $data	= array('ordering'	=> $arrData['ordering'][$id]);
                $where  = array('id' => $id);
                $this->tableGateway->update($data, $where);
            }
            
            return count($arrData['cid']);
	    }
	    return false;
	}
}