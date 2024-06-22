<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class ZaloNotifyConfigTable extends DefaultTable {

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
                        ->equalTo('code', $ssFilter['filter_keyword'])
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

                $select -> order(array('ordering' => 'ASC'));

    			
    			if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
    			    $select->where->equalTo('status', $ssFilter['filter_status']);
    			}
    			
    			if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select->where->NEST
                			      ->like('name', '%'. $ssFilter['filter_keyword'] . '%')
                			      ->or
                			      ->equalTo('code', $ssFilter['filter_keyword'])
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
	    $arrItem  = $arrParam['item'];
	    $arrRoute = $arrParam['route'];
	    
	    $date     = new \ZendX\Functions\Date();
	    $number   = new \ZendX\Functions\Number();
	    $filter   = new \ZendX\Filter\Purifier();
	    $gid      = new \ZendX\Functions\Gid();
        $sale_branch_ids     = $arrData['sale_branch_ids'] ? implode(',', $arrData['sale_branch_ids']) : '';
	    if($options['task'] == 'add-item') {
	        $id = $gid->getId();

	        $data	= array(
	            'id'                => $id,
	            'name'              => $arrData['name'],
	            'code'              => $arrData['code'],
	            'sale_branch_ids'   => $sale_branch_ids,
	            'template_id'       => $arrData['template_id'],
	            'order_status'      => $arrData['order_status'],
                'note'              => $arrData['note'],
	            'created'           => date('Y-m-d H:i:s'),
	            'created_by'        => $this->userInfo->getUserInfo('id'),
                'status'            => 1,
                'ordering'          => 255,
	        );

	        $this->tableGateway->insert($data);
            return $id;
	    }

        if($options['task'] == 'edit-item') {
            $id = $arrData['id'];
            $data	= array(
                'name'             => $arrData['name'],
                'code'             => $arrData['code'],
                'sale_branch_ids'  => $sale_branch_ids,
                'template_id'      => $arrData['template_id'],
                'order_status'     => $arrData['order_status'],
                'note'             => $arrData['note'],
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