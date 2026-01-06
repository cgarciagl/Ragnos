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
  PRIMARY KEY (`usu_id`) USING BTREE,
  UNIQUE INDEX `usu_login`(`usu_login` ASC) USING BTREE,
  INDEX `usu_grupo`(`usu_grupo` ASC) USING BTREE,
  INDEX `usu_token`(`usu_token` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of gen_usuarios
-- ----------------------------
INSERT INTO `gen_usuarios` VALUES (1, 'ADMIN', 'e0aa021e21dddbd6d8cecec71e9cf564', 'ADMINISTRADOR', 'S', 1, '36fc59a445906c482674e587434c766b2b912c045ea6f4e76e5eb14509e34913');
INSERT INTO `gen_usuarios` VALUES (4, 'CGARCIA', 'e0aa021e21dddbd6d8cecec71e9cf564', 'CARLOS GARCIA', 'S', 2, NULL);
INSERT INTO `gen_usuarios` VALUES (5, 'GUEST', '202cb962ac59075b964b07152d234b70', 'GUEST', 'N', 3, NULL);
INSERT INTO `gen_usuarios` VALUES (7, 'NUEVO', 'e0aa021e21dddbd6d8cecec71e9cf564', 'NUEVO', 'N', 3, NULL);
INSERT INTO `gen_usuarios` VALUES (8, 'JAHIR', 'd797c923b65fc09a009aae45aeb2c726', 'JAHIR CASTILLO', 'S', 1, '12345');


SET FOREIGN_KEY_CHECKS = 1;
