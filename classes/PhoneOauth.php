<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace passport\classes;

class PhoneOauth extends BaseOauth {
	public function check($data) {
		return true;
	}

	public function getName() {
		return '手机';
	}

	public function getDesc() {
		return '手机号登录';
	}
}