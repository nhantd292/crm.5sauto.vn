<?php
namespace Notifycation\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorInterface;

class DefaultTable extends AbstractTableGateway implements ServiceLocatorAwareInterface{
    
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
    
}