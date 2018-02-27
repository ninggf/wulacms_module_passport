<?php
@defined('APPROOT') or header('Page Not Found', true, 404) || die();

$tables['1.0.0'][] = "CREATE TABLE IF NOT EXISTS `{prefix}passport` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `create_time` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
    `update_time` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
    `deleted` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除',
    `username` VARCHAR(32) NOT NULL COMMENT '用户名',
    `nickname` VARCHAR(64) NULL COMMENT '昵称',
    `phone` VARCHAR(20) NULL COMMENT '手机号',
    `passwd` VARCHAR(32) NULL DEFAULT '' COMMENT '非第三方授权需要密码',
    `avatar` VARCHAR(255) NULL COMMENT '头像',
    `gender` TINYINT(1) UNSIGNED NULL DEFAULT 0 COMMENT '性别,1男2女0未知',
    `status` TINYINT(4) UNSIGNED NULL DEFAULT 0 COMMENT '状态0未激活1正常2禁用',
    `parent` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '推荐人ID',
    `spm` VARCHAR(90) NULL COMMENT '推荐追踪(最大10级)',
    `rec_code` VARCHAR(8) NULL COMMENT '推荐码',
    `channel` VARCHAR(16) NULL COMMENT '来源渠道',
    `remark` VARCHAR(255) NULL COMMENT '备注',
    PRIMARY KEY (`id`),
    UNIQUE INDEX `UDX_USERNAME` (`username` ASC),
    INDEX `IDX_PARENT` (`parent` ASC),
    INDEX `IDX_PHONE` (`phone` ASC),
    INDEX `IDX_CHANNEL` (`channel` ASC),
    INDEX `IDX_SPM` (`spm` ASC)
)  ENGINE=INNODB DEFAULT CHARACTER SET={encoding} COMMENT='通行证'";

$tables['1.0.0'][] = "CREATE TABLE IF NOT EXISTS `{prefix}passport_meta` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `passport_id` INT UNSIGNED NOT NULL COMMENT '通行证ID',
    `name` VARCHAR(32) NOT NULL COMMENT '键名',
    `value` TEXT NULL COMMENT '键值',
    `remark` VARCHAR(255) NULL COMMENT '备注',
    PRIMARY KEY (`id`),
    INDEX `FK_PASSPORT_ID` (`passport_id` ASC)
)  ENGINE=INNODB DEFAULT CHARACTER SET={encoding} COMMENT='通行证属性扩展'";

$tables['1.0.0'][] = "CREATE TABLE IF NOT EXISTS `{prefix}oauth` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `create_time` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '第一次授权时间',
    `update_time` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '最近一次授权时间',
    `deleted` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除',
    `type` VARCHAR(10) NOT NULL COMMENT '来源标识(qq,微信)',
    `passport_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '通行证ID',
    `open_id` VARCHAR(96) NOT NULL COMMENT '第三方openid',
    `union_id` VARCHAR(96) NOT NULL COMMENT 'UNIONID',
    `device` VARCHAR(10) NOT NULL COMMENT '设备登录标识(pc,app)',
    `remark` VARCHAR(255) NULL,
    PRIMARY KEY (`id`),
    INDEX `FK_PASSPORTID` USING BTREE (`passport_id` ASC),
    UNIQUE INDEX `UDX_TYPE_ID` (`type` ASC , `open_id` ASC)
)  ENGINE=INNODB DEFAULT CHARACTER SET={encoding} COMMENT='第三方授权登录'";

$tables['1.0.0'][] = "CREATE TABLE IF NOT EXISTS `{prefix}oauth_meta` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `oauth_id` INT UNSIGNED NOT NULL COMMENT '第三方ID',
    `name` VARCHAR(32) NOT NULL COMMENT '键名',
    `value` TEXT NULL COMMENT '键值',
    `remark` VARCHAR(255) NULL COMMENT '备注',
    PRIMARY KEY (`id`),
    INDEX `FK_OAUTH_ID` (`oauth_id` ASC)
)  ENGINE=INNODB DEFAULT CHARACTER SET={encoding} COMMENT='授权属性扩展'";

$tables['1.0.0'][] = "CREATE TABLE IF NOT EXISTS `{prefix}oauth_session` (
    `oauth_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '关联oauth',
    `create_time` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '登录时间',
    `token` VARCHAR(96) NOT NULL COMMENT '授权访问token',
    `expiration` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '过期时间',
    `device` VARCHAR(10) NOT NULL COMMENT '设备登录标识(pc,app...)',
    `ip` VARCHAR(64) NOT NULL COMMENT '登录IP',
    INDEX `IDX_OAUTHID` (`oauth_id` ASC),
    INDEX `IDX_CTIME` (`create_time` ASC),
    INDEX `IDX_EXPIRE` (`expiration` ASC)
)  ENGINE=INNODB DEFAULT CHARACTER SET={encoding} COMMENT='授权用户登录表'";
