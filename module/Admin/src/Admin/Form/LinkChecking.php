<?php
namespace Admin\Form;
use \Zend\Form\Form as Form;

class LinkChecking extends Form {
	
	public function __construct($sm){
		parent::__construct();
		
		// FORM Attribute
		$this->setAttributes(array(
			'action'	    => '',
			'method'	    => 'POST',
			'class'		    => 'horizontal-form',
			'role'		    => 'form',
			'name'		    => 'adminForm',
			'id'		    => 'adminForm',
		));
		
		// Id
		$this->add(array(
		    'name'			=> 'id',
		    'type'			=> 'Hidden',
		));
		
		// link
		$this->add(array(
		    'name'			=> 'link',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
		    ),
		));
		
		// Nguồn biết đến
		$this->add(array(
		    'name'			=> 'marketing_channel_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'marketing-channel')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		    )
		));

        // Nhóm sản phẩm
//        $this->add(array(
//            'name'			=> 'product_group_id',
//            'type'			=> 'Select',
//            'attributes'	=> array(
//                'class'		=> 'form-control select2 select2_basic',
//            ),
//            'options'		=> array(
//                'empty_option'	=> '- Nhóm sản phẩm quan tâm -',
//                'disable_inarray_validator' => true,
//                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-group')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
//            )
//        ));
		
		// Campaign
		// $this->add(array(
		//     'name'			=> 'campaign',
		//     'type'			=> 'Select',
		//     'attributes'	=> array(
		//         'class'		=> 'form-control select2 select2_basic',
		//     ),
		//     'options'		=> array(
		//         'empty_option'	=> '- Chọn -',
		//         'disable_inarray_validator' => true,
		//         'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\CampaignTable')->listItem(array('where' => array('status' => 1)), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
		//     )
		// ));
		
		/* $this->add(array(
		    'name'			=> 'marketer_id',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Chọn -',
		        'disable_inarray_validator' => true,
		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\UserTable')->listItem(array('where' => array('status' => 1)), array('task' => 'marketer')), array('key' => 'id', 'value' => 'name')),
	        )
		)); */
		
		// type
		// $this->add(array(
		//     'name'			=> 'type',
		//     'type'			=> 'Text',
		//     'attributes'	=> array(
		//         'class'		=> 'form-control',
		//     ),
		// ));
		
		// content
		$this->add(array(
		    'name'			=> 'content',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'class'		=> 'form-control',
		    ),
		));
		
		// flexible_1
		// $this->add(array(
		//     'name'			=> 'flexible_1',
		//     'type'			=> 'Text',
		//     'attributes'	=> array(
		//         'class'		=> 'form-control',
		//     ),
		// ));
		
		// flexible_2
		// $this->add(array(
		//     'name'			=> 'flexible_2',
		//     'type'			=> 'Text',
		//     'attributes'	=> array(
		//         'class'		=> 'form-control',
		//     ),
		// ));
	}
}