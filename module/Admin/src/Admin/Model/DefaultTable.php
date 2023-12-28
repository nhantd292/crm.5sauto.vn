<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DefaultTable extends AbstractTableGateway implements ServiceLocatorAwareInterface {
	
    protected $tableGateway;
	protected $userInfo;
	protected $serviceLocator;
	
	public function __construct(TableGateway $tableGateway) {
	    $this->tableGateway	= $tableGateway;
	    $this->userInfo	= new \ZendX\System\UserInfo();
	}
	
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
	    $this->serviceLocator = $serviceLocator;
	}
	
	public function getServiceLocator() {
	    return $this->serviceLocator;
	}
	
	public function defaultCount($arrParam = null, $options = null){
	    
        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
            $ssFilter  = $arrParam['ssFilter'];
            
            $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
            
            if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                $select -> where -> equalTo('status', $ssFilter['filter_status']);
            }
            
            if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
		        $select -> where -> like('name', '%'. trim($ssFilter['filter_keyword']) . '%');
			}
        })->current();
	    
	    return $result->count;
	}
	
	public function defaultList($arrParam = null, $options = null){
	    
		$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
            $paginator = $arrParam['paginator'];
            $ssFilter  = $arrParam['ssFilter'];
            
            if(!empty($options['fields'])) {
                $select -> columns($options['fields']);
            }
            
			$select -> limit($paginator['itemCountPerPage'])
			        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
			
			if(!empty($ssFilter['order_by']) && !empty($ssFilter['order'])) {
			    $select -> order(array($ssFilter['order_by'] .' '. strtoupper($ssFilter['order'])));
			}
			
			if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
			    $select->where->equalTo('status', $ssFilter['filter_status']);
			}
			
			if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
		        $select -> where -> like('name', '%'. $ssFilter['filter_keyword'] . '%');
			}
			
		});
		
		return $result;
	}
	
	public function defaultGet($arrParam = null, $options = null){
	
		$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
		    if($options['by'] == 'id') {
                $select->where->equalTo('id', $arrParam['id']);
		    }
		})->current();
	
		return $result;
	}
	
	public function defaultDelete($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];
	    
        $where = new Where();
        $where->in('id', $arrData['cid']);
        $this->tableGateway->delete($where);
        
        $result = count($arrData['cid']);
	
	    return $result;
	}
	
	public function defaultStatus($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];
	    
        if(!empty($arrData['cid'])) {
	        $data	= array( 'status'	=> ($arrData['status'] == 1) ? 0 : 1 );
			$this->tableGateway->update($data, array("id IN('". implode("','", $arrData['cid']) ."')"));
            return true;
        }
	    
	    return false;
	}
	
	public function defaultOrdering($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];
	    
        foreach ($arrData['cid'] AS $id) {
            $data	= array('ordering'	=> $arrData['ordering'][$id]);
            $where  = array('id' => $id);
            $this->tableGateway->update($data, $where);
        }
        
        return count($arrData['cid']);
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];
	     
	    $image    = new \ZendX\Functions\Thumbnail($arrData['image']);
	    $filter   = new \ZendX\Filter\Purifier();
	    $gid      = new \ZendX\Functions\Gid();
	     
	    if($options['task'] == 'add-item') {
	        $id = $gid->getId();
			$data	= array(
				'id'            => $id,
	            'code'          => $arrData['code'],
	            'name'          => $arrData['name'],
	            'ordering'      => $arrData['ordering'],
	            'status'        => $arrData['status'],
	            'created'       => date('Y-m-d H:i:s'),
	            'created_by'    => $this->userInfo->getUserInfo('id'),
	            'modified'      => date('Y-m-d H:i:s'),
	            'modified_by'   => $this->userInfo->getUserInfo('id'),
	        );
	        	
	        $this->tableGateway->insert($data);
	        return $id;
	    }
	
	    if($options['task'] == 'edit-item') {
	        $id = $arrData['id'];
	        $data	= array(
	            'code'          => $arrData['code'],
	            'name'          => $arrData['name'],
	            'ordering'      => $arrData['ordering'],
	            'status'        => $arrData['status'],
	            'modified'      => date('Y-m-d H:i:s'),
	            'modified_by'   => $this->userInfo->getUserInfo('id'),
	        );
	        	
	        $this->tableGateway->update($data, array('id' => $id));
	        return $id;
	    }
	}
	
	public function itemInSelectbox($arrParam = null, $options = null){
	    if($options == null) {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $select    -> order(array('ordering' => 'ASC', 'id' => 'ASC'));
	        })->toArray();
	    }
	
	    if($options['task'] == 'active') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $select    -> order(array('ordering' => 'ASC', 'id' => 'ASC'))
	            -> where->equalTo('status', 1);
	        })->toArray();
	    }
	
	    return $result;
	}
}