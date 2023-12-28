<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;

class PendingTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
                $number     = new \ZendX\Functions\Number();
                
                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
                $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_CONTRACT .'.contact_id', array(), 'inner');
                $select -> where -> isNotNull(TABLE_CONTRACT .'.pending');
                
	            if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
			        $filter_keyword = trim($ssFilter['filter_keyword']);
			        if(strlen($number->formatToPhone($filter_keyword)) >= 10) {
			            $select -> where -> equalTo(TABLE_CONTACT. '.phone', $number->formatToPhone($filter_keyword));
			        } elseif (filter_var($filter_keyword, FILTER_VALIDATE_EMAIL)) {
			            $select -> where -> equalTo(TABLE_CONTACT. '.email', $filter_keyword);
			        } else {
        		        $select -> where -> NEST
                    	                 -> like(TABLE_CONTACT. '.name', '%'. $filter_keyword .'%')
                        	             -> UNNEST;
			        }
    			}

	            if(!empty($ssFilter['filter_sale_branch'])) {
    			    $select -> where -> equalTo(TABLE_CONTRACT .'.sale_branch_id', $ssFilter['filter_sale_branch']);
    			}
    			
    			if(!empty($ssFilter['filter_product'])) {
    				$select -> where -> equalTo(TABLE_CONTRACT .'.product_id', $ssFilter['filter_product']);
    			}
    			 
    			if(!empty($ssFilter['filter_edu_class'])) {
    				$select -> where -> equalTo(TABLE_CONTRACT .'.edu_class_id', $ssFilter['filter_edu_class']);
    			}
            })->current();
	    }
	    
	    return $result->count;
	}
	
	public function listItem($arrParam = null, $options = null){
		if($options['task'] == 'list-item') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $paginator  = $arrParam['paginator'];
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
                $number     = new \ZendX\Functions\Number();
                
    			$select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_CONTRACT .'.contact_id', 
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
    			             ), 'inner');
    			$select -> order(array(TABLE_CONTRACT .'.index' => 'DESC'));
    			$select -> where -> isNotNull(TABLE_CONTRACT .'.pending');
    			
    			if(!isset($options['paginator']) || $options['paginator'] == true) {
        			$select -> limit($paginator['itemCountPerPage'])
        			        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
    			}
    			
			    if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
			        $filter_keyword = trim($ssFilter['filter_keyword']);
			        if(strlen($number->formatToPhone($filter_keyword)) >= 10) {
			            $select -> where -> equalTo(TABLE_CONTACT. '.phone', $number->formatToPhone($filter_keyword));
			        } elseif (filter_var($filter_keyword, FILTER_VALIDATE_EMAIL)) {
			            $select -> where -> equalTo(TABLE_CONTACT. '.email', $filter_keyword);
			        } else {
        		        $select -> where -> NEST
                    	                 -> like(TABLE_CONTACT. '.name', '%'. $filter_keyword .'%')
                        	             -> UNNEST;
			        }
    			}

	            if(!empty($ssFilter['filter_sale_branch'])) {
    			    $select -> where -> equalTo(TABLE_CONTRACT .'.sale_branch_id', $ssFilter['filter_sale_branch']);
    			}
    			
    			if(!empty($ssFilter['filter_product'])) {
    				$select -> where -> equalTo(TABLE_CONTRACT .'.product_id', $ssFilter['filter_product']);
    			}
    			 
    			if(!empty($ssFilter['filter_edu_class'])) {
    				$select -> where -> equalTo(TABLE_CONTRACT .'.edu_class_id', $ssFilter['filter_edu_class']);
    			}
    		});
		}
		
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
	
		if($options == null) {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
			    $select -> where -> equalTo('id', $arrParam['id']);
    		})->toArray();
		}
		
		return current($result);
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrItem  = $arrParam['item'];
	    $arrRoute = $arrParam['route'];
	    
	    $date     = new \ZendX\Functions\Date();
	    $number   = new \ZendX\Functions\Number();
	    $filter   = new \ZendX\Filter\Purifier();
	    $gid      = new \ZendX\Functions\Gid();
	    
		// Xác nhận duyệt chuyển lớp
		if($options['task'] == 'accept') {
		    $arrContract = $arrParam['contract'];
		    $arrContact = $arrParam['contact'];
		
		    $id = $arrContract['id'];
		    
		    $contract_pending = !empty($arrContract['pending']) ? unserialize($arrContract['pending']) : array();
		    $contract_options = !empty($arrContract['options']) ? unserialize($arrContract['options']) : array();
		    $contract_edu_class_ids = !empty($arrContract['edu_class_ids']) ? unserialize($arrContract['edu_class_ids']) : array();
		    
		    $pending_type = $arrData['pending_type'];
		    if($pending_type == 'edu_class_move') {
		        $log_action = 'Duyệt chuyển lớp';
		    } elseif ($pending_type == 'edu_class_relearn') {
		        $log_action = 'Duyệt đăng ký học lại';
		    }
		    
		    $pending = $contract_pending[$pending_type];
		    if(!empty($pending['edu_class_id'])) {
		        if(empty($contract_edu_class_ids[$pending['edu_class_id']])) {
		            if(!empty($contract_edu_class_ids[$arrContract['edu_class_id']])) {
		                $contract_edu_class_ids[$pending['edu_class_id']] = $contract_edu_class_ids[$arrContract['edu_class_id']];
		                $contract_edu_class_ids[$pending['edu_class_id']]['edit_date'] = date('Y-m-d H:i:s');
		                $contract_edu_class_ids[$pending['edu_class_id']]['edit_by_id'] = $this->userInfo->getUserInfo('id');
		                unset($contract_edu_class_ids[$arrContract['edu_class_id']]);
		            } else {
		                $contract_edu_class_ids[$pending['edu_class_id']] = array(
		                    'date_add' => date('Y-m-d H:i:s')
		                );
		            }
		        }
		         
		        // Thêm số lượng vào lớp mới
		        $edu_class_id = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->saveItem(array('data' => array('id' => $pending['edu_class_id'])), array('task' => 'update-student', 'type' => 'up'));
		         
		        // Trừ số lượng lớp cũ
		        if(!empty($arrContract['edu_class_id'])) {
		            $edu_class_id = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->saveItem(array('data' => array('id' => $arrContract['edu_class_id'])), array('task' => 'update-student', 'type' => 'down'));
		        }
		        
		        unset($contract_pending[$pending_type]);
		        
		        // Cập nhật
		        
		        $contract_options[$pending_type] = $pending['edu_class_id'];
		        
		        $data = array();
    		    $data['edu_class_id'] = $pending['edu_class_id'];
    		    $data['edu_class_ids'] = !empty($contract_edu_class_ids) ? serialize($contract_edu_class_ids) : null;
    		    $data['options'] = !empty($contract_options) ? serialize($contract_options) : null;
    		    $data['pending'] = !empty($contract_pending) ? serialize($contract_pending) : null;
    		    
    		    $this->tableGateway->update($data, array('id' => $id));
		    }
		    
		    // Thêm lịch sử hệ thống
		    if(!empty($id)) {
		        $arrParamLogs = array(
		            'data' => array(
		                'title'          => 'Lớp học',
		                'phone'          => $arrContact['phone'],
		                'name'           => $arrContact['name'],
		                'action'         => $log_action,
		                'contact_id'     => $arrContact['id'],
		                'contract_id'    => $id,
		                'options'        => array(
		                    'edu_class_from' => $arrContract['edu_class_id'],
		                    'edu_class_to' => $data['edu_class_id'],
		                    'status' => 'Đã duyệt'
		                )
		            )
		        );
		        $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
		    }
		    return $id;
		}
		
		// Xác nhận không duyệt chuyển lớp
		if($options['task'] == 'unaccept') {
		    $arrContract = $arrParam['contract'];
		    $arrContact = $arrParam['contact'];
		
		    $id = $arrContract['id'];
		    
		    $contract_pending = !empty($arrContract['pending']) ? unserialize($arrContract['pending']) : array();
		    $contract_edu_class_ids = !empty($arrContract['edu_class_ids']) ? unserialize($arrContract['edu_class_ids']) : array();
		    
		    $pending_type = $arrData['pending_type'];
		    if($pending_type == 'edu_class_move') {
		        $log_action = 'Không duyệt chuyển lớp';
		    } elseif ($pending_type == 'edu_class_relearn') {
		        $log_action = 'Không duyệt đăng ký học lại';
		    }
		    
		    $pending = $contract_pending[$pending_type];
		    unset($contract_pending[$pending_type]);
		        
	        // Cập nhật
	        $data = array();
		    $data['pending'] = !empty($contract_pending) ? serialize($contract_pending) : null;
		    
		    $this->tableGateway->update($data, array('id' => $id));
		    
		    // Thêm lịch sử hệ thống
		    if(!empty($id)) {
		        $arrParamLogs = array(
		            'data' => array(
		                'title'          => 'Lớp học',
		                'phone'          => $arrContact['phone'],
		                'name'           => $arrContact['name'],
		                'action'         => $log_action,
		                'contact_id'     => $arrContact['id'],
		                'contract_id'    => $id,
		                'options'        => array(
		                    'edu_class_from' => $arrContract['edu_class_id'],
		                    'edu_class_to' => $pending['edu_class_id'],
		                    'content' => $arrData['content'],
		                    'status' => 'Không duyệt'
		                )
		            )
		        );
		        $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
		    }
		    return $id;
		}
	}
}





