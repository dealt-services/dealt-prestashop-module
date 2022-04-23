CREATE TABLE IF NOT EXISTS `PREFIX_dealt_virtual_product_category` (
  id_dealt_mission INT NOT NULL,
  id_virtual_product INT NOT NULL,
  id_category INT NOT NULL
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;