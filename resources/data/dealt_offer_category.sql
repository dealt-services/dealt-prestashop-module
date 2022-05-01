CREATE TABLE IF NOT EXISTS `PREFIX_dealt_offer_category` (
  id INT AUTO_INCREMENT NOT NULL,
  id_offer INT NOT NULL,
  id_dealt_product INT NOT NULL,
  id_category INT NOT NULL,
  PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;