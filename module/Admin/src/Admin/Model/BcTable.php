<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class BcTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
                $number     = new \ZendX\Functions\Number();
                
                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
                $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_BC .'.contact_id', array(), 'inner');
                
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

    			if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> NEST
                    			     -> greaterThanOrEqualTo(TABLE_BC .'.'. $ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_begin']))
                    			     ->AND
                    			     -> lessThanOrEqualTo(TABLE_BC .'.'. $ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                    			     -> UNNEST;
    			} elseif (!empty($ssFilter['filter_date_begin'])) {
    			    $select->where->greaterThanOrEqualTo(TABLE_BC .'.'. $ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_begin']));
    			} elseif (!empty($ssFilter['filter_date_end'])) {
    			    $select->where->lessThanOrEqualTo(TABLE_BC .'.'. $ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
    			}
    			
	            if(!empty($ssFilter['filter_sale_branch'])) {
    			    $select -> where -> equalTo(TABLE_BC .'.sale_branch_id', $ssFilter['filter_sale_branch']);
    			}
    			
    			if(!empty($ssFilter['filter_sale_group'])) {
    			    $select -> where -> equalTo(TABLE_BC .'.sale_group_id', $ssFilter['filter_sale_group']);
    			}
    			
    			if(!empty($ssFilter['filter_user'])) {
    			    $select -> where -> equalTo(TABLE_BC .'.user_id', $ssFilter['filter_user']);
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
                
    			$select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_BC .'.contact_id', 
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
    			$select -> order(array(TABLE_BC .'.index' => 'DESC'));
    			
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

    			if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> NEST
                    			     -> greaterThanOrEqualTo(TABLE_BC .'.'. $ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_begin']))
                    			     ->AND
                    			     -> lessThanOrEqualTo(TABLE_BC .'.'. $ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                    			     -> UNNEST;
    			} elseif (!empty($ssFilter['filter_date_begin'])) {
    			    $select->where->greaterThanOrEqualTo(TABLE_BC .'.'. $ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_begin']));
    			} elseif (!empty($ssFilter['filter_date_end'])) {
    			    $select->where->lessThanOrEqualTo(TABLE_BC .'.'. $ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
    			}
    			
	            if(!empty($ssFilter['filter_sale_branch'])) {
    			    $select -> where -> equalTo(TABLE_BC .'.sale_branch_id', $ssFilter['filter_sale_branch']);
    			}
    			
    			if(!empty($ssFilter['filter_sale_group'])) {
    			    $select -> where -> equalTo(TABLE_BC .'.sale_group_id', $ssFilter['filter_sale_group']);
    			}
    			
    			if(!empty($ssFilter['filter_user'])) {
    			    $select -> where -> equalTo(TABLE_BC .'.user_id', $ssFilter['filter_user']);
    			}
    		});
		}
		
		if($options['task'] == 'list-item-multi') {
		    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
		        $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_BC .'.contact_id',
        		            array(
                                'contact_phone' => 'phone',
    			                'contact_name' => 'name',
    			                'contact_email' => 'email',
        		                'contact_birthday_' => 'birthday',
    			                'contact_birthday_year' => 'birthday_year',
    			                'contact_location_city_id' => 'location_city_id',
    			                'contact_location_district_id' => 'location_district_id',
    			                'contact_options' => 'options',
		            ), 'inner');
		         
	            $select -> where -> in(TABLE_BC .'.id', $arrParam['ids']);
		    });
		}
		
	    if($options['task'] == 'list-ajax') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
    			$select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_BC .'.contact_id', 
    			             array(
    			                 'contact_phone' => 'phone',
    			                 'contact_name' => 'name',
    			                 'contact_email' => 'email',
    			                 'contact_birthday_' => 'birthday',
    			                 'contact_birthday_year' => 'birthday_year',
    			                 'contact_location_city_id' => 'location_city_id',
    			                 'contact_location_district_id' => 'location_district_id',
    			                 'contact_options' => 'options',
    			             ), 'inner');
    			
    			$select->where->equalTo(TABLE_BC .'.contact_id', $arrParam['data']['contact_id']);
    			
    			if(!empty($arrParam['data']['bc_id'])) {
    			    $select->where->notEqualTo(TABLE_BC .'.id', $arrParam['data']['bc_id']);
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
	
		if($options['task'] == 'by-code') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
			    $select -> where -> equalTo('code', $arrParam['code']);
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
	    
		if($options['task'] == 'add-item') {
		    // Tham số liên hệ
		    $arrParamContact  = $arrParam;
		    
		    // Xóa phân tử không cần update
		    unset($arrParamContact['data']['date']);
		    
		    if(!empty($arrItem)) {
		        // Nếu khách hàng không phải kho
		        $arrParamContact['item']                = $arrItem;
		        $arrParamContact['data']['id']          = $arrItem['id'];
		        $arrParamContact['data']['bc_total']    = $arrItem['bc_total'] + 1;
		        
		        // Nếu là khách hàng kho. Chuyển về cho người nhập đơn hàng quản lý
		        if(!empty($arrItem['store'])) {
    		        $arrParamContact['data']['user_id']           = $this->userInfo->getUserInfo('id');
    		        $arrParamContact['data']['sale_group_id']     = $this->userInfo->getUserInfo('sale_group_id');
    		        $arrParamContact['data']['sale_branch_id']    = $this->userInfo->getUserInfo('sale_branch_id');
    		        $arrParamContact['data']['store']             = 'null';
		        }
		        
		        // Cập nhật liên hệ
		        $contact_id = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($arrParamContact, array('task' => 'edit-item'));
		    } else {
		        // Thêm mới liên hệ
		        $arrParamContact['data']['bc_total']          = 1;
		        $arrParamContact['data']['user_id']           = $this->userInfo->getUserInfo('id');
		        $arrParamContact['data']['sale_group_id']     = $this->userInfo->getUserInfo('sale_group_id');
		        $arrParamContact['data']['sale_branch_id']    = $this->userInfo->getUserInfo('sale_branch_id');
		        
		        $contact_id = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($arrParamContact, array('task' => 'add-item'));
		    }
		    
		    // Thêm đơn hàng
		    if(!empty($contact_id)) {
    		    $id = $gid->getId();
    		    $contract_options = array();
    		    $contract_options['contact_type']                 = $arrItem['type'];
    		    $contract_options['contact_source_group_id']      = $arrItem['source_group_id'] ? $arrItem['source_group_id'] : $arrData['source_group_id'];
    		    $contract_options['contact_history_created']      = $arrItem['history_created'];
    		    $contract_options['contact_store']                = $arrItem['store'];
    		    $contract_options['contact_contract_total']       = $arrItem['contract_total'];
    		    
    		    if(!empty($arrData['promotion_content'])){ // Lý do khuyến mại
    		        $contract_options['promotion_content'] = $arrData['promotion_content'];
    		    }
    		    if(!empty($arrData['test_ielts_listen'])) {
    		        $contract_options['test_ielts_listen'] = $arrData['test_ielts_listen'];
    		    }
    		    if(!empty($arrData['test_ielts_speak'])) {
    		        $contract_options['test_ielts_speak'] = $arrData['test_ielts_speak'];
    		    }
    		    if(!empty($arrData['test_ielts_read'])) {
    		        $contract_options['test_ielts_read'] = $arrData['test_ielts_read'];
    		    }
    		    if(!empty($arrData['test_ielts_write'])) {
    		        $contract_options['test_ielts_write'] = $arrData['test_ielts_write'];
    		    }
    		    if(!empty($arrData['contract_note'])) {
    		        $contract_options['contract_note'] = $arrData['contract_note'];
    		    }
    		    
    		    $data = array(
    		        'id'                      => $id,
    		        'date'                    => $date->formatToData($arrData['date']),
    		        'date_register'           => $date->formatToData($arrData['date_register']),
    		        'date_speaking'           => $date->formatToData($arrData['date_speaking']),
    		        'price'                   => $number->formatToData($arrData['price']),
    		        'price_promotion'         => $number->formatToData($arrData['price_promotion']),
    		        'price_promotion_percent' => $number->formatToData($arrData['price_promotion_percent']),
    		        'price_promotion_price'   => $number->formatToData($arrData['price_promotion_price']),
    		        'price_total'             => $number->formatToData($arrData['price_total']),
    		        'price_paid'              => 0,
    		        'price_accrued'           => 0,
    		        'price_owed'              => $number->formatToData($arrData['price_total']),
    		        'price_surcharge'         => 0,
    		        'contact_id'              => $contact_id,
    		        'user_id'                 => $arrParamContact['data']['user_id'] ? $arrParamContact['data']['user_id'] : $arrItem['user_id'],
    		        'sale_group_id'           => $arrParamContact['data']['sale_group_id'] ? $arrParamContact['data']['sale_group_id'] : $arrItem['sale_group_id'],
    		        'sale_branch_id'          => $arrParamContact['data']['sale_branch_id'] ? $arrParamContact['data']['sale_branch_id'] : $arrItem['sale_branch_id'],
    		        'created'                 => date('Y-m-d H:i:s'),
    		        'created_by'              => $this->userInfo->getUserInfo('id'),
    		        'options'                 => serialize($contract_options)
    		    );
    		    $this->tableGateway->insert($data); // Thực hiện lưu database
    		    
    		    // Thêm lịch sử hệ thống
    		    $arrParamLogs = array(
    		        'data' => array(
    		            'title'          => 'Hội Đồng Anh',
    		            'phone'          => $arrData['phone'],
    		            'name'           => $arrData['name'],
    		            'action'         => 'Thêm mới',
    		            'contact_id'     => $contact_id,
    		            'contract_id'    => $id,
    		            'options'        => array(
    		                'date'                    => $arrData['date'],
    		                'date_register'           => $arrData['date_register'],
    		                'date_speaking'           => $arrData['date_speaking'],
    		                'price'                   => $arrData['price'],
    		                'price_promotion'         => $arrData['price_promotion'],
    		                'price_promotion_percent' => $arrData['price_promotion_percent'],
    		                'price_promotion_price'   => $arrData['price_promotion_price'],
    		                'promotion_content'       => $arrData['promotion_content'],
    		                'price_total'             => $arrData['price_total'],
    		                'user_id'                 => $data['user_id'],
    		                'sale_branch_id'          => $data['sale_branch_id'],
    		                'sale_group_id'           => $data['sale_group_id'],
    		            )
    		        )
    		    );
    		    $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
    			
    			return $id;
		    }
		}
		
		if($options['task'] == 'edit-item') {
		    $arrContact = $arrParam['contact'];
		    $id = $arrData['id'];
			$data = array();
			$contract_options = !empty($arrItem['options']) ? unserialize($arrItem['options']) : null;
			
			if(!empty($arrData['promotion_content'])) {
			    $contract_options['promotion_content'] = $arrData['promotion_content'];
			}
			if(!empty($arrData['test_ielts_listen'])) {
			    $contract_options['test_ielts_listen'] = $arrData['test_ielts_listen'];
			}
			if(!empty($arrData['test_ielts_speak'])) {
			    $contract_options['test_ielts_speak'] = $arrData['test_ielts_speak'];
			}
			if(!empty($arrData['test_ielts_read'])) {
			    $contract_options['test_ielts_read'] = $arrData['test_ielts_read'];
			}
			if(!empty($arrData['test_ielts_write'])) {
			    $contract_options['test_ielts_write'] = $arrData['test_ielts_write'];
			}
			if(!empty($arrData['contract_note'])) {
			    $contract_options['contract_note'] = $arrData['contract_note'];
			}
			
			if(!empty($contract_options)) {
			    $data['options'] = serialize($contract_options);
			}
			
			if(!empty($arrData['date'])) {
			    $data['date'] = $date->formatToData($arrData['date']);
			    
			    // Ngày mới khác ngày cũ thì cập nhật lại hóa đơn theo ngày mới
			    if($arrData['date'] != $arrItem['date']) {
			        $bill = $this->getServiceLocator()->get('Admin\Model\BillTable')->saveItem(array('data' => array('contract_date' => $arrData['date'], 'contract_id' => $arrItem['id'])), array('task' => 'update-by-contract'));
			    }
			}
			if(!empty($arrData['date_register'])) {
			    $data['date_register'] = $date->formatToData($arrData['date_register']);
			}
			if(!empty($arrData['date_speaking'])) {
			    $data['date_speaking'] = $date->formatToData($arrData['date_speaking']);
			}
			if(isset($arrData['price'])) {
			    $data['price'] = $number->formatToNumber($arrData['price']);
			}
			if(isset($arrData['price_promotion'])) {
			    $data['price_promotion'] = $number->formatToNumber($arrData['price_promotion']);
			}
			if(isset($arrData['price_promotion_percent'])) {
			    $data['price_promotion_percent'] = $number->formatToNumber($arrData['price_promotion_percent']);
			}
			if(isset($arrData['price_promotion_price'])) {
			    $data['price_promotion_price'] = $number->formatToNumber($arrData['price_promotion_price']);
			}
			if(isset($arrData['price_total'])) {
			    $data['price_total'] = $number->formatToNumber($arrData['price_total']);
			}
			if(isset($arrData['price_paid'])) {
			    $data['price_paid'] = $number->formatToNumber($arrData['price_paid']);
			}
			if(isset($arrData['price_accrued'])) {
			    $data['price_accrued'] = $number->formatToNumber($arrData['price_accrued']);
			}
			if(isset($arrData['price_owed'])) {
			    $data['price_owed'] = $number->formatToNumber($arrData['price_owed']);
			}
			if(isset($arrData['price_surcharge'])) {
			    $data['price_surcharge'] = $number->formatToNumber($arrData['price_surcharge']);
			}
			if(!empty($arrData['contact_id'])) {
			    $data['contact_id'] = $arrData['contact_id'];
			}
			if(!empty($arrData['user_id'])) {
			    $data['user_id'] = $arrData['user_id'];
			}
			if(!empty($arrData['sale_branch_id'])) {
			    $data['sale_branch_id'] = $arrData['sale_branch_id'];
			}
			if(!empty($arrData['sale_group_id'])) {
			    $data['sale_group_id'] = $arrData['sale_group_id'];
			}
			if(!empty($arrData['created'])) {
			    $data['created'] = $date->formatToData($arrData['created'], 'Y-m-d H:i:s');
			}
			if(!empty($arrData['created_by'])) {
			    $data['created_by'] = $arrData['created_by'];
			}
			
			// Cập nhật đơn hàng
			$this->tableGateway->update($data, array('id' => $id));
			
			// Thêm lịch sử hệ thống
			if(!empty($id)) {
			    $arrCheckLogs = array('date', 'date_register', 'date_speaking', 'price', 'price_promotion', 'price_promotion_percent', 'price_promotion_price', 'promotion_content', 'contact_id', 'user_id', 'sale_branch_id', 'sale_group_id');
			    $arrCheckResult = array();
			    foreach ($arrCheckLogs AS $field) {
			        if(isset($data[$field])) {
    			        $check = $data[$field];
    	                if($field == 'date' || $field == 'date_register' || $field == 'date_speaking') {
    	                    $check = $date->formatToView($data[$field]);
    	                }
    		            if($check != $arrItem[$field]) {
    		                $arrCheckResult[$field] = $data[$field];
		                }
			        }
			    }
			    
			    if(!empty($arrData['note_log'])) {
			        $arrCheckResult['note_log'] = $arrData['note_log'];
			    }
			
			    if(!empty($arrCheckResult)) {
			        $arrParamLogs = array(
			            'data' => array(
			                'title'          => 'Hội Đông Anh',
			                'phone'          => $arrContact['phone'],
			                'name'           => $arrContact['name'],
			                'action'         => 'Sửa',
			                'contact_id'     => $arrContact['id'],
			                'contract_id'    => $id,
			                'options'        => $arrCheckResult
			            )
			        );
			        $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
			    }
			}
			
			return $id;
		}
		
		// Hủy đăng ký thi
		if($options['task'] == 'edu-class-leave') {
		    $arrContract = $arrParam['contract'];
		    $arrContact = $arrParam['contact'];
		
		    $contract_edu_class_ids = !empty($arrContract['edu_class_ids']) ? unserialize($arrContract['edu_class_ids']) : array();
		    $contract_edu_class_ids[$arrContract['edu_class_id']]['leave_sessions'] = $arrData['leave_sessions'];
		    $contract_edu_class_ids[$arrContract['edu_class_id']]['leave_date'] = $date->formatToData($arrData['leave_date']);
		    $contract_edu_class_ids[$arrContract['edu_class_id']]['leave_content'] = $arrData['leave_content'];
		
		    $id = $arrContract['id'];
		    $data = array(
		        'leave_date' => $date->formatToData($arrData['leave_date']),
		        'edu_class_ids' => !empty($contract_edu_class_ids) ? serialize($contract_edu_class_ids) : null
		    );
		    $this->tableGateway->update($data, array('id' => $id));
		
		    // Thêm lịch sử hệ thống
		    if(!empty($id)) {
		        $arrParamLogs = array(
		            'data' => array(
		                'title'          => 'Lớp học',
		                'phone'          => $arrContact['phone'],
		                'name'           => $arrContact['name'],
		                'action'         => 'Nghỉ học',
		                'contact_id'     => $arrContact['id'],
		                'contract_id'    => $id,
		                'options'        => array(
		                    'edu_class_id' => $arrContract['edu_class_id'],
		                    'leave_date' => $arrData['leave_date'],
		                    'leave_sessions' => $arrData['leave_sessions'],
		                    'leave_content' => $arrData['leave_content'],
		                )
		            )
		        );
		        $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
		    }
		    return $id;
		}
		
		// Hủy nghỉ học
		if($options['task'] == 'edu-class-leave-cancel') {
		    $arrContract = $arrParam['contract'];
		    $arrContact = $arrParam['contact'];
		
		    $contract_edu_class_ids = !empty($arrContract['edu_class_ids']) ? unserialize($arrContract['edu_class_ids']) : array();
		    unset($contract_edu_class_ids[$arrContract['edu_class_id']]['leave_date']);
		    unset($contract_edu_class_ids[$arrContract['edu_class_id']]['leave_sessions']);
		    unset($contract_edu_class_ids[$arrContract['edu_class_id']]['leave_content']);
		
		    $id = $arrContract['id'];
		    $data = array(
		        'leave_date' => null,
		        'edu_class_ids' => !empty($contract_edu_class_ids) ? serialize($contract_edu_class_ids) : null
		    );
		    $this->tableGateway->update($data, array('id' => $id));
		
		    // Thêm lịch sử hệ thống
		    if(!empty($id)) {
		        $arrParamLogs = array(
		            'data' => array(
		                'title'          => 'Lớp học',
		                'phone'          => $arrContact['phone'],
		                'name'           => $arrContact['name'],
		                'action'         => 'Hủy nghỉ học',
		                'contact_id'     => $arrContact['id'],
		                'contract_id'    => $id,
		                'options'        => array(
		                    'leave_cancel_content' => $arrData['leave_cancel_content'],
		                )
		            )
		        );
		        $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
		    }
		    return $id;
		}
		
		// Chuyển người quản lý
		if($options['task'] == 'change-user') {
		    $arrUser      = $arrParam['user'];
		    $arrContract  = $arrParam['contract'];
		    $arrContact   = $arrParam['contact'];
		    $arrBill      = $arrParam['bill'];
		
		    // Cập nhật quản lý đơn hàng
		    $id = $arrContract['id'];
	        $data = array(
	            'user_id'            => $arrUser['id'],
	            'sale_branch_id'     => $arrUser['sale_branch_id'],
	            'sale_group_id'      => $arrUser['sale_group_id'],
	        );
	        $where = new Where();
	        $where->equalTo('id', $id);
	        $this->tableGateway->update($data, $where);
	        
	        // Kiểm tra xem có được chuyển toàn bộ hóa đơn
	        if($arrData['transfer_bill'] == 'yes') {
	            $arrParamBill = $arrParam;
	            $arrParamBill['data']['contract_id'] = $arrContract['id'];
	            $arrParamBill['data']['user_id'] = $arrUser['id'];
	            $arrParamBill['data']['sale_branch_id'] = $arrUser['sale_branch_id'];
	            $arrParamBill['data']['sale_group_id'] = $arrUser['sale_group_id'];
	            $bill = $this->getServiceLocator()->get('Admin\Model\BcBillTable')->saveItem($arrParamBill, array('task' => 'contract-change-user'));
	        }
	        
	        // Kiểm tra xem có được chuyển quản lý liên hệ
	        if($arrData['transfer_contact'] == 'yes') {
	            $arrParamContact = $arrParam;
	            $arrParamContact['data']['id'] = $arrContact['id'];
	            $arrParamContact['data']['user_id'] = $arrUser['id'];
	            $arrParamContact['data']['sale_branch_id'] = $arrUser['sale_branch_id'];
	            $arrParamContact['data']['sale_group_id'] = $arrUser['sale_group_id'];
	            $contact = $this->getServiceLocator()->get('Admin\Model\BcTable')->saveItem($arrParamContact, array('task' => 'contract-change-user'));
	        }
	
		    // Thêm lịch sử hệ thống
			if(!empty($id)) {
			    $arrCheckLogs = array('date', 'price', 'price_promotion', 'contact_id', 'user_id', 'sale_branch_id', 'sale_group_id');
			    $arrCheckResult = array();
			    foreach ($arrCheckLogs AS $field) {
			        if($data[$field] != $arrContract[$field]) {
			            if(isset($data[$field])) {
			                $arrCheckResult[$field] = $data[$field];
			            }
			        }
			    }
			
			    if(!empty($arrCheckResult)) {
			        $arrParamLogs = array(
			            'data' => array(
			                'title'          => 'Hội Đồng Anh',
			                'phone'          => $arrContact['phone'],
			                'name'           => $arrContact['name'],
			                'action'         => 'Chuyển quản lý',
			                'contact_id'     => $arrContact['id'],
			                'contract_id'    => $id,
			                'options'        => $arrCheckResult
			            )
			        );
			        $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
			    }
			}
		     
		    return $id;
		}
	}
	
	public function deleteItem($arrParam = null, $options = null){
	    if($options['task'] == 'delete-item') {
	        $arrData  = $arrParam['data'];
    	    $arrRoute = $arrParam['route'];
    	    $arrItem  = $arrParam['item'];
    	    
    	    $contract_options = !empty($arrItem['options']) ? unserialize($arrItem['options']) : array();
    	    
    	    // Xóa đơn hàng
            $where = new Where();
            $where -> equalTo('id', $arrItem['id']);
            $this -> tableGateway -> delete($where);

            // Xóa toàn bộ hóa đơn của đơn hàng
            $bill_delete = $this->getServiceLocator()->get('Admin\Model\BcBillTable')->deleteItem(array('contract_id' => $arrItem['id']), array('task' => 'contract-delete'));
            
            // Cập nhật lại số bc của liên hệ
            $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $arrItem['contact_id']));
            $contract_total = intval($contact['bc_total']) - 1;
            $contact_data = array('id' => $contact['id'], 'bc_total' => $contract_total);
            if($contract_total <= 0) {
                $contact_data['bc_total'] = 0;
            }
            $contact_update = $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem(array('data' => $contact_data, 'item' => $contact), array('task' => 'edit-item'));
            
            // Thêm lịch sử xóa đơn hàng
            $arrParamLogs = array(
                'data' => array(
                    'title'          => 'Hội Đồng Anh',
                    'phone'          => $contact['phone'],
                    'name'           => $contact['name'],
                    'action'         => 'Xóa',
                    'contact_id'     => $contact['id'],
                    'contract_id'    => $arrItem['id'],
                    'options'        => array(
                        'date'                    => $arrItem['date'],
                        'date_register'           => $arrItem['date_register'],
                        'date_speaking'           => $arrItem['date_speaking'],
                        'price'                   => $arrItem['price'],
                        'price_promotion'         => $arrItem['price_promotion'],
                        'price_promotion_percent' => $arrItem['price_promotion_percent'],
                        'price_promotion_price'   => $arrItem['price_promotion_price'],
                        'promotion_content'       => $contract_options['promotion_content'],
                        'price_total'             => $arrItem['price_total'],
                        'user_id'                 => $arrItem['user_id'],
                        'sale_branch_id'          => $arrItem['sale_branch_id'],
                        'sale_group_id'           => $arrItem['sale_group_id'],
                    )
                )
            );
            $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
            
            $result = 1;
	    }
	
	    return $result;
	}
	
	public function changeStatus($arrParam = null, $options = null){
	    if($options['task'] == 'change-status') {
	        $result = $this->defaultStatus($arrParam, null);
	    }
	    
	    if($options['task'] == 'update-status') {
	        $arrData = $arrParam['data'];
            if(!empty($arrData['id'])) {
    	        $data	= array( $arrData['fields'] => ($arrData['value'] == 1) ? 0 : 1 );
    			$this->tableGateway->update($data, array('id' => $arrData['id']));
    			return true;
            }
    	    
    	    return false;
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
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];
	            $dateFormat = new \ZendX\Functions\Date();
	            
	            $columns = array('date', 'date_register', 'date_speaking', 'price_total', 'price_paid', 'price_accrued', 'user_id', 'sale_branch_id', 'sale_group_id');
	            if(!empty($options['columns'])) {
	                $columns = array_merge($columns, $options['columns']);
	            }
	            $select -> columns($columns)
	                    -> where -> greaterThanOrEqualTo('date', $dateFormat->formatToData($arrData['date_begin']) .' 00:00:00')
	                             -> lessThanOrEqualTo('date', $dateFormat->formatToData($arrData['date_end']) .' 23:59:59');
	            
                if(!empty($arrData['sale_branch_id'])) {
                    $select -> where -> equalTo('sale_branch_id', $arrData['sale_branch_id']);
                }
                if(!empty($arrData['sale_group_id'])) {
                    $select -> where -> equalTo('sale_group_id', $arrData['sale_group_id']);
                }
                if(!empty($arrData['user_id'])) {
                    $select -> where -> equalTo('user_id', $arrData['user_id']);
                }
	        });
	    }
	    
	    if($options['task'] == 'join-date') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];
	            $dateFormat = new \ZendX\Functions\Date();
	            
	            $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_BC .'.contact_id',
        	                array(
        	                    'contact_type' => 'type', 
        	                    'contact_sex' => 'sex', 
        	                    'contact_location_city_id' => 'location_city_id', 
        	                    'contact_location_district_id' => 'location_district_id', 
        	                    'contact_source_group_id' => 'source_group_id', 
        	                    'contact_birthday_year' => 'birthday_year', 
        	                    'contact_product_id' => 'product_id', 
        	                    'contact_options' => 'options'
        	                ), 'inner');
	            $select -> columns(array('date', 'date_register', 'date_speaking', 'options'))
                        -> where -> greaterThanOrEqualTo(TABLE_BC .' .date', $dateFormat->formatToData($arrData['date_begin']) .' 00:00:00')
	                             -> lessThanOrEqualTo(TABLE_BC .' .date', $dateFormat->formatToData($arrData['date_end']) .' 23:59:59');
	            
                 if(!empty($arrData['sale_branch_id'])) {
                     $select -> where -> equalTo(TABLE_BC.' .sale_branch_id', $arrData['sale_branch_id']);
                 }
                 if(!empty($arrData['sale_group_id'])) {
                     $select -> where -> equalTo(TABLE_BC.' .sale_group_id', $arrData['sale_group_id']);
                 }
                 if(!empty($arrData['user_id'])) {
                     $select -> where -> equalTo(TABLE_BC.' .user_id', $arrData['user_id']);
                 }
                 if(!empty($arrData['location_city_id'])) {
                     $select -> where -> equalTo(TABLE_CONTACT.' .location_city_id', $arrData['location_city_id']);
                 }
	        });
	    }
	    return $result;
	}
}





