-- ALTER TABLE `x_document`
--     ADD COLUMN `key_ghtk_ids` TEXT NULL DEFAULT NULL AFTER `note`,
-- 	ADD COLUMN `key_viettel_ids` TEXT NULL DEFAULT NULL AFTER `key_ghtk_ids`;
-- ALTER TABLE `x_contract`
--     ADD COLUMN `delivery_id` CHAR(22) NULL DEFAULT NULL COMMENT 'Lưu id nhân viên giục đơn' AFTER `user_id`;


-- ALTER TABLE `x_contract`
--     ADD COLUMN `paid_cost` CHAR(1) NULL DEFAULT 'f' COMMENT 'Đã thanh toán giá vốn chưa f: chưa , t: rồi' AFTER `returned`;
ALTER TABLE `x_marketing_report`
    ADD COLUMN `product_id` CHAR(25) NULL DEFAULT NULL AFTER `marketer_id`;
