CREATE TABLE IF NOT EXISTS `charityclear` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `transid` varchar(38) NOT NULL,
  `zen_order_id` varchar(38) NOT NULL,
  `received` int(11) NOT NULL,
  `xref` varchar(40) NOT NULL,
  `authorisationCode` int(11) NOT NULL,
  `action` varchar(10) NOT NULL,
  `responseMessage` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
