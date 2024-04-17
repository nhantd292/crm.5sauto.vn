<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;

class ContractFeeTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter   = $arrParam['ssFilter'];
                
                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));

                $select -> join(TABLE_CONTRACT, TABLE_CONTRACT .'.id = '. TABLE_CONTRACT_FEE .'.contract_id',
                    array(
                        'contract_code' => 'code',
                    ), 'inner');

                if(!empty($ssFilter['filter_date'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT_FEE .'.date', $ssFilter['filter_date']);
                }
                if(!empty($ssFilter['filter_contract_id'])) {
                    $select -> where -> equalTo(TABLE_CONTRACT_FEE .'.contract_id', $ssFilter['filter_contract_id']);
                }
                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $filter_keyword = trim($ssFilter['filter_keyword']);
                    $select -> where -> NEST
                        -> like(TABLE_CONTRACT. '.code', '%'. $filter_keyword .'%')
                        -> UNNEST;
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
				$userInfo = new \ZendX\System\UserInfo();
				$permission = $userInfo->getPermissionOfUser();

                if(!isset($options['paginator']) || $options['paginator'] == true) {
                    $select -> limit($paginator['itemCountPerPage'])
                        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }

                $select -> join(TABLE_CONTRACT, TABLE_CONTRACT .'.id = '. TABLE_CONTRACT_FEE .'.contract_id',
                    array(
                        'contract_code' => 'code',
                    ), 'inner');
                $select -> order(array(TABLE_CONTRACT .'.code' => 'DESC'));

                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $filter_keyword = trim($ssFilter['filter_keyword']);
                    $select -> where -> NEST
                        -> like(TABLE_CONTRACT. '.code', '%'. $filter_keyword .'%')
                        -> UNNEST;
                }
    		});
		}

        if($options['task'] == 'list-query') {
            $result = $this->tableGateway->getAdapter()->driver->getConnection()->execute($arrParam['query']);
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
        $arrData = $arrParam['data'];
	    $gid     = new \ZendX\Functions\Gid();
        $contract_id = $arrData['contract_id'];

        if($options['task'] == 'add-item') {
            $id = $gid->getId();
            $data = array(
                'id'            => $id,
                'contract_id'   => $contract_id,
                'date'          => $arrData['date'],
                'fee'           => $arrData['fee'],

                'created'       => date('Y-m-d H:i:s'),
                'created_by'    => $this->userInfo->getUserInfo('id'),
            );

            $this->tableGateway->insert($data);
            return $id;
        }

        if($options['task'] == 'delete_product_by_contract_id') {
            $sql_delete = "DELETE FROM ".TABLE_CONTRACT_FEE." WHERE ".TABLE_CONTRACT_FEE.".contract_id = '".$contract_id."'";
            $this->tableGateway->getAdapter()->driver->getConnection()->execute($sql_delete);
        }
	}
}





