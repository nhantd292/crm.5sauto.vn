ALTER TABLE `x_contract`
    ADD COLUMN `care_id` CHAR(22) NULL DEFAULT NULL COMMENT 'Lưu ID nhân viên chăm sóc' AFTER `delivery_id`;

ALTER TABLE `x_contact`
    CHANGE COLUMN `user_id` `user_id` CHAR(22) NOT NULL COMMENT 'id nhân viên sale' COLLATE 'utf8_general_ci' AFTER `address`,
    CHANGE COLUMN `marketer_id` `marketer_id` CHAR(25) NULL DEFAULT NULL COMMENT 'id nhân viên mkt' COLLATE 'utf8_general_ci' AFTER `user_id`,
    ADD COLUMN `care_id` CHAR(25) NULL DEFAULT NULL COMMENT 'id nhân viên chăm sóc' AFTER `marketer_id`;