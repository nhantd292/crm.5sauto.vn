<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class MarketingReportTable extends DefaultTable {

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
	    if($options['task'] == 'list-item-type') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $date = new \ZendX\Functions\Date();
                $ssFilter  = $arrParam['ssFilter'];

                $select -> join(TABLE_USER, TABLE_USER .'.ID = '. TABLE_MARKETING_REPORT .'.marketer_id',
                    array('user_name' => 'name', 'user_sale_branch_id' => 'sale_branch_id', 'user_sale_group_id' => 'sale_group_id',
                    ), 'inner');

                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select->where->equalTo('status', $ssFilter['filter_status']);
                }

                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo('date', $date->formatToData($ssFilter['filter_date_begin']))
                        ->AND
                        -> lessThanOrEqualTo('date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select -> where -> greaterThanOrEqualTo('date', $date->formatToData($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select -> where -> lessThanOrEqualTo('date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                }

                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $select->where->like(TABLE_USER.'.name', '%'. $ssFilter['filter_keyword'] . '%');
                }

                if(isset($ssFilter['filter_sale_branch']) && $ssFilter['filter_keyword'] != '') {
                    $select->where->like(TABLE_USER.'.sale_branch_id', '%'. $ssFilter['filter_sale_branch'] . '%');
                }

                if(isset($ssFilter['filter_sale_group']) && $ssFilter['filter_keyword'] != '') {
                    $select->where->like(TABLE_USER.'.sale_group_id', '%'. $ssFilter['filter_sale_group'] . '%');
                }

                if(isset($ssFilter['filter_marketer_id']) && $ssFilter['filter_marketer_id'] != '') {
                    $select->where->equalTo(TABLE_MARKETING_REPORT.'.marketer_id', $ssFilter['filter_marketer_id']);
                }

                $select->where->equalTo('type', $ssFilter['filter_type']);
            })->count();
	    }
	    
	    return $result;
	}
	
	public function listItem($arrParam = null, $options = null){

		if($options['task'] == 'list-item') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $paginator = $arrParam['paginator'];
                $ssFilter  = $arrParam['ssFilter'];

                if(!isset($options['paginator']) || $options['paginator'] == true) {
                    $select -> limit($paginator['itemCountPerPage'])
                        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }
    			
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
                $date = new \ZendX\Functions\Date();
                $paginator = $arrParam['paginator'];
                $ssFilter  = $arrParam['ssFilter'];

                $select -> join(TABLE_USER, TABLE_USER .'.ID = '. TABLE_MARKETING_REPORT .'.marketer_id',
                    array('user_name' => 'name', 'user_sale_branch_id' => 'sale_branch_id', 'user_sale_group_id' => 'sale_group_id',
                    ), 'inner');

                if(!isset($options['paginator']) || $options['paginator'] == true) {
                    $select -> limit($paginator['itemCountPerPage'])
                        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }

    			$select -> order(array('date' => 'ASC', 'user_name' => 'ASC' ));

                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo('date', $date->formatToData($ssFilter['filter_date_begin']))
                        ->AND
                        -> lessThanOrEqualTo('date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select -> where -> greaterThanOrEqualTo('date', $date->formatToData($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select -> where -> lessThanOrEqualTo('date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                }

                if(!empty($ssFilter['filter_sale_branch'])) {
                    $select->where->equalTo(TABLE_USER.'.sale_branch_id', $ssFilter['filter_sale_branch']);
                }

                if(!empty($ssFilter['filter_sale_group'])) {
                    $select->where->equalTo(TABLE_USER.'.sale_group_id', $ssFilter['filter_sale_group']);
                }

    			if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select->where->like(TABLE_USER.'.name', '%'. $ssFilter['filter_keyword'] . '%');
    			}

                if(!empty($ssFilter['filter_marketer_id'])) {
                    $select->where->equalTo(TABLE_MARKETING_REPORT.'.marketer_id', $ssFilter['filter_marketer_id']);
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

	public function report($arrParam = null, $options = null){
        // Lấy danh sách theo kiểu taget
		if($options['task'] == 'list-item-type') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $date = new \ZendX\Functions\Date();
                $ssFilter  = $arrParam['ssFilter'];

                $select -> join(TABLE_USER, TABLE_USER .'.ID = '. TABLE_MARKETING_REPORT .'.marketer_id',
                    array('user_name' => 'name', 'user_sale_branch_id' => 'sale_branch_id', 'user_sale_group_id' => 'sale_group_id',
                    ), 'inner');

                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo('date', $date->formatToData($ssFilter['filter_date_begin']))
                        ->AND
                        -> lessThanOrEqualTo('date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select -> where -> greaterThanOrEqualTo('date', $date->formatToData($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select -> where -> lessThanOrEqualTo('date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                }

                if(!empty($ssFilter['filter_sale_branch'])) {
                    $select->where->equalTo(TABLE_USER.'.sale_branch_id', $ssFilter['filter_sale_branch']);
                }

                if(!empty($ssFilter['filter_sale_group'])) {
                    $select->where->equalTo(TABLE_USER.'.sale_group_id', $ssFilter['filter_sale_group']);
                }

                if(!empty($ssFilter['filter_marketer_id'])) {
                    $select->where->equalTo(TABLE_MARKETING_REPORT.'.marketer_id', $ssFilter['filter_marketer_id']);
                }

                $select->where->equalTo('type', $ssFilter['filter_type']);
    		});
		}

		return $result->toArray();
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

		// check marketer đã được tạo trong ngày chưa
		if($options['task'] == 'marketer-date') {
		    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
		        if(!empty($arrParam['date'])){
                    $select -> where -> equalTo('date', $arrParam['date']);
                }
		        if(!empty($arrParam['marketer_id'])){
                    $select -> where -> equalTo('marketer_id', $arrParam['marketer_id']);
                }
		        if(!empty($arrParam['type'])){
                    $select -> where -> equalTo('type', $arrParam['type']);
                }
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
	    
	    if($options['task'] == 'add-all') {
	        $id = $gid->getId();
	        
	        $data	= array(
	            'id'            => $id,
	            'date'          => $date->formatToData($arrData['date']),
	            'month'         => $arrData['month'],
	            'marketer_id'   => $arrData['marketer_id'],
	            'year'          => $arrData['year'],
	            'type'          => $arrData['type'],
	            'created'       => date('Y-m-d H:i:s'),
	            'created_by'    => $this->userInfo->getUserInfo('id'),
	        );
	        	
	        $this->tableGateway->insert($data);
	        return $id;
	    }

	    if($options['task'] == 'add-item') {
	        $id = $gid->getId();

	        $data	= array(
	            'id'            => $id,
	            'date'          => $date->formatToData($arrData['date']),
	            'day'           => $arrData['day'],
	            'month'         => $arrData['month'],
	            'year'          => $arrData['year'],
	            'type'          => $arrData['type'],
	            'marketer_id'   => $arrData['marketer_id'],
	            'params'        => !empty($arrData['params']) ? serialize($arrData['params']): '',
	            'created'       => date('Y-m-d H:i:s'),
	            'created_by'    => $this->userInfo->getUserInfo('id'),
	        );

	        $this->tableGateway->insert($data);
	        return $id;
	    }

	    if($options['task'] == 'edit-item') {
	        $id = $arrData['id'];
            $params = !empty($arrItem['params']) ? unserialize($arrItem['params']) : array();

            foreach ($arrData['params'] as $key => $value) {
                $params[$key] = $value;
            }

	        $data	= array(
	            'params'       => !empty($params) ? serialize($params) : '',
	        );
	        	
	        $this->tableGateway->update($data, array('id' => $id));
	        return $id;
	    }

	    if($options['task'] == 'save-ajax') {
	        $id = $arrData['id'];
            $params = !empty($arrItem['params']) ? unserialize($arrItem['params']) : array();

            foreach ($arrData['params'] as $key => $value) {
                $params[$key] = $value;
            }

	        $data	= array(
	            'params'       => !empty($params) ? serialize($params) : '',
	        );

	        $this->tableGateway->update($data, array('id' => $id));
	        return $id;
	    }

	    // Cập nhật số điện thoại đổ về crm theo giờ từ nguồn (Nhập tay, import, landing page) cho marketer
	    if($options['task'] == 'update-number-phone') {
            $marketer_id = $arrData['marketer_id'];
            $date_time   = $arrData['date'];
            $datex       = substr($date->formatToData($date_time), 0, 10);
            $report_item = $this->getItem(array('date' => $datex, 'marketer_id' => $marketer_id, 'type' => 'mkt_report_day_hour'), array('task' => "marketer-date"));

            if(!empty($report_item)){
                $id = $report_item['id'];
                $params = !empty($report_item['params']) ? unserialize($report_item['params']) : array();

                if($date_time >= $datex.' 00:00:00' && $date_time < $datex.' 09:30:00'){
                    $params['9h30_sdt'] += 1;
                }
                elseif($date_time >= $datex.' 09:30:00' && $date_time < $datex.' 11:00:00'){
                    $params['11h00_sdt'] += 1;
                }
                elseif($date_time >= $datex.' 11:00:00' && $date_time < $datex.' 15:00:00'){
                    $params['15h00_sdt'] += 1;
                }
                elseif($date_time >= $datex.' 15:00:00' && $date_time < $datex.' 17:30:00'){
                    $params['17h30_sdt'] += 1;
                }
                elseif($date_time > $datex.' 17:30:00' && $date_time < $datex.' 22:00:00'){
                    $params['22h00_sdt'] += 1;
                }

                $params['total_sdt'] += 1;

                $data	= array(
                    'params'       => serialize($params),
                );

                $this->tableGateway->update($data, array('id' => $id));
                return $id;
            }
	    }

	    // Cập nhật số điện thoại khi xóa data mkt trong formdata cho marketer
	    if($options['task'] == 'update-number-phone-2') {
            $marketer_id = $arrData['marketer_id'];
            $date_time   = $arrData['date'];
            $datex       = substr($date->formatToData($date_time), 0, 10);
            $report_item = $this->getItem(array('date' => $datex, 'marketer_id' => $marketer_id, 'type' => 'mkt_report_day_hour'), array('task' => "marketer-date"));

            if(!empty($report_item)){
                $id = $report_item['id'];
                $params = !empty($report_item['params']) ? unserialize($report_item['params']) : array();

                if($date_time >= $datex.' 00:00:00' && $date_time < $datex.' 09:30:00'){
                    $params['9h30_sdt'] -= 1;
                    if($params['9h30_sdt'] < 0){
                        $params['9h30_sdt'] = 0;
                    }
                }
                elseif($date_time >= $datex.' 09:30:00' && $date_time < $datex.' 11:00:00'){
                    $params['11h00_sdt'] -= 1;
                    if($params['11h00_sdt'] < 0){
                        $params['11h00_sdt'] = 0;
                    }
                }
                elseif($date_time >= $datex.' 11:00:00' && $date_time < $datex.' 15:00:00'){
                    $params['15h00_sdt'] -= 1;
                    if($params['15h00_sdt'] < 0){
                        $params['15h00_sdt'] = 0;
                    }
                }
                elseif($date_time >= $datex.' 15:00:00' && $date_time < $datex.' 17:30:00'){
                    $params['17h30_sdt'] -= 1;
                    if($params['17h30_sdt'] < 0){
                        $params['17h30_sdt'] = 0;
                    }
                }
                elseif($date_time > $datex.' 17:30:00' && $date_time < $datex.' 22:00:00'){
                    $params['22h00_sdt'] -= 1;
                    if($params['22h00_sdt'] < 0){
                        $params['22h00_sdt'] = 0;
                    }
                }

                $params['total_sdt'] -= 1;
                if($params['total_sdt'] < 0){
                    $params['total_sdt'] = 0;
                }

                $data	= array(
                    'params'       => serialize($params),
                );

                $this->tableGateway->update($data, array('id' => $id));
                return $id;
            }
	    }

	    // Cập nhật doanh thu cho cho marketer khi trạng thái sản xuất thành đã sản xuất.
	    if($options['task'] == 'update-sales-finish') {
	        // lấy ra đơn hàng vừa cập nhật trạng thái đã sản xuất.
	        $contract_id = $arrData['contract_id'];
            $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $contract_id));
            // Lấy ra marketer được hưởng doanh thu từ đơn hàng
	        $contact_id = $contract['contact_id'];
	        $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('id' => $contact_id));
            $marketer_id = $contact['marketer_id'];
            // Lấy báo cáo chỉ tiêu marketing của marketer đó để ghi nhận doanh thu
            $report_item = $this->getItem(array('date' => $contract['date'], 'marketer_id' => $marketer_id, 'type' => 'mkt_target'), array('task' => "marketer-date"));

            if(!empty($report_item)){
                $id = $report_item['id'];
                $params = !empty($report_item['params']) ? unserialize($report_item['params']) : array();
                $params['sales_finish'] += $contract['price_total'];
                $data	= array(
                    'params'       => serialize($params),
                );
                $this->tableGateway->update($data, array('id' => $id));

                return $id;
            }
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