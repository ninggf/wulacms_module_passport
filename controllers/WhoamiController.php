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
use wulaphp\mvc\view\JsonView;
use wulaphp\mvc\view\SimpleView;

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
		$info = 'var vip_info = '.trim(json_encode($info),'"');
		return new SimpleView($info,['Content-type'=>'text/javascript']);
	}
}