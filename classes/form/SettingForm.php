<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace passport\classes\form;

use wulaphp\form\FormTable;

class SettingForm extends FormTable {
	public $table = null;
	/**
	 * 允许注册
	 * @var \backend\form\CheckboxField
	 * @type bool
	 */
	public $enabled = 1;
	/**
	 * 第三方登录必须绑定手机
	 * @var \backend\form\CheckboxField
	 * @type bool
	 */
	public $need_bind = 0;
	/**
	 * 短信登录
	 * @var \backend\form\CheckboxField
	 * @type bool
	 */
	public $code_login = 0;
	/**
	 * 开启验证码验证
	 * @var \backend\form\CheckboxField
	 * @type bool
	 */
	public $captcha = 0;
	/**
	 * 允许修改用户资料
	 * @var \backend\form\CheckboxField
	 * @type bool
	 */
	public $allow_update = 1;
	/**
	 * 允许客户端重新加载用户信息
	 * @var \backend\form\CheckboxField
	 * @type bool
	 */
	public $force_reload = 0;
	/**
	 * 必须邀请才能注册
	 * @var \backend\form\CheckboxField
	 * @type bool
	 * @layout 35,col-xs-4
	 */
	public $need_rec = 0;
	/**
	 *
	 * @var \backend\form\TextField
	 * @type int
	 * @digits
	 * @note   邀请码最多使用次数(0表示不限制)
	 * @layout 35,col-xs-8
	 */
	public $max_rec = 0;
	/**
	 * 注册限速
	 * @var \backend\form\TextField
	 * @type int
	 * @digits
	 * @layout 40,col-xs-4
	 * @note   最大账户，0为不限速
	 */
	public $max_count = 0;
	/**
	 * @var \backend\form\TextField
	 * @type int
	 * @digits
	 * @layout 40,col-xs-8
	 * @note   间隔，单位秒
	 */
	public $interval = 60;
	/**
	 * 登录过期时间(单位天)
	 * @var \backend\form\TextField
	 * @type int
	 * @digits
	 * @layout 45,col-xs-4
	 * @note   0表示久不过期
	 */
	public $expire = 0;
	/**
	 *
	 * @var \backend\form\TextField
	 * @type int
	 * @digits
	 * @range (0,15)
	 * @layout 45,col-xs-4
	 * @note   登录TOKEN存储在redis的哪个库
	 */
	public $redisdb = 10;
	/**
	 * 黑名单(一行一个IP)
	 * @var \backend\form\TextareaField
	 * @type string
	 * @option {"row":10}
	 */
	public $blackips;
}