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
use passport\classes\form\WxSetForm;
use wulaphp\form\FormTable;

class WebWechatOauth extends BaseOauth {
	public function check(array $data) {
		return true;
	}

	public function getName(): string {
		return '网页微信';
	}

	public function getDesc(): string {
		return '网页微信登录';
	}

	public function getForm(): ?FormTable {
		return new WxSetForm(true);
	}
}