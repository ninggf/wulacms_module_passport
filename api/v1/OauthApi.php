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

use passport\classes\model\OauthApp;
use passport\classes\model\PassportTable;
use passport\classes\RegisterUtil;
use rest\api\v1\ClientApi;
use rest\classes\API;
use wulaphp\app\App;
use wulaphp\db\DatabaseConnection;
use wulaphp\io\Request;

/**
 * Class OauthApi
 * @package passport\api\v1
 * @name 第三方登录
 */
class OauthApi extends API {
	/**
	 * 第三方登录接口.
	 *
	 * @apiName 登录
	 *
	 * @param string $type    (required) 第三方类型
	 * @param string $openid  (required) OPEN ID
	 * @param string $device  (required) 设备
	 * @param string $cid     (required) 终端编号
	 * @param string $unionid UNION ID
	 * @param string $recCode 推荐码
	 * @param string $channel 渠道
	 * @param array  $meta    (sample={"avatar":"...","gender":"","nickname":""}) 第三方提供的额外数据
	 *
	 * @paramo  int uid 用户ID
	 * @paramo  string token TOKEN
	 * @paramo  string username 用户名
	 * @paramo  string nickname 昵称
	 * @paramo  string phone 手机号
	 * @paramo  string avatar 头像
	 * @paramo  int gender 性别:0未知;1男;2女
	 * @paramo  string rec_code 推荐码
	 * @paramo  int status 状态，如果其值为`2`则表示需要绑定手机.
	 *
	 * @error   400=>未知设备
	 * @error   402=>不允许注册
	 * @error   403=>注册关闭
	 * @error   404=>非法终端
	 * @error   407=>非法的第三方登录
	 * @error   409=>用户已被禁用
	 * @error   800=>请填写推荐码
	 * @error   801=>推荐码不可用
	 * @error   406=>注册过快
	 * @error   500=>内部错误
	 * @error   600=>不支持的第三方登录
	 * @error   601=>OPENID为空
	 * @error   602=>登录失败
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
	 * @throws
	 */
	public function login(string $type, string $openid, string $device, string $cid, string $unionid = '', string $recCode = '', string $channel = '', array $meta = null) {

		if (empty($openid)) {
			$this->error(601, 'OPENID为空');
		}
		if (!ClientApi::checkDevice($device)) {
			$this->error(400, '未知设备');
		}
		$enabled = App::bcfg('enabled@passport', true);
		if (!$enabled) {
			$this->error(403, '注册停止');
		}
		if (!ClientApi::checkClient($cid)) {
			$this->error(404, '未知终端');
		}
		$ip = Request::getIp();
		if (!RegisterUtil::checkIp($ip)) {
			$this->error(402, '不允许注册');
		}

		if (!RegisterUtil::limit($ip)) {
			$this->error(406, '注册过快');
		}
		$appTable = new OauthApp();
		$apps     = $appTable->apps();
		if (!isset($apps[ $type ])) {
			$this->error(600, '不支持的第三方登录');
		}
		$oauthApp = $apps[ $type ];
		if (!$oauthApp['status'] || !$oauthApp[ $device ]) {
			$this->error(603, '第三方登录已停用');
		}
		//第三方登录校验
		/**@var \passport\classes\IOauth $oapp */
		$oapp    = $oauthApp['oauth'];
		$checked = $oapp->check(['openid' => $openid, 'unionid' => $unionid, 'meta' => $meta]);

		if (!$checked) {
			$this->error(407, '非法的第三方登录');
		}
		// 从第三方获取用户信息
		$oauthData = $oapp->getOauthData();
		if ($meta) {
			$meta = array_merge($meta, $oauthData);
		} else {
			$meta = $oauthData;
		}
		$meta = is_array($meta) ? $meta : [];
		$db   = App::db();
		$uid  = $db->trans(function (DatabaseConnection $dbx) use ($openid, $device, $type, $unionid, $channel, $recCode, $meta) {
			//通过unionid查找用户
			$passport_id = self::getPassportId($unionid);
			//第三方数据
			$oauth = $dbx->select('passport_id,id,union_id')->from('{oauth}')->where([
				'type'    => $type,
				'open_id' => $openid
			])->get();
			//没有第三方数据
			if (!$oauth) {
				$oauth['passport_id'] = $passport_id;
				$oauth['type']        = $type;
				$oauth['open_id']     = $openid;
				$oauth['union_id']    = $unionid;
				$oauth['create_time'] = $oauth['update_time'] = time();
				$oauth['device']      = $device;
				$rst                  = $dbx->insert($oauth)->into('{oauth}')->exec();
				if ($rst && $rst[0]) {
					$oauth['id'] = $rst[0];
				} else {
					throw_exception('无法创建第三方登录信息');
				}
			} else if (!$oauth['passport_id'] && $passport_id) {
				$rst = $dbx->update('{oauth}')->set([
					'passport_id' => $passport_id,
					'union_id'    => $unionid,
					'update_time' => time()
				])->where(['id' => $oauth['id']])->exec();
				if (!$rst) {
					throw_exception('更新登录数据失败');
				}
				$oauth['passport_id'] = $passport_id;
				$oauth['union_id']    = $unionid;
			} else {
				$rst = $dbx->update('{oauth}')->set([
					'update_time' => time(),
					'union_id'    => $unionid
				])->where(['id' => $oauth['id']])->exec();
				if (!$rst) {
					throw_exception('更新登录数据失败');
				}
			}
			//创建通行证
			if (!$oauth['passport_id']) {

				$needRecCode = App::bcfg('need_rec@passport');
				if ($needRecCode && empty($code)) {
					throw_exception('800@请填写推荐码');
				}
				if (!RegisterUtil::checkRecCode($recCode)) {
					throw_exception('801@推荐码不可用');
				}
				$pa['username'] = uniqid();
				if ($channel) {
					$pa['channel'] = $channel;
				}
				$pa['nickname'] = '';
				//$recCode 放师父id
				if ($recCode) {
					$pa['recom'] = $recCode;
				}
				$pa['status'] = 1;
				$pa['ip']     = Request::getIp();
				$ds_arr       = [];
				if (isset($meta['recCode'])) {
					$parent = (int)$meta['recCode'];
					unset($meta['recCode']);
					if (preg_match('/28ds/i', $channel)) {
						$ds_arr['mid']     = $parent;
						$ds_arr['channel'] = $channel;
					} else {
						$pa['parent'] = $parent;
					}
				}
				$table = new PassportTable();
				$pid   = $table->addPassport($pa);
				if ($pid && $dbx->update('{oauth}')->set(['passport_id' => $pid])->where(['id' => $oauth['id']])->exec()) {
					$oauth['passport_id'] = $pid;
					//28奖励
					if ($ds_arr['mid']) {
						fire('passport\onPassportCreated28', $pid, $ds_arr);
					}
				} else {
					throw_exception('更新登录数据失败[2]');
				}
			}
			//更新oauth_meta
			if ($meta) {
				foreach ($meta as $key => $value) {
					$rtn = $dbx->cud("INSERT INTO {oauth_meta} (oauth_id,name,value) VALUES(%d,%s,%s)", $oauth['id'], $key, $value);
					if (!$rtn) {
						$dbx->cud('UPDATE {oauth_meta} SET value=%s WHERE oauth_id=%d AND name=%s', $value, $oauth['id'], $key);
					}
				}
			}
			fire('passport\onOauthLogin', $type, $oauth['passport_id']);

			return $oauth['passport_id'];
		}, $errors);
		if (!$uid) {
			$this->error($errors);
		}
		$passport = $db->select('PS.*,OA.id AS oauth_id')->from('{oauth} AS OA')->left('{passport} AS PS', 'passport_id', 'PS.id')->where([
			'type'    => $type,
			'open_id' => $openid
		])->get();

		if (!$passport) {
			$this->error(602, '登录失败');
		}
		if (!$passport['status']) {
			$this->error(409, '用户已被禁用');
		}
		//创建登录信息
		$token = AccountApi::createSession($passport['oauth_id'], $device, $passport);
		if (!$token) {
			$this->error(500, '内部错误');
		}
		$account = new AccountApi($this->appKey, $this->ver);
		$info    = $account->info($token);

		return $info;
	}

	/**
	 * 绑定第三方账户
	 *
	 * @apiName 绑定
	 *
	 * @param string $token   (required) TOKEN
	 * @param string $type    (required) 第三方类型
	 * @param string $openid  (required) OPEN ID
	 * @param string $device  (required) 设备
	 * @param string $cid     (required) 终端编号
	 * @param string $unionid UNION ID
	 * @param int    $force   是否强制绑定
	 * @param object $meta    (sample={"avatar":"...","gender":"","nickname":""}) 第三方提供的额外数据
	 *
	 * @paramo  int status 绑定成功始终为1
	 *
	 * @return array {
	 *  "status":1
	 * }
	 *
	 * @error   400=>未知设备
	 * @error   403=>TOKEN为空
	 * @error   404=>非法终端
	 * @error   407=>非法的第三方登录
	 * @error   500=>内部错误
	 * @error   600=>不支持的第三方登录
	 * @error   601=>OPENID为空
	 * @error   602=>已经登录为其它用户
	 * @error   603=>已经绑定过了，请不要重复绑定
	 * @error   900=>绑定失败
	 *
	 * @throws
	 */
	public function bind($token, $type, $openid, $device, $cid, $unionid, $force = 0, $meta = null) {
		if (empty($token)) {
			$this->error(403, 'TOKEN为空');
		}
		$apps = OauthApp::getApps();
		if (!isset($apps[ $type ])) {
			$this->error(600, '不支持的第三方登录');
		}
		if (empty($openid)) {
			$this->error(601, 'OPENID为空');
		}
		if (!ClientApi::checkDevice($device)) {
			$this->error(400, '未知设备');
		}
		if (!ClientApi::checkClient($cid)) {
			$this->error(404, '未知终端');
		}
		$account = new AccountApi($this->appKey, $this->ver);
		$info    = $account->info($token);
		if (!$info) {
			$this->unauthorized();
		}
		$oauthApp = $apps[ $type ];
		//第三方登录校验
		$checked = $oauthApp->check(['openid' => $openid, 'unionid' => $unionid, 'meta' => $meta]);

		if (!$checked) {
			$this->error(407, '非法的第三方登录');
		}
		$meta = is_array($meta) ? $meta : [];
		$db   = App::db();
		$rst  = $db->trans(function (DatabaseConnection $dbx) use ($type, $openid, $unionid, $device, $info, $meta, $force) {
			$oauth = $dbx->select('passport_id,id')->from('{oauth}')->where([
				'type'    => $type,
				'open_id' => $openid
			])->get();
			if ($oauth) {
				if ($oauth['passport_id'] == $info['uid']) {
					throw_exception('603@已经绑定过了，请不要重复绑定');
				}
				if ($force) {
					//修改oauth表的passport_id。
					$rst = $dbx->update('{oauth}')->set(['passport_id' => $info['uid']])->where(['id' => $oauth['id']])->exec();
					if ($rst) {
						fire('passport\bindTo', $oauth['passport_id'], $info['uid']);

						return true;
					}
					throw_exception('900@绑定失败');
				}
				throw_exception('602@已经登录为其它用户');
			}
			$data['type']        = $type;
			$data['open_id']     = $openid;
			$data['union_id']    = $unionid;
			$data['create_time'] = $data['update_time'] = time();
			$data['passport_id'] = $info['uid'];
			$data['device']      = $device;
			$rtn                 = $dbx->insert($data)->into('{oauth}')->exec();
			if (!$rtn) {
				return false;
			}
			$data['id'] = $rtn[0];
			//更新oauth_meta
			if ($meta) {
				foreach ($meta as $key => $value) {
					$rtn = $dbx->cud("INSERT INTO {oauth_meta} (oauth_id,name,value) VALUES(%d,%s,%s)", $data['id'], $key, $value);
					if (!$rtn) {
						$dbx->cud('UPDATE {oauth_meta} SET value=%s WHERE oauth_id=%d AND name=%s', $value, $data['id'], $key);
					}
				}
			}
			fire('passport\bindOauth', $type, $info['uid']);

			return true;
		}, $errors);

		if (!$rst) {
			$this->error($errors);
		}

		return ['status' => 1];
	}

	protected static function getPassportId($unionid) {
		if ($unionid) {
			try {
				$db  = App::db();
				$uid = $db->select('passport_id')->from('{oauth}')->where(['union_id' => $unionid])->get('passport_id');

				return intval($uid);
			} catch (\Exception $e) {

			}
		}

		return 0;
	}
}