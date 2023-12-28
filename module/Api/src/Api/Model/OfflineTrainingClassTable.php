<?php
namespace Api\Model;

use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class OfflineTrainingClassTable extends AbstractTableGateway implements ServiceLocatorAwareInterface{
	
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
	            	$select->columns(array('id', 'name', 'product_id', 'training_location_id', 'training_room_id', 'public_date', 'public_status', 'student_max', 'time', 'schedule', 'teacher_ids'));
	            }
	            
	            $select -> order(array('public_date' => 'DESC'));
	            
	            $select -> where -> equalTo('status', 1)
	                             -> equalTo('public_status', 1);
	            
	            if(!empty($arrData['product_id'])) {
	                $select->where->equalTo('product_id', $arrData['product_id']);
	            }
	            
	            if(!empty($arrData['training_location_id'])) {
	                $select->where->equalTo('training_location_id', $arrData['training_location_id']);
	            }
	            
	            if(!empty($arrData['data-where'])) {
	                foreach ($arrData['data-where'] AS $key => $value) {
	                    $select->where->equalTo($key, $value);
	                }
	            }
	        })->toArray();
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