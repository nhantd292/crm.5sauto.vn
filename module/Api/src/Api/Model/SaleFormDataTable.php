<?php
namespace Api\Model;

use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SaleFormDataTable extends AbstractTableGateway implements ServiceLocatorAwareInterface{
	
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
		
		if($options['task'] == 'by-phone') {
		    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
		    	$number   = new \ZendX\Functions\Number();
		    	
		        $select -> where -> equalTo('phone', $number->fomartToData($arrParam['phone']));
		        
		        if(!empty($arrParam['event_id'])) {
		        	$select -> where -> equalTo('event_id', $arrParam['event_id']);
		        }
		        if(!empty($arrParam['form_id'])) {
		        	$select -> where -> equalTo('form_id', $arrParam['form_id']);
		        }
		    })->current();
		}
		
		if($options['task'] == 'by-email') {
		    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
		        $select -> where -> equalTo('email', $arrParam['email']);
		    })->current();
		}
	
		if($options['task'] == 'by-contact') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
				$select -> where -> equalTo('form_id', $arrParam['form_id'])
								 -> equalTo('contact_id', $arrParam['contact_id']);
			})->current();
		}
		
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];
	    
	    $image    = new \ZendX\Functions\Thumbnail($arrData['image']);
	    $filter   = new \ZendX\Filter\Purifier(array( array('HTML.AllowedElements', '') ));
	    $number   = new \ZendX\Functions\Number();
	    $gid      = new \ZendX\Functions\Gid();
	    
		if($options['task'] == 'add-item') {
		    // Check tồn tại liên hệ
		    $contact = $this->getServiceLocator()->get('Api\Model\SaleContactTable')->getItem(array('phone' => $number->fomartToData($arrData['phone'])), array('task' => 'by-phone'));
		    if(!empty($contact)) {
		    	$arrParam['contact'] = $contact;
		    	$contact_id = $this->getServiceLocator()->get('Api\Model\SaleContactTable')->saveItem($arrParam, array('task' => 'update-item'));
		    } else {
		    	$contact_id = $this->getServiceLocator()->get('Api\Model\SaleContactTable')->saveItem($arrParam, array('task' => 'add-item'));
		    }
		    
		    // Kiểm tra tồn tại trong form_data
		    $form_data = $this->getItem(array('form_id' => $arrData['form_id'], 'contact_id' => $contact_id), array('task' => 'by-contact'));
		    
		    $data = array();
		    foreach ($arrData AS $key => $val) {
		        if(is_array($val)) {
		            $arrTmp = array();
		            foreach ($val AS $k => $v) {
		                $arrTmp[$k] = $filter->filter($v);
		            }
		            $value = serialize($arrTmp);
		        } else {
		            $value = $filter->filter($val);
		        }
		        $data[$key] = $value;
		    }
		    
		    // Loại bỏ những trường dữ liệu không có trong form data
		    unset($data['name']);
		    unset($data['phone']);
		    unset($data['email']);
		    unset($data['location_city_id']);
		    unset($data['source_group_id']);
		    unset($data['source_channel_id']);
		    unset($data['password']);
		    unset($data['birthday_year']);
		    
		    if(!empty($data['training_location_id'])) {
		    	$training_location = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'training-location')), array('task' => 'cache'));
		    	$data['company_branch_id'] = $training_location[$data['training_location_id']]['document_id'];
		    }
		    
		    if(!empty($form_data)) {
		        $id = $form_data['id'];
		        $data['created'] = date('Y-m-d H:i:s');
		        
		        $this->tableGateway->update($data, array('id' => $id));
		    } else {
    			$data['created'] 	= date('Y-m-d H:i:s');
    			$data['contact_id'] = $contact_id;
    			
    			$this->tableGateway->insert($data);
    			$id = $this->tableGateway->getLastInsertValue();
		    }
			return $id;
		}
	}
}