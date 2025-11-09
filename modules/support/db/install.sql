--
-- Table structure for table `support_category`
--
DROP TABLE IF EXISTS `support_category`;
CREATE TABLE `support_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` varchar(512) DEFAULT NULL,
  `title` varchar(512) NOT NULL,
  `state_id` int(11)  DEFAULT '0',
  `type_id` int(11)  DEFAULT '0',
  `created_on` datetime DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL,
  `create_user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (create_user_id) REFERENCES user(id),
  CONSTRAINT `fk_support_category_create_user_id` FOREIGN KEY (`create_user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `support_solution`
--
DROP TABLE IF EXISTS `support_solution`;
CREATE TABLE `support_solution` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `title` varchar(512) NOT NULL,
  `description` text NOT NULL,
  `state_id` int(11)  DEFAULT '0',
  `type_id` int(11)  DEFAULT '0',
  `created_on` datetime DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL,
  `create_user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (create_user_id) REFERENCES user(id),
  FOREIGN KEY (category_id) REFERENCES support_category(id),
  CONSTRAINT `fk_support_solution_category_id` FOREIGN KEY (`create_user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_support_solution_create_user_id` FOREIGN KEY (`create_user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;