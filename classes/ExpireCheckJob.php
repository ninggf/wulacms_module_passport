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

use wulaphp\app\App;
use wulaphp\util\ICrontabJob;

class ExpireCheckJob implements ICrontabJob {
	public function run() {
		try {
			$db = App::db();
			//删除过期登录日志
			$day  = App::icfgn('keepday@passport', 365);
			$time = strtotime('-' . $day . ' days');
			$db->cud('DELETE FROM {oauth_session} WHERE expiration < ' . $time);
		} catch (\Exception $e) {
			log_warn($e->getMessage(), 'expire_check');
		}
	}
}