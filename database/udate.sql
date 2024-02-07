ALTER TABLE `x_document`
    ADD COLUMN `key_ghtk_ids` TEXT NULL DEFAULT NULL AFTER `note`,
	ADD COLUMN `key_viettel_ids` TEXT NULL DEFAULT NULL AFTER `key_ghtk_ids`;
