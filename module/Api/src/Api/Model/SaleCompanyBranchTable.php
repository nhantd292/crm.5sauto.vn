<?php
namespace Api\Model;

use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SaleCompanyBranchTable extends AbstractTableGateway implements ServiceLocatorAwareInterface{
	
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
	
	public function getItem($arrParam = null, $options = null){
		if($options == null) {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
				$select->where->equalTo('id', $arrParam['id']);
			})->current();
		}
	
		return $result;
	}
	
	public function listItem($arrParam = null, $options = null){
		if($options['task'] == 'cache') {
			$cache = $this->getServiceLocator()->get('cache');
			$cache_key = 'ApiSaleCompanyBranch';
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
}