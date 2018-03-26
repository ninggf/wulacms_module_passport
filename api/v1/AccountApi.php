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
use rest\classes\RestException;
use sms\classes\tpl\BindTemplate;
use sms\classes\tpl\LoginTemplate;
use sms\classes\tpl\RegCodeTemplate;
use sms\classes\tpl\ResetPasswdTemplate;
use wulaphp\app\App;
use wulaphp\auth\Passport;
use wulaphp\db\DatabaseConnection;
use wulaphp\io\Request;
use wulaphp\util\RedisClient;

/**
 * Class AccountApi
 * @package passport\api\v1
 * @name 账户
 */
class AccountApi extends API {
	/**
	 * 使用手机注册通行证
	 *
	 * @apiName       手机注册
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
	 * @paramo        int uid 用户ID
	 *
	 * @error         302=>手机号已经存在
	 * @error         400=>未知设备
	 * @error         401=>验证码不正确
	 * @error         402=>不允许注册
	 * @error         403=>注册关闭
	 * @error         404=>非法终端
	 * @error         405=>未开启SESSION
	 * @error         800=>请填写推荐码
	 * @error         801=>推荐码不可用
	 * @error         406=>注册过快
	 * @error         407=>手机号码不正确
	 * @error         500=>内部错误
	 *
	 * @return array {
	 *  "uid":10000
	 * }
	 * @throws
	 */
	public function register($phone, $code, $channel = '', $device = '', $cid = '', $password = '', $recCode = '') {
		if (!$this->sessionId) {
			$this->error(405, '未开启SESSION');
		}
		if (!isset(ClientApi::device[ $device ])) {
			$this->error(400, '未知设备');
		}
		if (!preg_match('/^1[3456789]\d{9}$/', $phone)) {
			$this->error(407, '手机号码格式不对');
		}
		$enabled = App::bcfg('enabled@passport', true);
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
		if (!ClientApi::checkClient($cid)) {
			$this->error(404, '非法终端');
		}
		$needRecCode = App::bcfg('need_rec@passport');
		if ($needRecCode && empty($code)) {
			$this->error(800, '请填写推荐码');
		}
		if (!RegisterUtil::checkRecCode($recCode)) {
			$this->error(801, '推荐码不可用');
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
		//新手任务 绑定手机号
		fire('ucenter\onGetTaskDone', $id, 'bind_phone');

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
	 * @error   405=>未开启SESSION
	 * @error   500=>内部错误
	 *
	 * @throws
	 */
	public function resetPasswd($phone, $code, $password) {
		if (!$this->sessionId) {
			$this->error(405, '未开启SESSION');
		}
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

	/**
	 * 支持两种登录：
	 *
	 * 1. 1.密码登录.
	 * 2. 2.验证码登录，需要通过短信接口发送模板为`login_code`的短信以获取验证码.
	 *
	 * @apiName 登录
	 * @session
	 *
	 * @param string $phone    (required,sample=18049920019) 手机号
	 * @param string $device   (required,sample=ios,android,wxapp,wxgame,h5,pc,web) 设备
	 * @param string $cid      (required) 设备ID
	 * @param string $password 密码(与`code`二选一)
	 * @param string $code     登录验证码(短信模板`login_code`)
	 *
	 * @paramo  int uid 用户ID
	 * @paramo  string token TOKEN
	 * @paramo  string username 用户名
	 * @paramo  string nickname 昵称
	 * @paramo  string phone 手机号
	 * @paramo  string avatar 头像
	 * @paramo  int gender 性别:0未知;1男;2女
	 * @paramo  string rec_code 推荐码
	 * @paramo  int status 状态
	 *
	 * @return array {
	 *  "id":10,
	 *  "token":"adfasdfasdfasdf",
	 *  "username":"adfasdf",
	 *  "nickname":"喜羊羊",
	 *  "phone":"18049920019",
	 *  "avatar":"http://adfasdf.com/afasd.png",
	 *  "gender":1,
	 *  "rec_code":"10",
	 *  "status":1
	 * }
	 *
	 * @error   402=>未知设备
	 * @error   401=>未知终端
	 * @error   407=>手机号码格式不对
	 * @error   408=>验证码不对
	 * @error   404=>手机未注册
	 * @error   409=>用户已被禁用
	 * @error   403=>手机或密码不正确
	 * @error   405=>密码或验证码为空
	 * @error   500=>内部错误
	 * @error   503=>未开启SESSION
	 *
	 * @throws
	 */
	public function login($phone, $device, $cid, $password = '', $code = '') {
		if (!ClientApi::checkDevice($device)) {
			$this->error(402, '未知设备');
		}
		if (!preg_match('/^1[3456789]\d{9}$/', $phone)) {
			$this->error(407, '手机号码格式不对');
		}
		if ($code) {//验证码登录
			if (!$this->sessionId) {
				$this->error(503, '未开启SESSION');
			}
			if (!LoginTemplate::validate($code)) {
				$this->error(408, '验证码不正确');
			}
		}
		if (!ClientApi::checkClient($cid)) {
			$this->error(401, '未知终端');
		}
		//获取手机号对应的账户
		$db       = App::db();
		$passport = $db->select('PS.*,OA.id AS oauth_id')->from('{oauth} AS OA')->left('{passport} AS PS', 'passport_id', 'PS.id')->where([
			'type'    => 'phone',
			'open_id' => $phone
		])->get();

		if (!$passport) {
			$this->error(404, '手机号未注册');
		}
		if (!$passport['status']) {
			$this->error(409, '用户已被禁用');
		}
		//验证密码
		if ($password) {
			if (!Passport::verify($password, $passport['passwd'])) {
				$this->error(403, '手机号或密码不正确');
			}
		} else if (!$code) {
			$this->error(405, '密码或验证码为空');
		}

		//创建登录信息
		$token = self::createSession($passport['oauth_id'], $device, $passport);
		if (!$token) {
			$this->error(500, '内部错误');
		}

		return $this->info($token);
	}

	/**
	 * 退出登录.
	 *
	 * @apiName 退出
	 *
	 * @param string $token (required) TOKEN
	 * @paramo  int status 退出状态，1为成功
	 *
	 * @error   403=>TOKEN为空
	 * @return array {
	 *  "status":1
	 * }
	 * @throws
	 */
	public function logout($token) {
		if (!$token) {
			$this->error(403, 'TOKEN为空');
		}
		self::forceLogout([$token]);

		return ['status' => 1];
	}

	/**
	 * 绑定手机
	 * @apiName 绑定手机
	 *
	 * @param string $token    (required) 登录TOKEN
	 * @param string $phone    (required) 要绑定的手机号
	 * @param string $code     (required) 绑定验证码(短信模板`bind_phone`)
	 * @param string $device   (required) 设备
	 * @param string $cid      (required) 终端ID
	 * @param string $password 密码，绑定时设置的登录密码
	 * @param int    $force    强制绑定，将清空原账户数据
	 *
	 * @paramo  int status 绑定结果,1绑定成功
	 *
	 * @return array {
	 *  "status":1
	 * }
	 *
	 * @error   403=>TOKEN为空
	 * @error   402=>未知设备
	 * @error   407=>手机号码格式不对
	 * @error   408=>验证码不正确
	 * @error   401=>未知终端
	 * @error   405=>手机号已经存在
	 * @error   500=>内部错误
	 * @error   900=>绑定失败
	 *
	 * @throws
	 */
	public function bind($token, $phone, $code, $device, $cid = '', $password = '', $force = 0) {
		if (!$token) {
			$this->error(403, 'TOKEN为空');
		}
		if (!ClientApi::checkDevice($device)) {
			$this->error(402, '未知设备');
		}
		if (!preg_match('/^1[3456789]\d{9}$/', $phone)) {
			$this->error(407, '手机号码格式不对');
		}
		if (!BindTemplate::validate($code)) {
			$this->error(408, '验证码不正确');
		}
		if (!ClientApi::checkClient($cid)) {
			$this->error(401, '未知终端');
		}
		$info = $this->info($token);
		if (!$info) {
			$this->unauthorized();
		}
		try {
			$db  = App::db();
			$rst = $db->trans(function (DatabaseConnection $dbx) use ($phone, $info, $device, $password, $force) {
				$passport = $dbx->select('OA.id,OA.passport_id')->from('{oauth} AS OA')->where([
					'type'    => 'phone',
					'open_id' => $phone
				])->get();
				if ($passport) {
					if ($force) {
						//修改oauth的passport_id；
						$rst = $dbx->update('{oauth}')->set(['passport_id' => $info['uid']])->where(['id' => $passport['id']])->exec();
						//更新原passport表中手机对应的phone为空。
						if ($rst) {
							$rst = $dbx->update('{passport}')->set(['phone' => ''])->where(['id' => $passport['passport_id']])->exec();
						}
						//更新当前用户的手机号
						if ($rst) {
							$dbx->update('{passport}')->set(['phone' => $phone])->where(['id' => $info['uid']])->exec();
						}
						if ($rst) {
							//通知业务合并账户
							fire('passport\bindTo', $passport['passport_id'], $info['uid']);

							return true;
						}
						throw_exception('900@绑定失败');
					}
					throw_exception('405@手机已经存在');
				}
				$data['type']        = 'phone';
				$data['open_id']     = $phone;
				$data['union_id']    = $phone;
				$data['create_time'] = $data['update_time'] = time();
				$data['passport_id'] = $info['uid'];
				$data['device']      = $device;
				$rtn                 = $dbx->insert($data)->into('{oauth}')->exec();
				if (!$rtn) {
					return false;
				}
				$pa['phone'] = $phone;
				if ($password) {
					$pa['passwd'] = Passport::passwd($password);
				}
				$pa['update_time'] = $data['update_time'];
				//新手任务 绑定手机号
				fire('ucenter\onGetTaskDone', $info['uid'], 'bind_phone');

				return $dbx->update('{passport}')->set($pa)->where(['id' => $info['uid']])->exec();
			}, $error);
			if (!$rst) {
				$this->error($error);
			}
		} catch (RestException $re) {
			throw $re;
		} catch (\Exception $e) {
			$this->error(500, '内部错误');
		}

		return ['status' => 1];
	}

	/**
	 * 修改用户属性.
	 *
	 * @apiName 修改属性
	 *
	 * @param string $token (required) TOKEN 登录TOKEN
	 * @param object $meta  (required,sample={"avatar":"https://...","gender":1,"nickname":""}) 要修改的数据
	 *
	 * @paramo  int status 如果修改成功始终为1,0表示无法将数据更新到SESSION。
	 *
	 * @return array {
	 *      "status":1
	 * }
	 *
	 * @error   403=>TOKEN为空
	 * @error   404=>要更新的属性为空
	 * @error   405=>昵称不可用
	 * @error   406=>更新用户信息失败
	 * @error   500=>内部错误
	 *
	 * @throws
	 */
	public function update($token, $meta) {
		if (empty($token)) {
			$this->error(403, 'TOKEN为空');
		}
		if (empty($meta)) {
			$this->error(404, '要更新的属性为空');
		}

		$info = $this->info($token);
		if (!$info) {
			$this->unauthorized();
		}
		try {
			$db  = App::db();
			$rst = $db->trans(function (DatabaseConnection $dbx) use (&$info, $meta) {
				$pa = [];
				foreach ($meta as $key => $value) {
					if ($key == 'gender') {
						$pa['gender'] = intval($value);
						if ($pa['gender'] < 0 || $pa['gender'] > 2) {
							$pa['gender'] = 0;
						}
						$info['gender'] = $pa['gender'];
					} else if ($key == 'nickname') {
						$pa['nickname'] = apply_filter('passport\onSetNickname', $value);
						if (!isset($pa['nickname'])) {
							throw_exception('405@昵称不可用');
						}
						$info['nickname'] = $value;
					} else if ($key == 'avatar') {
						$pa['avatar']   = $value;
						$info['avatar'] = $value;
					} else {
						$rtn = $dbx->cud("INSERT INTO {passport_meta} (passport_id,name,value) VALUES(%d,%s,%s)", $info['uid'], $key, $value);
						if (!$rtn) {
							$rtn = $dbx->cud('UPDATE {passport_meta} SET value=%s WHERE passport_id=%d AND name=%s', $value, $info['uid'], $key);
						}
						if ($rtn === null) {
							return false;
						}
						$info[ $key ] = $value;
					}
				}
				if ($pa && !$dbx->update('{passport}')->set($pa)->where(['id' => $info['uid']])->exec()) {
					throw_exception('406@更新用户信息失败');
				}

				return true;
			}, $error);

			if (!$rst) {
				$this->error($error);
			}
			$expire = App::icfg('expire@passport', 315360000);
			$redis  = RedisClient::getRedis(App::icfg('redisdb@passport', 10));
			$info   = json_encode($info);
			if ($expire) {
				$rst = $redis->setex($token, $expire, $info);
			} else {
				$rst = $redis->set($token, $info);
			}
			if (!$rst) {
				return ['status' => 0];
			}
		} catch (RestException $re) {
			throw $re;
		} catch (\Exception $e) {
			$this->error(500, '内部错误');
		}
		//新手任务 完善个人资料
		fire('ucenter\onGetTaskDone', $info['uid'], 'self_info');

		return ['status' => 1];
	}

	/**
	 * 请客户端保存用户两次输入的密码相同，服务器端不校验。
	 *
	 * @apiName 修改密码
	 *
	 * @param string $token  (required) TOKEN 登录TOKEN
	 * @param string $oldpwd (required) 原密码
	 * @param string $newpwd (required) 新密码
	 *
	 * @paramo  int status 修改成功为1
	 *
	 * @return array {
	 *      "status":1
	 * }
	 *
	 * @error   400=>TOKEN为空
	 * @error   401=>原密码不正确(1)
	 * @error   402=>新密码为空
	 * @error   403=>用户未登录
	 * @error   404=>原密码不正确(2)
	 * @error   1024=>内部错误(数据库)
	 * @error   1025=>无法修改密码
	 *
	 * @throws \Exception
	 */
	public function changePwd($token, $oldpwd, $newpwd) {
		if (empty($token)) {
			$this->error(400, 'TOKEN为空');
		}
		if (empty($newpwd)) {
			$this->error(402, '新密码为空');
		}
		$info = $this->info($token);
		if (!$info) {
			$this->error(403, '用户未登录');
		}
		try {
			$db     = App::db();
			$where  = ['id' => $info['uid']];
			$passwd = $db->select('passwd')->from('{passport}')->where($where)->get('passwd');
			if (!$passwd && $oldpwd) {
				$this->error(401, '原密码不正确(1)');
			}
			if ($passwd && !Passport::verify($oldpwd, $passwd)) {
				$this->error(404, '原密码不正确(2)');
			}
			$data['passwd'] = Passport::passwd($newpwd);
			$rst            = $db->update('{passport}')->set($data)->where($where)->exec();
			if (!$rst) {
				$this->error(1025, '无法修改密码');
			}
		} catch (\Exception $e) {
			$this->error(1024, '内部错误');
		}

		return ['status' => 1];
	}

	/**
	 * 获取用户登录信息.
	 *
	 * @apiName 用户信息
	 *
	 * @param string $token (required) TOKEN
	 * @param int    $force (sample=0) 是否强制从数据库加载用户属性
	 *
	 * @paramo  int uid 用户ID
	 * @paramo  string token TOKEN
	 * @paramo  string username 用户名
	 * @paramo  string nickname 昵称
	 * @paramo  string phone 手机号
	 * @paramo  string avatar 头像
	 * @paramo  int gender 性别:0未知;1男;2女
	 * @paramo  string rec_code 推荐码
	 * @paramo  int status 状态
	 *
	 * @return array {
	 *  "id":10,
	 *  "token":"adfasdfasdfasdf",
	 *  "username":"adfasdf",
	 *  "nickname":"喜羊羊",
	 *  "phone":"18049920019",
	 *  "avatar":"http://adfasdf.com/afasd.png",
	 *  "gender":1,
	 *  "rec_code":"10",
	 *  "status":1
	 * }
	 * @error   403=>TOKEN为空
	 * @error   404=>登录过期
	 * @error   500=>内部错误
	 * @throws
	 */
	public function info($token, $force = 0) {
		if (empty($token)) {
			$this->error(403, 'TOKEN为空');
		}
		try {
			$redis = RedisClient::getRedis(App::icfg('redisdb@passport', 10));
			$info  = $redis->get($token);
			if (!$info) {
				$this->error(404, '登录过期');
			}
			$info = @json_decode($info, true);
			if (!$info) {
				$redis->del($token);
				$this->error(404, '登录过期');
			}
			if ($force && App::bcfg('force_reload@passport', false)) {//重新加载用户属性
				$dbx      = App::db();
				$passport = $dbx->select('*')->from('{passport}')->where(['id' => $info['uid']])->get(0);
				if (!$passport) {
					$redis->del($token);
					$this->error(404, '用户不存在');
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
				$expire = App::icfg('expire@passport', 315360000);
				$infox  = json_encode($info);
				if ($expire) {
					$rtn = $redis->setex($token, $expire, $infox);
				} else {
					$rtn = $redis->set($token, $infox);
				}
				if (!$rtn) {
					$redis->del($token);
					$this->error(404, '更新数据出错');
				}
			}

			return $info;
		} catch (RestException $re) {
			throw  $re;
		} catch (\Exception $e) {
			$this->error(500, '内部错误');
		}

		return [];
	}

	/**
	 * 创建登录会话.
	 *
	 * @param int|string $oauthId  第三方
	 * @param string     $device   设备
	 * @param array      $passport 通行证
	 *
	 * @return string
	 */
	public static function createSession($oauthId, $device, $passport) {
		$token  = md5(uniqid() . $device . $passport['id']);
		$expire = App::icfg('expire@passport', 315360000);
		if ($expire <= 0) {
			$expire = 315360000;
		}
		$session['ip']          = Request::getIp();
		$session['create_time'] = time();
		$session['expiration']  = $session['create_time'] + $expire;
		$session['token']       = $token;
		$session['oauth_id']    = $oauthId;
		$session['device']      = $device;
		try {
			$db  = App::db();
			$rst = $db->trans(function (DatabaseConnection $dbx) use ($session, $passport, $expire, $oauthId) {
				$oauthMeta = $dbx->select('name,value')->from('{oauth_meta}')->where(['oauth_id' => $oauthId])->toArray('value', 'name');
				if ($oauthMeta) {
					$info = $oauthMeta;
				}
				$info['uid']      = $passport['id'];
				$info['token']    = $session['token'];
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
				$info['rec_code'] = $passport['rec_code'];
				$info['status']   = $passport['status'];
				$needBind         = App::bcfg('need_bind@passport');
				if ($needBind && empty($info['phone'])) {
					$info['status'] = 2;//需要绑定手机
				}
				$info = apply_filter('passport\onLogined', $info, $passport);
				if (!$info) {
					return false;
				}

				$login['login_time'] = $session['create_time'];
				$login['device']     = $session['device'];
				//更新passport
				if (!$dbx->update('{passport}')->set($login)->where(['id' => $passport['id']])->exec()) {
					return false;
				}
				//更新oauth
				if (!$dbx->update('{oauth}')->set($login)->where(['id' => $session['oauth_id']])->exec()) {
					return false;
				}
				//添加到oauth_session
				$rtn = $dbx->insert($session)->into('{oauth_session}')->exec();
				if (!$rtn) {
					return false;
				}
				$redis = RedisClient::getRedis(App::icfg('redisdb@passport', 10));
				$meta  = $dbx->select('name,value')->from('{passport_meta}')->where(['passport_id' => $info['uid']])->toArray('value', 'name');
				if ($meta) {
					$info = array_merge($meta, $info);
				}
				$info = json_encode($info);
				if ($expire) {
					return $redis->setex($session['token'], $expire, $info);
				} else {
					return $redis->set($session['token'], $info);
				}
			}, $error);

			if (!$rst) {
				log_info($error, 'session');

				return false;
			}
		} catch (\Exception $e) {
			return false;
		}

		return $token;
	}

	/**
	 * 强制退出.
	 *
	 * @param array $tokens
	 *
	 * @return bool
	 */
	public static function forceLogout($tokens) {
		try {
			$redis = RedisClient::getRedis(App::icfg('redisdb@passport', 10));
			foreach ((array)$tokens as $t) {
				$redis->del($t);
			}
			$db = App::db();
			$db->update('{oauth_session}')->set(['expiration' => time()])->where(['token IN' => (array)$tokens])->exec();

			return true;
		} catch (\Exception $e) {

		}

		return false;
	}
}