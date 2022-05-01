CREATE TABLE IF NOT EXISTS `PREFIX_dealt_offer` (
  id_offer INT AUTO_INCREMENT NOT NULL,
  dealt_id_offer VARCHAR(255) NOT NULL,
  title_offer VARCHAR(255) NOT NULL,
  id_virtual_product INT,
  date_add DATETIME NOT NULL,
  date_upd DATETIME NOT NULL,
  PRIMARY KEY(id_offer),
  UNIQUE (dealt_id_offer)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;