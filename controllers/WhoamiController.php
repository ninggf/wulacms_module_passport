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

use wulaphp\auth\PassportSupport;
use wulaphp\mvc\controller\Controller;
use wulaphp\mvc\controller\SessionSupport;

/**
 * 通过js调用获取登录用户信息.
 *
 * @package passport\controllers
 */
class WhoamiController extends Controller {
	use SessionSupport, PassportSupport;
	protected $passportType = 'vip';

	public function index() {
		$info = $this->passport->info();
		unset($info['phone'], $info['email']);
		$info = 'var userInfo = ' . trim(json_encode($info), '"');
		header('Content-type: application/javascript; charset=UTF-8');
		echo $info;
		exit();
	}
}