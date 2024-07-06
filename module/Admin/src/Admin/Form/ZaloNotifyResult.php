<?php

namespace Admin\Form;

use \Zend\Form\Form as Form;

class ZaloNotifyResult extends Form
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
    }
}