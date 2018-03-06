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
	 * 必须邀请才能注册
	 * @var \backend\form\CheckboxField
	 * @type bool
	 */
	public $need_rec = 0;
	/**
	 * 邀请码最多使用次数
	 * @var \backend\form\TextField
	 * @type int
	 * @digits
	 * @note 0表示不限制
	 */
	public $max_rec = 0;
	/**
	 * 注册限速
	 * @var \backend\form\TextField
	 * @type int
	 * @digits
	 * @layout 40,col-xs-6
	 * @note   最大账户，0为不限速
	 */
	public $max_count = 0;
	/**
	 * @var \backend\form\TextField
	 * @type int
	 * @digits
	 * @layout 40,col-xs-6
	 * @note   间隔，单位秒
	 */
	public $interval = 60;
	/**
	 * 黑名单(一行一个IP)
	 * @var \backend\form\TextareaField
	 * @type string
	 * @option {"row":10}
	 */
	public $blackips;
}