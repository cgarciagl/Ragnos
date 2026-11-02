-- =============================================================================
--  Ragnos Framework · Base de datos del sistema / System database
-- -----------------------------------------------------------------------------
--  Tablas mínimas para que Ragnos funcione: usuarios, grupos, bitácora de
--  auditoría y sesiones. Minimal tables Ragnos needs to run: users, groups,
--  audit log and sessions.
--
--  Uso / Usage (MariaDB 10.4+ / MySQL 8+):
--      mysql -u root -p ragnos < sampledatabase/ragnos_base.sql
--
--  Acceso inicial / Initial login:  admin / admin   (¡cámbialo! / change it!)
--
--  ⚠ Script de instalación inicial: elimina y recrea estas tablas.
--  ⚠ Fresh-install script: it drops and recreates these tables.
-- =============================================================================


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `gen_gruposdeusuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gen_gruposdeusuarios` (
  `gru_id` int(11) NOT NULL AUTO_INCREMENT,
  `gru_nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`gru_id`) USING BTREE,
  UNIQUE KEY `gru_nombre` (`gru_nombre`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `gen_gruposdeusuarios` DISABLE KEYS */;
INSERT INTO `gen_gruposdeusuarios` VALUES
(1,'ADMINISTRADOR'),
(2,'CAPTURISTA'),
(3,'INVITADO');
/*!40000 ALTER TABLE `gen_gruposdeusuarios` ENABLE KEYS */;
DROP TABLE IF EXISTS `gen_usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gen_usuarios` (
  `usu_id` int(11) NOT NULL AUTO_INCREMENT,
  `usu_login` varchar(50) NOT NULL,
  `usu_pword` varchar(255) NOT NULL,
  `usu_nombre` varchar(100) NOT NULL,
  `usu_activo` varchar(1) NOT NULL DEFAULT 'S',
  `usu_grupo` int(11) DEFAULT NULL,
  `usu_token` varchar(100) DEFAULT NULL,
  `usu_avatar` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`usu_id`) USING BTREE,
  UNIQUE KEY `usu_login` (`usu_login`) USING BTREE,
  KEY `usu_grupo` (`usu_grupo`) USING BTREE,
  KEY `usu_token` (`usu_token`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `gen_usuarios` DISABLE KEYS */;
INSERT INTO `gen_usuarios` VALUES
(1,'ADMIN','$2y$10$3fZ/HriMoOJgqGcnlsEDhe/KSE87.KIinYP2MVCwsfCi0L/XIEASu','Administrador','S',1,NULL,NULL);
/*!40000 ALTER TABLE `gen_usuarios` ENABLE KEYS */;
DROP TABLE IF EXISTS `gen_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gen_audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT 'ID del usuario que hizo el cambio',
  `table_name` varchar(100) NOT NULL COMMENT 'Tabla afectada',
  `record_id` varchar(50) NOT NULL COMMENT 'ID del registro afectado',
  `action` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `changes` longtext DEFAULT NULL COMMENT 'Guarda diferencias: {campo: {old: A, new: B}}',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_table_record` (`table_name`,`record_id`) USING BTREE,
  KEY `idx_user` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `gen_audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `gen_audit_logs` ENABLE KEYS */;
DROP TABLE IF EXISTS `ci_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ci_sessions` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `data` blob NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `ci_sessions_timestamp` (`timestamp`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `ci_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ci_sessions` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

