<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PermissionListTable extends AbstractTableGateway implements ServiceLocatorAwareInterface {
	
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
	            
	            if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
	                $select -> where -> equalTo('status', $ssFilter['filter_status']);
	            }
	            
	            if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
			        $select -> where -> NEST
                    			     -> like('name', '%'. $ssFilter['filter_keyword'] . '%')
                    			     ->or
                    			     -> like('module', '%'. $ssFilter['filter_keyword'] . '%')
                    			     ->or
                    			     -> like('controller', '%'. $ssFilter['filter_keyword'] . '%')
                    			     ->or
                    			     -> like('action', '%'. $ssFilter['filter_keyword'] . '%')
                    			     -> UNNEST;
				}
	        })->count();
	    }
	    
	    return $result;
	}
	
	public function listItem($arrParam = null, $options = null){
		if($options['task'] == 'list-item') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
                $paginator = $arrParam['paginator'];
                $ssFilter  = $arrParam['ssFilter'];
			    
                $select -> limit($paginator['itemCountPerPage'])
				        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
				
				if(!empty($ssFilter['order_by']) && !empty($ssFilter['order'])) {
				    $select ->order(array($ssFilter['order_by'] .' '. strtoupper($ssFilter['order'])));
				}
				
				if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
				    $select -> where -> equalTo('status', $ssFilter['filter_status']);
				}
				
                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
			        $select -> where -> NEST
                    			     -> like('name', '%'. $ssFilter['filter_keyword'] . '%')
                    			     ->or
                    			     -> like('module', '%'. $ssFilter['filter_keyword'] . '%')
                    			     ->or
                    			     -> like('controller', '%'. $ssFilter['filter_keyword'] . '%')
                    			     ->or
                    			     -> like('action', '%'. $ssFilter['filter_keyword'] . '%')
                    			     -> UNNEST;
				}
				
			});
		}
		
		if($options['task'] == 'all') {
		    $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
		        $select ->order(array('module' => 'ASC', 'controller' => 'ASC', 'action' => 'ASC'));
		    })->toArray();
		
		    $result = array();
		    foreach ($items AS $item) {
		        $value_r = !empty($item['desc']) ? $item['desc'] : $item['name'];
		        $result[$item['module']][$item['controller']][$item['action']] = array('id' => $item['id'], 'name' => $value_r);
		    }
		}

		if($options['task'] == 'all-status') {
		    $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
		        $select ->order(array('module' => 'ASC', 'controller' => 'ASC', 'action' => 'ASC'));
                $select -> where -> equalTo('status', 1);
		    })->toArray();

		    $result = array();
		    foreach ($items AS $item) {
		        $value_r = !empty($item['desc']) ? $item['desc'] : $item['name'];
		        $result[$item['module']][$item['controller']][$item['action']] = array('id' => $item['id'], 'name' => $value_r);
		    }
		}
		
		if($options['task'] == 'list-privileges') {
		    $ids = '';
		    foreach ($arrParam AS $key => $permission) {
		        $ids .= ($key == 0) ? $permission['permission_list_ids'] : ','. $permission['permission_list_ids'];
		        if($permission['permission_list_ids'] == 'full') {
		            return 'full';
		        }
		    }
		    if(empty($ids)) {
		        return '';
		    }
		    
			$actions = $this->tableGateway->select(function (Select $select) use ($ids){
			    $action = explode(',', $ids);
                $select -> columns(array('id', 'name', 'module', 'controller', 'action'))
                        -> where -> in('id', $action);				
			});
			
			if(!empty($actions)) {
			    foreach ($actions AS $action) {
			        $result[] = $action->module .'||'. $action->controller .'||'. $action->action;
			    }
			} else {
			    return '';
			}
		}
		
		if($options['task'] == 'cache') {
		    $cache = $this->getServiceLocator()->get('cache');
		    $cache_key = 'AdminPermissionList';
		    $result = $cache->getItem($cache_key);
		
		    if (empty($result)) {
		        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
		            $select->order(array('module' => 'ASC', 'controller' => 'ASC', 'action' => 'ASC'));
		        })->toArray();
		
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
		
		if($options['task'] == 'check-by-data') {
		    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
		        $select -> where -> equalTo('module', $arrParam['module'])
            		             -> equalTo('controller', $arrParam['controller'])
            		             -> equalTo('action', $arrParam['action']);
		    })->current();
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
			$id = $gid->getId();
			$data	= array(
				'id'            => $id,
				'name'          => $arrData['name'],
				'desc'          => $arrData['desc'],
				'module'        => $arrData['module'],
				'controller'    => $arrData['controller'],
				'action'        => $arrData['action'],
			    'status'        => $arrData['status'],
			    'ordering'      => $arrData['ordering'],
			);
			
			$this->tableGateway->insert($data);
			return $id;
		}
		
		if($options['task'] == 'edit-item') {
		    $id = $arrData['id'];
			$data	= array(
				'name'          => $arrData['name'],
                'desc'          => $arrData['desc'],
				'module'        => $arrData['module'],
				'controller'    => $arrData['controller'],
				'action'        => $arrData['action'],
			    'status'        => $arrData['status'],
			    'ordering'      => $arrData['ordering'],
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