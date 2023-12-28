<?php
// Đường dẫn đến thư mục chứa thư mục hiện thời
chdir(dirname(__FILE__));

// Key App
define('CLIENT_ID',     'e11e377c-825b-4778-8250-89764dfea861');
define('CLIENT_SECRET', '31CDD735450B1D67B6C154EC843676B10D9DFF70');
define('RETAILER',      'qclothes');

define('GHTK',      'a7E52E1DE3a56F03865c005BBC0Ee3d27C168084');
define('URLGHTK',   'https://services.ghtklab.com');
//define('URLGHTK',   'https://services.giaohangtietkiem.vn');
define('HASS',      '6A538A1ASD4FG8D83D5FG8D5D4FAS8E8DF4D5D54F4');
define('UPDATESHIPMENT',      'http://qclothes.local/xadmin/api/updateShipment?hass=6A538A1ASD4FG8D83D5FG8D5D4FAS8E8DF4D5D54F4');

define('URLWEBHOOK','https://webhook.site/5535e531-41c8-4063-9e73-f0290d49eaef');

define('BANK_TECHCOMBANK',      '1706');

// Key App
define('APP_KEY',                   'x2019');

// Định nghĩa môi trường thực thi của ứng dụng
define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));	// development - production

// Định nghĩa đường dẫn đến thư mục ứng dụng
define('PATH_APPLICATION',          realpath(dirname(__FILE__)));
define('PATH_LIBRARY',              PATH_APPLICATION . '/library');
define('PATH_VENDOR',               PATH_APPLICATION . '/vendor');
define('PATH_PUBLIC',               PATH_APPLICATION . '/public');
define('PATH_CAPTCHA',              PATH_PUBLIC . '/captcha');
define('PATH_FILES',                PATH_PUBLIC . '/files');
define('PATH_SCRIPTS',              PATH_PUBLIC . '/scripts');
define('PATH_TEMPLATE',             PATH_PUBLIC . '/template');

// Định nghĩa đường dẫn url
define('URL_APPLICATION',           '');
define('URL_MODULE',                URL_APPLICATION . '/module');
define('URL_PUBLIC',                URL_APPLICATION . '/public');
define('URL_FILES',                 URL_PUBLIC . '/files');
define('URL_SCRIPTS',               URL_PUBLIC . '/scripts');
define('URL_TEMPLATE',              URL_PUBLIC . '/template');

// HTMLPurifier
define('HTMLPURIFIER_PREFIX',       PATH_VENDOR);

// Table HR
define('TABLE_PREFIX',              'x_');
define('TABLE_USER',                TABLE_PREFIX . 'user');
define('TABLE_PERMISSION',     		TABLE_PREFIX . 'permission');
define('TABLE_PERMISSION_LIST',		TABLE_PREFIX . 'permission_list');

define('TABLE_DYNAMIC',             TABLE_PREFIX . 'dynamic');
define('TABLE_DOCUMENT',            TABLE_PREFIX . 'document');
define('TABLE_LOCATIONS',           TABLE_PREFIX . 'locations');

define('TABLE_SETTING',             TABLE_PREFIX . 'setting');
define('TABLE_CONTACT',             TABLE_PREFIX . 'contact');
define('TABLE_CONTRACT',            TABLE_PREFIX . 'contract');
define('TABLE_CONTRACT_DETAIL',     TABLE_PREFIX . 'contract_detail');
define('TABLE_BILL',                TABLE_PREFIX . 'bill');
define('TABLE_PRODUCT',             TABLE_PREFIX . 'product');
define('TABLE_PRODUCT_SETTING',     TABLE_PREFIX . 'product_setting');
define('TABLE_MATTER',              TABLE_PREFIX . 'matter');
define('TABLE_SALES_TARGET',        TABLE_PREFIX . 'sales_target');
define('TABLE_MARKETING_REPORT',    TABLE_PREFIX . 'marketing_report'); // Báo cáo marketing
define('TABLE_MATERIAL',            TABLE_PREFIX . 'material'); // Bảng nguyên liệu đầu kỳ
define('TABLE_EVENT',               TABLE_PREFIX . 'event');
define('TABLE_EVENT_CONTACT',		TABLE_PREFIX . 'event_contact');
define('TABLE_HISTORY',             TABLE_PREFIX . 'history');
define('TABLE_LOGS',                TABLE_PREFIX . 'logs');
define('TABLE_HISTORY_IMPORT',      TABLE_PREFIX . 'history_import');
define('TABLE_CAMPAIGN',            TABLE_PREFIX . 'campaign');
define('TABLE_CAMPAIGN_DATA',       TABLE_PREFIX . 'campaign_data');
define('TABLE_FORM_DATA',           TABLE_PREFIX . 'form_data');
define('TABLE_PRODUCT_LISTED',      TABLE_PREFIX . 'product_listed');
define('TABLE_COLOR',               TABLE_PREFIX . 'color');
define('TABLE_LINK_CHECKING',       TABLE_PREFIX . 'link_checking'); // Link tracking
define('TABLE_DATA_CONFIG',         TABLE_PREFIX . 'data_config'); // Cấu hình chi data tự động
define('TABLE_CHECK_IN',            TABLE_PREFIX . 'check_in'); // Chấm công
define('TABLE_TARGET',              TABLE_PREFIX . 'target'); // Bảng chỉ tiêu
define('TABLE_EVALUATE',            TABLE_PREFIX . 'evaluate'); // Bảng đánh giá nhân sự
define('TABLE_NOTIFI',              TABLE_PREFIX . 'notifi'); // Bảng thông báo
define('TABLE_NOTIFI_USER',         TABLE_PREFIX . 'notifi_user'); // Bảng trạng thái thông báo
define('TABLE_COMBO_PRODUCT',       TABLE_PREFIX . 'combo_product'); // Bảng combo sản phẩm

// Bảng Kiotviet
define('TABLE_KOV_BRANCHES',        TABLE_PREFIX . 'kov_branches'); // Kho hàng
define('TABLE_KOV_PRODUCTS',        TABLE_PREFIX . 'kov_products'); // Sản phẩm
define('TABLE_KOV_DISCOUNTS',       TABLE_PREFIX . 'kov_discounts'); // Quản lý khuyến mãi
define('TABLE_KOV_PRODUCT_BRANCH',  TABLE_PREFIX . 'kov_product_branch'); // Số lượng giá vốn sản phẩm
define('TABLE_PRODUCT_RETURN',      TABLE_PREFIX . 'product_return'); // Quản lý kho hàng hoàn
define('TABLE_PRODUCT_RETURN_KOV',  TABLE_PREFIX . 'product_return_kov'); // Quản lý kho hàng hoàn về kov

// Đào tạo
define('TABLE_EDU_CLASS',           TABLE_PREFIX . 'edu_class');

// Công việc
define('TABLE_TASK_CATEGORY',       TABLE_PREFIX . 'task_category');
define('TABLE_TASK_PROJECT',        TABLE_PREFIX . 'task_project');
define('TABLE_TASK_PROJECT_CONTENT',TABLE_PREFIX . 'task_project_content');
define('TABLE_TASK',                TABLE_PREFIX . 'task');

// Hội đồng Anh
define('TABLE_BC',                  TABLE_PREFIX . 'bc');
define('TABLE_BC_BILL',             TABLE_PREFIX . 'bc_bill');

// Notifycation
define('TABLE_NOTIFY',        		TABLE_PREFIX . 'notify');

// Permisions
define('SYSTEM', 'system');
define('ADMIN', 'admin');
define('MANAGER', 'manager');
define('GDCN', 'ch'); // Giám đốc chi nhánh
define('ACCOUNTING', 'accounting'); // Giám đốc chi nhánh
define('SALEADMIN', 'saleadmin'); // Sale admim
define('SALES', 'sales'); // Nhân viên sale
define('MARKETINGADMIN', 'admin_marketing'); // Mkt admin
define('MARKETING', 'marketing'); // Nhân viên Mkt
define('GROUP_MKT_LEADER', 'group-marketing-leader'); // Trưởng nhóm MKT
define('GROUP_SALES_LEADER', 'group-sales-leader'); // Trưởng nhóm Sales
define('CHECK_MANAGER', 'check_manager'); // Quản lý giục đơn

// status code
define('STATUS_CONTACT_CANCEL'      , 'huy');
define('STATUS_CONTACT_UNHEARD'     , 'khong-nghe-may');
define('STATUS_CONTACT_THINK'       , 'suy-nghi');
define('STATUS_CONTACT_ADVISORY'    , 'da-tu-van');
define('STATUS_CONTACT_POSITIVE'    , 'tich-cuc');
define('STATUS_CONTACT_NEGATIVE'    , 'tieu-cuc');

// Phân loại lịch sử chăm sóc
define('DA_CHOT'    , 'da-chot');
define('HUY_SALES'  , 'huy-sales');
define('HUY'        , 'huy');

// Trạng thái sản xuất
define('STATUS_CONTRACT_PRODUCT_CANCEL'     , 'huy-san-xuat');
define('STATUS_CONTRACT_PRODUCT_PRODUCTED'  , 'da-san-xuat');
define('STATUS_CONTRACT_PRODUCT_POST'       , 'da-giao-hang');
define('STATUS_CONTRACT_PRODUCT_NOT_SEND'   , 'huy-khong-gui');

// Trạng thái kế toán
define('STATUS_CONTRACT_ACOUNTING_CANCEL'       , 'huy-khong-gui');
define('STATUS_CONTRACT_ACOUNTING_RETURN'       , 'da-nhan-hoan');
define('STATUS_CONTRACT_ACOUNTING_MONEY'        , 'da-nhan-tien');
define('STATUS_CONTRACT_ACOUNTING_CANCEL_RETURN', 'huy-hang-co-san');

// Trạng thái giục đơn
define('STATUS_CONTRACT_CHECK_KEEP'    , 'giu-lai-buu-dien');
define('STATUS_CONTRACT_CHECK_SENDING' , 'dang-van-chuyen');
define('STATUS_CONTRACT_CHECK_POST'    , 'dang-phat');
define('STATUS_CONTRACT_CHECK_SUCCESS' , 'thanh-cong');
define('STATUS_CONTRACT_CHECK_RETURN'  , 'hoan');

// Phân loại khách hàng
define('CONTACT_TYPE_ONE'      , 'khach-le');
define('CONTACT_TYPE_MULTIL'   , 'khach-dai-ly');

// Color
define('CARPET_COLOR', 'carpet-color');
define('TANGLED_COLOR', 'tangled-color');

// Loại đơn sản xuất
define('DON_BAO_HANH', 'don-bao-hanh');
define('DON_HA_NOI', 'don-ha-noi');
define('DON_TINH', 'don-tinh');

// Chức vụ kiêm nhiệm
define('NHAN_VIEN_GIAO_HANG', 'nhan-vien-giao-hang');

