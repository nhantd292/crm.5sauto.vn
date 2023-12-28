<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;

class DynamicTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result = $this->defaultCount($arrParam, null);
	    }
	    
	    return $result;
	}
	
	public function listItem($arrParam = null, $options = null){
		if($options['task'] == 'list-item') {
			$result	= $this->defaultList($arrParam, null);
		}
		
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
	
		if($options == null) {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $select->where->equalTo('id', $arrParam['id']);
    		})->current();
		}
	
		if($options['task'] == 'code') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $select->where->equalTo('code', $arrParam['code']);
    		})->current();
		}
	
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];
	    
	    $gid      = new \ZendX\Functions\Gid();
	    
		if($options['task'] == 'add-item') {
			$id = $gid->getId();
			if($this->userInfo->getUserInfo('id') == '1111111111111111111111') {
    			$data	= array(
    				'id'                => $id,
    				'code'              => $arrData['code'],
    				'name'              => $arrData['name'],
    				'ordering'          => $arrData['ordering'],
    				'status'            => $arrData['status'],
    				'option'            => $arrData['option'],
    				'created'           => date('Y-m-d H:i:s'),
    				'created_by'        => $this->userInfo->getUserInfo('id'),
    			    'permission_ids'    => implode(',', $arrData['permission_ids']),
    			);
			}
			
			$this->tableGateway->insert($data);
			return $id;
		}
		if($options['task'] == 'edit-item') {
		    $id = $arrData['id'];
			$data = array();
			
			if($this->userInfo->getUserInfo('id') == '1111111111111111111111') {
    			if(!empty($arrData['code'])) {
    			    $data['code'] = $arrData['code'];
    			}
    			if(!empty($arrData['name'])) {
    			    $data['name'] = $arrData['name'];
    			}
    			if(!empty($arrData['ordering'])) {
    			    $data['ordering'] = $arrData['ordering'];
    			}
    			if(isset($arrData['status'])) {
    			    $data['status'] = $arrData['status'];
    			}
    			if(!empty($arrData['option'])) {
    			    $data['option'] = $arrData['option'];
    			}
			}
			if(!empty($arrData['permission_ids'])) {
			    $data['permission_ids'] = implode(',', $arrData['permission_ids']);
			}
			
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
}