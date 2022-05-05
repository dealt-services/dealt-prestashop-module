CREATE TABLE IF NOT EXISTS `PREFIX_dealt_mission` (
  id_mission INT AUTO_INCREMENT NOT NULL,
  id_offer INT NOT NULL,
  id_product INT NOT NULL,
  id_dealt_product INT NOT NULL,
  id_order INT NOT NULL,
  dealt_id_mission VARCHAR(255) NOT NULL,
  dealt_status_mission VARCHAR(255) NOT NULL,
  dealt_gross_price_mission DECIMAL(20, 6) NOT NULL,
  dealt_vat_price_mission DECIMAL(20, 6) NOT NULL,
  dealt_net_price_mission DECIMAL(20, 6) NOT NULL,
  date_add DATETIME NOT NULL,
  date_upd DATETIME NOT NULL,
  PRIMARY KEY(id_mission)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;