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

class QqOauth extends BaseOauth {
	public function check($data) {
		return true;
	}

	public function getName() {
		return 'QQ';
	}

	public function getDesc() {
		return '手机端QQ登录';
	}
}