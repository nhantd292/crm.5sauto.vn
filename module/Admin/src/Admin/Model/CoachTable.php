<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CoachTable extends AbstractTableGateway implements ServiceLocatorAwareInterface {
	
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
	    if($options == null) {
	        $result	= $this->tableGateway->select()->count();
	    }
	    
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $ssFilter  = $arrParam['ssFilter'];
	            
	            $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
	            $select -> where -> equalTo('company_position_id', '15418205965jl573h9p143');
	            
	            if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
	                $select -> where -> equalTo('status', $ssFilter['filter_status']);
	            }
	            
	            if(!empty($ssFilter['filter_company_branch'])) {
	                $select -> where -> equalTo('company_branch_id', $ssFilter['filter_company_branch']);
	            }
	            
	            if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
			        $select -> where -> NEST
                    			     -> like('name', '%'. $ssFilter['filter_keyword'] . '%')
                    			     -> OR
                    			     -> like('username', '%'. $ssFilter['filter_keyword'] . '%')
                    			     -> OR
                    			     -> like('phone', '%'. $ssFilter['filter_keyword'] . '%')
                    			     -> OR
                    			     -> like('email', '%'. $ssFilter['filter_keyword'] . '%')
                    			     -> UNNEST;
				}
				
				if($this->userInfo->getUserInfo('id') != '1111111111111111111111') {
				    $select -> where -> notEqualTo('id', '1111111111111111111111');
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
	             
	            $select -> where -> equalTo('company_position_id', '15418205965jl573h9p143');
	            
	            if(!isset($options['paginator']) || $options['paginator'] == true) {
	    			$select -> limit($paginator['itemCountPerPage'])
	    			        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }
	    
	            if(!empty($ssFilter['order_by']) && !empty($ssFilter['order'])) {
	                $select -> order(array(TABLE_USER .'.'. $ssFilter['order_by'] .' '. strtoupper($ssFilter['order'])));
	            }
	    
	            if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
	                $select -> where -> equalTo(TABLE_USER .'.status', $ssFilter['filter_status']);
	            }
	    
	            if(!empty($ssFilter['filter_company_branch'])) {
	                $select -> where -> equalTo('company_branch_id', $ssFilter['filter_company_branch']);
	            }
	            
	            if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
	                $select -> where -> NEST
                	                 -> like(TABLE_USER .'.name', '%'. $ssFilter['filter_keyword'] . '%')
                	                 -> OR
                	                 -> like(TABLE_USER .'.username', '%'. $ssFilter['filter_keyword'] . '%')
                	                 -> OR
                	                 -> like(TABLE_USER .'.phone', '%'. $ssFilter['filter_keyword'] . '%')
                	                 -> OR
                	                 -> like(TABLE_USER .'.email', '%'. $ssFilter['filter_keyword'] . '%')
                	                 -> UNNEST;
	            }
	    
	            if($this->userInfo->getUserInfo('id') != '1111111111111111111111') {
	                $select -> where -> notEqualTo('id', '1111111111111111111111');
	            }
	        });
	            	
	    }
	    
	    if($options['task'] == 'list-all') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];
	             
	            $select -> order(array('name' => 'ASC'));
	            $select -> where -> equalTo('company_position_id', '15418205965jl573h9p143');
	             
	            if(!empty($arrData['company_branch_id'])) {
	                $select -> where -> equalTo('company_branch_id', $arrData['company_branch_id']);
	            }
	             
	            if(!empty($arrData['status'])) {
	                $select -> where -> equalTo('status', $arrData['status']);
	            }
	             
	        })->toArray();
	    }
	    
	    if($options['task'] == 'cache') {
	        $cache = $this->getServiceLocator()->get('cache');
	        $cache_key = 'AdminUserCoach';
	        $result = $cache->getItem($cache_key);
	        if (empty($result)) {
	            $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	                $select -> order(TABLE_USER .'.name ASC');
	                $select -> where -> equalTo('company_position_id', '15418205965jl573h9p143');
	            });
	            $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));
	            $cache->setItem($cache_key, $result);
	        }
	    }
	    
	    if($options['task'] == 'cache-status') {
	        $cache = $this->getServiceLocator()->get('cache');
	        $cache_key = 'AdminUserCoachStatus';
	        $result = $cache->getItem($cache_key);
	        if (empty($result)) {
	            $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	                $select -> order(TABLE_USER .'.name ASC')
	                        -> where -> equalTo('company_position_id', '15418205965jl573h9p143')
	                                 -> equalTo(TABLE_USER .'.status', 1);
	            });
	            $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));
	            $cache->setItem($cache_key, $result);
	        }
	    }
	    
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
	
		if($options == null) {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
					$select->where->equalTo('id', $arrParam['id']);
			})->toArray();
		}
		
		if($options['task'] == 'by-username') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
			    $select->where->equalTo('username', $arrParam['username']);
			})->toArray();
		}
	
		return current($result);
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];
	    $arrItem  = $arrParam['item'];
	     
	    $image    = new \ZendX\Functions\Thumbnail($arrData['image']);
	    $filter   = new \ZendX\Filter\Purifier();
	    $gid      = new \ZendX\Functions\Gid();
	    
	    $permission_ids = $arrData['permission_ids'] ? implode(',', $arrData['permission_ids']) : '';
	    
		if($options['task'] == 'add-item') {
			$id = $gid->getId();
			$data	= array(
				'id'                    => $id,
				'name'                  => $arrData['name'],
				'username'              => $arrData['username'],
				'code'                  => $arrData['code'],
				'password'              => md5($arrData['password']),
				'email'                 => $arrData['email'],
				'phone'                 => $arrData['phone'],
				'status'                => $arrData['status'],
				'created'               => date('Y-m-d H:i:s'),
				'created_by'            => $this->userInfo->getUserInfo('id'),
				'permission_ids'        => $permission_ids,
				'company_branch_id'     => $arrData['company_branch_id'],
				'company_department_id' => $arrData['company_department_id'],
				'company_position_id'   => $arrData['company_position_id'],
			);
			
			$arrOptions = array('password_status' => $arrData['password_status']);
			$data['options'] = serialize($arrOptions);
			
			$this->tableGateway->insert($data);
			return $id;
		}
		
		if($options['task'] == 'edit-item') {
		    $id = $arrData['id'];
			$data	= array(
				'name'                  => $arrData['name'],
				'username'              => $arrData['username'],
			    'code'                  => $arrData['code'],
				'email'                 => $arrData['email'],
			    'phone'                 => $arrData['phone'],
				'status'                => $arrData['status'],
			    'permission_ids'        => $permission_ids,
			    'company_branch_id'     => $arrData['company_branch_id'],
			    'company_department_id' => $arrData['company_department_id'],
			    'company_position_id'   => $arrData['company_position_id'],
			);
			
			if(!empty($arrData['password'])) {
			    $data['password'] = md5($arrData['password']);
			}
			
			$arrOptions = !empty($arrItem['options']) ? unserialize($arrItem['options']) : array();
			$arrOptions['password_status'] = $arrData['password_status'];
			
			$data['options'] = serialize($arrOptions);
			
			$this->tableGateway->update($data, array('id' => $arrData['id']));
			return $arrData['id'];
		}
	}
	
	public function deleteItem($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];
	    
	    if($options['task'] == 'delete-item') {
	        $where = new Where();
	        $where -> in('id', $arrData['cid']);
	        $where -> notEqualTo('id', '1111111111111111111111');
	        $this -> tableGateway -> delete($where);
	        
	        return count($arrData['cid']);
	    }
	
	    return false;
	}
	
    public function changeStatus($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];
	    
	    if($options['task'] == 'change-status') {
	        if(!empty($arrData['cid'])) {
    	        $data = array( 
    	            'status' => !$arrData['status']
    	        );
    	        
    	        $where = new Where();
    	        $where -> in('id', $arrData['cid']);
    			$this -> tableGateway -> update($data, $where);
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
                $data	= array( 'ordering'	=> $arrData['ordering'][$id] );
                $where  = array('id' => $id);
                $this->tableGateway->update($data, $where);
            }
            
            return count($arrData['cid']);
	    }
	    return false;
	}
}