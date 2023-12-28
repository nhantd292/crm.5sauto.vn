<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class BcBillTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
                $number     = new \ZendX\Functions\Number();
                
                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
                
                $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_BC_BILL .'.contact_id', array(), 'inner')
                        -> join(TABLE_BC, TABLE_BC .'.id = '. TABLE_BC_BILL .'.contract_id', array(), 'inner');
                
                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $select -> where -> NEST
                                     -> equalTo(TABLE_CONTACT. '.phone', $ssFilter['filter_keyword'])
                                     ->OR
                                     -> equalTo(TABLE_CONTACT. '.email', $ssFilter['filter_keyword'])
                                     ->OR
                                     -> equalTo(TABLE_BC_BILL. '.code', trim($ssFilter['filter_keyword']))
                                     ->OR
                                     -> equalTo(TABLE_BC_BILL. '.index', $number->formatToNumber($ssFilter['filter_keyword']))
                                     -> UNNEST;
                }
                
    			if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> NEST
                    			     -> greaterThanOrEqualTo(TABLE_BC_BILL .'.'. $ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_begin']))
                    			     ->AND
                    			     -> lessThanOrEqualTo(TABLE_BC_BILL .'.'. $ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                    			     -> UNNEST;
    			} elseif (!empty($ssFilter['filter_date_begin'])) {
    			    $select -> where -> greaterThanOrEqualTo(TABLE_BC_BILL .'.'. $ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_begin']));
    			} elseif (!empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> lessThanOrEqualTo(TABLE_BC_BILL .'.'. $ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
    			}
    			
    			if(isset($ssFilter['filter_type']) && $ssFilter['filter_type'] != '') {
    			    $select -> where -> equalTo(TABLE_BC_BILL .'.type', $ssFilter['filter_type']);
    			}
    			
    			if(isset($ssFilter['filter_bill_type']) && $ssFilter['filter_bill_type'] != '') {
    			    $select -> where -> equalTo(TABLE_BC_BILL .'.bill_type_id', $ssFilter['filter_bill_type']);
    			}
    			
    			if(!empty($ssFilter['filter_sale_branch'])) {
    			    $select -> where -> equalTo(TABLE_BC_BILL .'.sale_branch_id', $ssFilter['filter_sale_branch']);
    			}
    			
    			if(!empty($ssFilter['filter_sale_group'])) {
    			    $select -> where -> equalTo(TABLE_BC_BILL .'.sale_group_id', $ssFilter['filter_sale_group']);
    			}
    			
    			if(!empty($ssFilter['filter_user'])) {
    			    $select -> where -> equalTo(TABLE_BC_BILL .'.user_id', $ssFilter['filter_user']);
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
                
                if(!isset($options['paginator']) || $options['paginator'] == true) {
        			$select -> limit($paginator['itemCountPerPage'])
        			        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }
    			
    			$select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_BC_BILL .'.contact_id', array( 'contact_phone' => 'phone', 'contact_name'  => 'name', 'contact_email' => 'email' ), 'inner')
        			    -> join(TABLE_BC, TABLE_BC .'.id = '. TABLE_BC_BILL .'.contract_id', array( 'contract_date' => 'date', 'contract_date_register' => 'date_register', 'contract_date_speaking' => 'date_speaking' ), 'inner');
    			
    			if(!empty($ssFilter['order_by']) && !empty($ssFilter['order'])) {
    			    $select -> order(array($ssFilter['order_by'] .' '. strtoupper($ssFilter['order'])));
    			}
    			
			    if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $select -> where -> NEST
                                     -> equalTo(TABLE_CONTACT. '.phone', $ssFilter['filter_keyword'])
                                     ->OR
                                     -> equalTo(TABLE_CONTACT. '.email', $ssFilter['filter_keyword'])
                                     ->OR
                                     -> equalTo(TABLE_BC_BILL. '.code', trim($ssFilter['filter_keyword']))
                                     ->OR
                                     -> equalTo(TABLE_BC_BILL. '.index', $number->formatToNumber($ssFilter['filter_keyword']))
                                     -> UNNEST;
                }
                
    			if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> NEST
                    			     -> greaterThanOrEqualTo(TABLE_BC_BILL .'.'. $ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_begin']))
                    			     ->AND
                    			     -> lessThanOrEqualTo(TABLE_BC_BILL .'.'. $ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                    			     -> UNNEST;
    			} elseif (!empty($ssFilter['filter_date_begin'])) {
    			    $select -> where -> greaterThanOrEqualTo(TABLE_BC_BILL .'.'. $ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_begin']));
    			} elseif (!empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> lessThanOrEqualTo(TABLE_BC_BILL .'.'. $ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
    			}
    			
    			if(isset($ssFilter['filter_type']) && $ssFilter['filter_type'] != '') {
    			    $select -> where -> equalTo(TABLE_BC_BILL .'.type', $ssFilter['filter_type']);
    			}
    			
    			if(isset($ssFilter['filter_bill_type']) && $ssFilter['filter_bill_type'] != '') {
    			    $select -> where -> equalTo(TABLE_BC_BILL .'.bill_type_id', $ssFilter['filter_bill_type']);
    			}
    			
    			if(!empty($ssFilter['filter_sale_branch'])) {
    			    $select -> where -> equalTo(TABLE_BC_BILL .'.sale_branch_id', $ssFilter['filter_sale_branch']);
    			}
    			
    			if(!empty($ssFilter['filter_sale_group'])) {
    			    $select -> where -> equalTo(TABLE_BC_BILL .'.sale_group_id', $ssFilter['filter_sale_group']);
    			}
    			
    			if(!empty($ssFilter['filter_user'])) {
    			    $select -> where -> equalTo(TABLE_BC_BILL .'.user_id', $ssFilter['filter_user']);
    			}
    		});
		}
		
	    if($options['task'] == 'list-ajax') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
			    $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_BC_BILL .'.contact_id', array( 'contact_phone' => 'phone', 'contact_name'  => 'name', 'contact_email' => 'email' ), 'inner')
			            -> join(TABLE_BC, TABLE_BC .'.id = '. TABLE_BC_BILL .'.contract_id', array( 'contract_date' => 'date', 'contract_date_register' => 'date_register', 'contract_date_speaking' => 'date_speaking' ), 'inner');
    			$select -> where -> equalTo(TABLE_BC_BILL .'.contract_id', $arrParam['data']['contract_id']);
    			
    			if(!empty($arrParam['data']['bill_id'])) {
    			    $select -> where -> notEqualTo(TABLE_BC_BILL .'.id', $arrParam['data']['bill_id']);
    			}
    		});
		}
		
	    if($options['task'] == 'list-all') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
			    $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_BC_BILL .'.contact_id', array( 'contact_phone' => 'phone', 'contact_name'  => 'name', 'contact_email' => 'email' ), 'inner')
			            -> join(TABLE_BC, TABLE_BC .'.id = '. TABLE_BC_BILL .'.contract_id', array( 'contract_date' => 'date', 'contract_date_register' => 'date_register', 'contract_date_speaking' => 'date_speaking' ), 'inner');
			    
    			if(!empty($arrParam['data']['contract_id'])) {
    			    $select->where->equalTo('contract_id', $arrParam['data']['contract_id']);
    			}
    			
    			if(!empty($arrParam['data']['id'])) {
    			    $select->where->notEqualTo('id', $arrParam['data']['id']);
    			}
    		});
		}
		
	    if($options['task'] == 'by-contract') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
			    $select->where->in('contract_id', $arrParam['data']['contract_ids']);
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
	    
		if($options['task'] == 'add-item') {
		    $arrContract = $arrParam['contract'];
		    $arrContact  = $arrParam['contact'];
		    
			$id = $gid->getId();
			
			$item_options = array();
			$item_options['note'] = $arrData['note'];
			
			$data	= array(
			    'id'                 => $id,
			    'date'               => $arrData['date'] ? $date->formatToData($arrData['date']) : date('Y-m-d'),
			    'code'               => trim($arrData['code']),
			    'paid_price'         => $number->formatToNumber($arrData['paid_price']),
			    'accrued_price'      => $number->formatToNumber($arrData['accrued_price']),
			    'type'               => $arrData['type'],
			    'contract_date'      => $arrContract['date'],
			    'bill_type_id'       => $arrData['bill_type_id'],
			    'contact_id'         => $arrContact['id'],
			    'contract_id'        => $arrContract['id'],
			    'user_id'            => $arrContract['user_id'],
			    'sale_branch_id'     => $arrContract['sale_branch_id'],
			    'sale_group_id'      => $arrContract['sale_group_id'],
			    'options'            => serialize($item_options),
			    'created'            => date('Y-m-d H:i:s'),
			    'created_by'         => $this->userInfo->getUserInfo('id'),
			);
			$this->tableGateway->insert($data);
			
			// Thêm lịch sử hệ thống
			if(!empty($id)) {
			    $arrParamLogs = array(
			        'data' => array(
			            'title'          => 'Hóa đơn',
			            'phone'          => $arrContact['phone'],
			            'name'           => $arrContact['name'],
			            'action'         => 'Thêm mới',
			            'contact_id'     => $arrContact['id'],
			            'contract_id'    => $arrContract['id'],
			            'options'        => array(
			                'date'           => $data['date'],
			                'code'           => trim($arrData['code']),
			                'paid_price'     => $data['paid_price'],
			                'accrued_price'  => $data['accrued_price'],
			                'type'           => $data['type'],
			                'bill_type_id'   => $data['bill_type_id'],
			                'note'           => $item_options['note'],
			            )
			        )
			    );
			    
		        $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
			}
			
			return $id;
		}
		
		if($options['task'] == 'contract-edit-item') {
		    $arrContract  = $arrParam['contract'];
		    $arrContact   = $arrParam['contact'];
		    $arrItem      = $arrParam['item'];
		    
		    $item_options = !empty($arrItem['options']) ? unserialize($arrItem['options']) : array();
		    $item_options['note'] = $arrData['note'];
		    
		    $id = $arrData['id'];
			$data = array();
			
			if(!empty($arrData['date'])) {
			    $data['date'] = $date->formatToData($arrData['date']);
			}
			if(!empty($arrData['bill_type_id'])) {
			    $data['bill_type_id'] = $arrData['bill_type_id'];
			}
			if(!empty($item_options)) {
			    $data['options'] = serialize($item_options);
			}
			if(!empty($arrData['code'])) {
			    $data['code'] = trim($arrData['code']);
			}
			
		    // Phiếu thu
			if($arrItem['type'] == 'Thu') {
			    $data['paid_price'] = $number->formatToData($arrData['paid_price']);
			    
		        // Tham số update đơn hàng
		        $arrParamContract = $arrParam;
		        $arrParamContract['data'] = array();
		        $arrParamContract['data']['id']              = $arrContract['id'];
		        $arrParamContract['data']['price_paid']      = $arrContract['price_paid'] - $arrItem['paid_price'] + $data['paid_price'];
		        $arrParamContract['data']['price_owed']      = $arrContract['price_total'] - $arrParamContract['data']['price_paid'] + $arrContract['price_accrued'];
		        $contract = $this->getServiceLocator()->get('Admin\Model\BcTable')->saveItem($arrParamContract, array('task' => 'edit-item'));
			}
			
			// Phiếu chi
			if($arrData['type'] == 'accrued') {
			    $data['accrued_price'] = $number->formatToData($arrData['accrued_price']);
			    
			    // Tham số update đơn hàng
		        $arrParamContract = $arrParam;
		        $arrParamContract['data'] = array();
		        $arrParamContract['data']['id']              = $arrContract['id'];
		        $arrParamContract['data']['price_accrued']   = $arrContract['price_accrued'] - $arrItem['accrued_price'] + $data['accrued_price'];
		        $arrParamContract['data']['price_owed']      = $arrContract['price_total'] - $arrContract['price_paid'] + $arrParamContract['data']['price_accrued'];
		        $contract = $this->getServiceLocator()->get('Admin\Model\BcTable')->saveItem($arrParamContract, array('task' => 'edit-item'));
			}
			
			// Cập nhật database
			$this->tableGateway->update($data, array('id' => $id));
			
			// Thêm lịch sử hệ thống
			if(!empty($id)) {
		        $arrCheckLogs = array('date', 'code', 'paid_price', 'accrued_price', 'bill_type_id');
		        $arrCheckResult = array();
			    foreach ($arrCheckLogs AS $field) {
			        if(isset($data[$field])) {
    			        $check = $data[$field];
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
        		            'title'          => 'Hóa đơn',
        		            'phone'          => $arrContact['phone'],
        		            'name'           => $arrContact['name'],
        		            'action'         => 'Sửa',
        		            'contact_id'     => $arrContact['id'],
        		            'contract_id'    => $arrContract['id'],
        		            'options'        => $arrCheckResult
        		        )
    		        );
    		        $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
		        }
		    }
			
			return $id;
		}
		
		if($options['task'] == 'update-by-contract') {
		    if(!empty($arrData['contract_id'])) {
		        $data = array();
		        if(!empty($arrData['contract_date'])) {
		            $data['contract_date'] = $date->formatToData($arrData['contract_date']);
		        }
		        if(!empty($data)) {
    		        $where = new Where();
    		        $where->equalTo('contract_id', $arrData['contract_id']);
    		        $this->tableGateway->update($data, $where);
		        }
		    }
		     
		    return $arrData['contract_id'];
		}
		
		if($options['task'] == 'contract-change-user') {
		    if(!empty($arrData['contract_id'])) {
		        $data = array(
		            'user_id'            => $arrData['user_id'],
		            'sale_branch_id'     => $arrData['sale_branch_id'],
		            'sale_group_id'      => $arrData['sale_group_id'],
		        );
		        $where = new Where();
		        $where->equalTo('contract_id', $arrData['contract_id']);
		        $this->tableGateway->update($data, $where);
		    }
		     
		    return $arrData['contract_id'];
		}
	}
	
	public function deleteItem($arrParam = null, $options = null){
	    if($options['task'] == 'delete-item') {
	        $result = $this->defaultDelete($arrParam, null);
	    }
	    
	    if($options['task'] == 'contract-delete') {
	        $where = new Where();
	        $where->equalTo('contract_id', $arrParam['contract_id']);
	        $this->tableGateway->delete($where);
	    
	        $result = $arrParam['contract_id'];
	    }
	    
	    if($options['task'] == 'contract-delete-item') {
	        $arrData       = $arrParam['data'];
	        $arrItem       = $arrParam['item'];
	        $arrContract   = $arrParam['contract'];
	        $arrContact    = $arrParam['contact'];
	         
	        $date          = new \ZendX\Functions\Date();
	        $number        = new \ZendX\Functions\Number();
	        
		    // Cập nhật đơn hàng
			if($arrItem['type'] == 'Thu') {
		        // Tham số update đơn hàng
		        $arrParamContract = $arrParam;
		        $arrParamContract['data'] = array();
		        $arrParamContract['data']['id']              = $arrContract['id'];
		        $arrParamContract['data']['price_paid']      = $arrContract['price_paid'] - $arrItem['paid_price'];
		        $arrParamContract['data']['price_owed']      = $arrContract['price_total'] - $arrParamContract['data']['price_paid'] + $arrContract['price_accrued'];
		        $contract = $this->getServiceLocator()->get('Admin\Model\BcTable')->saveItem($arrParamContract, array('task' => 'edit-item'));
			}
			
			// Cập nhật đơn hàng
			if($arrItem['type'] == 'Chi') {
			    // Tham số update đơn hàng
		        $arrParamContract = $arrParam;
		        $arrParamContract['data'] = array();
		        $arrParamContract['data']['id']              = $arrContract['id'];
		        $arrParamContract['data']['price_accrued']   = $arrContract['price_accrued'] - $arrItem['accrued_price'];
		        $arrParamContract['data']['price_owed']      = $arrContract['price_total'] - $arrContract['price_paid'] + $arrParamContract['data']['price_accrued'];
		        $contract = $this->getServiceLocator()->get('Admin\Model\BcTable')->saveItem($arrParamContract, array('task' => 'edit-item'));
			}
			
			// Xóa hóa đơn
			$id = $arrItem['id'];
            $where = new Where();            $where->equalTo('id', $id);
            $this->tableGateway->delete($where);
            
            // Thêm lịch sử hệ thống
			if(!empty($id)) {
			    $arrCheckResult = array(
			        'index' => $arrItem['index'],
			        'type' => $arrItem['type'],
			        'date' => $arrItem['date'],
			        'paid_price' => $arrItem['paid_price'],
			        'accrued_price' => $arrItem['accrued_price'],
			        'content' => $arrData['content'],
			    );
			    if(!empty($arrData['note_log'])) {
			        $arrCheckResult['note_log'] = $arrData['note_log'];
			    }
			    if(!empty($arrCheckResult)) {
			        $arrParamLogs = array(
			            'data' => array(
			                'title'          => 'Hóa đơn',
			                'phone'          => $arrContact['phone'],
			                'name'           => $arrContact['name'],
			                'action'         => 'Xóa',
			                'contact_id'     => $arrContact['id'],
			                'contract_id'    => $arrContract['id'],
			                'options'        => $arrCheckResult
			            )
			        );
			        $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
			    }
			}
            
            $result = $id;
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
	
	public function report($arrParam = null, $options = null){
	    if($options['task'] == 'date') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];
	            $dateFormat = new \ZendX\Functions\Date();
	            
	            $columns = array('date', 'paid_price', 'accrued_price', 'type', 'contract_date', 'user_id', 'sale_branch_id', 'sale_group_id');
	            if(!empty($options['columns'])) {
	                array_merge($columns, $options['columns']);
	            }
	            
	            $select -> columns($columns)
        	            -> where -> greaterThanOrEqualTo('date', $dateFormat->formatToData($arrData['date_begin']) .' 00:00:00')
        	                     -> lessThanOrEqualTo('date', $dateFormat->formatToData($arrData['date_end']) .' 23:59:59');
	            
                if(!empty($arrData['sale_branch_id'])) {
                    $select -> where -> equalTo('sale_branch_id', $arrData['sale_branch_id']);
                }
	        });
	    }
	    
	    if($options['task'] == 'join-date') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];
	            $dateFormat = new \ZendX\Functions\Date();
	            
	            $columns = array('date', 'paid_price', 'accrued_price', 'type', 'contract_date', 'user_id', 'sale_branch_id', 'sale_group_id');
	            if(!empty($options['columns'])) {
	                array_merge($columns, $options['columns']);
	            }
	            
	            $select -> join(TABLE_BC, TABLE_BC .'.id='. TABLE_BC_BILL .'.contract_id', array('contract_date' => 'date', 'contract_date_register' => 'date_register', 'contract_date_speaking' => 'date_speaking'), 'inner');
	            $select -> columns($columns)
        	            -> where -> greaterThanOrEqualTo(TABLE_BC_BILL .'.date', $dateFormat->formatToData($arrData['date_begin']) .' 00:00:00')
        	                     -> lessThanOrEqualTo(TABLE_BC_BILL .'.date', $dateFormat->formatToData($arrData['date_end']) .' 23:59:59');
	            
                if(!empty($arrData['sale_branch_id'])) {
                    $select -> where -> equalTo(TABLE_BC_BILL .'.sale_branch_id', $arrData['sale_branch_id']);
                }
	        });
	    }
	    
	    return $result;
	}
}