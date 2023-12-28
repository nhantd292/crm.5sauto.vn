<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class MatterTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
                
                $select -> join(TABLE_CONTRACT, TABLE_CONTRACT .'.id = '. TABLE_MATTER .'.contract_id',
                                array(
                                    'contract_product_id'        => 'product_id',
                                    'contract_training_class_id' => 'training_class_id',
                                ), 'inner')
                        -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_CONTRACT .'.contact_id',
                                array(
                                    'contact_name'   => 'name',
                                    'contact_email'  => 'email',
                                    'contact_phone'  => 'phone',
                                ), 'inner')
                        -> order(array('date' => 'DESC'));
                
                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $select->where->NEST
                                    ->like(TABLE_CONTACT .'.name', '%'. $ssFilter['filter_keyword'] . '%')
                                    ->or
                                    ->like(TABLE_CONTACT .'.phone', '%'. $ssFilter['filter_keyword'] . '%')
                                    ->UNNEST;
                }
                
                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select->where->NEST
                                    ->greaterThanOrEqualTo(TABLE_MATTER .'.date', $date->formatToData($ssFilter['filter_date_begin']))
                                    ->and
                                    ->lessThanOrEqualTo(TABLE_MATTER .'.date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                                    ->UNNEST;
                }
                 
                if(!empty($ssFilter['filter_date_begin'])) {
                    $select->where->greaterThanOrEqualTo(TABLE_MATTER .'.date', $date->formatToData($ssFilter['filter_date_begin']));
                }
                 
                if(!empty($ssFilter['filter_date_end'])) {
                    $select->where->lessThanOrEqualTo(TABLE_MATTER .'.date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                }

                if(!empty($ssFilter['filter_company_branch'])) {
                    $select->where->equalTo(TABLE_MATTER .'.company_branch_created', $ssFilter['filter_company_branch']);
                }
                
                if(!empty($ssFilter['filter_user'])) {
                    $select->where->equalTo(TABLE_MATTER .'.created_by', $ssFilter['filter_user']);
                }
            })->count();
	    }
	    
	    return $result;
	}
	
	public function listItem($arrParam = null, $options = null){
		if($options['task'] == 'list-item') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $paginator  = $arrParam['paginator'];
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
                
    			$select -> limit($paginator['itemCountPerPage'])
    			        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage'])
    			        -> join(TABLE_CONTRACT, TABLE_CONTRACT .'.id = '. TABLE_MATTER .'.contract_id',
        			            array(
        			                'contract_product_id'        => 'product_id',
        			                'contract_training_class_id' => 'training_class_id',
        			            ), 'inner')
			            -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_CONTRACT .'.contact_id',
    			                array(
    			                    'contact_name'   => 'name',
    			                    'contact_email'  => 'email',
    			                    'contact_phone'  => 'phone',
    			                ), 'inner')
			            -> order(array('date' => 'DESC'));
    			
    			if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select->where->NEST
                			      ->like(TABLE_CONTACT .'.name', '%'. $ssFilter['filter_keyword'] . '%')
                			      ->or
                			      ->like(TABLE_CONTACT .'.phone', '%'. $ssFilter['filter_keyword'] . '%')
                			      ->UNNEST;
    			}
    			
    			if(!empty($ssFilter['filter_name'])) {
    			    $select->where->equalTo('matter_id', $ssFilter['filter_name']);
    			}
    			
    			if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
    			    $select->where->NEST
                    			    ->greaterThanOrEqualTo(TABLE_MATTER .'.date', $date->formatToData($ssFilter['filter_date_begin']))
                    			    ->and
                    			    ->lessThanOrEqualTo(TABLE_MATTER .'.date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                    			    ->UNNEST;
    			}
    			
    			if(!empty($ssFilter['filter_date_begin'])) {
    			    $select->where->greaterThanOrEqualTo(TABLE_MATTER .'.date', $date->formatToData($ssFilter['filter_date_begin']));
    			}
    			
    			if(!empty($ssFilter['filter_date_end'])) {
    			    $select->where->lessThanOrEqualTo(TABLE_MATTER .'.date', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
    			}
    			
    			if(!empty($ssFilter['filter_company_branch'])) {
    			    $select->where->equalTo(TABLE_MATTER .'.company_branch_created', $ssFilter['filter_company_branch']);
    			}
    			 
    			if(!empty($ssFilter['filter_user'])) {
    			    $select->where->equalTo(TABLE_MATTER .'.created_by', $ssFilter['filter_user']);
    			}
    			
    		});
		}
		
	    if($options['task'] == 'list-ajax') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
    			$select->where->equalTo(TABLE_MATTER .'.contract_id', $arrParam['data']['contract_id']);
    			
    			if(!empty($arrParam['data']['matter_id'])) {
    			    $select->where->notEqualTo(TABLE_MATTER .'.id', $arrParam['data']['matter_id']);
    			}
    		});
		}
	    
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
	
		if($options == null) {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
			    $select -> where -> equalTo('id', $arrParam['id']);
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
	    
		// Thêm vật phẩm trong đơn hàng
		if($options['task'] == 'add-item') {
		    $arrContract  = $arrParam['contract'];
		    $arrContact   = $arrParam['contact'];
		    
		    // Thêm mới vật phẩm
		    foreach ($arrData['matter_ids'] AS $matter) {
		        $gid = new \ZendX\Functions\Gid();
    			$id = $gid->getId();
    			$data	= array(
    			    'id'                     => $id,
    			    'date'                   => $arrData['date'] ? $date->formatToData($arrData['date']) : date('Y-m-d'),
    			    'matter_id'              => $matter,
    			    'contact_id'             => $arrContract['contact_id'],
    			    'contract_id'            => $arrContract['id'],
    			    'branch_id'              => $this->userInfo->getUserInfo('company_branch_id'),
    			    'created'                => date('Y-m-d H:i:s'),
    			    'created_by'             => $this->userInfo->getUserInfo('id'),
    			);
    			$this->tableGateway->insert($data);
		    }
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
	        $arrContract   = $arrParam['contract'];
	        $arrContact    = $arrParam['contact'];
	    
	        $id = $arrItem['id'];
	        $where = new Where();
	        $where->equalTo('id', $id);
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