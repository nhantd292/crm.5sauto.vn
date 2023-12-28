<?php
namespace Report\Model;

use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ReportTable extends AbstractTableGateway implements ServiceLocatorAwareInterface{
	
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
	    
	    // Lấy danh sách đơn hàng theo khóa học
	    if($options['task'] == 'list-contract') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $date          = new \ZendX\Functions\Date();
	            $arrData       = $arrParam['data'];
	            $arrRoute      = $arrParam['route'];
	            
	            $select -> where -> equalTo('success_status', 1);
	            
		        if(!empty($arrData['filter_date_begin']) && !empty($arrData['filter_date_end'])) {
	    	        $select -> where -> NEST
	                    	         -> greaterThanOrEqualTo('success_date', $date->formatToData($arrData['filter_date_begin']))
	                    	         -> AND
	                    	         -> lessThanOrEqualTo('success_date', $date->formatToData($arrData['filter_date_end']))
	                    	         -> UNNEST;
	    	    }
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
		return $result;
	}
}