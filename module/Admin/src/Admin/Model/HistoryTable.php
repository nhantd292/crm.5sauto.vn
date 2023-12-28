<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;

class HistoryTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter  = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
                
                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')))
                        -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_HISTORY .'.contact_id', array(), 'inner');
                
	            if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $select -> where -> NEST
                                     -> equalTo(TABLE_CONTACT .'.phone', trim($ssFilter['filter_keyword']))
                                     ->OR
                                     -> equalTo(TABLE_CONTACT .'.email', trim($ssFilter['filter_keyword']))
                                     -> UNNEST;
                }
    			
	            if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> NEST
                    			     -> greaterThanOrEqualTo(TABLE_HISTORY .'.'. $ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_begin']))
                    			     ->AND
                    			     -> lessThanOrEqualTo(TABLE_HISTORY .'.'. $ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                    			     -> UNNEST;
    			} elseif (!empty($ssFilter['filter_date_begin'])) {
    			    $select -> where -> greaterThanOrEqualTo(TABLE_HISTORY .'.'. $ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_begin']));
    			} elseif (!empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> lessThanOrEqualTo(TABLE_HISTORY .'.'. $ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
    			}
    			
	            if(!empty($ssFilter['filter_sale_branch'])) {
    			    $select -> where -> equalTo(TABLE_HISTORY .'.'. 'sale_branch_id', $ssFilter['filter_sale_branch']);
    			}
    			
    			if(!empty($ssFilter['filter_sale_group'])) {
    			    $select -> where -> equalTo(TABLE_HISTORY .'.'. 'sale_group_id', $ssFilter['filter_sale_group']);
    			} else {
        			if(!empty($this->userInfo->getUserInfo('sale_group_ids'))){
        			    $select -> where -> in(TABLE_HISTORY .'.'. 'sale_group_id', explode(',', $this->userInfo->getUserInfo('sale_group_ids')));
        			}
    			}
    			
    			if(!empty($ssFilter['filter_user'])) {
    			    $select -> where -> equalTo(TABLE_HISTORY .'.'. 'user_id', $ssFilter['filter_user']);
    			}
    			
    			if(!empty($ssFilter['filter_action'])) {
    			    $select -> where -> equalTo(TABLE_HISTORY .'.action_id', $ssFilter['filter_action']);
    			}
    			
    			if(!empty($ssFilter['filter_result'])) {
    			    $select -> where -> equalTo(TABLE_HISTORY .'.result_id', $ssFilter['filter_result']);
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
                
                $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_HISTORY .'.contact_id', array('contact_name' => 'name', 'contact_phone' => 'phone', 'contact_email' => 'email', 'contact_birthday_year' => 'birthday_year', 'contact_location_city_id' => 'location_city_id', 'contact_location_district_id' => 'location_district_id'), 'inner')
    			        -> limit($paginator['itemCountPerPage'])
    			        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
    			
    			if(!empty($ssFilter['order_by']) && !empty($ssFilter['order'])) {
    			    $select ->ORder(array($ssFilter['order_by'] .' '. strtoupper($ssFilter['order'])));
    			}
    			
			    if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $select -> where -> NEST
                                     -> equalTo(TABLE_CONTACT .'.phone', trim($ssFilter['filter_keyword']))
                                     ->OR
                                     -> equalTo(TABLE_CONTACT .'.email', trim($ssFilter['filter_keyword']))
                                     -> UNNEST;
                }
    			
	            if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> NEST
                    			     -> greaterThanOrEqualTo(TABLE_HISTORY .'.'. $ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_begin']))
                    			     ->AND
                    			     -> lessThanOrEqualTo(TABLE_HISTORY .'.'. $ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                    			     -> UNNEST;
    			} elseif (!empty($ssFilter['filter_date_begin'])) {
    			    $select -> where -> greaterThanOrEqualTo(TABLE_HISTORY .'.'. $ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_begin']));
    			} elseif (!empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> lessThanOrEqualTo(TABLE_HISTORY .'.'. $ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
    			}
    			
    			if(!empty($ssFilter['filter_sale_branch'])) {
    			    $select -> where -> equalTo(TABLE_HISTORY .'.'. 'sale_branch_id', $ssFilter['filter_sale_branch']);
    			}
    			 
    			if(!empty($ssFilter['filter_sale_group'])) {
    			    $select -> where -> equalTo(TABLE_HISTORY .'.'. 'sale_group_id', $ssFilter['filter_sale_group']);
    			} else {
    			    if(!empty($this->userInfo->getUserInfo('sale_group_ids'))){
    			        $select -> where -> in(TABLE_HISTORY .'.'. 'sale_group_id', explode(',', $this->userInfo->getUserInfo('sale_group_ids')));
    			    }
    			}
    			 
    			if(!empty($ssFilter['filter_user'])) {
    			    $select -> where -> equalTo(TABLE_HISTORY .'.'. 'user_id', $ssFilter['filter_user']);
    			}
    			
    			if(!empty($ssFilter['filter_action'])) {
    			    $select -> where -> equalTo(TABLE_HISTORY .'.action_id', $ssFilter['filter_action']);
    			}
    			 
    			if(!empty($ssFilter['filter_result'])) {
    			    $select -> where -> equalTo(TABLE_HISTORY .'.result_id', $ssFilter['filter_result']);
    			}
    		});
		}
		
		if($options['task'] == 'list-ajax') {
		    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
		        $select ->ORder(array(TABLE_HISTORY .'.created' => 'DESC'));
	            $select -> where -> equalTo(TABLE_HISTORY .'.contact_id', $arrParam['data']['contact_id']);
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
	    
	    $date     = new \ZendX\Functions\Date();
	    $filter   = new \ZendX\Filter\Purifier();
	    $gid      = new \ZendX\Functions\Gid();
	    
		if($options['task'] == 'add-item') {
		    $arrContact   = $arrParam['item'];
		    $settings     = $arrParam['settings'];
            $number   = new \ZendX\Functions\Number();
		    
		    // Thêm lịch sử chăm sóc
	        $id = $gid->getId();
	        $data	= array(
	            'id'               => $id,
	            'contact_id'       => $arrContact['id'],
	            'action_id'        => $arrData['history_action_id'],
	            'result_id'        => $arrData['history_result_id'],
	            'type_id'          => $arrData['history_type_id'],
	            'content'          => $arrData['history_content'],
	            'history_success'  => $arrData['history_success'],
	            'return'           => !empty($arrData['history_return']) ? $date->formatToData($arrData['history_return']) : null,
	            'user_id'          => $this->userInfo->getUserInfo('id'),
	            'sale_branch_id'   => $this->userInfo->getUserInfo('sale_branch_id'),
	            'sale_group_id'    => $this->userInfo->getUserInfo('sale_group_id'),
	            'created'          => date('Y-m-d H:i:s'),
	            'created_by'       => $this->userInfo->getUserInfo('id'),
	        );
	        if($arrData['history_type_alias'] == DA_CHOT){
                $data['sales_expected'] = $number->formatToData($arrData['sales_expected']);
	        }

	        $this->tableGateway->insert($data);
	        
	        // Thêm lịch sử hệ thống
	        $arrParamLogs = array(
	            'data' => array(
	                'title'          => 'Liên hệ',
	                'phone'          => $arrContact['phone'],
	                'name'           => $arrContact['name'],
	                'action'         => 'Thêm lịch sử chăm sóc',
	                'contact_id'     => $id,
	                'options'        => array(
	                    'action_id'       => $arrData['history_action_id'],
	                    'result_id'       => $arrData['history_result_id'],
	                    'content'         => $arrData['history_content'],
	                    'return'          => $date->formatToData($arrData['history_return']),
	                    'user_id'         => $data['user_id'],
	                    'sale_branch_id'  => $data['sale_branch_id'],
	                    'sale_group_id'   => $data['sale_group_id'],
	                )
	            )
	        );
	        $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
		    return $id;
		}
		
		if($options['task'] == 'import-add') {
		    $arrContact   = $arrParam['item'];
		    $settings     = $arrParam['settings'];
		    
		    // Thêm lịch sử chăm sóc
	        $id = $gid->getId();
	        $data	= array(
	            'id'               => $id,
	            'contact_id'       => $arrContact['id'],
	            'action_id'        => $arrData['history_action_id'],
	            'result_id'        => $arrData['history_result_id'],
	            'content'          => $arrData['history_content'],
	            'return'           => !empty($arrData['history_return']) ? $date->formatToData($arrData['history_return']) : null,
	            'user_id'          => $arrContact['user_id'],
	            'sale_branch_id'   => $arrContact['sale_branch_id'],
	            'sale_group_id'    => $arrContact['sale_group_id'],
	            'created'          => $arrData['history_created'] ? $date->formatToData($arrData['history_created'], 'Y-m-d H:i:s') : date('Y-m-d H:i:s'),
	            'created_by'       => $arrContact['user_id'],
	        );
	        $this->tableGateway->insert($data);
		    return $id;
		}
	}
	
//	public function deleteItem($arrParam = null, $options = null){
//	    if($options['task'] == 'delete-item') {
//	        $result = $this->defaultDelete($arrParam, null);
//	    }
//
//	    return $result;
//	}
	
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
	
	public function report($arrParam = null, $options = null){
	    if($options['task'] == 'date') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];
	            $dateFormat = new \ZendX\Functions\Date();
	
	            $select -> columns(array('sale_branch_id', 'sale_group_id', 'user_id', 'contact_id', 'action_id', 'result_id'))
        	            -> where -> greaterThanOrEqualTo('created', $dateFormat->formatToData($arrData['date_begin']) .' 00:00:00')
        	                     -> lessThanOrEqualTo('created', $dateFormat->formatToData($arrData['date_end']) .' 23:59:59');
	             
	            if(!empty($arrData['sale_branch_id'])) {
	                $select -> where -> equalTo('sale_branch_id', $arrData['sale_branch_id']);
	            }
	        });
	    }
	    return $result;
	}
}