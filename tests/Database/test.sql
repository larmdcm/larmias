DROP TABLE IF EXISTS `t_user`;
CREATE TABLE `t_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键id',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` char(32) NOT NULL DEFAULT '' COMMENT '密码',
  `balance` decimal(20,3) NOT NULL DEFAULT 0 COMMENT '余额',
  `integral` int(10) NOT NULL DEFAULT 0 COMMENT '积分',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `info` JSON DEFAULT NULL COMMENT '字段数据表',
  `deleted` boolean NOT NULL DEFAULT 0 COMMENT '是否删除',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

DROP TABLE IF EXISTS `t_user_info`;
CREATE TABLE `t_user_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键id',
  `user_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '关联id',
  `age` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '年龄',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
  `deleted` boolean NOT NULL DEFAULT 0 COMMENT '是否删除',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户信息表';

DROP TABLE IF EXISTS `t_user_message`;
CREATE TABLE `t_user_message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键id',
  `user_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '关联id',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '内容',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户消息表';

DROP TABLE IF EXISTS `t_role`;
CREATE TABLE `t_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键id',
  `name` varchar(50) not null default '' COMMENT '角色名称',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色表';

DROP TABLE IF EXISTS `t_user_role`;
CREATE TABLE `t_user_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键id',
  `user_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '关联用户id',
  `role_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '关联角色id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户角色表';


INSERT INTO `larmias_test`.`t_role`(`id`, `name`, `update_time`, `create_time`) VALUES (1, '超级管理员', NULL, '2023-07-25 16:43:20');
INSERT INTO `larmias_test`.`t_role`(`id`, `name`, `update_time`, `create_time`) VALUES (2, '普通管理员', NULL, '2023-07-25 16:43:23');
INSERT INTO `larmias_test`.`t_role`(`id`, `name`, `update_time`, `create_time`) VALUES (3, '游客', NULL, '2023-07-25 16:43:28');
