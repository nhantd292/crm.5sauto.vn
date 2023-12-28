<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;

class EventContactTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter  = $arrParam['ssFilter'];
                
                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
                $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_EVENT_CONTACT .'.contact_id', array(), 'inner');
                $select -> where -> equalTo(TABLE_EVENT_CONTACT.'.event_id', $ssFilter['event_id']);
                
                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select -> where -> equalTo(TABLE_EVENT_CONTACT .'.'. $ssFilter['filter_status'], 1);
                }
                
	            if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select -> where -> NEST
                	                 -> like(TABLE_CONTACT. '.phone', '%'. $ssFilter['filter_keyword'] . '%')
                	                 -> OR
                	                 -> like(TABLE_CONTACT. '.name', '%'. $ssFilter['filter_keyword'] . '%')
                	                 -> OR
                	                 -> like(TABLE_CONTACT. '.email', '%'. $ssFilter['filter_keyword'] . '%')
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
                
                $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_EVENT_CONTACT .'.contact_id',
                            array(
                                'contact_phone' => 'phone',
    			                'contact_name' => 'name',
    			                'contact_email' => 'email',
    			                'contact_sex' => 'sex',
    			                'contact_birthday' => 'birthday',
    			                'contact_birthday_year' => 'birthday_year',
    			                'contact_location_city_id' => 'location_city_id',
    			                'contact_location_district_id' => 'location_district_id',
    			                'contact_options' => 'options',
                            ), 'inner')
                        -> where->equalTo(TABLE_EVENT_CONTACT.'.event_id', $ssFilter['event_id']);
                
                if(!isset($options['paginator']) || $options['paginator'] == true) {
        			$select -> limit($paginator['itemCountPerPage'])
        			        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }
    			
    			if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
    			    $select -> where -> equalTo(TABLE_EVENT_CONTACT .'.'. $ssFilter['filter_status'], 1);
    			}
    			
			    if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select -> where -> NEST
                	                 -> like(TABLE_CONTACT. '.phone', '%'. $ssFilter['filter_keyword'] . '%')
                	                 -> OR
                	                 -> like(TABLE_CONTACT. '.name', '%'. $ssFilter['filter_keyword'] . '%')
                	                 -> OR
                	                 -> like(TABLE_CONTACT. '.email', '%'. $ssFilter['filter_keyword'] . '%')
                	                 -> UNNEST;
    			}
    		});
		}
		
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
	
		if($options == null) {
			$result	= $this->defaultGet($arrParam, array('by' => 'id'));
		}
		
		if($options['task'] == 'by-contact') {
		    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $select ->where -> equalTo('contact_id', $arrParam['contact_id'])
                                -> equalTo('event_id', $arrParam['event_id']);
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
		    $settings         = $this->getServiceLocator()->get('Admin\Model\SettingTable')->listItem(array('code' => 'General'), array('task' => 'cache-by-code'));
		    $user             = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
		    $company_group    = $this->getServiceLocator()->get('Admin\Model\CompanyGroupTable')->listItem(null, array('task' => 'cache'));
		    $company_branch   = $this->getServiceLocator()->get('Admin\Model\CompanyBranchTable')->listItem(null, array('task' => 'cache'));
		    $contact          = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem($arrData, array('task' => 'by-phone'));
		    $event            = $arrParam['event'];
		    
		    // Tồn tại liên hệ
		    if(!empty($contact)) {
		        // Khách hàng kho
		        if(!empty($contact['store'])) {
    		        $arrParamContact = $arrParam;
    		        $arrParamContact['data']['id']                = $contact['id'];
    		        $arrParamContact['data']['store']             = 'null';
    		        $arrParamContact['data']['deploy_date']       = date('d/m/Y H:i:s');
    		        $arrParamContact['data']['user_id']           = $this->userInfo->getUserInfo('id');
    		        $arrParamContact['data']['company_branch_id'] = $this->userInfo->getUserInfo('company_branch_id');
    		        $arrParamContact['data']['company_group_id']  = $this->userInfo->getUserInfo('company_group_id');
    		        
    		        $contact_id = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($arrParamContact, array('task' => 'edit-item'));
    		        
    		        // Thêm lịch sử hệ thống
    		        $user_id             = $user[$contact['user_id']]['fullname'];
    		        $company_group_id    = $contact['company_group_id'] ? ' - '. $company_group[$contact['company_group_id']]['name'] : '';
    		        $company_branch_id   = $contact['company_branch_id'] ? ' - '. $company_branch[$contact['company_branch_id']]['name'] : '';
    		        $arrParamLogs = $arrParam;
    		        $logsContent  = 'Người quản lý trước: '. $user_id . $company_group_id . $company_branch_id;
    		        $arrParamLogs['data'] = array(
    		            'title'          => 'Nhập lại khách hàng kho',
    		            'phone'          => $contact['phone'],
    		            'name'           => $contact['name'],
    		            'action'         => 'Đăng ký kho',
    		            'content'        => $logsContent,
    		            'contact_id'     => $contact['id'],
    		        );
    		        $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
		        } else {
		            $arrParamContact = $arrParam;
		            $arrParamContact['data']['id'] = $contact['id'];
		            $arrParamContact['item'] = $contact;
		            
		            $contact_id = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($arrParamContact, array('task' => 'edit-item'));
		            
		            // Thêm lịch sử chăm sóc
		            $date = new \ZendX\Functions\Date();
		            if($contact['history_access'] == 0) {
		                $history_day_access = 0;
		                if(!empty($contact['history_created'])) {
		                    $history_day_access = $date->diff($contact['history_created']);
		                } else {
		                    $history_day_access = $date->diff($contact['deploy_date']);
		                }
		            
		                $day_in_history = $settings['General.Contact.DayInHistory']['value'];
		                if($history_day_access >= $day_in_history) {
    		                if(!empty($arrData['history_action_id'])) {
                			    // Tham số lưu vào liên hệ
                			    $data['history_created']       = $arrData['history_action_id'] ? date('Y-m-d H:i:s') : null;
                			    $data['history_action_id']     = $arrData['history_action_id'];
                			    $data['history_purpose_id']    = $arrData['history_purpose_id'];
                			    $data['history_result_id']     = $arrData['history_result_id'];
                			    $data['history_time_return']   = $arrData['history_time_return'] ? $date->fomartToData($arrData['history_time_return']) : null;
                			    $data['history_content']       = $arrData['history_content'];
                			    
                			    // Tham số lưu lịch sử chăm sóc
                			    $arrParamHistory = $arrParam;
                			    $arrParamHistory['data'] = array(
                			        'action_id'      => $arrData['history_action_id'],
                			        'purpose_id'     => $arrData['history_purpose_id'],
                			        'result_id'      => $arrData['history_result_id'],
                			        'time_return'    => $arrData['history_time_return'],
                			        'content'        => $arrData['history_content'],
                			        'contact_id'     => $contact_id,
                			    );
                			    $history = $this->getServiceLocator()->get('Admin\Model\HistoryTable')->saveItem($arrParamHistory, array('task' => 'add-item'));
                			}
		                }
		            }
		        }
		    } else {
		        $arrParamContact = $arrParam;
		        $contact_id = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($arrParamContact, array('task' => 'add-item'));
		    }
		    
		    // Thêm khách hàng vào sự kiện 
		    $check_contact = $this->getItem(array('contact_id' => $contact_id, 'event_id' => $event['id']), array('task' => 'by-contact'));
		    if(empty($check_contact)) {
    			$id = $gid->getId();
    			$data	= array(
    				'id'            => $id,
    				'contact_id'	=> $contact_id,
    				'event_id'		=> $event['id'],
    				'created'       => date('Y-m-d H:i:s'),
    				'created_by'    => $this->userInfo->getUserInfo('id'),
    			);
    			$this->tableGateway->insert($data);
    			
    			// Update số lượng liên hệ trong sự kiện
    			if(!empty($event)) {
    			    $contact_event = $this->tableGateway->select(function (Select $select) use ($event){
        			    $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
                        $select -> where->equalTo('event_id', $event['id']);
    			    })->current();
                    
        			$arrParamEvent = $arrParam;
        			$arrParamEvent['data'] = array();
        			$arrParamEvent['data']['id'] = $event['id'];
        			$arrParamEvent['data']['contact_total'] = $contact_event->count;
        			$eventUpdate = $this->getServiceLocator()->get('Admin\Model\EventTable')->saveItem($arrParamEvent, array('task' => 'update'));
    			}
    			
    			// Thêm lịch sử hệ thống
    			$arrParamLogs = $arrParam;
    			$arrParamLogs['data'] = array(
    			    'title'          => 'Thêm liên hệ vào sự kiện',
    			    'phone'          => $contact['phone'],
    			    'name'           => $contact['name'],
    			    'action'         => 'Thêm mới',
    			    'content'        => $event['name'],
    			    'contact_id'     => $contact['id'],
    			);
    			$logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
    			return $id;
		    }
		}
		
		if($options['task'] == 'edit-item') {
		    $id = $arrData['id'];
			$data	= array(
				'contact_id'	=> $arrData['contact_id'],
				'event_id'		=> $arrData['event_id'],
				'ordering'      => $arrData['ordering'],
				'status'        => $arrData['status'],
			);
			
			$this->tableGateway->update($data, array('id' => $id));
			return $id;
		}
		
		if($options['task'] == 'update-item') {
		    $id       = $arrData['id'];
		    if(!empty($arrData['data_field'])) {
		        $data = array(
		            $arrData['data_field'] => $arrData['data_value']
		        );
		
		        $this->tableGateway->update($data, array('id' => $id));
		    }
		    return $id;
		}
		
		if($options['task'] == 'add-contact') {
		    $id = $gid->getId();
			$data	= array(
				'id'			=> $id,
				'contact_id'	=> $arrData['contact_id'],
				'event_id'		=> $arrData['event_id'],
				'created'       => date('Y-m-d H:i:s'),
				'created_by'    => $this->userInfo->getUserInfo('id'),
			);
			
			$this->tableGateway->insert($data);
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
	    
	    if($options['task'] == 'update-status') {
	        $arrData   = $arrParam['data'];
    	    $arrRoute  = $arrParam['route'];
    	    $arrItem   = $arrParam['item'];
    	    $arrEvent  = $arrParam['event'];
    	    
	        if($arrData['type'] == 'join'){
    	        $arrParamContact = array(
    	            'data' => array(
        	            'id' => $arrItem['contact_id'],
        	            'event_id' => ($arrItem['join'] == 0) ? $arrItem['event_id'] : ''
        	        )
    	        );
    	        $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($arrParamContact, array('task' => 'update-contact'));
    	    }
    	    
    	    $event_options = !empty($arrEvent['options']) ? explode(',', $arrEvent['options']) : array($arrEvent['name']);
    	    $value = explode(',', $arrItem[$arrData['type']]);
    	    
    	    foreach ($event_options AS $key => $val) {
    	        $value[$key] = !empty($value[$key]) ? $value[$key] : 0;
    	        if($key == $arrData['index']) {
    	            if($value[$arrData['index']] == 0) {
    	                $value[$arrData['index']] = 1;
    	            } else {
    	                $value[$arrData['index']] = 0;
    	            }
    	        }
    	    }
    	    
    	    $id = $arrItem['id'];
    	    $data = array(
    	        $arrData['type']   => implode(',', $value),
    	    );
    	    
    	    $this->tableGateway->update($data, array('id' => $id));
    	    
    	    $result = $id;
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