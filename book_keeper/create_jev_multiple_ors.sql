CREATE TABLE IF NOT EXISTS `jev_multiple_ors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jev_id` int(11) NOT NULL,
  `ors_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `jev_id` (`jev_id`),
  KEY `ors_id` (`ors_id`),
  CONSTRAINT `jev_multiple_ors_ibfk_1` FOREIGN KEY (`jev_id`) REFERENCES `jev` (`jev_id`) ON DELETE CASCADE,
  CONSTRAINT `jev_multiple_ors_ibfk_2` FOREIGN KEY (`ors_id`) REFERENCES `ors` (`ors_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 