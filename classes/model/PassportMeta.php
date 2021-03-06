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

class PassportMeta extends Table {

	public function get_field($passport_id, $name) {
		$res = $this->get(['passport_id' => $passport_id, 'name' => $name], 'value')[0];
		if ($res) {
			return $res['value'];
		}

		return false;
	}

	public function insert($data, $cb = null) {
		return parent::insert($data, $cb); // TODO: Change the autogenerated stub
	}

	public function update($data = null, $con = null, $cb = null) {
		return parent::update($data, $con, $cb); // TODO: Change the autogenerated stub
	}

	public function inserts($datas, \Closure $cb = null) {
		return parent::inserts($datas, $cb); // TODO: Change the autogenerated stub
	}

}