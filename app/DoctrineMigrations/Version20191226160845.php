<?php

namespace Supla\Migrations;

/**
 * 1. https://github.com/SUPLA/supla-core/issues/115
 * 2. https://github.com/SUPLA/supla-core/issues/116
 * 3. Additional column for configuration in json format
 *
 */
class Version20191226160845 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('UPDATE `supla_dev_channel` SET func = 315 WHERE type = 5010 AND func = 310');
        $this->addSql('ALTER ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `supla_v_user_channel_group` AS select `g`.`id` AS `id`,`g`.`func` AS `func`,`g`.`caption` AS `caption`,`g`.`user_id` AS `user_id`,`g`.`location_id` AS `location_id`,ifnull(`g`.`alt_icon`,0) AS `alt_icon`,`rel`.`channel_id` AS `channel_id`,`c`.`iodevice_id` AS `iodevice_id`,ifnull(`g`.`hidden`,0) AS `hidden` from ((((`supla`.`supla_dev_channel_group` `g` join `supla`.`supla_location` `l` on(`l`.`id` = `g`.`location_id`)) join `supla`.`supla_rel_cg` `rel` on(`rel`.`group_id` = `g`.`id`)) join `supla`.`supla_dev_channel` `c` on(`c`.`id` = `rel`.`channel_id`)) join `supla`.`supla_iodevice` `d` on(`d`.`id` = `c`.`iodevice_id`)) where `g`.`func` is not null and `g`.`func` <> 0 and `l`.`enabled` = 1 and `d`.`enabled` = 1');
        $this->addSql('ALTER TABLE `supla_iodevice` ADD `json_config` LONGTEXT NULL DEFAULT NULL AFTER `product_id`');
     }
}