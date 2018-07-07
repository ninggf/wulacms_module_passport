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
use wulaphp\app\App;
use wulaphp\auth\PassportSupport;
use wulaphp\io\Response;
use wulaphp\mvc\controller\Controller;
use wulaphp\mvc\controller\SessionSupport;

class AuthController extends Controller {
	use SessionSupport, PassportSupport;
	protected $passportType = 'vip';

	/**
	 * 获取用户信息.
	 *
	 * @param string $type     目前只支持info.do
	 * @param string $callback JSONP的回调(如果是的话)
	 *
	 * @throws
	 * @return mixed
	 */
	public function index($type, $callback = '') {
		if ($type == 'info.do') {
			$utoken = $_COOKIE['utoken'];
			if ($utoken && !$this->passport->isLogin) {
				$arr       = explode(':', $utoken);
				$uid       = base_convert($arr[1], 36, 10);
				$pass_info = App::db()->select('id,nickname,avatar,passwd,phone,status')->from('{passport}')->where(['id' => $uid])->get();
				if ($pass_info['passwd']) {
					$agent = $_SERVER['HTTP_USER_AGENT'];
					$hash  = md5($uid . $pass_info['passwd'] . $agent) . ':' . base_convert($uid, 10, 36);
					if (($hash == $utoken) && ($pass_info['status'] == 1)) {
						$this->passport->isLogin  = true;
						$this->passport->uid      = $pass_info['id'];
						$this->passport->phone    = $pass_info['phone'];
						$this->passport->nickname = $pass_info['nickname'] ? $pass_info['nickname'] : substr_replace($pass_info['phone'], '****', 3, 4);
						$this->passport->avatar   = $pass_info['avatar'] ? $pass_info['avatar'] : 'images/touxiang.png';
						$this->passport->store();
					} else {
						$this->passport->isLogin = false;
						$this->passport->uid     = '';
						$this->passport->store();
					}
				}

			}
			$info = $this->passport->info();
			unset($info['phone'], $info['email']);
			if ($callback) {
				$info = $callback . '(' . json_encode($info) . ')';
				header('Content-type: application/javascript; charset=UTF-8');
				echo $info;
				exit();
			} else {
				return $info;
			}
		}
		Response::respond(404);

		return null;
	}

	/**
	 * @param    string $type
	 * @param    string $account
	 * @param    string $passwd
	 * @param string    $captcha
	 *
	 * @throws
	 * @return array
	 */
	public function loginPost($type, $account, $passwd, $captcha = '') {
		//是否开启自动登录
		$auto_login = rqst('auto_login');
		if (App::bcfg('captcha@passport', false)) {
			if (!$captcha) {
				return ['error' => 1, 'msg' => '请输入验证码'];
			}
			$validate = (new CaptchaCode('vip_captcha_code'))->validate($captcha, false, true);
			if (!$validate) {
				return ['error' => 1, 'msg' => '验证码错误'];
			}
		}
		$data['type']    = $type;
		$data['account'] = $account;
		$data['passwd']  = $passwd;
		if ($this->passport->login($data)) {
			//获取手机号对应的账户
			$db       = App::db();
			$passport = $db->select('PS.*,OA.id AS oauth_id')->from('{oauth} AS OA')->left('{passport} AS PS', 'passport_id', 'PS.id')->where([
				'type'    => 'phone',
				'open_id' => $account
			])->get();
			if ($auto_login) {
				$uid   = $this->passport->uid;
				$agent = $_SERVER['HTTP_USER_AGENT'];
				$pass  = $passport['passwd'];
				$hash  = md5($uid . $pass . $agent) . ':' . base_convert($uid, 10, 36);
				Response::cookie('utoken', $hash, 86400 * 30);

			} else {
				Response::cookie('utoken');
			}
			fire('passport\bindPhone', $this->passport->uid);
			//创建登录信息
			AccountApi::createSession($passport['oauth_id'], 'web', $passport, session_id());

			return $this->passport->info();
		} else {
			return ['error' => 1, 'msg' => '登录失败(' . $this->passport->error . ')'];
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
		$arr ['code'] = [
			'characters' => 'A-H,K-N,P-R,U-Y,2-4,6-9',
			'length'     => 4,
			'deflect'    => true,
			'multicolor' => true
		];
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
		Response::cookie('utoken');
		$this->passport->logout();

		return ['error' => 0];
	}

	/**
	 * 登录二维码
	 */
	public function qrcode() {
		try {
			$redis = AccountApi::redis();
			//删除前一个key
			$key = sess_get('myqr_uuid');
			if ($key) {
				$redis->del($key);
			}
			//生成新的key
			$uuid = uniqid('', true);
			$key  = md5($uuid . '@qrloing');
			if ($redis->setex($key, 120, '0')) {
				$_SESSION['myqr_uuid'] = $key;
				$text                  = "@{$uuid}@" . (time() + 120) . '@1.0@';
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
	 * @param string $callback jsonp的callback(如果是的话)
	 *
	 * @return array|string
	 *
	 * @throws
	 */
	public function qrlogin($callback = '') {
		try {
			$redis = AccountApi::redis();
			$key   = sess_get('myqr_uuid');
			if (!$key || !$redis->exists($key)) {
				return $this->qrrtn(['id' => -1, 'error' => 0, 'msg' => 'refresh qrcode'], $callback);
			}
			$uid = $redis->get($key);
			if ($uid) {
				if (!is_numeric($uid)) {
					//等待手机端确认.
					return $this->qrrtn(['id' => -1, 'error' => 1, 'msg' => 'please confirm on your phone'], $callback);
				}
				$redis->del($key);
				if ($this->passport->login($uid)) {
					$info          = $this->passport->info();
					$info['error'] = 0;
					unset($info['phone'], $info['email']);

					//登录成功
					return $this->qrrtn($info, $callback);
				}

				//需要刷新二维码
				return $this->qrrtn(['id' => -1, 'error' => 0, 'msg' => 'login fail'], $callback);
			}
		} catch (\Exception $e) {
			throw_exception('cannot connect to passport server');
		}

		//等待用户扫描
		return $this->qrrtn(['id' => 0, 'error' => 0, 'msg' => 'please scan the qrcode'], $callback);
	}

	/**
	 * 生成qr的返回.
	 *
	 * @param array  $rtn
	 * @param string $callback
	 *
	 * @return mixed
	 */
	private function qrrtn($rtn, $callback) {
		if ($callback) {
			$info = $callback . '(' . json_encode($rtn) . ')';
			header('Content-type: application/javascript; charset=UTF-8');
			echo $info;
			exit();
		} else {
			return $rtn;
		}
	}

}