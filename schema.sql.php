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
    `passwd` VARCHAR(256) NULL DEFAULT '' COMMENT '非第三方授权需要密码',
    `avatar` VARCHAR(255) NULL COMMENT '头像',
    `gender` TINYINT(1) UNSIGNED NULL DEFAULT 0 COMMENT '性别,1男2女0未知',
    `status` TINYINT(4) UNSIGNED NULL DEFAULT 0 COMMENT '状态0未激活1正常2禁用',
    `parent` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '推荐人ID',
    `spm` VARCHAR(90) NULL COMMENT '推荐追踪(最大10级)',
    `rec_code` VARCHAR(8) NULL COMMENT '推荐码',
    `channel` VARCHAR(16) NULL COMMENT '来源渠道',
    `ip` VARCHAR(64) NOT NULL COMMENT '注册IP地址',
    `login_time` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '最后登录时间',
    `device` VARCHAR(10) NULL COMMENT '最后登录设备',
    `remark` VARCHAR(255) NULL COMMENT '备注',
    PRIMARY KEY (`id`),
    UNIQUE INDEX `UDX_USERNAME` (`username` ASC),
    INDEX `IDX_PARENT` (`parent` ASC),
    INDEX `IDX_PHONE` (`phone` ASC),
    INDEX `IDX_CHANNEL` (`channel` ASC),
    INDEX `IDX_SPM` (`spm` ASC)
)  ENGINE=INNODB AUTO_INCREMENT=500000 DEFAULT CHARACTER SET={encoding} COMMENT='通行证'";

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
    `login_time` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '登录时间',
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

$tables['1.0.0'][] = "CREATE TABLE IF NOT EXISTS `{prefix}oauth_app` (
    `type` VARCHAR(10) NOT NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '启用',
    `ios` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'IOS登录',
    `android` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '安卓登录',
    `web` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'WEB登录',
    `options` TEXT NULL COMMENT '配置数据',
    PRIMARY KEY (`type`)
)  ENGINE=INNODB DEFAULT CHARACTER SET={encoding} COMMENT='第三方登录APP'";

$tables['1.0.0'][] = "INSERT INTO `{prefix}oauth_app` VALUES ('phone', 1, 1, 1, 1, NULL), ('email', 1, 0, 0, 1, NULL), ('wechat', 0, 0, 0, 0, NULL),('qq', 0, 0, 0, 0, NULL),('weibo', 0, 0, 0, 0, NULL)";

$tables['1.1.0'][] = "ALTER TABLE `{prefix}passport_meta` ADD UNIQUE INDEX `UDX_ID_NAME` (`passport_id` ASC, `name` ASC)";

$tables['1.1.0'][] = "ALTER TABLE `{prefix}oauth_meta` ADD UNIQUE INDEX `UDX_ID_NAME` (`oauth_id` ASC, `name` ASC)";

$tables['1.1.0'][] = "ALTER TABLE `{prefix}oauth_session` ADD INDEX `IDX_TOKEN` (`token` ASC)";

$tables['1.2.0'][] = "ALTER TABLE `{prefix}passport` ADD COLUMN `spl` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '推荐等级' AFTER `spm`";

$tables['1.2.1'][] = "ALTER TABLE `{prefix}oauth_session` 
ADD COLUMN `passport_id` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '通行证ID' AFTER `create_time`";

$tables['1.2.1'][] = "ALTER TABLE `{prefix}oauth_session` ADD INDEX `IDX_UID` (`passport_id` ASC)";

$tables['1.2.2'][] = "ALTER TABLE `{prefix}oauth_app` 
ADD COLUMN `pad` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '安卓pad' AFTER `web`,
ADD COLUMN `ipad` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'iPad' AFTER `pad`";

$tables['1.2.3'][] = "ALTER TABLE `{prefix}oauth_app` 
ADD COLUMN `pc` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '桌面' AFTER `ipad`,
ADD COLUMN `wxapp` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序' AFTER `pc`,
ADD COLUMN `wxgame` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小游戏' AFTER `wxapp`,
ADD COLUMN `h5` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'H5' AFTER `wxgame`";