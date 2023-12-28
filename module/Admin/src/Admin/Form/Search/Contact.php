<?php

namespace Admin\Form\Search;

use kcfinder\zipFolder;
use \Zend\Form\Form as Form;

class Contact extends Form
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

        // Số đơn hàng
        $this->add(array(
            'name' => 'filter_number_contract',
            'type' => 'Text',
            'attributes' => array(
                'placeholder' => 'Số lần mua từ',
                'class' => 'form-control input-sm',
            ),
        ));
        $this->add(array(
            'name' => 'filter_number_contract2',
            'type' => 'Text',
            'attributes' => array(
                'placeholder' => 'Số lần mua đến',
                'class' => 'form-control input-sm',
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

        // Đội nhóm
        $sale_group_id  = $userInfo['sale_group_id'];
        $sale_group_ids = !empty($userInfo['sale_group_ids']) ? explode(',', $userInfo['sale_group_ids']) : null;
        $group          = $sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
        $group_data     = array();
        if (!empty($params['filter_sale_branch'])) {
            foreach ($group AS $key => $val) {
                if ($val['document_id'] == $params['filter_sale_branch']) {
                    if (!empty($sale_group_ids)) {
                        if (in_array($val['id'], $sale_group_ids)) {
                            $group_data[] = $val;
                        }
                    } elseif (!empty($sale_group_id)) {
                        if ($val['id'] == $sale_group_id) {
                            $group_data[] = $val;
                        }
                    } else {
                        $group_data[] = $val;
                    }
                }
            }
        }
        $this->add(array(
            'name' => 'filter_sale_group',
            'type' => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
            ),
            'options' => array(
                'empty_option' => '- Đội nhóm -',
                'value_options' => \ZendX\Functions\CreateArray::createSelect($group_data, array('key' => 'id', 'value' => 'name,content', 'symbol' => ' - '))
            )
        ));

        // nhân viên - chỉ lấy nhân viên sale và chăm sóc khách hàng
        $user_sale      = $sm->get('Admin\Model\UserTable')->listItem($params, array('task' => 'list-sale'));
        $user_care      = $sm->get('Admin\Model\UserTable')->listItem($params, array('task' => 'list-care'));
        $user = array_merge($user_sale, $user_care);

        $user_data = array();
        if (!empty($params['filter_sale_group'])) {
            foreach ($user AS $key => $val) {
                if ($val['sale_group_id'] == $params['filter_sale_group']) {
                    if (!empty($userInfo['sale_group_ids'])) {
                        $user_data[] = $val;
                    } else {
                        if (!empty($userInfo['sale_group_id'])) {
                            if ($val['id'] == $userInfo['id']) {
                                $user_data[] = $val;
                            }
                        } else {
                            $user_data[] = $val;
                        }
                    }
                }
            }
        }
        $this->add(array(
            'name' => 'filter_user',
            'type' => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
            ),
            'options' => array(
                'empty_option' => '- Người quản lý -',
                'value_options' => \ZendX\Functions\CreateArray::create($user, array('key' => 'id', 'value' => 'name')),
            )
        ));

        // Nguồn khách hàng
        $this->add(array(
            'name' => 'filter_source_group',
            'type' => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
            ),
            'options' => array(
                'empty_option' => '- Nguồn liên hệ -',
                'value_options' => \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-source-group')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
            )
        ));

        // Nguồn biết đến
        $this->add(array(
            'name' => 'filter_source_known',
            'type' => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
            ),
            'options' => array(
                'empty_option' => '- Nguồn biết đến -',
                'value_options' => \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-source-known')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
            )
        ));
        // Hành động chăm sóc
        $this->add(array(
            'name' => 'filter_last_action',
            'type' => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
            ),
            'options' => array(
                'empty_option' => '- Hành động chăm sóc cuối -',
                'value_options' => \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-action')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
            )
        ));


        // Kết quả chăm sóc
        $this->add(array(
            'name' => 'filter_history_result',
            'type' => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
            ),
            'options' => array(
                'empty_option' => '- Kết quả chăm sóc -',
                'value_options' => \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-result')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
            )
        ));

        // Tình trạng chăm sóc
        $this->add(array(
            'name' => 'filter_history_status',
            'type' => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
            ),
            'options' => array(
                'empty_option' => '- Tình trạng chăm sóc -',
                'value_options' => array('yes' => 'Đã chăm sóc', 'no' => 'Chưa chăm sóc', 'return' => 'Không chăm sóc lại'),
            )
        ));

        // Phân loại
        $this->add(array(
            'name' => 'filter_contact_type',
            'type' => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
            ),
            'options' => array(
                'empty_option' => '- Phân loại -',
                'value_options' => \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-contact-type')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'name')),
            )
        ));

        // Sản phẩm quan tâm
//		$this->add(array(
//		    'name'			=> 'filter_product_interest',
//		    'type'			=> 'Select',
//		    'attributes'	=> array(
//		        'class'		=> 'form-control select2 select2_basic',
//		    ),
//		    'options'		=> array(
//		        'empty_option'	=> '- Sản phẩm quan tâm -',
//		        'disable_inarray_validator' => true,
//		        'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'product-interest')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
//		    ),
//		));

        // Tỉnh thành
        $this->add(array(
            'name' => 'filter_location_city',
            'type' => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
            ),
            'options' => array(
                'empty_option' => '- Tỉnh thành -',
                'disable_inarray_validator' => true,
                'value_options' => \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\LocationsTable')->listItem(array('level' => 1), array('task' => 'cache')), array('key' => 'code', 'value' => 'name')),
            ),
        ));

        // Quận/huyện
        $list_district = \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\LocationsTable')->listItem(array('level' => 2, 'parent' => $params['filter_location_city']), array('task' => 'list-parent')), array('key' => 'code', 'value' => 'name'));
        $this->add(array(
            'name' => 'filter_location_district',
            'type' => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
            ),
            'options' => array(
                'empty_option' => '- Quận huyện -',
                'disable_inarray_validator' => true,
                'value_options' => $list_district,
            ),
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