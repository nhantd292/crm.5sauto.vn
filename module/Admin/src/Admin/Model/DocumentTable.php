<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DocumentTable extends AbstractTableGateway implements ServiceLocatorAwareInterface {

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
	            $configs   = $arrParam['configs'];
                $ssFilter  = $arrParam['ssFilter'];
                
                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
                $select -> where -> equalTo('code', $configs['code']);
                 
                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select -> where -> equalTo('status', $ssFilter['filter_status']);
                }
                
                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select -> where -> like('name', '%'. $ssFilter['filter_keyword'] . '%');
    			}
    			
                if(!empty($ssFilter['filter_document'])) {
                    $select -> where -> equalTo('document_id', $ssFilter['filter_document']);
                }
            })->current();
	    }
	    
	    return $result->count;
	}
	
	public function listItem($arrParam = null, $options = null){
		if($options['task'] == 'list-item') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
			    $configs   = $arrParam['configs'];
                $paginator = $arrParam['paginator'];
                $ssFilter  = $arrParam['ssFilter'];
                
                $select -> limit($paginator['itemCountPerPage'])
    			        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage'])
    			        -> where -> equalTo('code', $configs['code']);
    			
    			if(!empty($ssFilter['order_by']) && !empty($ssFilter['order'])) {
    			    $select -> order(array($ssFilter['order_by'] .' '. strtoupper($ssFilter['order'])));
    			}
    			
    			if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
    			    $select -> where -> equalTo('status', $ssFilter['filter_status']);
    			}
    			
    			if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select -> where -> like('name', '%'. $ssFilter['filter_keyword'] . '%');
    			}
    			
    			if(!empty($ssFilter['filter_document'])) {
    			    $select -> where -> equalTo('document_id', $ssFilter['filter_document']);
    			}
    		});
		}
		
	    if($options['task'] == 'list-all') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
                if(!empty($arrParam['order'])) {
                    $select->order($arrParam['order']);
                }
                if(!empty($arrParam['where'])) {
                    foreach ($arrParam['where'] AS $key => $value) {
                        if(!empty($value)) {
                            $select -> where -> equalTo($key, $value);
                        }
                    }
                }
            });
	    }

	    if($options['task'] == 'list-all-multil') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
                if(!empty($arrParam['order'])) {
                    $select->order($arrParam['order']);
                }
                if(!empty($arrParam['where'])) {
                    foreach ($arrParam['where'] AS $key => $value) {
                        if(!empty($value)) {
                            if($key == 'id'){
                                $select -> where -> in($key, $value);
                            }
                            else{
                                $select -> where -> equalTo($key, $value);
                            }
                        }
                    }
                }
            });
	    }
		
	    if($options['task'] == 'list-parent') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
                if(!empty($arrParam['order'])) {
                    $select->order($arrParam['order']);
                }
                if(!empty($arrParam['where'])) {
                    foreach ($arrParam['where'] AS $key => $value) {
                        if(!empty($value)) {
                            $select -> where -> equalTo($key, $value);
                        }
                    }
                }
                
                $select -> where -> equalTo('document_id', $arrParam['data']['document_id']);
            });
	    }
		
	    if($options['task'] == 'cache') {
	        $cache = $this->getServiceLocator()->get('cache');
	        $cache_key = 'AdminDocument'. $arrParam['where']['code'];
	        $result = $cache->getItem($cache_key);
	         
	        if (empty($result)) {
	            $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	                if(!empty($arrParam['order'])) {
                        $select->order($arrParam['order']);
	                } else {
	                    $select->order(array('ordering' => 'ASC'));
	                }
	                if(!empty($arrParam['where'])) {
	                    foreach ($arrParam['where'] AS $key => $value) {
	                        if(!empty($value)) {
                                $select -> where -> equalTo($key, $value);
	                        }
	                    }
					}
					$select -> where -> equalTo('status', 1);
	            });
                $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));
                 
                $cache->setItem($cache_key, $result);
	        }
	        
	        if($arrParam['parent']) {
	            $tmp = array();
	            foreach ($arrParam['parent'] AS $key_where => $val_where) {
	                foreach ($result AS $key => $val) {
	                    if($val[$key_where] == $val_where) {
	                        $tmp[$key] = $val;
	                    }
	                }
	            }
	            $result = $tmp;
	        }
	        
	        if($arrParam['key']) {
	            $tmp = $result[$arrParam['key']];
	            $result = array();
	            $result[$arrParam['key']] = $tmp;
	        }
	    }
		
	    if($options['task'] == 'cache-alias') {
	        $cache = $this->getServiceLocator()->get('cache');
	        $cache_key = 'AdminDocumentAlias'. $arrParam['where']['code'];
	        $result = $cache->getItem($cache_key);
	         
	        if (empty($result)) {
	            $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	                if(!empty($arrParam['order'])) {
                        $select->order($arrParam['order']);
	                } else {
	                    $select->order(array('ordering' => 'ASC'));
	                }
	                if(!empty($arrParam['where'])) {
	                    foreach ($arrParam['where'] AS $key => $value) {
	                        if(!empty($value)) {
                                $select -> where -> equalTo($key, $value);
	                        }
	                    }
	                }
	            });
                $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'alias', 'value' => 'object'));
                 
                $cache->setItem($cache_key, $result);
	        }
	        
	        if($arrParam['parent']) {
	            $tmp = array();
	            foreach ($arrParam['parent'] AS $key_where => $val_where) {
	                foreach ($result AS $key => $val) {
	                    if($val[$key_where] == $val_where) {
	                        $tmp[$key] = $val;
	                    }
	                }
	            }
	            $result = $tmp;
	        }
	        
	        if($arrParam['key']) {
	            $tmp = $result[$arrParam['key']];
	            $result = array();
	            $result[$arrParam['key']] = $tmp;
	        }
	    }
		
	    if($options['task'] == 'cache-public') {
	        $cache = $this->getServiceLocator()->get('cache');
	        $cache_key = 'AdminDocumentPublic'. $arrParam['where']['code'];
	        $result = $cache->getItem($cache_key);
	         
	        if (empty($result)) {
	            $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	                if(!empty($arrParam['order'])) {
                        $select->order($arrParam['order']);
	                } else {
	                    $select->order(array('ordering' => 'ASC'));
	                }
	                if(!empty($arrParam['where'])) {
	                    foreach ($arrParam['where'] AS $key => $value) {
	                        if(!empty($value)) {
                                $select -> where -> equalTo($key, $value);
	                        }
	                    }
	                }
	                $select -> where -> equalTo('public', 1);
	            });
                $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));
                 
                $cache->setItem($cache_key, $result);
	        }
	        
	        if($arrParam['key']) {
	            $tmp = $result[$arrParam['key']];
	            $result = array();
	            $result[$arrParam['key']] = $tmp;
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

		if($options['task'] == 'by-name') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
			    $select -> where -> equalTo('name', $arrParam['name']);
    		})->current();
		}

		if($options['task'] == 'by-custom-name') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
				$select -> where -> equalTo('name', $arrParam['name']);
				if (!empty($arrParam['code'])) {
					$select -> where -> equalTo('code', $arrParam['code']);
				}
    		})->current();
		}

		if($options['task'] == 'by-custom-alias') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
				$select -> where -> equalTo('alias', $arrParam['alias']);
				if (!empty($arrParam['code'])) {
					$select -> where -> equalTo('code', $arrParam['code']);
				}
    		})->current();
		}
	
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $arrConfigs    = $arrParam['configs'];
	    $arrData       = $arrParam['data'];
	    $arrItem       = $arrParam['item'];
	    $arrRoute      = $arrParam['route'];
	    
	    $filter   = new \ZendX\Filter\Purifier();
	    $image    = new \ZendX\Functions\Thumbnail($arrData['image']);
	    $date     = new \ZendX\Functions\Date();
	    $number   = new \ZendX\Functions\Number();
	    $gid      = new \ZendX\Functions\Gid();
	    
		if($options['task'] == 'add-item') {
		    $name = explode("\n", $arrData['name']);
		    if(count($name) > 1) {
		        foreach ($name AS $key => $val) {
		            if(!empty($val)) {
    		            // Lưu các giá trị cố định
    		            $gid = new \ZendX\Functions\Gid();
    		            $id = $gid->getId();
    		            $data	= array(
    		                'id'            => $id,
    		                'code'          => $arrConfigs['code'],
    		                'created'       => date('Y-m-d H:i:s'),
    		                'created_by'    => $this->userInfo->getUserInfo('id'),
    		                'public'        => $arrData['public'],
    		                'developer'     => $arrData['developer'],
    		            );
    		            	
    		            // Lấy các field được setting
    		            foreach ($arrConfigs['form']['fields'] AS $filed) {
    		                switch ($filed['options']['to_data']) {
    		                    case 'date':
    		                        $valueData = $date->formatToData($arrData[$filed['name']]);
    		                        break;
    		                    case 'datetime':
    		                        $valueData = $date->formatToData($arrData[$filed['name']], 'Y-m-d H:i:s');
    		                        break;
    		                    case 'integer':
    		                        $valueData = $number->formatToData($arrData[$filed['name']]);
    		                        break;
    		                    default:
    		                        $valueData = $arrData[$filed['name']];
    		                }
    		                 
    		                $data[$filed['name']] = ($valueData || $valueData == 0) ? $valueData : null;
    		            }
    	                $data['name'] = $val;
    		            
    		            $this->tableGateway->insert($data);
		            }
		        }
		    } else {
    		    // Lưu các giá trị cố định
    			$id = !empty($arrData['id']) ? $arrData['id'] : $gid->getId();
    			$data	= array(
    				'id'            => $id,
    				'code'          => $arrConfigs['code'],
    				'created'       => date('Y-m-d H:i:s'),
    				'created_by'    => $this->userInfo->getUserInfo('id'),
    				'public'        => $arrData['public'],
    				'developer'     => $arrData['developer'],
    			);
    			
    			// Lấy các field được setting
    			foreach ($arrConfigs['form']['fields'] AS $filed) {
    			    switch ($filed['options']['to_data']) {
    			        case 'date':
    			            $valueData = $date->formatToData($arrData[$filed['name']]);
    			            break;
    			        case 'datetime':
    			            $valueData = $date->formatToData($arrData[$filed['name']], 'Y-m-d H:i:s');
    			            break;
    			        case 'integer':
    			            $valueData = $number->formatToData($arrData[$filed['name']]);
    			            break;
    			        default:
    			            $valueData = $arrData[$filed['name']];
    			    }
    			    
    			    $data[$filed['name']] = ($valueData || $valueData == 0) ? $valueData : null;
    			}
    			
    			$this->tableGateway->insert($data);
		    }
		    
			return $id;
		}
		
		if($options['task'] == 'edit-item') {
		    $id = $arrData['id'];
			$data	= array(
			    'public'        => $arrData['public'],
			    'developer'     => $arrData['developer'],
			);
			
			// Lấy các field được setting
			foreach ($arrConfigs['form']['fields'] AS $filed) {
			    switch ($filed['options']['to_data']) {
			        case 'date':
			            $valueData = $date->formatToData($arrData[$filed['name']]);
			            break;
			        case 'datetime':
			            $valueData = $date->formatToData($arrData[$filed['name']], 'Y-m-d H:i:s');
			            break;
			        case 'integer':
			            $valueData = $number->formatToData($arrData[$filed['name']]);
			            break;
			        default:
			            $valueData = $arrData[$filed['name']];
			    }
			    
			    $data[$filed['name']] = ($valueData || $valueData == 0) ? $valueData : null;
			}
			
			$this->tableGateway->update($data, array('id' => $id));
			return $id;
		}
		
		if($options['task'] == 'update-item') {
		    $id = $arrData['id'];
		    $data = array();
		    
		    $data[$arrData['field']] = $arrData['field_value'];
		    $this->tableGateway->update($data, array('id' => $id));
		    return $id;
		}
	}
	
	public function deleteItem($arrParam = null, $options = null){
	    if($options['task'] == 'delete-item') {
	        $arrData  = $arrParam['data'];
    	    $arrRoute = $arrParam['route'];
    	    
            $where = new Where();
            $where->in('id', $arrData['cid']);
            $where->equalTo('developer', 0);
            $this->tableGateway->delete($where);
            
            $result = count($arrData['cid']);
	    }
	
	    return $result;
	}
	
	public function changeStatus($arrParam = null, $options = null){
	    if($options['task'] == 'change-status') {
	        $arrData  = $arrParam['data'];
    	    $arrRoute = $arrParam['route'];
    	    
    	    $result = false;
            if(!empty($arrData['cid'])) {
    	        $data	= array( 'status'	=> ($arrData['status'] == 1) ? 0 : 1 );
    			$this->tableGateway->update($data, array("id IN('". implode("','", $arrData['cid']) ."')"));
                $result = true;
            }
	    }
	     
	    return $result;
	}
	
	public function changeOrdering($arrParam = null, $options = null){
	    if($options['task'] == 'change-ordering') {
	        $arrData  = $arrParam['data'];
    	    $arrRoute = $arrParam['route'];
    	    
            foreach ($arrData['cid'] AS $id) {
                $data	= array('ordering'	=> $arrData['ordering'][$id]);
                $where  = array('id' => $id);
                $this->tableGateway->update($data, $where);
            }
            
            $result = count($arrData['cid']);
	    }
	    return $result;
	}
}