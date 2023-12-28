<?php
namespace Api\Model;

use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class OfflineProductTable extends AbstractTableGateway implements ServiceLocatorAwareInterface{
	
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
	    if($options['task'] == 'public') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];

	        	if(!empty($arrData['columns'])) {
	                $select->columns($arrData['columns']);
	            } else {
	            	$select->columns(array('id', 'code', 'name', 'price'));
	            }
	            
	            $select->where->equalTo('status', 1);
	            
	            if(!empty($arrData['data-where'])) {
	                foreach ($arrData['data-where'] AS $key => $value) {
	                    $select->where->equalTo($key, $value);
	                }
	            }
	        })->toArray();
	    }
	    
	    if($options['task'] == 'cache') {
	    	$cache = $this->getServiceLocator()->get('cache');
	    	$cache_key = 'ApiOfflineProduct';
	    	$result = $cache->getItem($cache_key);
	    
	    	if (empty($result)) {
	    		$items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	    		});
	    		$result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));
	    		 
	    		$cache->setItem($cache_key, $result);
	    	}
	    }
	    
	    if($options['task'] == 'cache-status') {
	    	$cache = $this->getServiceLocator()->get('cache');
	    	$cache_key = 'ApiOfflineProductStatus';
	    	$result = $cache->getItem($cache_key);
	    
	    	if (empty($result)) {
	    		$items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	    			$select -> where -> equalTo('status', 1);
	    		});
	    		$result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));
	    		 
	    		$cache->setItem($cache_key, $result);
	    	}
	    }
	    
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
	
		if($options == null) {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
				$arrData = $arrParam['data'];
				
				if(!empty($arrData['fields'])) {
					$select -> columns($arrData['fields']);
				};
				
				$select -> where -> equalTo('id', $arrData['id']);
			})->current();
		}
		
		return $result;
	}
}