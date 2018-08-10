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

class EmailOauth extends BaseOauth {
	public function check(array $data) {
		return true;
	}

	public function getName():string {
		return '邮件';
	}

	public function getDesc():string {
		return '邮件登录';
	}
}