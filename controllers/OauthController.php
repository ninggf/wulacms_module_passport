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

use backend\classes\IFramePageController;
use passport\classes\model\OauthApp;
use passport\classes\model\OauthTable;
use wulaphp\db\sql\Condition;
use wulaphp\io\Ajax;

/**
 * Class OauthController
 * @package passport\controllers
 * @acl     m:system/passport
 */
class OauthController extends IFramePageController {
	public function index() {
		$data['groups'] = OauthApp::getAppsName();

		return $this->render($data);
	}

	public function data($q, $type, $oid, $token, $count) {
		$table = new OauthTable();
		$sql   = $table->alias('OA')->select('OA.*,OS.ip,OS.token,OS.expiration');
		$sql->join('{oauth_session} AS OS', 'OA.id = OS.oauth_id AND OA.login_time = OS.create_time AND OA.device = OS.device');

		$where = [];
		if ($type) {
			$where['type'] = $type;
		}

		if ($oid) {
			$where['open_id'] = $oid;
		}
		if ($token) {
			$where['token'] = $token;
		}
		if ($q) {
			$qw = null;
			if (!is_numeric($q)) {
				$qw = Condition::parseSearchExpression($q, [
					'通行证'  => 'OA.passport_id',
					'设备'   => 'OA.device',
					'创建时间' => '@OA.create_time',
					'最近登录' => '@login_time',
					'过期时间' => '@expiration',
					'类型'   => 'type'
				]);
			}
			if ($qw) {
				$sql->where($qw);
			} else {
				$where['OA.passport_id'] = (int)$q;
			}
		}
		$sql->sort()->page()->where($where);

		if ($count) {
			$data['total'] = $sql->total('OA.id');
		}

		$data['rows']  = $sql->toArray();
		$data['ctime'] = time();

		return view($data);
	}

	/**
	 * 查看第三方登录数据.
	 *
	 * @param string $id 通行证ID.
	 *
	 * @return \wulaphp\mvc\view\View
	 */
	public function view($id) {
		$data['OA.passport_id'] = $id;
		$table                  = new OauthTable();
		$sql                    = $table->alias('OA')->select('OA.*,OS.ip,OS.token,OS.expiration');
		$sql->join('{oauth_session} AS OS', 'OA.id = OS.oauth_id AND OA.login_time = OS.create_time AND OA.device = OS.device');
		$sql->asc('OA.id')->where($data);
		$data['rows']  = $sql->toArray();
		$data['ctime'] = time();

		return $this->render($data);
	}

	/**
	 * 强制退出.
	 *
	 * @param string $token
	 * @param string $type 类型
	 *
	 * @return \wulaphp\mvc\view\JsonView
	 */
	public function logout($token, $type = '') {
		$table = new OauthTable();
		$web   = $type && preg_match('/^web.*/', $type);
		$table->forceLogout($token, $web);

		return Ajax::reload('document', '用户已被强制退出');
	}
}