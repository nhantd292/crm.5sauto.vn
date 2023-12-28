<?php

namespace Admin\Form;

use \Zend\Form\Form as Form;

class Material extends Form
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
                'empty_option'	=> '- Tháng -',
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
                'empty_option'	=> '- Năm -',
                'value_options' => $year,
            )
        ));


        // Sản phẩm
        $this->add(array(
            'name'			=> 'material_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Sản phẩm -',
                'disable_inarray_validator' => true,
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'list-item')), array('key' => 'id', 'value' => 'name')),
            )
        ));
    }
}