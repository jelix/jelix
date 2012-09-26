
CREATE TABLE IF NOT EXISTS `towns` (
  `postalcode` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `department` varchar(30) NOT NULL,
  PRIMARY KEY (`postalcode`)
) ENGINE=InnoDB;

DELETE FROM `towns`;
INSERT INTO `towns` (`postalcode`, `name`, `department`) VALUES
(29000, 'Quimper', 'finistere'),
(29100, 'Douarnenez', 'finistere'),
(29150, 'Chateaulin', 'finistere'),
(29200, 'Brest', 'finistere'),
(37000, 'Tours', 'touraine'),
(37300, 'Joué-lès-Tours', 'touraine'),
(37310, 'Chambourg sur indre', 'touraine'),
(37400, 'Amboise', 'touraine'),
(98701, 'Arue', 'polynesia'),
(98714, 'Papeete', 'polynesia'),
(98730, 'Bora Bora', 'polynesia'),
(98731, 'Huahine', 'polynesia');
