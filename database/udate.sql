ALTER TABLE `x_contract`
    ADD COLUMN `care_id` CHAR(22) NULL DEFAULT NULL COMMENT 'Lưu ID nhân viên chăm sóc' AFTER `delivery_id`;