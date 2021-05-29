CREATE TABLE `configs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `business_name` varchar(16) NOT NULL DEFAULT '' COMMENT '所属业务',
  `key` varchar(64) DEFAULT '' COMMENT '配置KEY',
  `value` varchar(9600) DEFAULT '' COMMENT '配置值',
  `desc` varchar(255) DEFAULT '' COMMENT '配置说明',
  `json` tinyint(1) unsigned DEFAULT '1' COMMENT '是否为JSON数据，1 不是 2 是',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;