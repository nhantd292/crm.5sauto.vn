<?php
namespace Admin\Form;
use \Zend\Form\Form as Form;
use Zend\Db\TableGateway\TableGateway;

class Document extends Form {
	
	public function __construct($sm, $configs = null){
		parent::__construct();
		
		$userInfo = new \ZendX\System\UserInfo();
		$adapter = $sm->get('dbConfig');
		
		// FORM Attribute
		$this->setAttributes(array(
			'action'	=> '',
			'method'	=> 'POST',
			'class'		=> 'horizontal-form',
			'role'		=> 'form',
			'name'		=> 'adminForm',
			'id'		=> 'adminForm',
		));
		
		// Id - Cố định
		if($userInfo->getPermissionListInfo('privileges') == 'full') {
		    $this->add(array(
		        'name'			=> 'id',
		        'type'			=> 'Text',
		        'attributes'	=> array(
		            'class'			=> 'form-control',
		            'id'			=> 'id',
		            'placeholder'	=> 'Để trống sẽ khởi tạo id tự động',
		        ),
		    ));
		} else {
    		$this->add(array(
    		    'name'			=> 'id',
    		    'type'			=> 'Hidden',
    		));
		}
		
		foreach ($configs['form']['fields'] AS $field) {
		    if(!empty($field['options']['data_source'])) {
		        $tableGateway = new TableGateway(TABLE_PREFIX . $field['options']['data_source']['table'], $adapter, null);
		        $table        = new \Admin\Model\DocumentTable($tableGateway);
		        $service      = $table->setServiceLocator($sm);
		        $task         = $field['options']['data_source']['task'] ? $field['options']['data_source']['task'] : 'cache';
		        $data_source  = $table->listItem($field['options']['data_source'], array('task' => $task));
		        $field['options']['value_options'] = \ZendX\Functions\CreateArray::create($data_source, $field['options']['data_source']['view']);
		    }
    		$this->add($field);
		}
		
		// Có cho public hiển thị hay không - Chỉ dành cho Developer
		$this->add(array(
		    'name'			=> 'public',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		        'value'     => 1
		    ),
		    'options'		=> array(
		        'value_options'	=> array(1 => 'Có', 0 => 'Không'),
		    ),
		));
		
		// Chỉ dành cho Developer
		$this->add(array(
		    'name'			=> 'developer',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		        'value'     => 0
		    ),
		    'options'		=> array(
		        'value_options'	=> array(1 => 'Có', 0 => 'Không'),
		    ),
		));
	}
}