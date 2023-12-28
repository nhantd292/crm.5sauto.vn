<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class HistoryImportTable extends DefaultTable {	
	public function getItem($arrParam = null, $options = null){
	
		if($options == null) {
			$result	= $this->defaultGet($arrParam, array('by' => 'id'));
		}
	
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];
	    
	    $filter   = new \ZendX\Filter\Purifier();
	    $gid      = new \ZendX\Functions\Gid();
	    
		if($options['task'] == 'add-item') {
			$id = $gid->getId();
			$data	= array(
				'id'                => $id,
				'title'             => $arrData['title'],
		        'note'              => $arrData['note'],
				'created'           => date('Y-m-d H:i:s'),
				'created_by'        => $this->userInfo->getUserInfo('id'),
			);
			
			$this->tableGateway->insert($data);
			return $id;
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
	    
	    return $result;
	}
}