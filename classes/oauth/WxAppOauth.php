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

class WxAppOauth extends BaseOauth {
	public function check($data) {
		return true;
	}

	public function getName() {
		return '小程序';
	}

	public function getDesc() {
		return '微信小程序登录';
	}

	public function getForm() {
		return new WxSetForm(true);
	}

	public function getOauthData() {

		return [];
	}
}