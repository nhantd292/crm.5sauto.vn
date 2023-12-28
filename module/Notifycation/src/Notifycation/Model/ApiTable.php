<?php
namespace Notifycation\Model;

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
	    // Số contact có ngày hẹn chăm sóc lại ngày hôm nay
	    if($options['task'] == 'contact-history-return') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];
	            
	            $select -> where -> equalTo('user_id', $arrData['id'])
	            				 -> equalTo('history_return', date('Y-m-d'));
	        })->count();

	    }
	    // Số contact l1 được phân ngày hôm nay.
	    if($options['task'] == 'contact-level') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];
	            
	            $select -> where -> equalTo('user_id', $arrData['id']);
	            $select -> where -> like('date', '%'.date('Y-m-d').'%');
	            $select -> where -> equalTo('level', 'l1');
	        })->count();
	    }

	    // Số contact có ngày hẹn chăm sóc lại ngày hôm trước nhưng chưa được chăm sóc
	    if($options['task'] == 'contact-history-return-yesterday') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];
	            
	            $select -> where -> equalTo('user_id', $arrData['id'])
	            				 -> equalTo('history_return', date('Y-m-d',strtotime("-1 days")));
	        })->count();

	    }

	    // Số contact l1 được phân ngày hôm trước nhưng chưa được chăm sóc.
	    if($options['task'] == 'contact-level-yesterday') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];
	            
	            $select -> where -> equalTo('user_id', $arrData['id']);
	            $select -> where -> like('date', '%'.date('Y-m-d',strtotime("-1 days")).'%');
	            $select -> where -> equalTo('level', 'l1');
	        })->count();
	    }
	    
	    if($options['task'] == 'contact-coincider-today') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
                $date     = new \ZendX\Functions\Date();
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];

	            $select -> join(TABLE_CONTACT, TABLE_CONTACT .'.id = '. TABLE_CONTACT_COINCIDE .'.contact_id', array(), 'inner');
                if(!empty($arrData['today'])) {
                    $select -> where -> NEST
                                     -> greaterThanOrEqualTo(TABLE_CONTACT_COINCIDE .'.created', $date->formatToData($arrData['today']). ' 00:00:00')
                                     -> AND
                                     -> lessThanOrEqualTo(TABLE_CONTACT_COINCIDE .'.created', $date->formatToData($arrData['today']) . ' 23:59:59')
                                     -> UNNEST;
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
	    if($options['task'] == 'list-user-admin-sale') {
	    	$result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];
	            
	            if(!empty($arrData['permission_ids'])) {
                    $select->where->like('permission_ids', '%'.$arrData['permission_ids'].'%');
	            }
	        });
	    }
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
		if($options == null) {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
				$select -> where -> equalTo('id', $arrParam['id']);
			})->current();
		}
	
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $date 	= new \ZendX\Functions\Date();
	    $number = new \ZendX\Functions\Number();
	    $gid    = new \ZendX\Functions\Gid();

	    if($options['task'] == 'add-item') {
			$data 			 = $arrParam['data'];
			$id 			 = $gid->getId();
			$data['id'] 	 = $id;
			$data['created'] = date('Y-m-d H:i:s');
			
			$this->tableGateway->insert($data);
			return $id;
		}
	}
}