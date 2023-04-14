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