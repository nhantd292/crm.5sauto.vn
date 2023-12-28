<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class SalesTargetTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter  = $arrParam['ssFilter']; 
                 
                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select->where->equalTo('status', $ssFilter['filter_status']);
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
		if($options['task'] == 'list-item') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $paginator = $arrParam['paginator'];
                $ssFilter  = $arrParam['ssFilter'];
                
    			$select -> limit($paginator['itemCountPerPage'])
    			        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
    			
    			$select -> order(array('year' => 'DESC', 'month' => 'DESC'));
    			
    			if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
    			    $select->where->equalTo('status', $ssFilter['filter_status']);
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

        // Lấy danh sách theo kiểu taget
		if($options['task'] == 'list-item-type') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $paginator = $arrParam['paginator'];
                $ssFilter  = $arrParam['ssFilter'];

    			$select -> limit($paginator['itemCountPerPage'])
    			        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);

    			$select -> order(array('year' => 'DESC', 'month' => 'DESC'));

    			if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
    			    $select->where->equalTo('status', $ssFilter['filter_status']);
    			}

    			if(isset($ssFilter['filter_year'])) {
    			    $select->where->equalTo('year', $ssFilter['filter_year']);
    			}

    			if(isset($ssFilter['filter_month'])) {
    			    $select->where->equalTo('month', $ssFilter['filter_month']);
    			}

    			if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select->where->NEST
                			      ->like('name', '%'. $ssFilter['filter_keyword'] . '%')
                			      ->or
                			      ->equalTo('id', $ssFilter['filter_keyword'])
                			      ->UNNEST;
    			}

                $select->where->equalTo('type', $ssFilter['filter_type']);

    		});
		}
		
	    if($options['task'] == 'cache') {
	        $cache = $this->getServiceLocator()->get('cache');
	        $cache_key = 'AdminSaleTarget';
	        $result = $cache->getItem($cache_key);
	         
	        if (empty($result)) {
	            $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	                $select->order(array('ordering' => 'ASC', 'name' => 'ASC'));
	            });
                $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));
                 
                $cache->setItem($cache_key, $result);
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
		
		if($options['task'] == 'month-year') {
		    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
		        $select -> where -> equalTo('month', $arrParam['month'])
		                         -> equalTo('year', $arrParam['year']);
		    })->current();
		}
		
		return $result;
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
	        $id = $gid->getId();
	        
	        $params = array();
	        if(!empty($arrData['params'])) {
	            foreach ($arrData['params'] AS $key => $val) {
	                if(!empty($val)) {
	                    foreach ($val AS $k => $v) {
	                        $params[$key][$k] = $number->formatToData($v);
	                    }
	                }
	            }
	        }
	        
	        $data	= array(
	            'id'            => $id,
	            'date'          => $date->formatToData($arrData['date']),
	            'day'           => $arrData['day'],
	            'month'         => $arrData['month'],
	            'year'          => $arrData['year'],
	            'name'          => $arrData['name'],
	            'type'          => $arrData['type'],
	            'params'        => serialize($params),
	            'created'       => date('Y-m-d H:i:s'),
	            'created_by'    => $this->userInfo->getUserInfo('id'),
	        );
	        	
	        $this->tableGateway->insert($data);
	        return $id;
	    }
	    
	    if($options['task'] == 'edit-item') {
	        $id = $arrData['id'];
	        
	        $params = array();
	        if(!empty($arrData['params'])) {
	            foreach ($arrData['params'] AS $key => $val) {
	                if(!empty($val)) {
	                    foreach ($val AS $k => $v) {
	                        $params[$key][$k] = $number->formatToData($v);
	                    }
	                }
	            }
	        }
	        
	        $data	= array(
	            'date'         => $date->formatToData($arrData['date']),
	            'day'          => $arrData['day'],
	            'month'        => $arrData['month'],
	            'year'         => $arrData['year'],
	            'name'         => $arrData['name'],
	            'params'       => serialize($params),
	        );
	        	
	        $this->tableGateway->update($data, array('id' => $id));
	        return $id;
	    }
	}
	
	public function deleteItem($arrParam = null, $options = null){
	    if($options['task'] == 'delete-item') {
	        $result = $this->defaultDelete($arrParam, null);
	    }
	    
	    if($options['task'] == 'contract-delete-item') {
	        $arrData       = $arrParam['data'];
	        $arrItem       = $arrParam['item'];
	        $arrRoute      = $arrParam['route'];
	        $arrContract   = $arrParam['contract'];
	        $arrContact    = $arrParam['contact'];
	        $arrItem       = $arrParam['item'];
	         
	        $date          = new \ZendX\Functions\Date();
	        $number        = new \ZendX\Functions\Number();
	        
		    // Lấy các tham số dữ liệu nguồn
		    $bill_type            = array('paid' => 'Thu', 'accrued' => 'Chi', 'surcharge' => 'Phụ phí');
		    $paid_type_id         = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "table" => "document", "where" => array( "code" => "bill-type-paid" ), "order" => array("ordering" => "ASC", "created" => "ASC", "name" => "ASC"), "view"  => array( "key" => "id", "value" => "name", "sprintf" => "%s" ) ), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name'));
		    $accrued_type_id      = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "table" => "document", "where" => array( "code" => "bill-type-accrued" ), "order" => array("ordering" => "ASC", "created" => "ASC", "name" => "ASC"), "view"  => array( "key" => "id", "value" => "name", "sprintf" => "%s" ) ), array('task' => 'cache')), array('key' => 'id', 'value' => 'name'));
		    $surcharge_type_id    = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "table" => "document", "where" => array( "code" => "bill-type-surcharge" ), "order" => array("ordering" => "ASC", "created" => "ASC", "name" => "ASC"), "view"  => array( "key" => "id", "value" => "name", "sprintf" => "%s" ) ), array('task' => 'cache')), array('key' => 'id', 'value' => 'name'));
		    
		    $id = $arrItem['id'];
			
		    // Phụ phí
			if($arrItem['type'] == 'surcharge') {
			    // Tham số update đơn hàng
			    $arrParamContract = $arrParam;
			    $arrParamContract['data'] = array();
			    $arrParamContract['data']['id']              = $arrContract['id'];
			    $arrParamContract['data']['price_surcharge'] = $arrContract['price_surcharge'] - $arrItem['paid_price'];
			    $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($arrParamContract, array('task' => 'edit-item'));
			}
			
		    // Phiếu thu
			if($arrItem['type'] == 'paid') {
		        // Tham số update đơn hàng
		        $arrParamContract = $arrParam;
		        $arrParamContract['data'] = array();
		        $arrParamContract['data']['id']              = $arrContract['id'];
		        $arrParamContract['data']['price_paid']      = $arrContract['price_paid'] - $arrItem['paid_price'];
		        $arrParamContract['data']['price_owed']      = $arrContract['price_owed'] + $arrItem['paid_price'];
		        $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($arrParamContract, array('task' => 'edit-item'));
			}
			
			// Phiếu chi
			if($arrItem['type'] == 'accrued') {
			    // Tham số update đơn hàng
		        $arrParamContract = $arrParam;
		        $arrParamContract['data'] = array();
		        $arrParamContract['data']['id']              = $arrContract['id'];
		        $arrParamContract['data']['price_accrued']   = $arrContract['price_accrued'] - $arrItem['accrued_price'];
		        $arrParamContract['data']['price_owed']      = $arrContract['price_owed'] - $arrItem['accrued_price'];
		        $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($arrParamContract, array('task' => 'edit-item'));
			}
			
			// Thêm lịch sử hệ thống
			if(!empty($id)) {
			    $arrParamLogs     = $arrParam;
			    
			    $old_date         = $arrItem['date'];
			    $old_number       = $arrItem['paid_number'] ? $arrItem['paid_number'] : $arrItem['accrued_number'];
			    $old_price        = $arrItem['paid_price'] ? $arrItem['paid_price'] : $arrItem['accrued_price'];
			    $old_type         = $bill_type[$arrItem['type']];
			    $old_type_detail  = '';
			    
			    if(!empty($arrItem['paid_type_id'])) {
			        if(!empty($arrItem['surcharge_type_id'])) {
			            $logsTitle   = 'Xóa phụ phí';
			            $old_type_detail = $surcharge_type_id[$arrItem['surcharge_type_id']];
			        } else {
    			        $logsTitle   = 'Xóa phiếu thu';
    			        $old_type_detail = $paid_type_id[$arrItem['paid_type_id']];
			        }
			    } elseif(!empty($arrItem['accrued_type_id'])) {
			        $logsTitle   = 'Xóa phiếu chi';
			        $old_type_detail = $accrued_type_id[$arrItem['accrued_type_id']];
			    }
			    
			    $logsContent .= 'THÔNG TIN<br>';
			    $logsContent .= 'Ngày: '. $old_date .'<br>';
			    $logsContent .= 'Số phiếu: '. $old_number .'<br>';
			    $logsContent .= 'Số tiền: '. $old_price .'<br>';
			    $logsContent .= 'Loại: '. $old_type .'<br>';
			    $logsContent .= 'Chi tiết: '. $old_type_detail .'<br><br>';
			    
			    $logsContent .= 'NỘI DUNG<br>';
			    $logsContent .= $arrData['content'] .'<br>';
			    
			    $arrParamLogs['data'] = array(
			        'title'          => $logsTitle,
			        'phone'          => $arrContact['phone'],
			        'name'           => $arrContact['name'],
			        'action'         => 'Sửa',
			        'content'        => $logsContent,
			        'contact_id'     => $arrContract['contact_id'],
			        'contract_id'    => $arrContract['id'],
			    );
			    $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
			}
    	    
            $where = new Where();            $where->equalTo('id', $id);
            $this->tableGateway->delete($where);
            
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
}