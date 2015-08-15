CREATE TABLE usuarios(
  'id' INTEGER PRIMARY KEY,
  'email' VARCHAR(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `apellido` varchar(255) NOT NULL,
  `img_tipo` int(10) NOT NULL,
  `img_hash` varchar(255) NOT NULL,
  `huella` varchar(255) DEFAULT NULL,
  `puntos` int(11) NOT NULL DEFAULT '0',
  `advertencia` varchar(255) DEFAULT NULL,
  `suspendido` tinyint(1) NOT NULL DEFAULT '0',
  `es_funcionario` tinyint(1) NOT NULL DEFAULT '0',
  `es_jefe` tinyint(1) NOT NULL DEFAULT '0',
  `dni` varchar(255) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `fin_advertencia` timestamp NULL DEFAULT NULL,
  `fin_suspension` timestamp NULL DEFAULT NULL,
  `partido_id` int(10) DEFAULT NULL,
  `patrulla_id` int(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp DEFAULT NULL
);

INSERT INTO `usuarios` VALUES (NULL,'admin@virtuago.ra','$2y$10$gNhh1Kn979JFvf5UGkVg1eXbgyYqR53lVTHJO47KfOeFEjijRH8CS','Administrador','Test',1,'7a7eac7bb4e8a2d426f3cf61de2b119c','AdministradorTest',55,NULL,0,1,1,NULL,NULL,NULL,NULL,1,1,'2015-07-25 23:02:41','2015-07-25 23:55:55',NULL),(NULL,'user@virtuago.ra','$2y$10$LHrEZGwy0Y0R8S9OmVzcte8aytPwdzsJgiF0gnjgrdpc6ku0e6wmq','Usuario','Test',1,'41405fd19c2acb9616d7d42f7599fff5','UsuarioTest',0,NULL,0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2015-07-26 00:16:36','2015-07-26 00:16:36',NULL),(NULL,'borrar@virtuago.ra','$2y$10$M7ddh10EYjOkQm4hGKQM3Odedwo8MYhQ/zRVodi5fvEpQCgT01cR2','Borrable','Test',1,'6e5500a639ff50cf8f68fcbd5cb753a7','BorrableTest',0,NULL,0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2015-07-26 00:16:50','2015-07-26 00:16:50',NULL);
