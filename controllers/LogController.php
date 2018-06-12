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
use passport\classes\model\OauthSessionTable;
use wulaphp\db\sql\Condition;

/**
 * Class LogController
 * @package passport\controllers
 * @acl     m:system/passport
 */
class LogController extends IFramePageController {
	public function index($oauth = '', $pid = '', $type = '') {
		$data['oauth']  = $oauth;
		$data['pid']    = $pid;
		$data['type']   = $type;
		$data['groups'] = OauthApp::getAppsName();

		return $this->render($data);
	}

	public function data($q, $type, $oid, $token, $count) {
		$table = new OauthSessionTable();
		$sql   = $table->alias('OS')->select('OS.*,OA.passport_id,OA.type,OA.open_id');
		$sql->left('{oauth} AS OA', 'OA.id', 'OS.oauth_id');

		$where = [];
		if ($type) {
			$where['type'] = $type;
		}

		if ($oid) {
			$where['oauth_id'] = $oid;
		}
		if ($token) {
			$where['token'] = $token;
		}
		if ($q) {
			$qw = Condition::parseSearchExpression($q, [
				'通行证'  => 'OS.passport_id',
				'设备'   => 'OS.device',
				'最近登录' => '@OS.create_time',
				'过期时间' => '@expiration',
				'类型'   => 'type'
			]);
			if ($qw) {
				$sql->where($qw);
			} else {
				$where['OA.passport_id'] = (int)$q;
			}
		}
		$sql->sort()->page()->where($where);

		if ($count) {
			$data['total'] = $sql->total('*');
		}

		$data['rows'] = $sql->toArray();

		return view($data);
	}
}