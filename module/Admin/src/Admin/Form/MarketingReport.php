<?php

namespace Admin\Form;

use \Zend\Form\Form as Form;

class MarketingReport extends Form
{

    public function __construct($sm)
    {
        parent::__construct();

        // FORM Attribute
        $this->setAttributes(array(
            'action' => '',
            'method' => 'POST',
            'class'  => 'horizontal-form',
            'role'   => 'form',
            'name'   => 'adminForm',
            'id'     => 'adminForm',
        ));

        // Id
        $this->add(array(
            'name' => 'id',
            'type' => 'Hidden',
        ));

        // User
        $this->add(array(
            'name'			=> 'marketer_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
//                'disabled'	    => true,
            ),
            'options'		=> array(
                'empty_option'	=> '- Marketer -',
                'disable_inarray_validator' => true,
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-marketing')), array('key' => 'id', 'value' => 'name')),
            )
        ));

//         Ngày báo cáo
        $this->add(array(
            'name'			=> 'date',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'		    => 'form-control date-picker not-push',
                'placeholder'	=> 'dd/mm/yyyy',
//                'disabled'	    => true,
            )
        ));
    }
}