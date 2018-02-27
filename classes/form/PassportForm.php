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
use wulaphp\validator\JQueryValidator;

class PassportForm extends FormTable {
	use JQueryValidator;
	/**
	 * @var \wulacms\ui\classes\HiddenField
	 * @type int
	 */
	public $id;
	/**
	 * 登录账户(<b class="text-danger">*</b>)
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @callback (checkUsername(id)) => 账户已经存在
	 * @layout 2, col-xs-6
	 */
	public $username;
	/**
	 * 昵称(<b class="text-danger">*</b>)
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @layout 2, col-xs-6
	 */
	public $nickname;
	/**
	 * 手机号
	 * @var \backend\form\TextField
	 * @type string
	 * @phone
	 * @callback (checkPhone(id)) => 手机已经存在
	 * @layout 3, col-xs-6
	 */
	public $phone;
	/**
	 * 邮件地址
	 * @var \backend\form\TextField
	 * @type string
	 * @email
	 * @callback (checkEmail(id)) => 邮箱已经存在
	 * @layout 3,col-xs-6
	 */
	public $email;
	/**
	 * 密码(<b class="text-danger">*</b>)
	 * @var \backend\form\PasswordField
	 * @type string
	 * @required
	 * @minlength (8)
	 * @passwd (3) => 必须由大、小写字母，符号，数字组成
	 * @layout 4,col-xs-6
	 */
	public $password;
	/**
	 * 确认密码
	 * @var \backend\form\PasswordField
	 * @type string
	 * @equalTo (#password)
	 * @layout 4,col-xs-6
	 */
	public $password1;
	/**
	 * 渠道
	 * @var \backend\form\TextField
	 * @type string
	 * @layout   5,col-xs-6
	 */
	public $channel;
	/**
	 * 激活
	 * @var \backend\form\CheckboxField
	 * @type bool
	 * @layout 5,col-xs-6
	 */
	public $status = 1;

	public function checkUsername($value, $data, $msg) {
		$id                = unget($data, 'id');
		$where['username'] = $value;
		if ($id) {
			$where['id <>'] = $id;
		}
		if ($this->exist($where)) {
			return $msg;
		}

		return true;
	}

	public function checkEmail($value, $data, $msg) {
		$id               = unget($data, 'id');
		$where['open_id'] = $value;
		$where['type']    = 'email';
		if ($id) {
			$where['passport_id <>'] = $id;
		}
		if ($this->db()->select('id')->from('{oauth}')->exist($where)) {
			return $msg;
		}

		return true;
	}

	public function checkPhone($value, $data, $msg) {
		$id               = unget($data, 'id');
		$where['open_id'] = $value;
		$where['type']    = 'phone';
		if ($id) {
			$where['passport_id <>'] = $id;
		}
		if ($this->db()->select('id')->from('{oauth}')->exist($where)) {
			return $msg;
		}

		return true;
	}
}