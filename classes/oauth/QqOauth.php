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
use passport\classes\form\QqSetForm;
use wulaphp\form\FormTable;

class QqOauth extends BaseOauth {
	public function check(array $data) {
		$app_id = $this->options['app_id'] ?? false;
		if (!$data['openid'] || !$data['meta']['accessToken'] || !$app_id) {
			log_error('openid or accessToken appid not find', 'oauth_qq');

			return false;
		}
		$qq_check = $this->checkQq($data['openid'], $data['meta']['accessToken'], $app_id);

		return $qq_check;
	}

	public function getName(): string {
		return 'QQ';
	}

	public function getDesc(): string {
		return '手机端QQ登录';
	}

	public function getForm(): ?FormTable {
		return new QqSetForm(true);
	}
}