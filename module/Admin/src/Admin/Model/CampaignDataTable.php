<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;

class CampaignDataTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter  = $arrParam['ssFilter'];
                $date      = new \ZendX\Functions\Date();
                
                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
                $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_FORM_DATA .'.contact_id', array(), 'inner');
                
    			if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> NEST
                    			     -> greaterThanOrEqualTo(TABLE_FORM_DATA .'.created', $date->fomartToData($ssFilter['filter_date_begin']))
                    			     ->AND
                    			     -> lessThanOrEqualTo(TABLE_FORM_DATA .'.created', $date->fomartToData($ssFilter['filter_date_end']) . ' 23:59:59')
                    			     -> UNNEST;
    			} elseif (!empty($ssFilter['filter_date_begin'])) {
    			    $select -> where -> greaterThanOrEqualTo(TABLE_FORM_DATA .'.created', $date->fomartToData($ssFilter['filter_date_begin']));
    			} elseif (!empty($ssFilter['filter_date_end'])) {
    			    $select->where->lessThanOrEqualTo(TABLE_FORM_DATA .'.created', $date->fomartToData($ssFilter['filter_date_end']) . ' 23:59:59');
    			}
    			
    			if(!empty($ssFilter['filter_form'])) {
    				$select -> where -> equalTo(TABLE_FORM_DATA .'.form_id', $ssFilter['filter_form']);
    			} else {
    			    if(!empty($arrParam['form_ids']) && $arrParam['permissionListInfo']['privileges'] != 'full') {
    			        $select -> where -> in(TABLE_FORM_DATA .'.form_id', $arrParam['form_ids']);
    			    }
    			}
                
    			if(!empty($ssFilter['filter_location_city'])) {
    			    $select -> where -> equalTo(TABLE_CONTACT .'.location_city_id', $ssFilter['filter_location_city']);
    			}
    			
    			if($ssFilter['filter_active'] == 'active') {
    			    $select -> where -> isNotNull(TABLE_CONTACT .'.user_id');
    			} elseif($ssFilter['filter_active'] == 'unactive') {
    			    $select -> where -> isNull(TABLE_CONTACT .'.user_id');
    			}
    			
	            if(!empty($ssFilter['filter_sale_branch'])) {
    			    $select -> where ->equalTo(TABLE_FORM_DATA .'.sale_branch_id', $ssFilter['filter_sale_branch']);
    			}
                
	            if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select -> where -> NEST
                			      	 -> like(TABLE_CONTACT .'.phone', '%'. $ssFilter['filter_keyword'] . '%')
                			      	 ->OR
                			      	 -> like(TABLE_CONTACT .'.name', '%'. $ssFilter['filter_keyword'] . '%')
                			      	 ->OR
                			      	 -> like(TABLE_CONTACT .'.email', '%'. $ssFilter['filter_keyword'] . '%')
                			       	 -> UNNEST;
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
                
    			$select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_FORM_DATA .'.contact_id', array(
            			    'contact_phone' => 'phone',
            			    'contact_name'  => 'name',
            			    'contact_email' => 'email',
            			    'contact_location_city_id' => 'location_city_id',
            			    'contact_user_id' => 'user_id',
            			    'contact_sale_group_id' => 'sale_group_id',
            			    'contact_sale_branch_id' => 'sale_branch_id',
            			), 'inner');
    			
    			if(!isset($options['paginator']) || $options['paginator'] == true) {
    			    $select -> limit($paginator['itemCountPerPage'])
    			            -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
    			}
    			
    			if(!empty($ssFilter['order_by']) && !empty($ssFilter['order'])) {
    			    $select -> order(array($ssFilter['order_by'] => strtoupper($ssFilter['order'])));
    			}
    			
			    if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> NEST
                    			     -> greaterThanOrEqualTo(TABLE_FORM_DATA .'.created', $date->fomartToData($ssFilter['filter_date_begin']))
                    			     ->AND
                    			     -> lessThanOrEqualTo(TABLE_FORM_DATA .'.created', $date->fomartToData($ssFilter['filter_date_end']) . ' 23:59:59')
                    			     -> UNNEST;
    			} elseif (!empty($ssFilter['filter_date_begin'])) {
    			    $select -> where -> greaterThanOrEqualTo(TABLE_FORM_DATA .'.created', $date->fomartToData($ssFilter['filter_date_begin']));
    			} elseif (!empty($ssFilter['filter_date_end'])) {
    			    $select->where->lessThanOrEqualTo(TABLE_FORM_DATA .'.created', $date->fomartToData($ssFilter['filter_date_end']) . ' 23:59:59');
    			}
    			
    			if(!empty($ssFilter['filter_form'])) {
    				$select -> where -> equalTo(TABLE_FORM_DATA .'.form_id', $ssFilter['filter_form']);
    			} else {
    			    if(!empty($arrParam['form_ids']) && $arrParam['permissionListInfo']['privileges'] != 'full') {
    			        $select -> where -> in(TABLE_FORM_DATA .'.form_id', $arrParam['form_ids']);
    			    }
    			}
                
    			if(!empty($ssFilter['filter_location_city'])) {
    			    $select -> where -> equalTo(TABLE_CONTACT .'.location_city_id', $ssFilter['filter_location_city']);
    			}
    			
    			if($ssFilter['filter_active'] == 'active') {
    			    $select -> where -> isNotNull(TABLE_CONTACT .'.user_id');
    			} elseif($ssFilter['filter_active'] == 'unactive') {
    			    $select -> where -> isNull(TABLE_CONTACT .'.user_id');
    			}
    			
	            if(!empty($ssFilter['filter_sale_branch'])) {
    			    $select -> where ->equalTo(TABLE_FORM_DATA .'.sale_branch_id', $ssFilter['filter_sale_branch']);
    			}
                
	            if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select -> where -> NEST
                			      	 -> like(TABLE_CONTACT .'.phone', '%'. $ssFilter['filter_keyword'] . '%')
                			      	 ->OR
                			      	 -> like(TABLE_CONTACT .'.name', '%'. $ssFilter['filter_keyword'] . '%')
                			      	 ->OR
                			      	 -> like(TABLE_CONTACT .'.email', '%'. $ssFilter['filter_keyword'] . '%')
                			       	 -> UNNEST;
    			}
    		});
		}
		
	    if($options['task'] == 'cache') {
	        $cache = $this->getServiceLocator()->get('cache');
	        $cache_key = 'Form';
	        $result = $cache->getItem($cache_key);
	         
	        
	        if (empty($result)) {
	            $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	                $select->order(array('ordering' => 'ASC', 'name' => 'ASC'));
	            });
                $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));
                 
                $cache->setItem($cache_key, $result);
	        }
	    }
	    
	    //TiênNV
	    if($options['task'] == 'list-cache') {
	        $cache = $this->getServiceLocator()->get('cache');
	        $cache_key = 'Admin'. $arrParam['where']['options'];
	        $result = $cache->getItem($cache_key);
	         
	        if (empty($result)) {
	            $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	                if(!empty($arrParam['order'])) {
	                    $select->order($arrParam['order']);
	                }
	                if(!empty($arrParam['where'])) {
	                    foreach ($arrParam['where'] AS $key => $value) {
	                        if(!empty($value)) {
	                            $select->where->equalTo($key, $value);
	                        }
	                    }
	                }
	            });
	            $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));
	    
	            $cache->setItem($cache_key, $result);
	        }
	    }
	    //end
		
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
	
		if($options == null) {
			$result	= $this->defaultGet($arrParam, array('by' => 'id'));
		}
		
		if($options['task'] == 'get-by-phone') {
		    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $select -> where -> equalTo('form_id', $arrParam['form_id'])
                                 -> equalTo('phone', $arrParam['phone'])
                                 -> greaterThanOrEqualTo('created', date('Y-m-d'));
    		})->current();
		}
		
		if($options['task'] == 'form-contact') {
		    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
		        $select -> where -> equalTo('form_id', $arrParam['form_id'])
		                         -> equalTo('contact_id', $arrParam['contact_id']);
		    })->current();
		}
	
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];
	    
	    $image    = new \ZendX\Functions\Thumbnail($arrData['image']);
	    $filter   = new \ZendX\Filter\Purifier(array( array('HTML.AllowedElements', '') ));
	    $gid      = new \ZendX\Functions\Gid();
	    
		if($options['task'] == 'public-add') {
		    $contact = $this->getItem($arrData, array('task' => 'get-by-phone'));
		    
		    $data = array();
		    foreach ($arrData AS $key => $val) {
		        if(is_array($val)) {
		            $arrTmp = array();
		            foreach ($val AS $k => $v) {
		                $arrTmp[$k] = $filter->filter($v);
		            }
		            $value = serialize($arrTmp);
		        } else {
		            $value = $filter->filter($val);
		        }
		        $data[$key] = $value;
		    }
		    
		    if(!empty($arrData['form_new_id'])) {
		    	$data['form_id'] = $arrData['form_new_id'];
		    	unset($data['form_new_id']);
		    }
		    
		    if(!empty($contact)) {
		        $id = $contact['id'];
		        
		        $this->tableGateway->update($data, array('id' => $id));
		    } else {
    			$data['status']  = 0;
    			$data['created'] = date('Y-m-d H:i:s');
    			
    			$this->tableGateway->insert($data);
    			$id = $this->tableGateway->getLastInsertValue();
		    }
			return $id;
		}
		
		if($options['task'] == 'update-status') {
			$id = $arrData['id'];
			
			$data = array(
				'status' => $arrData['status'],
				'user_id' => $arrData['user_id'],
				'company_branch_id' => $arrData['company_branch_id'],
				'company_group_id' => $arrData['company_group_id'],
			);
			
			$this->tableGateway->update($data, array('id' => $id));
			return $id;
		}
		
	   if($options['task'] == 'add-history') {
			$id = $arrData['id'];
			
			$history = array(
				'history_1' => $arrData['history_1'],
				'history_2' => $arrData['history_2'],
				'history_3' => $arrData['history_3'],
			);
			
			$data = array(
				'history' => serialize($history)
			);
			
			$this->tableGateway->update($data, array('id' => $id));
			return $id;
		}
		
		//TienNV
		if($options['task'] == 'add-manager') {
		    $id = $arrData['id'];
		    	
		    $history['manager'] = array(
		        'date_add' => date('Y-m-d H:i:s'),
		        'manager_add' => $arrData['manager_add'],
		        'add_by' => $this->userInfo->getUserInfo('id')
		    );

		    $data = array(
		        'manager' => serialize($history)
		    );
		    	
		    $this->tableGateway->update($data, array('id' => $id));
		    return $id;
		}
		
		if($options['task'] == 'import-insert') {
		    if(!empty($arrData['form_id']) && !empty($arrData['contact_id'])) {
    		    $date     = new \ZendX\Functions\Date();
    		    $data = array(
    		        'form_id' => $arrData['form_id'],
    		        'contact_id' => $arrData['contact_id'],
    		        'source' => $arrData['source'] ? $arrData['source'] : null,
    		        'created' => date('Y-m-d H:i:s'),
    		    );
    		    	
    		    $this->tableGateway->insert($data);
		    }
		    
		    return 'ok';
		}
		
		if($options['task'] == 'edit-result') {
		    $id = $arrData['id'];
		    	
		    $data = array(
		        'result_id' => $arrData['result_id']
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