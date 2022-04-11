CREATE TABLE IF NOT EXISTS `PREFIX_dealt_mission` (
  id_mission INT AUTO_INCREMENT NOT NULL,
  dealt_id_mission VARCHAR(255) NOT NULL,
  title_mission VARCHAR(255) NOT NULL,
  description_mission VARCHAR(255),
  date_added DATETIME NOT NULL,
  date_updated DATETIME NOT NULL,
  PRIMARY KEY(id_mission)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;