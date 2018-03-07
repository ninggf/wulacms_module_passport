<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace passport\classes\model;

use passport\classes\EmailOauth;
use passport\classes\oauth\QqOauth;
use passport\classes\oauth\WebQqOauth;
use passport\classes\oauth\WebWechatOauth;
use passport\classes\oauth\WechatOauth;
use passport\classes\PhoneOauth;
use wulaphp\db\Table;

class OauthApp extends Table {
	protected $autoIncrement = false;
	protected $primaryKeys   = ['type'];

	public function newApp($app) {
		try {
			return $this->insert($app);
		} catch (\Exception $e) {
			return false;
		}
	}

	public function updateApp($app) {
		return $this->update($app, ['type' => $app['type']]);
	}

	public function apps() {
		$apps = self::getApps();
		$data = [];
		if ($apps) {
			$ids  = array_keys($apps);
			$sql  = $this->find(['type IN' => $ids]);
			$list = $sql->toArray(null, 'type');

			foreach ($apps as $id => $app) {
				$list[ $id ]['id']      = $id;
				$list[ $id ]['name']    = $app->getName();
				$list[ $id ]['desc']    = $app->getDesc();
				$list[ $id ]['hasForm'] = $app->getForm() ? true : false;
				$data[]                 = $list[ $id ];
			}
		}

		return $data;
	}

	/**
	 * @return \passport\classes\IOauth[]
	 */
	public static function getApps() {
		static $apps = false;
		if ($apps === false) {
			$apps = apply_filter('passport\getOauthApps', [
				'phone'     => new PhoneOauth(),
				'email'     => new EmailOauth(),
				'qq'        => new QqOauth(),
				'wechat'    => new WechatOauth(),
				'webqq'     => new WebQqOauth(),
				'webwechat' => new WebWechatOauth()
			]);
		}

		return $apps;
	}

	/**
	 * @return string[]
	 */
	public static function getAppsName() {
		$apps  = self::getApps();
		$names = [];
		foreach ($apps as $ap => $app) {
			$names[ $ap ] = $app->getName();
		}

		return $names;
	}
}