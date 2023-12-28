<?php
namespace Api\Model;

use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SaleContactTable extends AbstractTableGateway implements ServiceLocatorAwareInterface{
	
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
		
		if($options['task'] == 'login') {
		    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
		    	$arrData = $arrParam['data'];
		    	
		    	$select -> columns(array('id', 'name', 'phone', 'email', 'sex', 'avatar', 'birthday', 'address', 'contract_total', 'status', 'test_online'));
		    	
		        $select -> where -> NEST
		        				 -> equalTo(TABLE_CONTACT .'.phone', trim($arrData['username']))
		        				 ->OR
		        				 -> equalTo(TABLE_CONTACT .'.email', trim($arrData['username']));
		        
		        $select -> where -> equalTo(TABLE_CONTACT .'.password', md5($arrData['password']))
		        				 -> equalTo(TABLE_CONTACT .'.status', 1);
		        				 //-> greaterThanOrEqualTo(TABLE_CONTACT .'.contract_total', 1);
		    })->current();
		}
		
		if($options['task'] == 'by-phone') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
				$select -> where -> equalTo('phone', $arrParam['phone']);
			})->current();
		}
	
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
		$arrData  = $arrParam['data'];
		$arrRoute = $arrParam['route'];
		
	    $filter   = new \ZendX\Filter\Purifier(array(array('HTML.AllowedElements', '')));
	    $gid      = new \ZendX\Functions\Gid();
	    $date     = new \ZendX\Functions\Date();
	    $number	  = new \ZendX\Functions\Number();
	    
	    if($options['task'] == 'add-item') {
	    	$id = $gid->getId();
	    	$data	= array(
    			'id'                => $id,
    			'name'              => $filter->filter($arrData['name']),
    			'email'             => $filter->filter($arrData['email']),
    			'phone'             => $number->fomartToData($arrData['phone']),
    			'password'          => $arrData['password'] ? md5($arrData['password']) : md5($arrData['12345678']),
    			'password_status'   => 1,
    			'status'            => 1,
    			'deploy_date'       => date('Y-m-d H:i:s'),
    			'created'           => date('Y-m-d H:i:s'),
	    	);
	    		
	    	if(!empty($arrData['location_city_id'])) {
	    		$data['location_city_id'] = $arrData['location_city_id'];
	    	}
	    	if(!empty($arrData['source_group_id'])) {
	    		$data['source_group_id'] = $arrData['source_group_id'];
	    	}
	    	if(!empty($arrData['source_channel_id'])) {
	    		$data['source_channel_id'] = $arrData['source_channel_id'];
	    	}
	    	if(!empty($arrData['birthday_year'])) {
	    		$data['birthday_year'] = $arrData['birthday_year'];
	    	}
	    	
	    	$this->tableGateway->insert($data);
	    	return $id;
	    }
	    
	    if($options['task'] == 'update-item') {
	    	$arrContact  = $arrParam['contact'];
	    	
	    	$id = $arrContact['id'];
	    	$data = array();
	    		
	    	if(!empty($arrData['name']) && empty($arrContact['name'])) {
	    		$data['name'] = trim($filter->filter($arrData['name']));
	    	}
	    	if(!empty($arrData['email']) && empty($arrContact['email'])) {
	    		$data['email'] = strtolower(trim($filter->filter($arrData['email'])));
	    	}
	    	if(!empty($arrData['sex']) && empty($arrContact['sex'])) {
	    		$data['sex'] = $filter->filter($arrData['sex']);
	    	}
	    	if(!empty($arrData['birthday_year']) && empty($arrContact['birthday_year'])) {
	    		$data['birthday_year'] = $filter->filter($arrData['birthday_year']);
	    	}
	    	if(!empty($arrData['location_city_id']) && empty($arrContact['location_city_id'])) {
	    		$data['location_city_id'] = $filter->filter($arrData['location_city_id']);
	    	}
	    		
	    	if(!empty($data)) {
	    		$this->tableGateway->update($data, array('id' => $id));
	    	}
	    	return $id;
	    }
	    
	    if($options['task'] == 'update-data') {
	        $id = $arrParam['id'];
	        $data = $arrParam['data'];
	        	
	        $this->tableGateway->update($data, array('id' => $id));
	        return $id;
	    }
	}
}