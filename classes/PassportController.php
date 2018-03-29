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

use wulaphp\auth\PassportSupport;
use wulaphp\auth\RbacSupport;
use wulaphp\mvc\controller\Controller;
use wulaphp\mvc\controller\SessionSupport;

class PassportController extends Controller {
	use SessionSupport, PassportSupport, RbacSupport;
	protected $passportType = 'vip';

	protected function needLogin($view) {
		return apply_filter('passport\needLogin', $view);
	}

	protected function onDenied($message, $view) {
		return apply_filter('passport\onDenied', $view, $message);
	}
}