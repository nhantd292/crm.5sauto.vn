<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class ZaloNotifyResultTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter  = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
                $date_type  = 'created';
                
                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo($date_type, $date->formatToSearch($ssFilter['filter_date_begin']))
                        ->AND
                        -> lessThanOrEqualTo($date_type, $date->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select -> where -> greaterThanOrEqualTo($date_type, $date->formatToSearch($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select -> where -> lessThanOrEqualTo($date_type, $date->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59');
                }

                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select->where->equalTo('status', $ssFilter['filter_status']);
                }

                if($ssFilter['filter_error'] == 'success') {
                    $select->where->equalTo('result_error', '0');
                }
                if($ssFilter['filter_error'] == 'error') {
                    $select->where->notEqualTo('result_error', '0');
                }

                if(isset($ssFilter['filter_result_error'])  && $ssFilter['filter_result_error'] != '') {
                    $select->where->EqualTo('result_error', $ssFilter['filter_result_error']);
                }

                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $select->where->NEST
                        ->like('template_data', '%'. $ssFilter['filter_keyword'] . '%')
                        ->or
                        ->equalTo('phone', $ssFilter['filter_keyword'])
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
                $date       = new \ZendX\Functions\Date();
                $date_type  = 'created';

                if(!isset($options['paginator']) || $options['paginator'] == true) {
                    $select -> limit($paginator['itemCountPerPage'])
                        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }

                $select -> order(array('created' => 'DESC'));
                
                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo($date_type, $date->formatToSearch($ssFilter['filter_date_begin']))
                        ->AND
                        -> lessThanOrEqualTo($date_type, $date->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select -> where -> greaterThanOrEqualTo($date_type, $date->formatToSearch($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select -> where -> lessThanOrEqualTo($date_type, $date->formatToSearch($ssFilter['filter_date_end']) . ' 23:59:59');
                }
    			
    			if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
    			    $select->where->equalTo('status', $ssFilter['filter_status']);
    			}

    			if($ssFilter['filter_error'] == 'success') {
    			    $select->where->equalTo('result_error', '0');
    			}
    			if($ssFilter['filter_error'] == 'error') {
    			    $select->where->notEqualTo('result_error', '0');
    			}

    			if(isset($ssFilter['filter_result_error'])  && $ssFilter['filter_result_error'] != '') {
    			    $select->where->EqualTo('result_error', $ssFilter['filter_result_error']);
    			}

                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $select->where->NEST
                        ->like('template_data', '%'. $ssFilter['filter_keyword'] . '%')
                        ->or
                        ->equalTo('phone', $ssFilter['filter_keyword'])
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
	
	public function getItem($arrParam = null, $options = null){
	
		if($options == null) {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
			    $select -> where -> equalTo('id', $arrParam['id']);
    		})->current();
		}

        if($options['task'] == 'code') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
			    $select -> where -> equalTo('code', $arrParam['code']);
                if(!empty($arrParam['status'])) {
                    $select -> where -> equalTo('status', $arrParam['status']);
                }
                if(!empty($arrParam['sale_branch_id'])) {
                    $select -> where -> like('sale_branch_ids', '%'.$arrParam['sale_branch_id'].'%');
                }

    		})->current();
		}
		
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $res      = $arrParam['res'];
	    $arrItem  = $arrParam['item'];
	    $arrRoute = $arrParam['route'];
	    
	    $date     = new \ZendX\Functions\Date();
	    $number   = new \ZendX\Functions\Number();
	    $filter   = new \ZendX\Filter\Purifier();
	    $gid      = new \ZendX\Functions\Gid();
	    if($options['task'] == 'add-auto') {
	        $id = $gid->getId();

	        $data	= array(
	            'id'                => $id,
	            'phone'             => $arrData['phone'],
                'template_id'       => $arrData['template_id'],
                'template_data'     => serialize($arrData['template_data']),
	            'result_error'      => $res['error'],
	            'result_message'    => $res['message'],
                'result_data'       => isset($res['data']) ? serialize($res['data']) : '',
	            'created'           => date('Y-m-d H:i:s'),
	            'created_by'        => '2222222222222222222222',
                'status'            => 1,
                'ordering'          => 255,
	        );

	        $this->tableGateway->insert($data);
            return $id;
	    }

        if($options['task'] == 'update-item') {
            $id = $arrItem['id'];
            $data	= array(
                'phone'             => $arrData['phone'],
                'template_id'       => $arrData['template_id'],
                'template_data'     => serialize($arrData['template_data']),
                'result_error'      => $res['error'],
                'result_message'    => $res['message'],
                'result_data'       => isset($res['data']) ? serialize($res['data']) : '',
            );

            $this->tableGateway->update($data, array('id' => $id));
            return $id;
        }

        if($options['task'] == 'edit-item') {
            $id = $arrData['id'];
            $data	= array(
                'template_data' => serialize($arrData['template_data']),
            );

            $this->tableGateway->update($data, array('id' => $id));
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
            $result = $this->tableGateway->delete($where);
        }
        return $result;
    }
}