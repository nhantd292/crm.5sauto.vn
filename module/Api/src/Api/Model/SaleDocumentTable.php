<?php
namespace Api\Model;

use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SaleDocumentTable extends AbstractTableGateway implements ServiceLocatorAwareInterface {

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
    
	public function listItem($arrParam = null, $options = null){
		
	    if($options['task'] == 'cache') {
	    	if(!empty($arrParam['where']['code'])) {
		        $cache = $this->getServiceLocator()->get('cache');
		        $cache_key = 'ApiSale'. $arrParam['where']['code'];
		        $result = $cache->getItem($cache_key);
		         
		        if (empty($result)) {
		            $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
		                if(!empty($arrParam['order'])) {
	                        $select->order($arrParam['order']);
		                }
		                if(!empty($arrParam['where'])) {
		                    foreach ($arrParam['where'] AS $key => $value) {
		                        if(!empty($value)) {
	                                $select->where->equalTo($key, $value);
		                        }
		                    }
		                }
		            });
	                $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));
	                 
	                $cache->setItem($cache_key, $result);
		        }
	    	}
	    }
		
	    if($options['task'] == 'cache-status') {
	    	if(!empty($arrParam['where']['code'])) {
		        $cache = $this->getServiceLocator()->get('cache');
		        $cache_key = 'ApiSale'. $arrParam['where']['code'];
		        $result = $cache->getItem($cache_key);
		         
		        if (empty($result)) {
		            $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
		            	$select -> where -> equalTo('status', 1);
		            	
		                if(!empty($arrParam['order'])) {
	                        $select->order($arrParam['order']);
		                }
		                if(!empty($arrParam['where'])) {
		                    foreach ($arrParam['where'] AS $key => $value) {
		                        if(!empty($value)) {
	                                $select->where->equalTo($key, $value);
		                        }
		                    }
		                }
		            });
	                $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));
	                 
	                $cache->setItem($cache_key, $result);
		        }
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
	
		return $result;
	}
}