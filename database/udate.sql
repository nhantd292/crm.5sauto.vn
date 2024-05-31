ALTER TABLE `x_contract`
    ADD COLUMN `care_id` CHAR(22) NULL DEFAULT NULL COMMENT 'Lưu ID nhân viên chăm sóc' AFTER `delivery_id`;

ALTER TABLE `x_contact`
    CHANGE COLUMN `user_id` `user_id` CHAR(22) NOT NULL COMMENT 'id nhân viên sale' COLLATE 'utf8_general_ci' AFTER `address`,
    CHANGE COLUMN `marketer_id` `marketer_id` CHAR(25) NULL DEFAULT NULL COMMENT 'id nhân viên mkt' COLLATE 'utf8_general_ci' AFTER `user_id`,
    ADD COLUMN `care_id` CHAR(25) NULL DEFAULT NULL COMMENT 'id nhân viên chăm sóc' AFTER `marketer_id`;

ALTER TABLE `x_contact`
    ADD COLUMN `care_date` DATETIME NULL DEFAULT NULL COMMENT 'Ngày nhận liên hệ chăm sóc' AFTER `care_id`;

ALTER TABLE `x_document`
    CHANGE COLUMN `key_ghtk_ids` `key_ghtk_ids` TEXT NULL DEFAULT NULL COMMENT 'Danh sách tài khoản ghtk' COLLATE 'utf8_general_ci' AFTER `note`,
    CHANGE COLUMN `key_viettel_ids` `key_viettel_ids` TEXT NULL DEFAULT NULL COMMENT 'Danh sách tài khoản viettel' COLLATE 'utf8_general_ci' AFTER `key_ghtk_ids`,
    ADD COLUMN `inventory_ids` TEXT NULL DEFAULT NULL COMMENT 'Danh sách kho hàng' AFTER `key_viettel_ids`;

ALTER TABLE `x_contract`
    ADD COLUMN `ship_ext` INT(11) NULL DEFAULT '0' COMMENT 'Tổng các loại phí khác lưu kho, hàng hoàn' AFTER `price_transport`;

ALTER TABLE `x_contract`
    ADD COLUMN `date_success` DATETIME NULL DEFAULT NULL COMMENT 'Thời gian thành công' AFTER `shipped_date`;
