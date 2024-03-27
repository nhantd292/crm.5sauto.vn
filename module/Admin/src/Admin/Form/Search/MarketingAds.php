<?php

namespace Admin\Form\Search;

use kcfinder\zipFolder;
use \Zend\Form\Form as Form;

class MarketingAds extends Form
{

    public function __construct($sm, $params)
    {
        parent::__construct();

        $userInfo = new \ZendX\System\UserInfo();
        $userInfo = $userInfo->getUserInfo();

        // FORM Attribute
        $this->setAttributes(array(
            'action' => '',
            'method' => 'POST',
            'class' => 'horizontal-form',
            'role' => 'form',
            'name' => 'adminForm',
            'id' => 'adminForm',
        ));

        // Keyword
        $this->add(array(
            'name' => 'filter_keyword',
            'type' => 'Text',
            'attributes' => array(
                'placeholder' => 'Từ khóa',
                'class' => 'form-control input-sm',
                'id' => 'filter_keyword',
            ),
        ));

        // Bắt đầu từ ngày
        $this->add(array(
            'name' => 'filter_date_begin',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control date-picker',
                'placeholder' => 'Từ ngày',
                'autocomplete' => 'off'
            )
        ));

        // Ngày kết thúc
        $this->add(array(
            'name' => 'filter_date_end',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control date-picker',
                'placeholder' => 'Đến ngày',
                'autocomplete' => 'off'
            )
        ));

        // Phân loại ngày tìm kiếm
        $this->add(array(
            'name' => 'filter_date_type',
            'type' => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
                'value' => 'date'
            ),
            'options' => array(
                'value_options' => array('date' => 'Ngày tiếp nhận', 'created' => 'Ngày tạo', 'history_created' => 'Ngày chăm sóc', 'history_return' => 'Ngày hẹn chăm sóc lại', 'date_return' => 'Hẹn test/đăng ký'),
            )
        ));

        // Cơ sở
        $this->add(array(
            'name' => 'filter_sale_branch',
            'type' => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
            ),
            'options' => array(
                'empty_option' => '- Cơ sở -',
                'value_options' => \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
            )
        ));

        $data_filter = array(
            'company_department_id' => 'marketing',
            'sale_branch_id' => $params['filter_sale_branch'],
            'sale_group_id' => $params['filter_sale_group'],
        );
        $this->add(array(
            'name'			=> 'filter_marketer_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Nhân viên marketing -',
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\UserTable')->listItem($data_filter, array('task' => 'list-user-department')), array('key' => 'id', 'value' => 'name')),
            )
        ));

        // Nhóm sản phẩm quan tâm
        $this->add(array(
            'name'			=> 'filter_product_group_id',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Sản phẩm quan tâm -',
                'disable_inarray_validator' => true,
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-group')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
            )
        ));

        // Submit
        $this->add(array(
            'name' => 'filter_submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Tìm',
                'class' => 'btn btn-sm green',
            ),
        ));

        // Xóa
        $this->add(array(
            'name' => 'filter_reset',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Xóa',
                'class' => 'btn btn-sm red',
            ),
        ));

        // Order
        $this->add(array(
            'name' => 'order',
            'type' => 'Hidden',
        ));

        // Order By
        $this->add(array(
            'name' => 'order_by',
            'type' => 'Hidden',
        ));
    }
}