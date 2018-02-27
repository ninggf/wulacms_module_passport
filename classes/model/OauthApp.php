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
use passport\classes\PhoneOauth;
use wulaphp\db\Table;

class OauthApp extends Table {
	public static function getApps() {
		static $apps = false;
		if ($apps === false) {
			$apps = apply_filter('passport\getOauthApps', [
				'phone' => new PhoneOauth(),
				'email' => new EmailOauth()
			]);
		}

		return $apps;
	}

	public static function getAppsName() {
		$apps  = self::getApps();
		$names = [];
		foreach ($apps as $ap => $app) {
			$names[ $ap ] = $app->getName();
		}

		return $names;
	}
}