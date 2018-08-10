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

use EasyWeChat\Kernel\Exceptions\DecryptException;
use passport\classes\BaseOauth;
use passport\classes\form\WxSetForm;
use weixin\classes\WxAccount;
use wulaphp\form\FormTable;

class WxAppOauth extends BaseOauth {
	/**
	 * @var WxAccount
	 */
	private $wechat;

	/**
	 * @param array $data
	 *
	 * @return bool|array
	 */
	public function check(array $data) {
		$wxid = $this->options['wxid'] ?? false;
		if (!$wxid) {
			log_error('wxid not configured', 'oauth_wxapp');

			return false;
		}
		$wechat = WxAccount::getWechat($wxid);
		if (!$wechat) {
			log_error($wxid . ' is not available', 'oauth_wxapp');

			return false;
		}
		try {
			$session = $wechat->miniApp->auth->session($data['meta']['code']);
		} catch (\Exception $e) {
			$session = null;
			log_error($e->getMessage(), 'oauth_wxapp');

			return false;
		}
		if (!$session) {
			log_error('session not find', 'oauth_wxapp');

			return false;
		}
		$rtn['session_key'] = $session['session_key'];
		$rtn['openid']      = $session['openid'];
		if ($session['unionid']) {
			$rtn['unionid'] = $session['unionid'];
		} else {
			$rtn['unionid'] = $session['openid'];
		}
		$this->wechat = $wechat;

		return $rtn;
	}

	public function getName(): string {
		return '小程序';
	}

	public function getDesc(): string {
		return '微信小程序登录';
	}

	public function getForm(): ?FormTable {
		return new WxSetForm(true);
	}

	public function getOauthData(?array $meta = null): array {
		if (!$this->wechat || !$meta) {
			return [];
		}
		try {
			$info = $this->wechat->miniApp->encryptor->decryptData($meta['session_key'],$meta['iv'],$meta['encryptedData']);
		} catch (DecryptException $e) {
			$info = false;
			log_error($e->getMessage(), 'oauth_wxapp');
		}

		return $info;
	}
}