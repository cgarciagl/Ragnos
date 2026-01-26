SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for gen_gruposdeusuarios
-- ----------------------------
DROP TABLE IF EXISTS `gen_gruposdeusuarios`;
CREATE TABLE `gen_gruposdeusuarios`  (
  `gru_id` int NOT NULL AUTO_INCREMENT,
  `gru_nombre` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`gru_id`) USING BTREE,
  UNIQUE INDEX `gru_nombre`(`gru_nombre` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of gen_gruposdeusuarios
-- ----------------------------
INSERT INTO `gen_gruposdeusuarios` VALUES (1, 'ADMINISTRADOR');
INSERT INTO `gen_gruposdeusuarios` VALUES (2, 'CAPTURISTA');
INSERT INTO `gen_gruposdeusuarios` VALUES (3, 'INVITADO');

-- ----------------------------
-- Table structure for gen_usuarios
-- ----------------------------
DROP TABLE IF EXISTS `gen_usuarios`;
CREATE TABLE `gen_usuarios`  (
  `usu_id` int NOT NULL AUTO_INCREMENT,
  `usu_login` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `usu_pword` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `usu_nombre` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `usu_activo` varchar(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'S',
  `usu_grupo` int NULL DEFAULT NULL,
  `usu_token` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `usu_avatar` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`usu_id`) USING BTREE,
  UNIQUE INDEX `usu_login`(`usu_login` ASC) USING BTREE,
  INDEX `usu_grupo`(`usu_grupo` ASC) USING BTREE,
  INDEX `usu_token`(`usu_token` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of gen_usuarios
-- ----------------------------
INSERT INTO `gen_usuarios` VALUES (1, 'ADMIN', '73acd9a5972130b75066c82595a1fae3', 'ADMINISTRADOR', 'S', 1, '36fc59a445906c482674e587434c766b2b912c045ea6f4e76e5eb14509e34913', NULL);
INSERT INTO `gen_usuarios` VALUES (4, 'CGARCIA', '73acd9a5972130b75066c82595a1fae3', 'CARLOS GARCIA', 'S', 2, NULL, NULL);
INSERT INTO `gen_usuarios` VALUES (5, 'GUEST', '202cb962ac59075b964b07152d234b70', 'GUEST', 'N', 3, NULL, NULL);
INSERT INTO `gen_usuarios` VALUES (7, 'NUEVO', '73acd9a5972130b75066c82595a1fae3', 'NUEVO', 'N', 3, NULL, NULL);
INSERT INTO `gen_usuarios` VALUES (8, 'JAHIR', 'd797c923b65fc09a009aae45aeb2c726', 'JAHIR CASTILLO', 'S', 1, '12345', NULL);

-- ----------------------------
-- Table structure for gen_audit_logs
-- ----------------------------
DROP TABLE IF EXISTS `gen_audit_logs`;
CREATE TABLE `gen_audit_logs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int NULL DEFAULT NULL COMMENT 'ID del usuario que hizo el cambio',
  `table_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tabla afectada',
  `record_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID del registro afectado',
  `action` enum('INSERT','UPDATE','DELETE') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL COMMENT 'Guarda diferencias: {campo: {old: A, new: B}}',
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_at` datetime NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_table_record`(`table_name` ASC, `record_id` ASC) USING BTREE,
  INDEX `idx_user`(`user_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 0 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;



SET FOREIGN_KEY_CHECKS = 1;
