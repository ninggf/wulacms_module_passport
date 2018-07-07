<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace passport\classes\model;

use wulaphp\db\Table;

class OauthTable extends Table {

	public function forceLogout($token,$web=false) {
		$where['token'] = $token;

		try {
			$rst = $this->dbconnection->update('{oauth_session}')->set(['expiration' => time()])->where($where)->exec();
			if (!$rst) {
				return false;
			}
			if($web){
				fire('passport\webForceLogout', [$token]);
			}else{
				fire('passport\onForceLogout', [$token]);
			}


			return true;
		} catch (\Exception $e) {
			return false;
		}
	}
}