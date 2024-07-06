<?php

namespace Admin\Form\Search;

use kcfinder\zipFolder;
use \Zend\Form\Form as Form;

class ZaloNotifyResult extends Form
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
                'placeholder' => 'Từ khóa: SĐT/Mã ĐH...',
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

        // Mã lỗi
        $this->add(array(
            'name' => 'filter_error',
            'type' => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
            ),
            'options' => array(
                'empty_option' => '- Trạng thái -',
                'value_options' => array('success' => 'Thành công', 'error' => 'Gửi lỗi'),
            )
        ));

        // Mã lỗi
        $this->add(array(
            'name' => 'filter_result_error',
            'type' => 'Select',
            'attributes' => array(
                'class' => 'form-control select2 select2_basic',
            ),
            'options' => array(
                'empty_option' => '- Mã lỗi -',
                'value_options' => array(
                    '0' => '0: Gửi thành công',
                    '-100' => '-100: Xảy ra lỗi không xác định, vui lòng thử lại sau',
                    '-101' => '-101: Ứng dụng gửi ZNS không hợp lệ',
                    '-102' => '-102: Ứng dụng gửi ZNS không tồn tại',
                    '-103' => '-103: Ứng dụng chưa được kích hoạt',
                    '-104' => '-104: Secret key của ứng dụng không hợp lệ',
                    '-105' => '-105: Ứng dụng gửi ZNS chưa đươc liên kết với OA nào',
                    '-106' => '-106: Phương thức không được hỗ trợ',
                    '-107' => '-107: ID thông báo không hợp lệ',
                    '-108' => '-108: Số điện thoại không hợp lệ',
                    '-109' => '-109: ID mẫu ZNS không hợp lệ',
                    '-110' => '-110: Phiên bản Zalo app không được hỗ trợ. Người dùng cần cập nhật phiên bản mới nhất',
                    '-111' => '-111: Mẫu ZNS không có dữ liệu',
                    '-112' => '-112: Nội dung mẫu ZNS không hợp lệ',
                    '-1123' => '-1123: Không thể tạo QR code, vui lòng kiểm tra lại',
                    '-113' => '-113: Button không hợp lệ',
                    '-114' => '-114: Người dùng không nhận được ZNS vì các lý do: Trạng thái tài khoản, Tùy chọn nhận ZNS, Sử dụng Zalo phiên bản cũ, hoặc các lỗi nội bộ khác',
                    '-115' => '-115: Tài khoản ZNS không đủ số dư',
                    '-116' => '-116: Nội dung không hợp lệ',
                    '-117' => '-117: OA hoặc ứng dụng gửi ZNS chưa được cấp quyền sử dụng mẫu ZNS này',
                    '-118' => '-118: Tài khoản Zalo không tồn tại hoặc đã bị vô hiệu hoá',
                    '-119' => '-119: Tài khoản không thể nhận ZNS',
                    '-120' => '-120: OA chưa được cấp quyền sử dụng tính năng này',
                    '-121' => '-121: Mẫu ZNS không có nội dung',
                    '-122' => '-122: Body request không đúng định dạng JSON',
                    '-123' => '-123: Giải mã nội dung thông báo RSA thất bại',
                    '-124' => '-124: Mã truy cập không hợp lệ',
                    '-125' => '-125: ID Official Account không hợp lệ',
                    '-126' => '-126: Ví (development mode) không đủ số dư',
                    '-127' => '-127: Template test chỉ có thể được gửi cho quản trị viên',
                    '-128' => '-128: Mã encoding key không tồn tại',
                    '-129' => '-129: Không thể tạo RSA key, vui lòng thử lại sau',
                    '-130' => '-130: Nội dung mẫu ZNS vượt quá giới hạn kí tự',
                    '-131' => '-131: Mẫu ZNS chưa được phê duyệt',
                    '-132' => '-132: Tham số không hợp lệ',
                    '-133' => '-133: Mẫu ZNS này không được phép gửi vào ban đêm (từ 22h-6h)',
                    '-134' => '-134: Người dùng chưa phản hồi gợi ý nhận ZNS từ OA',
                    '-135' => '-135: OA chưa có quyền gửi ZNS (chưa được xác thực, đang sử dụng gói miễn phí)',
                    '-1351' => '-1351: OA không có quyền gửi ZNS (Hệ thống chặn do phát hiện vi phạm) ',
                    '-136' => '-136: Cần kết nối với ZCA để sử dụng tính năng này',
                    '-137' => '-137: Thanh toán ZCA thất bại (ví không đủ số dư, ...)',
                    '-138' => '-138: Ứng dụng gửi ZNS chưa có quyền sử dụng tính năng này',
                    '-139' => '-139: Người dùng từ chối nhận loại ZNS này',
                    '-140' => '-140: OA chưa được cấp quyền gửi ZNS hậu mãi cho người dùng này',
                    '-141' => '-141: Người dùng từ chối nhận ZNS từ Official Account',
                    '-142' => '-142: RSA key không tồn tại, vui lòng gọi API tạo RSA key',
                    '-143' => '-143: RSA key đã tồn tại, vui lòng gọi API lấy RSA key',
                    '-144' => '-144: OA đã vượt giới hạn gửi ZNS trong ngày',
                    '-145' => '-145: OA không được phép gửi loại nội dung ZNS này',
                    '-146' => '-146: Mẫu ZNS này đã bị vô hiệu hoá do chất lượng gửi thấp',
                    '-147' => '-147: Mẫu ZNS đã vượt giới hạn gửi trong ngày',
                    '-1471' => '-1471: OA đã vượt giới hạn gửi tin ZNS hậu mãi cho người dùng này trong tháng.',
                    '-148' => '-148: Không tìm thấy ZNS journey token',
                    '-149' => '-149: ZNS journey token không hợp lệ',
                    '-150' => '-150: ZNS journey token đã hết hạn',
                    '-151' => '-151: Không phải mẫu ZNS E2EE',
                    '-152' => '-152: Lấy E2EE key thất bại'
                ),
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