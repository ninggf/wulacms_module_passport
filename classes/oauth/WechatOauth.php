<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace passport\classes\oauth;

use passport\classes\BaseOauth;

class WechatOauth extends BaseOauth {
	public function check($data) {
		return true;
	}

	public function getName() {
		return '微信';
	}

	public function getDesc() {
		return '手机端微信登录';
	}
}