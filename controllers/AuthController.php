<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace passport\controllers;

use backend\classes\CaptchaCode;
use passport\api\v1\AccountApi;
use wulaphp\auth\PassportSupport;
use wulaphp\io\Response;
use wulaphp\mvc\controller\Controller;
use wulaphp\mvc\controller\SessionSupport;

class AuthController extends Controller {
	use SessionSupport, PassportSupport;
	protected $passportType = 'vip';

	public function index($type, $callback) {
		if ($type == 'info.do' && $callback) {
			$info = $this->passport->info();
			unset($info['phone'], $info['email']);
			$info = $callback . '(' . json_encode($info) . ')';
			header('Content-type: application/javascript; charset=UTF-8');
			echo $info;
			exit();
		}
		Response::respond(404);
	}

	public function loginPost($type, $account, $passwd) {
		$data['type']    = $type;
		$data['account'] = $account;
		$data['passwd']  = $passwd;
		if ($this->passport->login($data)) {
			return $this->passport->info();
		} else {
			return ['error' => 1, 'msg' => '登录失败'];
		}
	}

	/**
	 * 验证码.
	 *
	 * @nologin
	 *
	 * @param string $type
	 * @param string $size
	 * @param int    $font
	 */
	public function captcha($type = 'png', $size = '90x30', $font = 13) {
		Response::nocache();
		$size = explode('x', $size);
		if (count($size) == 1) {
			$width  = intval($size [0]);
			$height = $width * 3 / 4;
		} else if (count($size) >= 2) {
			$width  = intval($size [0]);
			$height = intval($size [1]);
		} else {
			$width  = 60;
			$height = 20;
		}
		$font          = intval($font);
		$font          = max([18, $font]);
		$type          = in_array($type, ['gif', 'png']) ? $type : 'png';
		$auth_code_obj = new CaptchaCode('vip_captcha_code');
		// 定义验证码信息
		$arr ['code'] = ['characters' => 'A-H,J-K,M-N,P-Z,3-9', 'length' => 4, 'deflect' => true, 'multicolor' => true];
		$auth_code_obj->setCode($arr ['code']);
		// 定义干扰信息
		$arr ['molestation'] = ['type' => 'both', 'density' => 'normal'];
		$auth_code_obj->setMolestation($arr ['molestation']);
		// 定义图像信息. 设置图象类型请确认您的服务器是否支持您需要的类型
		$arr ['image'] = ['type' => $type, 'width' => $width, 'height' => $height];
		$auth_code_obj->setImage($arr ['image']);
		// 定义字体信息
		$arr ['font'] = ['space' => 5, 'size' => $font, 'left' => 5];
		$auth_code_obj->setFont($arr ['font']);
		// 定义背景色
		$arr ['bg'] = ['r' => 255, 'g' => 255, 'b' => 255];
		$auth_code_obj->setBgColor($arr ['bg']);
		$auth_code_obj->paint();
		Response::getInstance()->close(true);
	}

	/**
	 * 登出.
	 *
	 * @return array
	 */
	public function signout() {
		$this->passport->logout();

		return ['error' => 0];
	}

	/**
	 * 登录二维码
	 */
	public function qrcode() {
		try {
			$redis = AccountApi::redis();
			$uuid  = uniqid('', true);
			$key   = md5($uuid . '@qrloing');
			if ($redis->setex($key, 60, '0')) {
				$_SESSION['myqr_uuid'] = $key;
				$text                  = "@{$uuid}@" . (time() + 60) . '@';
				\QRcode::png($text, false, QR_ECLEVEL_M, 6, 2, true);
				exit();
			}
		} catch (\Exception $e) {
		}
		Response::respond(500);
	}

	/**
	 * 检测二维码登录状态.
	 *
	 *
	 * @return array
	 */
	public function qrlogin() {
		try {
			$redis = AccountApi::redis();
			$key   = sess_get('myqr_uuid');
			if (!$key || !$redis->exists($key)) {
				return ['uid' => -1, 'error' => 0];
			}
			$uid = $redis->get($key);
			if ($uid) {
				$redis->del($key);
				if ($this->passport->login($uid)) {
					$info          = $this->passport->info();
					$info['error'] = 0;
					unset($info['phone'], $info['email']);

					//登录成功
					return $info;
				}

				//需要刷新二维码
				return ['uid' => -1, 'error' => 0];
			}
		} catch (\Exception $e) {
		}

		//等待用户扫描
		return ['uid' => 0, 'error' => 0];
	}
}