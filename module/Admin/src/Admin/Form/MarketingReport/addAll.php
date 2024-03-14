<?php

namespace Admin\Form\MarketingReport;

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
//        $this->add(array(
//            'name'       => 'month',
//            'type'       => 'Select',
//            'attributes' => array(
//                'class' => 'form-control select2 select2_basic',
//                'value' => date('m')
//            ),
//            'options'    => array(
//                'value_options' => array('01' => '01',
//                                         '02' => '02',
//                                         '03' => '03',
//                                         '04' => '04',
//                                         '05' => '05',
//                                         '06' => '06',
//                                         '07' => '07',
//                                         '08' => '08',
//                                         '09' => '09',
//                                         '10' => '10',
//                                         '11' => '11',
//                                         '12' => '12'),
//            )
//        ));

        // Year
//        $year = array();
//        for ($i = (date('Y') + 1); $i >= 2014; $i--) {
//            $year[$i] = $i;
//        }
//        $this->add(array(
//            'name'       => 'year',
//            'type'       => 'Select',
//            'attributes' => array(
//                'class' => 'form-control select2 select2_basic',
//                'value' => date('Y')
//            ),
//            'options'    => array(
//                'value_options' => $year,
//            )
//        ));

        // Từ ngày
        $this->add(array(
            'name'			=> 'from_date',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'		    => 'form-control date-picker',
                'placeholder'	=> 'dd/mm/yyyy',
            )
        ));

        // Từ ngày
        $this->add(array(
            'name'			=> 'to_date',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'		    => 'form-control date-picker',
                'placeholder'	=> 'dd/mm/yyyy',
            )
        ));

        $this->add(array(
            'name'			=> 'product_ids',
            'type'			=> 'Select',

            'attributes'	=> array(
                'multiple'	=> true,
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Sản phẩm chạy ADS-',
//                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\KovProductsTable')->listItem(null, array('task' => 'cache')), array('key' => 'id', 'value' => 'fullName')),
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-group')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),

            )
        ));
    }
}