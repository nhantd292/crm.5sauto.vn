<?php

namespace Admin\Form;

use \Zend\Form\Form as Form;

class ZaloNotifyConfig extends Form
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

        // name
        $this->add(array(
            'name'			=> 'name',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'			=> 'form-control',
                'id'			=> 'name',
                'placeholder'	=> 'Tên',
            ),
        ));

        $this->add(array(
            'name'			=> 'code',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'			=> 'form-control',
            ),
        ));

        $this->add(array(
            'name'			=> 'sale_branch_ids',
            'type'			=> 'MultiCheckbox',
            'options'		=> array(
                'label_attributes' => array(
                    'class'		=> 'checkbox-inline',
                ),
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
            ),
        ));

        $templates = json_decode($sm->zalo_call("/template/all?offset=0&limit=100&status=1", [], 'GET'), true);
        $template_array = \ZendX\Functions\CreateArray::create($templates['data'], array('key' => 'templateId', 'value' => 'templateName'));

        $this->add(array(
            'name'			=> 'template_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Chọn mẫu gửi -',
                'disable_inarray_validator' => true,
                'value_options'	=> $template_array,
            )
        ));

        $this->add(array(
            'name'			=> 'order_status',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'		     => 'form-control',
                'placeholder'	=> '101,102,da_xac_nhan',
            ),
        ));

        $this->add(array(
            'name'			=> 'note',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'		     => 'form-control',
                'placeholder'	=> 'đang giao hàng',
            ),
        ));
    }
}