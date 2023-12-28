<?php

namespace Admin\Form\Material;

use \Zend\Form\Form as Form;

class addAll extends Form
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

        // Month
        $this->add(array(
            'name'       => 'month',
            'type'       => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
                'value' => date('m')
            ),
            'options'    => array(
                'value_options' => array('01' => '01',
                                         '02' => '02',
                                         '03' => '03',
                                         '04' => '04',
                                         '05' => '05',
                                         '06' => '06',
                                         '07' => '07',
                                         '08' => '08',
                                         '09' => '09',
                                         '10' => '10',
                                         '11' => '11',
                                         '12' => '12'),
            )
        ));

        // Year
        $year = array();
        for ($i = (date('Y') + 1); $i >= 2014; $i--) {
            $year[$i] = $i;
        }
        $this->add(array(
            'name'       => 'year',
            'type'       => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
                'value' => date('Y')
            ),
            'options'    => array(
                'value_options' => $year,
            )
        ));
    }
}