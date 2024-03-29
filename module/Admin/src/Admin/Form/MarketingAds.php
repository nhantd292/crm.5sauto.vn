<?php

namespace Admin\Form;

use \Zend\Form\Form as Form;

class MarketingAds extends Form
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
//        $this->add(array(
//            'name'			=> 'marketer_id',
//            'type'			=> 'Select',
//            'attributes'	=> array(
//                'class'		=> 'form-control select2 select2_basic',
//            ),
//            'options'		=> array(
//                'empty_option'	=> '- Marketer -',
//                'disable_inarray_validator' => true,
//                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-marketing')), array('key' => 'id', 'value' => 'name')),
//            )
//        ));

        $this->add(array(
            'name'			=> 'price',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'		=> 'form-control mask_currency',
            )
        ));

        // Nhóm sản phẩm
        $this->add(array(
            'name'			=> 'product_group_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Sản phẩm quan tâm -',
                'disable_inarray_validator' => true,
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-group')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
            )
        ));

        // Từ ngày
        $this->add(array(
            'name'			=> 'from_date',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'		    => 'form-control date-picker not-push',
                'placeholder'	=> 'dd/mm/yyyy',
            )
        ));

        // Đến ngày
        $this->add(array(
            'name'			=> 'to_date',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'		    => 'form-control date-picker not-push',
                'placeholder'	=> 'dd/mm/yyyy',
            )
        ));

        $this->add(array(
            'name'			=> 'note',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'		     => 'form-control',
            ),
        ));
    }
}