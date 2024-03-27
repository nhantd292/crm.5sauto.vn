<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class MarketingAdsTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter  = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();

                $date_type = !empty($ssFilter['filter_date_type']) ? $ssFilter['filter_date_type'] : 'from_date';

                if (!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select->where->NEST
                        ->greaterThanOrEqualTo($date_type, $date->formatToData($ssFilter['filter_date_begin']))
                        ->AND
                        ->lessThanOrEqualTo($date_type, $date->formatToData($ssFilter['filter_date_end'] . ' 23:59:59'))
                        ->UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select->where->greaterThanOrEqualTo($date_type, $date->formatToData($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select->where->lessThanOrEqualTo($date_type, $date->formatToData($ssFilter['filter_date_end'] . ' 23:59:59'));
                }
                 
                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select->where->equalTo('status', $ssFilter['filter_status']);
                }
                if(isset($ssFilter['filter_sale_branch']) && $ssFilter['filter_sale_branch'] != '') {
                    $select->where->equalTo('sale_branch_id', $ssFilter['filter_sale_branch']);
                }
                if(isset($ssFilter['filter_marketer_id']) && $ssFilter['filter_marketer_id'] != '') {
                    $select->where->equalTo('marketer_id', $ssFilter['filter_marketer_id']);
                }
                if(isset($ssFilter['filter_product_group_id']) && $ssFilter['filter_product_group_id'] != '') {
                    $select->where->equalTo('product_group_id', $ssFilter['filter_product_group_id']);
                }

                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $select->where->NEST
                        ->like('note', '%'. $ssFilter['filter_keyword'] . '%')
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

                if(!isset($options['paginator']) || $options['paginator'] == true) {
                    $select -> limit($paginator['itemCountPerPage'])
                        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }

                $select -> order(array('from_date' => 'DESC'));
                $date       = new \ZendX\Functions\Date();
                $date_type = !empty($ssFilter['filter_date_type']) ? $ssFilter['filter_date_type'] : 'from_date';

                if (!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select->where->NEST
                        ->greaterThanOrEqualTo($date_type, $date->formatToData($ssFilter['filter_date_begin']))
                        ->AND
                        ->lessThanOrEqualTo($date_type, $date->formatToData($ssFilter['filter_date_end'] . ' 23:59:59'))
                        ->UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select->where->greaterThanOrEqualTo($date_type, $date->formatToData($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select->where->lessThanOrEqualTo($date_type, $date->formatToData($ssFilter['filter_date_end'] . ' 23:59:59'));
                }
    			
    			if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
    			    $select->where->equalTo('status', $ssFilter['filter_status']);
    			}
    			if(isset($ssFilter['filter_sale_branch']) && $ssFilter['filter_sale_branch'] != '') {
    			    $select->where->equalTo('sale_branch_id', $ssFilter['filter_sale_branch']);
    			}
    			if(isset($ssFilter['filter_marketer_id']) && $ssFilter['filter_marketer_id'] != '') {
    			    $select->where->equalTo('marketer_id', $ssFilter['filter_marketer_id']);
    			}
    			if(isset($ssFilter['filter_product_group_id']) && $ssFilter['filter_product_group_id'] != '') {
    			    $select->where->equalTo('product_group_id', $ssFilter['filter_product_group_id']);
    			}
    			
    			if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select->where->NEST
                			      ->like('note', '%'. $ssFilter['filter_keyword'] . '%')
                			      ->or
                			      ->equalTo('id', $ssFilter['filter_keyword'])
                			      ->UNNEST;
    			}
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
                    array('user_name' => 'name', 'user_sale_branch_id' => 'sale_branch_id', 'user_sale_group_id' => 'sale_group_id',), 'inner');

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

                if(!empty($ssFilter['filter_product_group_id'])) {
                    $select->where->equalTo(TABLE_MARKETING_REPORT.'.product_group_id', $ssFilter['filter_product_group_id']);
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

		// check marketer đã được tạo trong ngày chưa
		if($options['task'] == 'marketer-date') {
		    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
		        if(!empty($arrParam['date'])){
                    $select -> where -> equalTo('date', $arrParam['date']);
                }
		        if(!empty($arrParam['marketer_id'])){
                    $select -> where -> equalTo('marketer_id', $arrParam['marketer_id']);
                }
		        if(!empty($arrParam['product_group_id'])){
                    $select -> where -> equalTo('product_group_id', $arrParam['product_group_id']);
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

	    if($options['task'] == 'add-item') {
	        $id = $gid->getId();

	        $data	= array(
	            'id'                => $id,
	            'from_date'         => $date->formatToData($arrData['from_date'], 'Y-m-d'),
	            'to_date'           => $date->formatToData($arrData['to_date'], 'Y-m-d'),
	            'price'             => $number->formatToData($arrData['price']),
	            'sale_branch_id'    => $this->userInfo->getUserInfo('sale_branch_id'),
	            'product_group_id'  => $arrData['product_group_id'],
	            'marketer_id'       => $this->userInfo->getUserInfo('id'),
	            'note'              => $arrData['note'],
	            'created'           => date('Y-m-d H:i:s'),
	            'created_by'        => $this->userInfo->getUserInfo('id'),
                'status'            => 1,
                'ordering'          => 255,
	        );

	        $record = $this->tableGateway->insert($data);
	        if($record){
	            # cập nhật chi phí mkt cho liên hệ
                $from_date          = $data['from_date'];
                $to_date            = $data['to_date'];
                $marketer_id        = $data['marketer_id'];
                $product_group_id   = $data['product_group_id'];

                $sql_count = "select count(id) contact_item from ".TABLE_CONTACT." WHERE date >= '".$from_date."' AND date <= '".$to_date." 23:59:59' AND marketer_id ='".$marketer_id."' AND product_group_id = '".$product_group_id."'";
                $count_contact = $this->tableGateway->getAdapter()->driver->getConnection()->execute($sql_count)->current();
                $contact_count = $count_contact['contact_item'];
                $cost_ads           = (int)($data['price'] / $contact_count);

                $sql_update = "UPDATE ".TABLE_CONTACT." SET cost_ads = ".$cost_ads." WHERE date >= '".$from_date."' 
                AND date <= '".$to_date." 23:59:59' AND marketer_id ='".$marketer_id."' AND product_group_id = '".$product_group_id."'";
                $this->tableGateway->getAdapter()->driver->getConnection()->execute($sql_update);
	        }
            return $id;
	    }

        if($options['task'] == 'edit-item') {
            $id = $arrData['id'];
            $data	= array(
                'from_date'         => $date->formatToData($arrData['from_date'],'Y-m-d'),
                'to_date'           => $date->formatToData($arrData['to_date'],'Y-m-d'),
                'price'             => $number->formatToData($arrData['price']),
                'product_group_id'  => $arrData['product_group_id'],
                'note'              => $arrData['note'],
            );

            $record = $this->tableGateway->update($data, array('id' => $id));
//            if($record){
                # cập nhật chi phí mkt cho liên hệ
                $from_date          = $data['from_date'];
                $to_date            = $data['to_date'];
                $marketer_id        = $arrItem['marketer_id'];
                $product_group_id   = $data['product_group_id'];
                # cập nhật chi phí về 0 cho ác liên hệ
                $sql_reset = "UPDATE ".TABLE_CONTACT." SET cost_ads = 0 WHERE date >= '".$arrItem['from_date']."' 
                AND date <= '".$arrItem['to_date']." 23:59:59' AND marketer_id ='".$arrItem['marketer_id']."' AND product_group_id = '".$arrItem['product_group_id']."'";
                $this->tableGateway->getAdapter()->driver->getConnection()->execute($sql_reset);
                # Đếm số lượng contact chia chi phí ads
                $sql_count = "select count(id) contact_item from ".TABLE_CONTACT." WHERE date >= '".$from_date."' AND date <= '".$to_date." 23:59:59' AND marketer_id ='".$marketer_id."' AND product_group_id = '".$product_group_id."'";
                $count_contact = $this->tableGateway->getAdapter()->driver->getConnection()->execute($sql_count)->current();
                $contact_count = $count_contact['contact_item'];
                $cost_ads           = (int)($data['price'] / $contact_count);
                # cập nhật chi phí ads cho contact
                $sql_update = "UPDATE ".TABLE_CONTACT." SET cost_ads = ".$cost_ads." WHERE date >= '".$from_date."' 
                AND date <= '".$to_date." 23:59:59' AND marketer_id ='".$marketer_id."' AND product_group_id = '".$product_group_id."'";
                $this->tableGateway->getAdapter()->driver->getConnection()->execute($sql_update);
//            }
            return $id;
        }
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

    public function deleteItem($arrParam = null, $options = null){
        if($options['task'] == 'delete-item') {
            $arrData  = $arrParam['data'];

            $where = new Where();
            $where->in('id', $arrData['cid']);
            $where->equalTo('price', 0);
            $result = $this->tableGateway->delete($where);
        }
        return $result;
    }
}