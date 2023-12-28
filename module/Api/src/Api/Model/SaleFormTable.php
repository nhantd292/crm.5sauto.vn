<?php
namespace Api\Model;

use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SaleFormTable extends AbstractTableGateway implements ServiceLocatorAwareInterface{
	
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
	
	public function countItem($arrParam = null, $options = null){
	    if($options == null) {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];
	            
	            if(!empty($arrRoute['id'])) {
                    $select->where->equalTo('id', $arrRoute['id']);
	            }
	            
	            if(!empty($arrData['data-parent-field'])) {
	                $select->where->equalTo($arrData['data-parent-field'], $arrData['data-parent']);
	            }
	        })->count();
	    }
	    return $result;
	}
	
	public function listItem($arrParam = null, $options = null){
	    if($options == null) {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];
	            
	            if(!empty($arrRoute['id'])) {
                    $select->where->equalTo('id', $arrRoute['id']);
	            }
	            
	            if(!empty($arrData['data-parent-field'])) {
	                $select->where->equalTo($arrData['data-parent-field'], $arrData['data-parent']);
	            }
	            
	            if(!empty($arrData['data-order'])) {
	                $select->order($arrData['data-order']);
	            }
	            
	            if(!empty($arrData['data-where'])) {
	                foreach ($arrData['data-where'] AS $key => $value) {
	                    $select->where->equalTo($key, $value);
	                }
	            }
	        });
	    }
	    
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];
	             
	            if(!empty($arrData['id'])) {
	                $select->where->equalTo('id', $arrData['id']);
	            }
	             
	            if(!empty($arrData['data-parent-field'])) {
	                $select->where->equalTo($arrData['data-parent-field'], $arrData['data-parent']);
	            }
	            
	            if(!empty($arrData['data-where'])) {
	                foreach ($arrData['data-where'] AS $key => $value) {
	                    $select->where->equalTo($key, $value);
	                }
	            }
	        })->toArray();
	    }
	    
	    if($options['task'] == 'import') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];
	             
	            $select -> order(array('id' => 'ASC'));
	            
                $select -> where -> NEST
            	                -> greaterThanOrEqualTo('created', '2010-01-01 00:00:00')
            	                -> and
            	                -> lessThanOrEqualTo('created', '2018-08-31 23:59:59')
            	                -> UNNEST;
	        });
	    }
	    
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
	
		if($options == null) {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
				$select->where->equalTo('id', $arrParam['id']);
			})->current();
		}
		
		if($options['task'] == 'by-phone') {
		    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
		        $select -> where -> equalTo(TABLE_CONTACT .'.phone', $arrParam['phone']);
		    })->current();
		}
	
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $date = new \ZendX\Functions\Date();
	    $number = new \ZendX\Functions\Number();
	     
	    if($options['task'] == 'update-data') {
	        $id = $arrParam['id'];
	        $data = $arrParam['data'];
	        	
	        $this->tableGateway->update($data, array('id' => $id));
	        return $id;
	    }
	}
}