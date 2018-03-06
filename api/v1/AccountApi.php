<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace passport\api\v1;

use passport\classes\model\PassportTable;
use passport\classes\RegisterUtil;
use rest\api\v1\ClientApi;
use rest\classes\API;
use sms\classes\tpl\RegCodeTemplate;
use sms\classes\tpl\ResetPasswdTemplate;
use wulaphp\app\App;
use wulaphp\auth\Passport;
use wulaphp\io\Request;

/**
 * Class AccountApi
 * @package passport\api\v1
 * @name 账户
 */
class AccountApi extends API {
	/**
	 * 使用手机注册通行证
	 *
	 * @apiName 手机注册
	 * @session
	 *
	 * @param string $phone    (required) 手机号
	 * @param string $code     (required) 手机验证码(短信模板`register_code`)
	 * @param string $channel  渠道
	 * @param string $device   (required,sample=ios,android,h5,wxapp,wxgame,pc) 设备
	 * @param string $cid      (required,sample=adfa2232sa) 端ID
	 * @param string $password 密码
	 * @param string $recCode  (sample=defx) 推荐码
	 *
	 * @paramo  int uid 用户ID
	 *
	 * @error   302=>手机号已经存在
	 * @error   400=>未知设备
	 * @error   401=>验证码不正确
	 * @error   402=>不允许注册
	 * @error   403=>注册关闭
	 * @error   404=>非法终端
	 * @error   405=>推荐码不可用
	 * @error   406=>注册过快
	 * @error   501=>内部错误
	 *
	 * @return array {
	 *  "uid":10000
	 * }
	 * @throws
	 */
	public function register($phone, $code, $channel = '', $device = '', $cid = '', $password = '', $recCode = '') {
		if (!isset(ClientApi::device[ $device ])) {
			$this->error(400, '未知设备');
		}
		if (!preg_match('/^1[3456789]\d{9}$/', $phone)) {
			$this->error(407, '手机号码格式不对');
		}
		$enabled = App::bcfg('enabled@passport');
		if (!$enabled) {
			$this->error(403, '注册停止');
		}
		if (!RegCodeTemplate::validate($code)) {
			$this->error(401, '验证码不正确');
		}

		$ip = Request::getIp();
		if (!RegisterUtil::checkIp($ip)) {
			$this->error(402, '不允许注册');
		}
		if (!RegisterUtil::checkClient($cid)) {
			$this->error(404, '非法终端');
		}
		if (!RegisterUtil::checkRecCode($recCode)) {
			$this->error(405, '推荐码不可用');
		}
		if (!RegisterUtil::limit($ip)) {
			$this->error(406, '不允许注册');
		}

		if (!RegisterUtil::checkPhone($phone)) {
			$this->error(302, '手机号已经存在');
		}
		$table = new PassportTable();

		$data['phone'] = $phone;
		//推荐码
		if ($recCode) {
			$data['recom'] = $recCode;
		}
		//密码
		if ($password) {
			$data['passwd'] = Passport::passwd($password);
		}
		$data['username'] = uniqid();
		$data['status']   = 1;
		$data['channel']  = $channel;
		$data['device']   = $device;
		$data['ip']       = $ip;
		$id               = $table->newAccount($data);
		if (!$id) {
			$this->error(500, '内部错误');
		}

		return ['uid' => $id];
	}

	/**
	 * 重设密码。
	 * @apiName 重设密码
	 * @session
	 *
	 * @param string $phone    (required) 手机号
	 * @param string $code     (required) 验证码(短信模板`reset_pwd`)
	 * @param string $password (required) 密码
	 *
	 * @paramo  int status 如果修改成功则始终为1
	 *
	 * @return array {
	 *  "status":1
	 * }
	 *
	 * @error   400=>密码为空
	 * @error   401=>验证码不正确
	 * @error   407=>手机号码格式不对
	 * @error   500=>内部错误
	 *
	 * @throws
	 */
	public function resetPasswd($phone, $code, $password) {
		if (!preg_match('/^1[3456789]\d{9}$/', $phone)) {
			$this->error(407, '手机号码格式不对');
		}
		if (empty($password)) {
			$this->error(400, '密码为空');
		}
		if (!ResetPasswdTemplate::validate($code)) {
			$this->error(401, '验证码不正确');
		}
		try {
			$db = App::db();
			$db->update('{passport}')->set([
				'passwd'      => Passport::passwd($password),
				'update_time' => time()
			])->where(['phone' => $phone])->exec(true);
		} catch (\Exception $e) {
			$this->error(500, '内部错误');
		}

		return ['status' => 1];
	}
}