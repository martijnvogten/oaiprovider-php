CREATE DATABASE IF NOT EXISTS library DEFAULT CHARSET utf8;
USE library;
DROP TABLE IF EXISTS `books`;
CREATE TABLE `books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(255) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
);

INSERT INTO `books` (`deleted`, `category`, `author`, `title`, `description`) VALUES
(0, 'FICTION', 'Leo Tolstoy', 'Anna Karenina', 'Anna Karenina is one of the most loved and memorable heroines of literature. Her overwhelming charm dominates a novel of unparalleled richness and density.'),
(1, 'NON-FICTION', 'Isaac Newton', 'Mathematical Principles of Natural Philosophy', 'In his monumental 1687 work Philosophiae Naturalis Principia Mathematica, known familiarly as the Principia, Isaac Newton laid out in mathematical terms the principles of time, force, and motion that have guided the development of modern physical science.'),
(0, 'NON-FICTION', 'Albert Einstein', 'Relativity, the Special and the General Theory', 'Relativity to those readers who, from a general scientific and philosophical point of view, are interested in the theory, but who are not conversant with the mathematical apparatus of theoretical physics.');