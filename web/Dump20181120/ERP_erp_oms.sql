-- MySQL dump 10.13  Distrib 5.7.23, for Linux (x86_64)
--
-- Host: localhost    Database: ERP
-- ------------------------------------------------------
-- Server version	5.5.5-10.1.28-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `erp_oms`
--

DROP TABLE IF EXISTS `erp_oms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `erp_oms` (
  `id_erp_oms` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tracking_nr` varchar(255) NOT NULL,
  `order_nr` bigint(100) DEFAULT NULL,
  `package_nr` varchar(255) DEFAULT NULL,
  `fk_delivery_company` int(10) unsigned NOT NULL,
  `return_reasons` varchar(1024) DEFAULT NULL,
  `shipped_date` datetime NOT NULL,
  `delivered_date` datetime NOT NULL,
  `receivables` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_erp_oms`),
  KEY `fk_erp_delivery_company_idx` (`fk_delivery_company`),
  KEY `status_erp_oms_index` (`status`),
  KEY `created_at_erp_oms_index` (`created_at`),
  KEY `updated_at_erp_oms_index` (`updated_at`),
  KEY `tracking_nr_erp_oms_index` (`tracking_nr`),
  KEY `order_nr_erp_oms_index` (`order_nr`),
  KEY `receivables_erp_oms_index` (`receivables`),
  KEY `shipped_date_erp_oms_index` (`shipped_date`),
  KEY `delivered_date_erp_oms_index` (`delivered_date`),
  CONSTRAINT `fk_erp_delivery_company` FOREIGN KEY (`fk_delivery_company`) REFERENCES `erp_delivery_company` (`id_erp_delivery_company`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `erp_oms`
--

LOCK TABLES `erp_oms` WRITE;
/*!40000 ALTER TABLE `erp_oms` DISABLE KEYS */;
/*!40000 ALTER TABLE `erp_oms` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-11-20 11:57:54
