--
-- Table structure for table `media`
--

DROP TABLE IF EXISTS `media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_id` int(11) NOT NULL,
  `model_type` varchar(255)  DEFAULT NULL,
  `size` varchar(255)  DEFAULT NULL,
  `alt` varchar(512)  DEFAULT NULL,
  `title` varchar(255)  DEFAULT NULL,
  `file_name` varchar(255)  DEFAULT NULL,
  `original_name` varchar(255)  DEFAULT NULL,
  `thumb_file` varchar(255)  DEFAULT NULL, 
  `extension` varchar(255)  DEFAULT NULL,
  `state_id` int(11)  DEFAULT '0',
  `type_id` int(11)  DEFAULT '0',
  `created_on` datetime NOT NULL,
  `updated_on` datetime DEFAULT NULL,
  `create_user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (create_user_id) REFERENCES user(id),
  CONSTRAINT `fk_media_create_user_id` FOREIGN KEY (`create_user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
