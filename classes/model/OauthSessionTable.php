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

use wulaphp\app\App;
use wulaphp\db\Table;
use wulaphp\util\RedisClient;

class OauthSessionTable extends Table {
	/**
	 * 获取用户信息.
	 *
	 * @param string $token 登录令牌
	 * @param bool   $force 是否强制加载用户信息
	 *
	 * @return array|bool 用户信息,加载失败返回false.
	 */
	public function getInfo($token, $force = false) {
		if (empty($token)) {
			return false;
		}
		try {
			$redis = RedisClient::getRedis(App::icfg('redisdb@passport', 10));
			$info  = $redis->get($token);
			if (!$info) {
				return false;
			}
			$info = @json_decode($info, true);
			if (!$info) {
				$redis->del($token);

				return false;
			}
			if ($force) {//重新加载用户属性
				$dbx      = $this->dbconnection;
				$passport = $dbx->select('*')->from('{passport}')->where(['id' => $info['uid']])->get(0);
				if (!$passport) {
					$redis->del($token);

					return false;
				}
				$info['uid']      = $passport['id'];
				$info['username'] = $passport['username'];
				if ($passport['nickname']) {
					$info['nickname'] = $passport['nickname'];
				}
				if ($passport['phone']) {
					$info['phone'] = $passport['phone'];
				}
				if ($passport['avatar']) {
					$info['avatar'] = $passport['avatar'];
				}
				if ($passport['gender']) {
					$info['gender'] = $passport['gender'];
				}
				$info['status'] = $passport['status'];
				$needBind       = App::bcfg('need_bind@passport');
				if ($needBind && empty($info['phone'])) {
					$info['status'] = 2;//需要绑定手机
				}
				$info = apply_filter('passport\onLogined', $info, $passport);
				$meta = $dbx->select('name,value')->from('{passport_meta}')->where(['passport_id' => $info['uid']])->toArray('value', 'name');
				if ($meta) {
					$info = array_merge($meta, $info);
				}
				$expire = App::icfgn('expire@passport', 3650)*86400;
				$infox  = json_encode($info);
				if ($expire) {
					$rtn = $redis->setex($token, $expire, $infox);
				} else {
					$rtn = $redis->set($token, $infox);
				}
				if (!$rtn) {
					$redis->del($token);

					return false;
				}
			}

			return $info;
		} catch (\Exception $e) {

		}

		return false;
	}
}