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

use passport\classes\model\PassportTable;
use wulaphp\app\App;
use wulaphp\auth\Passport;

class VipPassport extends Passport {
	/**
	 * 登录
	 *
	 * @param int|string|array $data
	 *
	 * @return bool
	 */
	protected function doAuth($data = null) {
		$user = false;
		if (is_numeric($data)) {
			$passportTable = new PassportTable();
			$user          = $passportTable->get($data, 'id,username,nickname,phone,avatar,gender,rec_code,status')->ary();
			if (!$user) {
				$this->error = '用户不存在';

				return false;
			}
		} else if (is_array($data)) {
			$type    = aryget('type', $data, 'phone');
			$account = aryget('account', $data);
			$passwd  = aryget('passwd', $data);
			if (!$account) {
				$this->error = '用户名为空';

				return false;
			}
			if ($type == 'phone') {
				$where['phone'] = $account;
			} else {
				$where['username'] = $account;
			}
			$passportTable = new PassportTable();
			$user          = $passportTable->get($where, 'id,username,nickname,phone,avatar,gender,rec_code,status,passwd')->ary();
			if (!$user) {
				$this->error = '用户名密码不匹配';

				return false;
			}
			$passwdCheck = Passport::verify($passwd, $user['passwd']);
			if (!$passwdCheck) {
				$this->error = '用户名密码不匹配';

				return false;
			}
		}
		if (!$user) {
			$this->error = '用户不存在';

			return false;
		}
		//封号
		if ($user['status'] == 0) {
			$this->error = '账号已被禁用,联系客服';

			return false;
		}
		$needBind = App::bcfg('need_bind@passport');
		if ($needBind && empty($user['phone'])) {
			$user['status'] = 2;//需要绑定手机
		}
		$user = apply_filter('passport\onLogined', $user, $user);
		if (!$user) {
			$this->error = '不允许登录';

			return false;
		}

		$this->uid               = $user['id'];
		$this->username          = $user['username'];
		$this->nickname          = $user['nickname'] ? $user['nickname'] : substr_replace($user['phone'], '****', 3, 4);
		$this->phone             = $user['phone'];
		$this->email             = $user['email'];
		$this->avatar            = $user['avatar'] ? $user['avatar'] : 'images/touxiang.png';
		$this->data['gender']    = $user['gender'];
		$this->data['status']    = $user['status'];
		$this->data['rec_code']  = $user['rec_code'];
		$this->data['logintime'] = time();

		return true;
	}

	public function store() {
		fire('passport\onVipPassportLogin', $this);

		return parent::store();
	}
}