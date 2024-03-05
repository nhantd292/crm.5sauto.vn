-- ALTER TABLE `x_document`
--     ADD COLUMN `key_ghtk_ids` TEXT NULL DEFAULT NULL AFTER `note`,
-- 	ADD COLUMN `key_viettel_ids` TEXT NULL DEFAULT NULL AFTER `key_ghtk_ids`;
ALTER TABLE `x_contract`
    ADD COLUMN `delivery_id` CHAR(22) NULL DEFAULT NULL COMMENT 'Lưu id nhân viên giục đơn' AFTER `user_id`;


