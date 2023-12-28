<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SettingTable extends NestedTable implements ServiceLocatorAwareInterface {
	
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
	
	public function itemInSelectbox($arrParam = null, $options = null){
	    if($options == null) {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $select    -> order(array('left' => 'ASC'))
	                       -> where->greaterThan('level', 0);
	        })->toArray();
	    }
	    
	    if($options['task'] == 'list-level') {
	        $result = $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $node = $arrParam['node'];
	            
	            $select    -> columns(array('id', 'level'))
	                       -> order(array('level' => 'DESC'))
	                       -> limit(1)
	                       -> where->greaterThanOrEqualTo('left', $node['left'])
	                       -> where->lessThanOrEqualTo('right', $node['right']);
	        })->current();
	    }
	    
	    if($options['task'] == 'form-category') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $select    -> order(array('left' => 'ASC'));
	        })->toArray();
	    }
	    
	    if($options['task'] == 'form-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $select    -> order(array('left' => 'ASC'))
	                       -> where->greaterThan('level', 0);
	        })->toArray();
	    }
	
	    return $result;
	}
	
	public function countItem($arrParam = null, $options = null){
	    if($options == null) {
	        $result	= $this->tableGateway->select()->count();
	    }
	    
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $node       = $arrParam['node'];
	            
	            $select -> where->greaterThan('level', 0)
                        -> where->greaterThanOrEqualTo('left', $node['left'])
	                    -> where->lessThanOrEqualTo('right', $node['right']);
	            
	            $ssFilter  = $arrParam['ssFilter'];
	             
	            if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
	                $select->where->equalTo('status', $ssFilter['filter_status']);
	            }
	            
	            if(isset($ssFilter['filter_level']) && $ssFilter['filter_level'] != '') {
	                $select->where->lessThanOrEqualTo('level', $ssFilter['filter_level']);
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
	    
	    return $result;
	}
	
	public function listItem($arrParam = null, $options = null){
	    if($options == null) {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $select    -> order(array('left' => 'ASC'))
	                       -> where->greaterThan('level', 0);
	        })->toArray();
	    }
	    
		if($options['task'] == 'list-item') {
		    
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $paginator  = $arrParam['paginator'];
                $ssFilter   = $arrParam['ssFilter'];
                $node       = $arrParam['node'];
                
                $select -> order('left ASC')
	                    -> where->greaterThan('level', 0)
	                    -> where->greaterThanOrEqualTo('left', $node['left'])
	                    -> where->lessThanOrEqualTo('right', $node['right']);
                
                if(empty($options['item_first'])) {
                    $select -> limit($paginator['itemCountPerPage'])
                            -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                } else {
                    $select -> limit(1);
                }
				
				if(!empty($ssFilter['order_by']) && !empty($ssFilter['order'])) {
				    $select -> order(array($ssFilter['order_by'] .' '. strtoupper($ssFilter['order'])));
				}
				
				if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
				    $select->where->equalTo('status', $ssFilter['filter_status']);
				}
				
				if(isset($ssFilter['filter_level']) && $ssFilter['filter_level'] != '') {
				    $select->where->lessThanOrEqualTo('level', $ssFilter['filter_level']);
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
		
		if($options['task'] == 'list-branch') {
		    $result = $this->listNodes($arrParam, array('task' => 'list-branch'))->toArray();
		}
		
		if($options['task'] == 'list-edit') {
		    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
		        $select   -> order(array('left' => 'ASC'))
		                  -> where->greaterThan('level', 0)
		                          ->NEST
		                          ->lessThan('left', $arrParam['left'])
		                          ->or
		                          ->greaterThan('left', $arrParam['right'])
		                          ->UNNEST
		                          ->lessThanOrEqualTo('level', 1);
		    })->toArray();
		}
		
	    if($options['task'] == 'list-by-code') {
		    $nodeInfo = $this->getItem(array('code' => $arrParam['code']), array('task' => 'code'));
		
		    $result	= $this->tableGateway->select(function (Select $select) use ($nodeInfo){
		        $select -> order('left ASC')
        		        -> where->greaterThan('level', 0)
        		        -> where->between('left', $nodeInfo->left, $nodeInfo->right);
		    })->toArray();
		}
		
		if($options['task'] == 'cache-by-code') {
		    $cache = $this->getServiceLocator()->get('cache');
		    $cache_key = 'AdminSetting_' . str_replace('.', '_', $arrParam['code']);
		    $result = $cache->getItem($cache_key);
		    
		    if (empty($result)) {
    		    $nodeInfo = $this->getItem(array('code' => $arrParam['code']), array('task' => 'code'));
    		
    		    $settings = $this->tableGateway->select(function (Select $select) use ($nodeInfo){
    		        $select -> order('left ASC')
            		        -> where->greaterThan('level', 0)
            		        -> where->between('left', $nodeInfo->left, $nodeInfo->right);
    		    });
    		    
    		    $result   = array();
    		    foreach ($settings AS $setting) {
    		        $result[$setting['code']] = $setting;
    		    }
		         
		        $cache->setItem($cache_key, $result);
		    }
		}
		

		if($options['task'] == 'cache-by-code-list') {
		    $cache = $this->getServiceLocator()->get('cache');
		    $cache_key = 'AdminSetting_' . str_replace('.', '_', $arrParam['code']);
		    $result = $cache->getItem($cache_key);
		
		    if (empty($result)) {
		        $nodeInfo = $this->getItem(array('code' => $arrParam['code']), array('task' => 'code'));
		
		        $result = $this->tableGateway->select(function (Select $select) use ($nodeInfo){
		            $select -> order('left ASC')
        		            -> where->greaterThan('level', 0)
        		            -> where->between('left', $nodeInfo->left, $nodeInfo->right);
		        })->toArray();
		         
		        $cache->setItem($cache_key, $result);
		    }
		}
		
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
	
		if($options == null) {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
					$select->where->equalTo('id', $arrParam['id']);
			})->current();
		}
		
		if($options['task'] == 'code') {
		    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
		        $select->where->equalTo('code', $arrParam['code']);
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
				'id'                => $id,
				'name'              => $arrData['name'],
				'code'              => $arrData['code'],
				'value'             => $arrData['value'],
			    'description'       => $filter->filter($arrData['description']),
			    'content'           => $filter->filter($arrData['content']),
				'image'             => $image->getFull(),
				'image_medium'      => $image->getMedium(),
				'image_thumb'       => $image->getThumb(),
				'created'           => date('Y-m-d H:i:s'),
				'created_by'        => $this->userInfo->getUserInfo('id'),
			);
			
			$this->insertNode($data, $arrRoute['reference'], array('position' => $arrRoute['type']));
			return $id;
		}
		
		if($options['task'] == 'edit-item') {
		    $id = $arrData['id'];
			$data	= array(
				'name'              => $arrData['name'],
				'code'              => $arrData['code'],
			    'value'             => $arrData['value'],
			    'description'       => $filter->filter($arrData['description']),
			    'content'           => $filter->filter($arrData['content']),
			    'image'             => $image->getFull(),
			    'image_medium'      => $image->getMedium(),
			    'image_thumb'       => $image->getThumb(),
			);
			
			$this->updateNode($data, $id, null);
			return $id;
		}
		
		if($options['task'] == 'move-item') {
		    $data	= array(
		        'name'              => $arrParam['name'],
		        'code'              => $arrParam['code'],
		        'description'       => $filter->filter($arrParam['description']),
		        'content'           => $filter->filter($arrParam['content']),
		        'image'             => $image->getFull(),
		        'image_medium'      => $image->getMedium(),
		        'image_thumb'       => $image->getThumb(),
		        'parent'            => $arrParam['parent'],
		    );
		    	
		    if($arrParam['parent'] == $arrParam['id']) {
		        $arrParam['parent'] = null;
		    }
		    $this->updateNode($data, $arrParam['id'], $arrParam['parent']);
		    return $arrParam['id'];
		}
	}
	
	public function deleteItem($arrParam = null, $options = null){
	    if($options['task'] == 'delete-item') {
	        foreach ($arrParam['cid'] AS $id) {
	            $this->removeNode($id, array('type' => 'only'));
	        }
	        
	        return count($arrParam['cid']);
	    }
	
	    return false;
	}
	
	public function moveItem($arrParam = null, $options = null){
	    if($options == null) {
	        if(!empty($arrParam['move-id'])) {
	            if($arrParam['move-type'] == 'up') {
                    $this->moveUp($arrParam['move-id']);
	            } elseif ($arrParam['move-type'] == 'down') {
	                $this->moveDown($arrParam['move-id']);
	            }
	            return true;
	        }
	    }
	
	    return false;
	}
	
	public function changeStatus($arrParam = null, $options = null){
	    if($options['task'] == 'change-status') {
	        if(!empty($arrParam['cid'])) {
    	        $data	= array( 'status'	=> ($arrParam['status'] == 1) ? 0 : 1 );
    			$this->tableGateway->update($data, array("id IN('". implode(',', $arrParam['cid']) ."')"));
	        }
	        return true;
	    }
	    
	    return false;
	}
	
	public function changeOrdering($arrParam = null, $options = null){
	    $result = 0;
	    
	    if($options['task'] == 'change-ordering') {
            foreach ($arrParam['cid'] AS $id) {
                $data	= array( 'ordering'	=> $arrParam['ordering'][$id] );
                $where  = array('id' => $id);
                $this->tableGateway->update($data, $where);
            }
            
            return count($arrParam['cid']);
	    }
	    return false;
	}
}