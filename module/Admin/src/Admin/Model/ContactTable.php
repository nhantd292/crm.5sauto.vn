<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class ContactTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
                $number     = new \ZendX\Functions\Number();
                
                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
                
	            if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $filter_keyword = trim($ssFilter['filter_keyword']);
                    if(strlen($number->formatToPhone($filter_keyword)) == 10) {
                        $select -> where -> equalTo('phone', $number->formatToPhone($filter_keyword));
                    } elseif(strlen($number->formatToNumber($filter_keyword)) == 4) {
                        $select -> where -> equalTo('birthday_year', $filter_keyword);
                    } elseif (filter_var($filter_keyword, FILTER_VALIDATE_EMAIL)) {
                        $select -> where -> equalTo('email', $filter_keyword);
                    } else {
                        $select -> where -> NEST
                                         -> like('name', '%'. $filter_keyword .'%')
                                         -> UNNEST;
                    }
                }

    			if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> NEST
                    			     -> greaterThanOrEqualTo($ssFilter['filter_date_type'], $date->formatToSearch($ssFilter['filter_date_begin']))
                    			     ->AND
                    			     -> lessThanOrEqualTo($ssFilter['filter_date_type'], $date->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59')
                    			     -> UNNEST;
    			} elseif (!empty($ssFilter['filter_date_begin'])) {
    			    $select -> where -> greaterThanOrEqualTo($ssFilter['filter_date_type'], $date->formatToSearch($ssFilter['filter_date_begin']));
    			} elseif (!empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> lessThanOrEqualTo($ssFilter['filter_date_type'], $date->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59');
    			}



                if (isset($ssFilter['filter_number_contract']) && $ssFilter['filter_number_contract'] != '' && isset($ssFilter['filter_number_contract2']) && $ssFilter['filter_number_contract2'] != '') {
                    $select->where->NEST
                        ->greaterThanOrEqualTo('contract_number', $ssFilter['filter_number_contract'])
                        ->AND
                        ->lessThanOrEqualTo('contract_number', $ssFilter['filter_number_contract2'])
                        ->UNNEST;
                }
                elseif (isset($ssFilter['filter_number_contract']) && $ssFilter['filter_number_contract'] != '') {
                    $select->where->greaterThanOrEqualTo('contract_number', $ssFilter['filter_number_contract']);
                }
                elseif (isset($ssFilter['filter_number_contract2']) && $ssFilter['filter_number_contract2'] != '') {
                    $select->where->lessThanOrEqualTo('contract_number', $ssFilter['filter_number_contract2']);
                }
    			
	            if(!empty($ssFilter['filter_sale_branch'])) {
    			    $select -> where -> equalTo('sale_branch_id', $ssFilter['filter_sale_branch']);
    			}
    			
    			if(!empty($ssFilter['filter_sale_group'])) {
    			    $select -> where -> equalTo('sale_group_id', $ssFilter['filter_sale_group']);
    			} else {
        			if(!empty($this->userInfo->getUserInfo('sale_group_ids'))){
        			    $select -> where -> in('sale_group_id', explode(',', $this->userInfo->getUserInfo('sale_group_ids')));
        			}
    			}
    			if(!empty($ssFilter['filter_user'])) {
                    $select -> where -> NEST
                        -> equalTo('user_id', $ssFilter['filter_user'])
                        ->Or
                        -> equalTo('care_id', $ssFilter['filter_user'])
                        -> UNNEST;
    			}
    			if(!empty($ssFilter['filter_marketer_id'])) {
    			    $select -> where -> equalTo('marketer_id', $ssFilter['filter_marketer_id']);
    			}
                if(!empty($ssFilter['filter_product_group_id'])) {
                    $select -> where -> equalTo('product_group_id', $ssFilter['filter_product_group_id']);
                }
    			
    			if(!empty($ssFilter['filter_history_result'])) {
    				$select -> where -> like('options', '%'. $ssFilter['filter_history_result'] .'%');
    			}
    			if(!empty($ssFilter['filter_history_type_id'])) {
    				$select -> where -> like('options', '%'. $ssFilter['filter_history_type_id'] .'%');
    			}
    			
    			if(!empty($ssFilter['filter_contact_type'])) {
    			    if($ssFilter['filter_contact_type'] == 'contract') {
    			        $select -> where -> greaterThan('contract_total', 0);
    			    } else {
    			        $select -> where -> equalTo('type', $ssFilter['filter_contact_type']);
    			    }
    			}
    			
    			if(!empty($ssFilter['filter_product_interest'])) {
    			    $select -> where -> equalTo('product_id', $ssFilter['filter_product_interest']);
    			}
			    if(!empty($ssFilter['filter_last_action'])) {
    			    $select -> where -> equalTo('last_history_action_id', $ssFilter['filter_last_action']);
    			}
    			if(!empty($ssFilter['filter_location_city'])) {
    			    $select -> where -> equalTo('location_city_id', $ssFilter['filter_location_city']);
    			}
    			 
    			if(!empty($ssFilter['filter_location_district'])) {
    			    $select -> where -> equalTo('location_district_id', $ssFilter['filter_location_district']);
    			}
    			
    			if(!empty($ssFilter['filter_history_status'])) {
    			    if($ssFilter['filter_history_status'] == 'yes') {
    			        $select -> where -> isNotNull('history_created');
    			    }
    			    if($ssFilter['filter_history_status'] == 'no') {
    			        $select -> where -> isNull('history_created');
    			    }
    			    if($ssFilter['filter_history_status'] == 'return') {
    			        $select -> where -> NEST
    			                         -> lessThan('history_return', date('Y-m-d'))
    			                         ->AND
    			                         -> equalTo('contract_total', 0)
    			                         -> UNNSET;
    			    }
    			}
            })->current();
	    }
	    
	    return $result->count;
	}
	
	public function listItem($arrParam = null, $options = null){
		if($options['task'] == 'list-item') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options) {
                $paginator = $arrParam['paginator'];
                $ssFilter = $arrParam['ssFilter'];
                $date = new \ZendX\Functions\Date();
                $number = new \ZendX\Functions\Number();

                if (!isset($options['paginator']) || $options['paginator'] == true) {
                    $select->limit($paginator['itemCountPerPage'])
                        ->offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }

                if (!empty($ssFilter['order_by']) && !empty($ssFilter['order'])) {
                    $select->order(array($ssFilter['order_by'] . ' ' . strtoupper($ssFilter['order'])));
                }

                if (isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $filter_keyword = trim($ssFilter['filter_keyword']);
                    if (strlen($number->formatToPhone($filter_keyword)) == 10) {
                        $select->where->equalTo('phone', $number->formatToPhone($filter_keyword));
                    } elseif (strlen($number->formatToNumber($filter_keyword)) == 4) {
                        $select->where->equalTo('birthday_year', $filter_keyword);
                    } elseif (filter_var($filter_keyword, FILTER_VALIDATE_EMAIL)) {
                        $select->where->equalTo('email', $filter_keyword);
                    } else {
                        $select->where->NEST
                            ->like('name', '%' . $filter_keyword . '%')
                            ->UNNEST;
                    }
                }

                if (!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select->where->NEST
                        ->greaterThanOrEqualTo($ssFilter['filter_date_type'],
                            $date->formatToSearch($ssFilter['filter_date_begin']))
                        ->AND
                        ->lessThanOrEqualTo($ssFilter['filter_date_type'],
                            $date->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59')
                        ->UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select->where->greaterThanOrEqualTo($ssFilter['filter_date_type'],
                        $date->formatToSearch($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select->where->lessThanOrEqualTo($ssFilter['filter_date_type'],
                        $date->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59');
                }

                if (isset($ssFilter['filter_number_contract']) && $ssFilter['filter_number_contract'] != '' && isset($ssFilter['filter_number_contract2']) && $ssFilter['filter_number_contract2'] != '') {
                    $select->where->NEST
                        ->greaterThanOrEqualTo('contract_number', $ssFilter['filter_number_contract'])
                        ->AND
                        ->lessThanOrEqualTo('contract_number', $ssFilter['filter_number_contract2'])
                        ->UNNEST;
                }
                elseif (isset($ssFilter['filter_number_contract']) && $ssFilter['filter_number_contract'] != '') {
                    $select->where->greaterThanOrEqualTo('contract_number', $ssFilter['filter_number_contract']);
                }
                elseif (isset($ssFilter['filter_number_contract2']) && $ssFilter['filter_number_contract2'] != '') {
                    $select->where->lessThanOrEqualTo('contract_number', $ssFilter['filter_number_contract2']);
                }

    			if(!empty($ssFilter['filter_sale_branch'])) {
    			    $select -> where -> equalTo('sale_branch_id', $ssFilter['filter_sale_branch']);
    			}
    			
    			if(!empty($ssFilter['filter_sale_group'])) {
    			    $select -> where -> equalTo('sale_group_id', $ssFilter['filter_sale_group']);
    			} else {
        			if(!empty($this->userInfo->getUserInfo('sale_group_ids'))){
        			    $select -> where -> in('sale_group_id', explode(',', $this->userInfo->getUserInfo('sale_group_ids')));
        			}
    			}

                if(!empty($ssFilter['filter_user'])) {
                    $select -> where -> NEST
                        -> equalTo('user_id', $ssFilter['filter_user'])
                        ->Or
                        -> equalTo('care_id', $ssFilter['filter_user'])
                        -> UNNEST;
                }
                if(!empty($ssFilter['filter_marketer_id'])) {
                    $select -> where -> equalTo('marketer_id', $ssFilter['filter_marketer_id']);
                }
                if(!empty($ssFilter['filter_product_group_id'])) {
                    $select -> where -> equalTo('product_group_id', $ssFilter['filter_product_group_id']);
                }
    			
    			if(!empty($ssFilter['filter_history_result'])) {
    			    $select -> where -> like('options', '%'. $ssFilter['filter_history_result'] .'%');
    			}
    			if(!empty($ssFilter['filter_history_type_id'])) {
    			    $select -> where -> like('options', '%'. $ssFilter['filter_history_type_id'] .'%');
    			}
    			
			    if(!empty($ssFilter['filter_contact_type'])) {
    			    if($ssFilter['filter_contact_type'] == 'contract') {
    			        $select -> where -> greaterThan('contract_total', 0);
    			    } else {
    			        $select -> where -> equalTo('type', $ssFilter['filter_contact_type']);
    			    }
    			}
    			
			    if(!empty($ssFilter['filter_product_interest'])) {
    			    $select -> where -> equalTo('product_id', $ssFilter['filter_product_interest']);
    			}
			    if(!empty($ssFilter['filter_last_action'])) {
    			    $select -> where -> equalTo('last_history_action_id', $ssFilter['filter_last_action']);
    			}
    			
			    if(!empty($ssFilter['filter_location_city'])) {
    			    $select -> where -> equalTo('location_city_id', $ssFilter['filter_location_city']);
    			}
    			
			    if(!empty($ssFilter['filter_location_district'])) {
    			    $select -> where -> equalTo('location_district_id', $ssFilter['filter_location_district']);
    			}
    			
    			if(!empty($ssFilter['filter_history_status'])) {
    			    if($ssFilter['filter_history_status'] == 'yes') {
    			        $select -> where -> isNotNull('history_created');
    			    }
    			    if($ssFilter['filter_history_status'] == 'no') {
    			        $select -> where -> isNull('history_created');
    			    }
    			    if($ssFilter['filter_history_status'] == 'return') {
    			        $select -> where -> NEST
    			                         -> lessThan('history_return', date('Y-m-d'))
    			                         ->AND
    			                         -> equalTo('contract_total', 0)
    			                         -> UNNSET;
    			    }
    			}
    		});
		}
		
		if($options['task'] == 'search') {
		    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
		        $ssFilter = $arrParam['ssFilter'];
		        $number   = new \ZendX\Functions\Number();
		        
		        $select -> limit(50);
		        
		        if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
		            $filter_keyword = trim($ssFilter['filter_keyword']);
                    $phone_code = explode('***', $filter_keyword);

		            if(count($phone_code) > 1){
                        $select -> where -> NEST
                                            -> like('phone', $phone_code[0] .'%')
                                            ->AND
                                            -> like('phone', '%'. $phone_code[1])
                                            -> UNNEST;
                    }
		            else{
                        if(strlen($number->formatToPhone($filter_keyword)) >= 10) {
                            $select -> where -> equalTo('phone', $number->formatToPhone($filter_keyword));
                        } elseif(strlen($number->formatToNumber($filter_keyword)) == 4) {
                            $select -> where -> equalTo('birthday_year', $filter_keyword);
                        } elseif (filter_var($filter_keyword, FILTER_VALIDATE_EMAIL)) {
                            $select -> where -> equalTo('email', $filter_keyword);
                        } else {
                            $select -> where -> NEST
                                -> like('name', '%'. $filter_keyword .'%')
                                -> UNNEST;
                        }
                    }
		        }
		    });
		}

        if($options['task'] == 'by-item') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $arrData  = $arrParam['data'];
                $dateFormat = new \ZendX\Functions\Date();

                $select -> order('history_created DESC');

                if(!empty($arrData['phone'])) {
                    $select -> where -> equalTo('phone', $arrData['phone']);
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
		
		if($options['task'] == 'by-phone') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
			    if($arrParam['phone']){
                    $select -> where -> equalTo('phone', $arrParam['phone']);
			    }
			    if($arrParam['user_id']){
                    $select -> where -> equalTo('user_id', $arrParam['user_id']);
			    }
			    if($arrParam['sale_branch_id']){
                    $select -> where -> equalTo('sale_branch_id', $arrParam['sale_branch_id']);
			    }

    		})->toArray();
		}

		if($options['task'] == 'check-share-data') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
			    $select -> where -> equalTo('phone', $arrParam['phone']);
			    $select -> where -> equalTo('marketer_id', $arrParam['marketer_id']);
			    $select -> where -> equalTo('user_id', $arrParam['user_id']);
    		})->toArray();
		}
	
		return current($result);
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $arrData       = $arrParam['data'];
	    $arrItem       = $arrParam['item'];
	    $arrRoute      = $arrParam['route'];
	    
	    $dateFormat    = new \ZendX\Functions\Date();
	    $filter        = new \ZendX\Filter\Purifier();
		$gid           = new \ZendX\Functions\Gid();
		$number   = new \ZendX\Functions\Number();
	    
	    // Thêm mới liên hệ - NamNV
		if($options['task'] == 'add-item') {
			$id = $gid->getId();
			
			// Xác định những phần tử lưu vào options
			$item_options = array();
			$item_options['password_status'] = 1;
			if(!empty($arrData['contact_received'])) {
			    $item_options['contact_received'] = $arrData['contact_received'];
			}
			if(!empty($arrData['address'])) {
			    $item_options['address'] = $arrData['address'];
			}
			if(!empty($arrData['facebook'])) {
			    $item_options['facebook'] = $arrData['facebook'];
			}
			if(!empty($arrData['subject_id'])) {
			    $item_options['subject_id'] = $arrData['subject_id'];
			}
			if(!empty($arrData['school_name'])) {
			    $item_options['school_name'] = $arrData['school_name'];
			}
			if(!empty($arrData['major_name'])) {
			    $item_options['major_name'] = $arrData['major_name'];
			}
			if(!empty($arrData['class_name'])) {
			    $item_options['class_name'] = $arrData['class_name'];
			}
			if(!empty($arrData['test_score'])) {
			    $item_options['test_score'] = $arrData['test_score'];
			}
			if(!empty($arrData['note'])) {
			    $item_options['note'] = $arrData['note'];
			}
            if(!empty($arrData['content'])) {
                $item_options['content'] = $arrData['content'];
            }
			if(!empty($arrData['product_ids'])) { // Sản phẩm đã mua
			    $item_options['product_ids'] = $arrData['product_ids'];
			}
			if(!empty($arrData['identify'])) { // Chứng minh thư
			    $item_options['identify'] = $arrData['identify'];
			}
			if(!empty($arrData['history_action_id'])) {
		        $item_options['history_created_by']   = $this->userInfo->getUserInfo('id');
		        $item_options['history_action_id']    = $arrData['history_action_id'];
		        $item_options['history_result_id']    = $arrData['history_result_id'];
		        $item_options['history_type_id']      = $arrData['history_type_id'];
		        $item_options['history_content']      = $arrData['history_content'];
		        $item_options['history_count']        = !empty($item_options['history_count']) ? $item_options['history_count'] + 1 : 1;
			}
			
			$data	= array(
				'id'                    => $id,
			    'date'                  => date('Y-m-d H:i:s'),
				'name'                  => $arrData['name'],
				'phone'                 => !empty($arrData['phone']) ? $arrData['phone'] : date('dmYHis'),
				'location_city_id'      => $arrData['location_city_id'],
				'location_district_id'  => $arrData['location_district_id'],
				'location_town_id'      => $arrData['location_town_id'],
				'address'               => $arrData['address'],
                'marketer_id'           => $arrData['marketer_id'],
                'email'                 => $arrData['email'],
                'password'              => md5('12345678'),
                'sex'                   => $arrData['sex'],
                'birthday_year'         => $arrData['birthday_year'] ? $arrData['birthday_year'] : null,
			    'type'                  => $arrData['type'],
			    'contact_group'         => $arrData['contact_group'],
			    'license_plate'         => $arrData['license_plate'],
				'product_id'            => $arrData['product_id'] ? $arrData['product_id'] : null,
				'product_group_id'      => $arrData['product_group_id'] ? $arrData['product_group_id'] : null,
				'contract_total'        => $arrData['contract_total'] ? $arrData['contract_total'] : 0,
				'contract_number'       => $arrData['contract_number'] ? $arrData['contract_number'] : 0,
				'history_created'       => $arrData['history_action_id'] ? date('Y-m-d H:i:s') : null,
				'history_return'        => $arrData['history_return'] ? $dateFormat->formatToData($arrData['history_return'], 'Y-m-d') : null,
				'user_id'               => $arrData['user_id'] ? $arrData['user_id'] : $this->userInfo->getUserInfo('id'),
				'sale_branch_id'        => $arrData['sale_branch_id'] ? $arrData['sale_branch_id'] : $this->userInfo->getUserInfo('sale_branch_id'),
				'sale_group_id'         => $arrData['sale_group_id'] ? $arrData['sale_group_id'] : $this->userInfo->getUserInfo('sale_group_id'),
			    'options'               => !empty($item_options) ? serialize($item_options) : null,
				'created'               => date('Y-m-d H:i:s'),
				'created_by'            => $this->userInfo->getUserInfo('id'),
			);
		    if(!empty($arrData['birthday'])) {
		        $data['birthday'] = $dateFormat->formatToData($arrData['birthday'], 'Y-m-d');
		        $data['birthday_year'] = $dateFormat->formatToData($arrData['birthday'], 'Y');
			}
			$this->tableGateway->insert($data);
			
			// Thêm lịch sử hệ thống
			if(!empty($id)) {
			    $arrParamLogs = array(
			        'data' => array(
    			        'title'          => 'Liên hệ',
    			        'phone'          => $arrData['phone'],
    			        'name'           => $arrData['name'],
    			        'action'         => 'Thêm mới',
    			        'contact_id'     => $id,
    			        'options'        => array(
    			            'user_id'                => $data['user_id'],
    			            'sale_branch_id'         => $data['sale_branch_id'],
    			            'sale_group_id'          => $data['sale_group_id'],
    			            'location_city_id'       => $data['location_city_id'],
    			            'location_district_id'   => $data['location_district_id'],
    			        )
    			    ) 
			    );
			    $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
			}
			
			return $id;
		}

	    // Thêm mới liên hệ - Khi share data từ data marketing - NhanTD
		if($options['task'] == 'add-data') {
			$id = $gid->getId();
			// Xác định những phần tử lưu vào options
			$item_options = array();

			if(!empty($arrData['note'])) {
			    $item_options['note'] = $arrData['note'];
			}
			if(!empty($arrData['address'])) {
			    $item_options['address'] = $arrData['address'];
			}
			if(!empty($arrData['job'])) {
			    $item_options['job'] = $arrData['job'];
			}
			if(!empty($arrData['content'])) {
			    $item_options['content'] = $arrData['content'];
			}

			$data	= array(
				'id'                    => $id,
			    'date'                  => date('Y-m-d H:i:s'),
				'name'                  => $arrData['name'],
				'phone'                 => $arrData['phone'],
				'sex'                   => $arrData['sex'],
				'location_city_id'      => $arrData['city_id'],
				'location_district_id'  => $arrData['district_id'],
				'user_id'               => $arrData['user_id'] ? $arrData['user_id'] : '',
				'marketer_id'           => $arrData['marketer_id'] ? $arrData['marketer_id'] : '',
				'product_id'            => $arrData['product_id'] ? $arrData['product_id'] : '',
				'product_group_id'      => $arrData['product_group_id'] ? $arrData['product_group_id'] : '',
				'sale_branch_id'        => $arrData['sale_branch_id'] ? $arrData['sale_branch_id'] : '',
				'sale_group_id'         => $arrData['sale_group_id'] ? $arrData['sale_group_id'] : '',
			    'options'               => !empty($item_options) ? serialize($item_options) : null,
				'created'               => date('Y-m-d H:i:s'),
				'created_by'            => $this->userInfo->getUserInfo('id'),
			);
		    if(!empty($arrData['birthday'])) {
		        $data['birthday'] = $dateFormat->formatToData($arrData['birthday'], 'Y-m-d');
		        $data['birthday_year'] = $dateFormat->formatToData($arrData['birthday'], 'Y');
			}
			$this->tableGateway->insert($data);

			// Thêm lịch sử hệ thống
			if(!empty($id)) {
			    $arrParamLogs = array(
			        'data' => array(
    			        'title'          => 'Liên hệ',
    			        'phone'          => $arrData['phone'],
    			        'name'           => $arrData['name'],
    			        'action'         => 'Share từ data MKT',
    			        'contact_id'     => $id,
    			        'options'        => array(
    			            'user_id'                => $data['user_id'],
    			            'sale_branch_id'         => $data['sale_branch_id'],
    			            'sale_group_id'          => $data['sale_group_id'],
    			            'location_city_id'       => $data['city_id'],
    			            'location_district_id'   => $data['district_id'],
    			            'marketer_id'            => $data['marketer_id'],
    			        )
    			    )
			    );
			    $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
			}

			return $id;
		}

	    // Thêm mới liên hệ - Khi import data marketing có sẵn - NhanTD
		if($options['task'] == 'add-item-import-data-marketing') {
			$id = $gid->getId();
			// Xác định những phần tử lưu vào options
			$item_options = array();

			if(!empty($arrData['note'])) {
			    $item_options['note'] = $arrData['note'];
			}
			if(!empty($arrData['address'])) {
			    $item_options['address'] = $arrData['address'];
			}
			if(!empty($arrData['job'])) {
			    $item_options['job'] = $arrData['job'];
			}
			if(!empty($arrData['content'])) {
			    $item_options['content'] = $arrData['content'];
			}

			$data	= array(
				'id'                    => $id,
			    'date'                  => $dateFormat->formatToData($arrData['date'], 'Y-m-d'),
				'name'                  => $arrData['name'],
				'phone'                 => $arrData['phone'],
				'sex'                   => $arrData['sex'],
				'location_city_id'      => $arrData['city_id'],
				'location_district_id'  => $arrData['district_id'],
				'user_id'               => $arrData['sales_id'],
				'marketer_id'           => $arrData['marketer_id'],
				'sale_branch_id'        => $arrData['branch_id'],
				'sale_group_id'         => $arrData['group_id'],
			    'options'               => !empty($item_options) ? serialize($item_options) : null,
				'created'               => date('Y-m-d H:i:s'),
				'created_by'            => $this->userInfo->getUserInfo('id'),
			);
			$this->tableGateway->insert($data);

			// Thêm lịch sử hệ thống
			if(!empty($id)) {
			    $arrParamLogs = array(
			        'data' => array(
    			        'title'          => 'Liên hệ',
    			        'phone'          => $arrData['phone'],
    			        'name'           => $arrData['name'],
    			        'action'         => 'Tạo khi import data MKT có sẵn',
    			        'contact_id'     => $id,
    			        'options'        => array(
    			            'user_id'                => $data['user_id'],
    			            'sale_branch_id'         => $data['sale_branch_id'],
    			            'sale_group_id'          => $data['sale_group_id'],
    			            'location_city_id'       => $data['city_id'],
    			            'location_district_id'   => $data['district_id'],
    			            'marketer_id'            => $data['marketer_id'],
    			        )
    			    )
			    );
			    $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
			}

			return $id;
		}
		// Sửa liên hệ - NamNV
		if($options['task'] == 'edit-item') {
		    $id = $arrData['id'];
		    $data = array();
		    // Xác định những phần tử lưu vào options
			$item_options = !empty($arrItem['options']) ? unserialize($arrItem['options']) : array();
			if(!empty($arrData['contact_received'])) {
			    $item_options['contact_received'] = $arrData['contact_received'];
			}
		    if(isset($arrData['password_status'])) {
                $item_options['password_status']  = $arrData['password_status'];
		    }
		    if(isset($arrData['address'])) {
                $item_options['address']  = $arrData['address'];
		    }
		    if(isset($arrData['facebook'])) {
                $item_options['facebook']  = $arrData['facebook'];
		    }
			if(!empty($arrData['note'])) {
			    $item_options['note'] = $arrData['note'];
			}
            if(!empty($arrData['content'])) {
                $item_options['content'] = $arrData['content'];
            }
		    if(isset($arrData['product_ids'])) { // Sản phẩm đã mua
		        $item_options['product_ids']  = $arrData['product_ids'];
		    }
		    if(!empty($arrData['identify'])) { // Chứng minh thư
		        $item_options['identify'] = $arrData['identify'];
		    }
		    if(!empty($arrData['history_action_id'])) {
                $item_options['history_created_by'] = $this->userInfo->getUserInfo('id');
                $item_options['history_action_id']  = $arrData['history_action_id'];
                $item_options['history_result_id']  = $arrData['history_result_id'];
                $item_options['history_type_id']    = $arrData['history_type_id'];
                $item_options['history_content']    = $arrData['history_content'];
                $item_options['history_count']      = !empty($item_options['history_count']) ? $item_options['history_count'] + 1 : 1;
		    }
		    
		    // Dữ liệu bảng
		    if(!empty($arrData['date'])) {
		        $data['date'] = $dateFormat->formatToData($arrData['date'], 'Y-m-d H:i:s');
		    }
		    if(!empty($arrData['name'])) {
		        $data['name'] = $arrData['name'];
		    }
		    if(!empty($arrData['marketer_id'])) {
		        $data['marketer_id'] = $arrData['marketer_id'];
		    }
		    if(!empty($arrData['phone'])) {
		        if(empty($arrItem['marketer_id'])){
                    $data['phone'] = $arrData['phone'];
		        }
		    }
		    if(isset($arrData['email'])) {
		        $data['email'] = $arrData['email'];
		    }
		    if(!empty($arrData['password'])) {
		        $data['password'] = md5($arrData['password']);
		    }
		    if(!empty($arrData['sex'])) {
		        $data['sex'] = $arrData['sex'];
		    }
		    if(!empty($arrData['license_plate'])) {
		        $data['license_plate'] = $arrData['license_plate'];
		    }
		    if(isset($arrData['birthday'])) {
		        $data['birthday'] = !empty($arrData['birthday']) ? $dateFormat->formatToData($arrData['birthday'], 'Y-m-d') : null;
		        $data['birthday_year'] = !empty($arrData['birthday']) ? $dateFormat->formatToData($arrData['birthday'], 'Y') : null;
		    }
		    if(isset($arrData['birthday_year'])) {
		        $data['birthday_year'] = !empty($arrData['birthday_year']) ? $arrData['birthday_year'] : null;
		    }
		    if(isset($arrData['location_city_id'])) {
		        $data['location_city_id'] = $arrData['location_city_id'];
		    }
		    if(isset($arrData['location_district_id'])) {
		        $data['location_district_id'] = $arrData['location_district_id'];
		    }
		    if(isset($arrData['location_town_id'])) {
		        $data['location_town_id'] = $arrData['location_town_id'];
		    }
		    if(isset($arrData['product_id'])) {
		        $data['product_id'] = $arrData['product_id'];
		    }
		    if(isset($arrData['contract_total'])) {
		        $data['contract_total'] = !empty($arrData['contract_total']) ? $arrData['contract_total'] : 0;
		    }
		    if(isset($arrData['contract_number'])) {
		        $data['contract_number'] = !empty($arrData['contract_number']) ? $arrData['contract_number'] : 0;
		    }
		    if(isset($arrData['contract_price_total'])) {
		        $data['contract_price_total'] = !empty($arrData['contract_price_total']) ? $number->formatToData($arrData['contract_price_total']) : 0;
		    }
		    if(!empty($arrData['history_action_id'])) {
		        $data['history_created'] = date('Y-m-d H:i:s');
		        $data['last_history_action_id'] = $arrData['history_action_id'];
		    }
		    if(!empty($arrData['history_return'])) {
		        $data['history_return'] = $dateFormat->formatToData($arrData['history_return'], 'Y-m-d');
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
		    if(!empty($arrData['type'])) {
		        $data['type'] = $arrData['type'];
		    }
		    if(!empty($arrData['contact_group'])) {
		        $data['contact_group'] = $arrData['contact_group'];
		    }
		    if(!empty($arrData['history_success']) && $arrData['history_success'] == 'true') {
		        $data['history_success'] = $arrItem['history_success'] + 1;
		    }
		    if(!empty($arrData['history_action_id'])) {
		        $data['history_number'] = $arrItem['history_number'] + 1;
		    }
		    if($arrData['history_type_alias'] == DA_CHOT) {
		        if(empty($arrItem['sales_expected'])){
                    $data['sales_expected'] = $number->formatToData($arrData['sales_expected']);
		        }
                $data['latched'] = $arrItem['latched'] + 1;
		    }
		    if(!empty($item_options)) {
		        $data['options'] = serialize($item_options);
		    }
		    $this->tableGateway->update($data, array('id' => $id));
		    
		    // Thêm lịch sử hệ thống
		    if(!empty($id)) {
		        $arrCheckLogs = array('phone', 'name', 'email', 'birthday', 'user_id', 'sale_branch_id', 'sale_group_id', 'location_city_id', 'location_district_id', 'sales_expected', 'latched');
		        $arrCheckResult = array();
		        foreach ($arrCheckLogs AS $field) {
		            if($data[$field] != $arrItem[$field]) {
		                if(isset($data[$field])) {
                            $arrCheckResult[$field] = $data[$field];
		                }
		            }
		        }
		        
		        if(!empty($arrCheckResult)) {
    		        $arrParamLogs = array(
    		            'data' => array(
        		            'title'          => 'Liên hệ',
        		            'phone'          => $arrItem['phone'],
        		            'name'           => $arrItem['name'],
        		            'action'         => 'Sửa',
        		            'contact_id'     => $id,
        		            'options'        => $arrCheckResult
        		        )
    		        );
    		        $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
		        }
		    }
			
			return $id;
		}

		// Cập nhật thông tin đơn hàng đâu tiên cho liên hệ
        if($options['task'] == 'update-contract-first') {
            $contract_id       = $arrParam['contract_id'];
            $contract = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $contract_id));
            $contact  = $this->getItem(array('id' => $contract['contact_id']));
            if(empty($contact['contract_first_date'])){
                $data = [];
                $data['contract_first_date'] = $contract['created'];
                $data['contract_first_code'] = $contract['code'];
                $this->tableGateway->update($data, array('id' => $contract['contact_id']));
                return $contract['contact_id'];
            }
        }

		// Cập nhật thông tin thời gian thành công của đơn hàng đầu tiên
        if($options['task'] == 'update-contract-time-success') {
            $contact_id     = $arrParam['contact_id'];
            $contact  = $this->getItem(array('id' => $contact_id));
            if(empty($contact['contract_time_success'])){
                $data['contract_time_success'] = $arrParam['date_success'];
                $this->tableGateway->update($data, array('id' => $contact_id));
            }
            else if($arrParam['date_success'] < $contact['contract_time_success']) {
                $data['contract_time_success'] = $arrParam['date_success'];
                $this->tableGateway->update($data, array('id' => $contact_id));
            }
        }

		// Đăng ký lại khách hàng kho - NamNV
		if($options['task'] == 'register-store') {
		    $id = $arrItem['id'];
			$data	= array(
				'date'              => date('Y-m-d H:i:s'),
				'user_id'           => $this->userInfo->getUserInfo('id'),
				'sale_branch_id'    => $this->userInfo->getUserInfo('sale_branch_id'),
				'sale_group_id'     => $this->userInfo->getUserInfo('sale_group_id'),
			);
			
			$this->tableGateway->update($data, array('id' => $id));
			
			// Thêm lịch sử hệ thống
			if(!empty($id)) {
			    $arrCheckLogs = array('user_id', 'sale_branch_id', 'sale_group_id');
			    $arrCheckResult = array();
			    foreach ($arrCheckLogs AS $field) {
			        if($data[$field] != $arrItem[$field]) {
			            if(isset($data[$field])) {
			                $arrCheckResult[$field] = $data[$field];
			            }
			        }
			    }
			
			    if(!empty($arrCheckResult)) {
			        $arrParamLogs = array(
			            'data' => array(
			                'title'          => 'Liên hệ',
			                'phone'          => $arrItem['phone'],
			                'name'           => $arrItem['name'],
			                'action'         => 'Nhập kho',
			                'contact_id'     => $arrItem['id'],
			                'options'        => $arrCheckResult
			            )
			        );
			        $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
			    }
			}
			
			return $id;
		}
		
		// Chuyển người quản lý - NamNV
		if($options['task'] == 'change-user') {
		    $arrUser = $arrParam['user'];
		    
		    $contact_ids = explode(',', $arrData['contact_ids']);
		    if(count($contact_ids) > 0) {
    		    $data = array(
    		        'date'            => date('Y-m-d H:i:s'),
    		        'user_id'         => $arrUser['id'],
    		        'sale_branch_id'  => $arrUser['sale_branch_id'],
    		        'sale_group_id'   => $arrUser['sale_group_id'],
    		    );
    		    $where = new Where();
    		    $where->in('id', $contact_ids);
    		    $this->tableGateway->update($data, $where);
    		    
    		    // Thêm lịch sử hệ thống
    		    $arrCheckResult = array(
    		        'contact_ids'     => $contact_ids,
    		        'user_id'         => $arrUser['id'],
    		        'sale_branch_id'  => $arrUser['sale_branch_id'],
    		        'sale_group_id'   => $arrUser['sale_group_id'],
    		    );
    		    $arrParamLogs = array(
		            'data' => array(
    		            'title'          => 'Liên hệ',
    		            'phone'          => null,
    		            'name'           => null,
    		            'action'         => 'Chuyển quản lý',
    		            'contact_id'     => null,
    		            'options'        => $arrCheckResult
    		        )
		        );
    		    $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
		    }
		    	
		    return count($contact_ids);
		}

        // Cập nhật nhân viên marketer và nhóm sản phẩm quan tâm khi data mới đổ về
        if($options['task'] == 'update-new-data') {
            $id = $arrParam['id'];
            $data	= array(
                'marketer_id'       => $arrParam['marketer_id'],
                'product_group_id'  => $arrParam['product_group_id'],
            );

            $result = $this->tableGateway->update($data, array('id' => $id));
            return $result;
        }

        # cập nhật quyền giục đơn cho liên hệ
        if($options['task'] == 'update-care-contact') {
            $id = $arrParam['id'];
            $data	= array(
                'care_id'       => $arrParam['care_id'],
                'care_date'     => date('Y-m-d H:i:s'),
            );

            $result = $this->tableGateway->update($data, array('id' => $id));
            return $result;
        }

        // Cập nhật số lần chăm sóc đã chốt khi xóa lịch sử chăm sóc đã chốt.
        if($options['task'] == 'update-latched') {
            $id = $arrParam['id'];
            $item = $this->getItem(array('id' => $id));
            $data	= array(
                'latched'       => $item['latched'] - 1,
            );

            $result = $this->tableGateway->update($data, array('id' => $id));
            return $result;
        }
		
		// Chuyển người quản lý khi chuyển đơn hàng - NamNV
		if($options['task'] == 'contract-change-user') {
		    if(!empty($arrData['contract_id'])) {
		        $data = array(
		            'user_id'         => $arrData['user_id'],
		            'sale_branch_id'  => $arrData['sale_branch_id'],
		            'sale_group_id'   => $arrData['sale_group_id'],
		        );
		        $where = new Where();
		        $where->equalTo('id', $arrData['id']);
		        $this->tableGateway->update($data, $where);
		    }
		    	
		    return count($contact_ids);
		}
		
        // Chuyển người quản lý khi chuyển nhượng đơn hàng
		if($options['task'] == 'contract-transfer') {
		    if(!empty($arrData['id'])) {
		        $id = $arrData['id'];
		        $data = array(
		            'date'                  => date('Y-m-d H:i:s'),
		            'user_id'               => $arrData['user_id'],
		            'company_branch_id'     => $arrData['company_branch_id'],
		            'company_group_id'      => $arrData['company_group_id'],
		        );
		        $where = new Where();
		        $where->equalTo('id', $id);
		        $this->tableGateway->update($data, $where);
		    }
		     
		    return $id;
		}
		
		// Đổi mật khẩu - NamNV
		if($options['task'] == 'change-password') {
		    $item_options = !empty($arrItem['options']) ? unserialize($arrItem['options']) : array();
		    if(isset($arrData['password_status'])) {
		        $item_options['password_status'] = $arrData['password_status'];
		    }
		
		    $id = $arrData['id'];
		    $data = array(
		        'password'    => md5($arrData['password']),
		        'options'     => serialize($item_options),
		    );
		    
		    $this->tableGateway->update($data, array('id' => $id));
		
		    // Thêm lịch sử hệ thống
		    $arrParamLogs = array(
		        'data' => array(
		            'title'          => 'Liên hệ',
		            'phone'          => $arrItem['phone'],
		            'name'           => $arrItem['name'],
		            'action'         => 'Đổi mật khẩu',
		            'contact_id'     => $arrItem['id'],
		            'options'        => null
		        )
		    );
		    $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
		
		    return $id;
		}
		
		// Import - Insert
		if($options['task'] == 'import-insert') {
			$id = $gid->getId();
			
			// Xác định những phần tử lưu vào options
			$item_options = array();
			if(!empty($arrData['note'])) {
			    $item_options['note'] = $arrData['note'];
			}
			if(!empty($arrData['content'])) {
			    $item_options['content'] = $arrData['content'];
			}
			if(!empty($arrData['address'])) {
			    $item_options['address'] = $arrData['address'];
			}

			$data	= array(
				'id'                    => $id,
			    'date'                  => $dateFormat->formatToData($arrData['date']),
				'name'                  => $arrData['name'],
				'phone'                 => !empty($arrData['phone']) ? $arrData['phone'] : date('dmYHis'),
				'sex'                   => $arrData['sex'] ? $arrData['sex'] : 'khong-xac-dinh',
                'birthday'              => $dateFormat->formatToData($arrData['birthday']),
                'history_return'        => $dateFormat->formatToData($arrData['history_return']),
                'type'                  => $arrData['type'],
                'product_group_id'      => $arrData['product_group_id'],
				'user_id'               => $arrData['user_id'] ? $arrData['user_id'] : '',
				'marketer_id'           => $arrData['marketer_id'] ? $arrData['marketer_id'] : '',
				'sale_branch_id'        => $arrData['sale_branch_id'] ? $arrData['sale_branch_id'] : '',
				'sale_group_id'         => $arrData['sale_group_id'] ? $arrData['sale_group_id'] : '',
			    'options'               => !empty($item_options) ? serialize($item_options) : null,
				'created'               => date('Y-m-d H:i:s'),
				'created_by'            => $this->userInfo->getUserInfo('id'),
			);
			// Lưu thêm 2 trường để khi import nếu mã nhân viên mkt hoặc nv sale chưa có trên hệ thống thì sẽ tạo và cập nhật lại
            $data['mkt_code']              = $arrData['marketer_code'];
            $data['sales_code']            = $arrData['sales_code'];

			$this->tableGateway->insert($data);
			
			// Thêm lịch sử hệ thống
			if(!empty($id)) {
			    $arrParamLogs = array(
			        'data' => array(
    			        'title'          => 'Liên hệ',
    			        'phone'          => $arrData['phone'],
    			        'name'           => $arrData['name'],
    			        'action'         => 'Import',
    			        'contact_id'     => $id,
    			        'options'        => array(
    			            'marketer_id'            => $data['marketer_id'],
    			            'user_id'                => $data['user_id'],
    			            'sale_branch_id'         => $data['sale_branch_id'],
    			            'sale_group_id'          => $data['sale_group_id'],
    			        )
    			    ) 
			    );
			    $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
			}
			
			return $id;
		}
		
		// Import - Update
		if($options['task'] == 'import-update') {
		    $id = $arrData['id'];
		    $data = array();
		    // Xác định những phần tử lưu vào options
		    $item_options = !empty($arrItem['options']) ? unserialize($arrItem['options']) : array();
		    if(isset($arrData['password_status'])) {
		        $item_options['password_status']  = $arrData['password_status'];
		    }
		    if(isset($arrData['address'])) {
		        $item_options['address']  = $arrData['address'];
		    }
		    if(isset($arrData['facebook'])) {
		        $item_options['facebook']  = $arrData['facebook'];
		    }
		    if(isset($arrData['note'])) {
		        $item_options['note']  = $arrData['note'];
		    }
		    if(isset($arrData['product_id'])) { // Sản phẩm quan tâm
		        $item_options['product_id']  = $arrData['product_id'];
		    }
		    if(isset($arrData['product_ids'])) { // Sản phẩm đã mua
		        $item_options['product_ids']  = $arrData['product_ids'];
		    }
		
		    // Dữ liệu bảng
		    if(!empty($arrData['date'])) {
		        $data['date'] = $dateFormat->formatToData($arrData['date'], 'Y-m-d H:i:s');
		    }
		    if(!empty($arrData['name'])) {
		        $data['name'] = $arrData['name'];
		    }
		    if(!empty($arrData['phone'])) {
		        $data['phone'] = $arrData['phone'];
		    }
		    if(isset($arrData['email'])) {
		        $data['email'] = $arrData['email'];
		    }
		    if(!empty($arrData['password'])) {
		        $data['password'] = md5($arrData['password']);
		    }
		    if(!empty($arrData['sex'])) {
		        $data['sex'] = $arrData['sex'];
		    }
		    if(isset($arrData['birthday'])) {
		        $data['birthday'] = !empty($arrData['birthday']) ? $dateFormat->formatToData($arrData['birthday'], 'Y-m-d') : null;
		        $data['birthday_year'] = !empty($arrData['birthday']) ? $dateFormat->formatToData($arrData['birthday'], 'Y') : null;
		    }
		    if(isset($arrData['birthday_year'])) {
		        $data['birthday_year'] = !empty($arrData['birthday_year']) ? $arrData['birthday_year'] : null;
		    }
		    if(isset($arrData['location_city_id'])) {
		        $data['location_city_id'] = $arrData['location_city_id'];
		    }
		    if(isset($arrData['location_district_id'])) {
		        $data['location_district_id'] = $arrData['location_district_id'];
		    }
		    if(!empty($arrData['type'])) {
		        $data['type'] = $arrData['type'];
		    }
		    if(isset($arrData['contract_total'])) {
		        $data['contract_total'] = !empty($arrData['contract_total']) ? $arrData['contract_total'] : null;
		    }
		    if(!empty($arrData['history_created'])) {
		        $data['history_created'] = $dateFormat->formatToData($arrData['history_created'], 'Y-m-d H:i:s');
		    }
		    if(!empty($arrData['history_return'])) {
		        $data['history_return'] = $dateFormat->formatToData($arrData['history_return'], 'Y-m-d');
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
		    if(!empty($item_options)) {
		        $data['options'] = serialize($item_options);
		    }
		
		    $this->tableGateway->update($data, array('id' => $id));
		
		    return $id;
		}
		
		// Cập nhật lại thông tin chăm sóc cuối cùng vào liên hệ - NamNV
		if($options['task'] == 'import-history') {
		    $item_options = !empty($arrItem['options']) ? unserialize($arrItem['options']) : array();
		    $item_options['history_created_by']       = $arrItem['user_id'];
		    $item_options['history_action_id']        = $arrData['history_action_id'];
		    $item_options['history_result_id']        = $arrData['history_result_id'];
		    $item_options['history_content']          = $arrData['history_content'];
		    $item_options['history_count']            = !empty($item_options['history_count']) ? $item_options['history_count'] + 1 : 1;
		
		    $id = $arrData['id'];
		    $data	= array(
		        'history_created'     => $arrData['history_created'] ? $dateFormat->formatToData($arrData['history_created'], 'Y-m-d H:i:s') : date('Y-m-d H:i:s'),
		        'history_return'      => $arrData['history_return'] ? $dateFormat->formatToData($arrData['history_return']) : null,
		        'options'             => serialize($item_options),
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
	
	public function report($arrParam = null, $options = null){
        if($options['task'] == 'date') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $arrData  = $arrParam['data'];
                $dateFormat = new \ZendX\Functions\Date();

                $select ->  where -> greaterThanOrEqualTo(TABLE_CONTACT.'.'.'date', $dateFormat->formatToSearch($arrData['date_begin']) .' 00:00:00')
                    -> lessThanOrEqualTo(TABLE_CONTACT.'.'.'date', $dateFormat->formatToSearch($arrData['date_end']) .' 23:59:59');

                if(!empty($arrData['sale_branch_id'])) {
                    $select -> where -> equalTo(TABLE_CONTACT.'.'.'sale_branch_id', $arrData['sale_branch_id']);
                }
                if(!empty($arrData['sale_group_id'])) {
                    $select -> where -> equalTo(TABLE_CONTACT.'.'.'sale_group_id', $arrData['sale_group_id']);
                }
                if(!empty($arrData['user_id'])) {
                    $select -> where -> equalTo(TABLE_CONTACT.'.'.'user_id', $arrData['user_id']);
                }
                if(!empty($arrData['sale_id'])) {
                    $select -> where -> equalTo(TABLE_CONTACT.'.'.'user_id', $arrData['sale_id']);
                }
                if(!empty($arrData['location_city_id'])) {
                    $select -> where -> equalTo(TABLE_CONTACT.'.'.'location_city_id', $arrData['location_city_id']);
                }
            });
        }

        if($options['task'] == 'join-contract') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $arrData  = $arrParam['data'];
                $dateFormat = new \ZendX\Functions\Date();
                $select -> join(TABLE_CONTRACT, TABLE_CONTRACT .'.contact_id = '. TABLE_CONTACT .'.id',
                    array(
                        'contract_price_total' => 'price_total',
                        'contract_status_id' => 'status_id',
                    ), 'inner');

                $select ->  where -> greaterThanOrEqualTo(TABLE_CONTACT.'.'.'date', $dateFormat->formatToSearch($arrData['date_begin']) .' 00:00:00')
                    -> lessThanOrEqualTo(TABLE_CONTACT.'.'.'date', $dateFormat->formatToSearch($arrData['date_end']) .' 23:59:59');

                if(!empty($arrData['sale_branch_id'])) {
                    $select -> where -> equalTo(TABLE_CONTACT.'.'.'sale_branch_id', $arrData['sale_branch_id']);
                }
                if(!empty($arrData['sale_group_id'])) {
                    $select -> where -> equalTo(TABLE_CONTACT.'.'.'sale_group_id', $arrData['sale_group_id']);
                }
                if(!empty($arrData['user_id'])) {
                    $select -> where -> equalTo(TABLE_CONTACT.'.'.'user_id', $arrData['user_id']);
                }
                if(!empty($arrData['sale_id'])) {
                    $select -> where -> equalTo(TABLE_CONTACT.'.'.'user_id', $arrData['sale_id']);
                }
                if(!empty($arrData['location_city_id'])) {
                    $select -> where -> equalTo(TABLE_CONTACT.'.'.'location_city_id', $arrData['location_city_id']);
                }
            });
        }

        if($options['task'] == 'history-date') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $arrData  = $arrParam['data'];
                $dateFormat = new \ZendX\Functions\Date();

                $select ->  where -> greaterThanOrEqualTo('history_created', $dateFormat->formatToSearch($arrData['date_begin']) .' 00:00:00')
                    -> lessThanOrEqualTo('history_created', $dateFormat->formatToSearch($arrData['date_end']) .' 23:59:59');

                if(!empty($arrData['sale_branch_id'])) {
                    $select -> where -> equalTo('sale_branch_id', $arrData['sale_branch_id']);
                }
                if(!empty($arrData['sale_group_id'])) {
                    $select -> where -> equalTo('sale_group_id', $arrData['sale_group_id']);
                }
                if(!empty($arrData['user_id'])) {
                    $select -> where -> equalTo('user_id', $arrData['user_id']);
                }
                if(!empty($arrData['location_city_id'])) {
                    $select -> where -> equalTo('location_city_id', $arrData['location_city_id']);
                }
//                $select -> where -> greaterThanOrEqualTo('history_number', '2');
            });
        }

        if($options['task'] == 'join-user') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $arrData  = $arrParam['data'];
                $dateFormat = new \ZendX\Functions\Date();

                $select -> join(TABLE_USER, TABLE_USER .'.id = '. TABLE_CONTACT .'.marketer_id',
                    array(
                        'branch_marketer_id' => 'sale_branch_id',
                        'group_marketer_id' => 'sale_group_id',
                    ), 'inner');

                $select ->  where -> greaterThanOrEqualTo('date', $dateFormat->formatToSearch($arrData['date_begin']) .' 00:00:00')
                    -> lessThanOrEqualTo('date', $dateFormat->formatToSearch($arrData['date_end']) .' 23:59:59');

                if(!empty($arrData['sale_branch_id'])) {
                    $select -> where -> equalTo(TABLE_USER .'.sale_branch_id', $arrData['sale_branch_id']);
                }
                if(!empty($arrData['sale_group_id'])) {
                    $select -> where -> equalTo(TABLE_USER .'.sale_group_id', $arrData['sale_group_id']);
                }
                if(!empty($arrData['marketer_id'])) {
                    $select -> where -> equalTo('marketer_id', $arrData['marketer_id']);
                }
            });
        }

        if($options['task'] == 'query') {
            if(!empty($arrParam['sql'])){
                $result = $this->tableGateway->getAdapter()->driver->getConnection()->execute($arrParam['sql']);
            }
        }

	    return $result;
	}
}