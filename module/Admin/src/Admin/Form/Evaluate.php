<?php

namespace Admin\Form;

use \Zend\Form\Form as Form;

class Evaluate extends Form
{

    public function __construct($sm, $options=null)
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


        $this->add(array(
            'name' => 'contract_id',
            'type' => 'Hidden',
        ));

        $this->add(array(
            'name' => 'code',
            'type' => 'Hidden',
        ));

        // Modal
        $this->add(array(
            'name'			=> 'modal',
            'type'			=> 'Hidden',
            'attributes'	=> array(
                'value'		=> 'success',
            ),
        ));

        $level = \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'evalua-level')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name'));
        foreach($level as $key => $value){
            $tm[$key] = ' ';
        }
        // Mức độ hài lòng nhân viên sale
        $this->add(array(
            'name'			=> 'sale_level',
            'type'			=> 'Radio',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Mức độ hài lòng -',
                'disable_inarray_validator' => true,
                'value_options'	=> $tm,
            )
        ));

        // Ý kiến đóng góp sale
        $this->add(array(
            'name'			=> 'sale_note',
            'type'			=> 'TextArea',
            'attributes'	=> array(
                'class'		    => 'form-control',
                'placeholder'	=> 'Ý kiến đóng góp',
                'rows' => 3
            )
        ));

        // Mức độ hài lòng nhân viên kỹ thật
        $this->add(array(
            'name'			=> 'technical_level',
            'type'			=> 'Radio',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Mức độ hài lòng -',
                'disable_inarray_validator' => true,
                'value_options'	=> $tm,
            )
        ));

        // sản phẩm
        $this->add(array(
            'name'			=> 'technical_product',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
                'multiple'  => 'multiple',
            ),
            'options'		=> array(
                'empty_option'  => '- Chọn -',
                'disable_inarray_validator' => true,
                'value_options'	=> $options['products'],
            )
        ));

        // Ý kiến đóng góp sale
        $this->add(array(
            'name'			=> 'technical_note',
            'type'			=> 'TextArea',
            'attributes'	=> array(
                'class'		    => 'form-control',
                'placeholder'	=> 'Ý kiến đóng góp',
                'rows' => 3
            )
        ));

        // Mức độ hài lòng thợ may
        $this->add(array(
            'name'			=> 'tailors_level',
            'type'			=> 'Radio',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Mức độ hài lòng -',
                'disable_inarray_validator' => true,
                'value_options'	=> $tm,
            )
        ));

        // sản phẩm thợ may
        $this->add(array(
            'name'			=> 'tailors_product',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
                'multiple'  => 'multiple',
            ),
            'options'		=> array(
                'empty_option'  => '- Chọn -',
                'disable_inarray_validator' => true,
                'value_options'	=> $options['product_tailors'],
            )
        ));

        // Ý kiến đóng góp sale
        $this->add(array(
            'name'			=> 'tailors_note',
            'type'			=> 'TextArea',
            'attributes'	=> array(
                'class'		    => 'form-control',
                'placeholder'	=> 'Ý kiến đóng góp',
                'rows' => 3
            )
        ));
    }
}