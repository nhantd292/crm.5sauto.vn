<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Where;

class ApiTable extends AbstractTableGateway implements ServiceLocatorAwareInterface{
	
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
	    
	    if($options['task'] == 'contact-history-return') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];
	            
	            $select -> where -> equalTo('user_id', $this->userInfo->getUserInfo('id'))
	            				 -> equalTo('history_return', date('Y-m-d'))
	                             -> equalTo('contract_total', 0);
	        })->count();
	    }

	    if($options['task'] == 'contract-notifi-false') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];

	            $select -> where -> equalTo('user_id', $this->userInfo->getUserInfo('id'))
	                             -> equalTo('status', 0);
	        })->count();
	    }
	    
	    if($options['task'] == 'contact-history-status') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];
	    
	            $select -> where -> equalTo('user_id', $this->userInfo->getUserInfo('id'))
	                             -> isNull('history_created')
	                             -> equalTo('contract_total', 0);
	        })->count();
	    }
	    
	    if($options['task'] == 'pending') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];
	    
	            $select -> where -> isNotNull('pending');
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
                    $select->where->equalTo($arrData['data-id'], $arrRoute['id']);
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
	    
	    if($options['task'] == 'list-class') {
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
	    
	    if($options['task'] == 'list-class-public') {
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
	            } else {
	                $select->order(array('name' => 'DESC'));
	            }
	            
	            if(!empty($arrData['data-where'])) {
	                foreach ($arrData['data-where'] AS $key => $value) {
	                    $select->where->equalTo($key, $value);
	                }
	            }
	        });
	    }
	    
	    if($options['task'] == 'list-contract-in-class') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];
	            
	            $select -> order(array('training_class_id' => 'DESC'));
	            
	            if(!empty($arrData['training_class_ids'])) {
	                $select -> where -> in('training_class_id', $arrData['training_class_ids']);
	            }
	        })->toArray();
	    }
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
	
		if($options == null) {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
				$select -> where -> equalTo('id', $arrParam['id']);
			})->current();
		}
	
		if($options['task'] == 'by-phone') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
				$select -> where -> equalTo('phone', $arrParam['phone']);
			})->current();
		}
	
		return $result;
	}
	
	public function updateStore($arrParam = null, $options = null){
	    $date = new \ZendX\Functions\Date();
	    $day_in_store = $arrParam['day_in_store'];
	    $day_point = $date->sub(date('Y-m-d H:i:s'), $day_in_store);
	    
	    if($options == null) {
	        $data	= array(
	            'store' => date('Y-m-d H:i:s'),
	        );
	        
	        $where = new Where();
	        $where->isNull('store');
	        $where->lessThan('history_return', $day_point);
	        $where->equalTo('contract_total', 0);
	        $where->notEqualTo('type', 'ok');
	        $this->tableGateway->update($data, $where);
	        
	        echo $day_point;
	        return $day_point;
	    }
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