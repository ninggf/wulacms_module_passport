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

class PassportTable extends Table {

	/**
	 * 更新账户信息.
	 *
	 * @param array $data
	 *
	 * @return bool 更新成功返回true,反之返回false.
	 */
	public function updateAccount($data) {
		try {
			$id = isset($data['id']) ? $data['id'] : 0;
			if ($id) {
				$rst = $this->trans(function ($db) use ($data) {
					if (isset($data['email'])) {

					}
					if (isset($data['phone'])) {

					}

					return $this->update($data, ['id' => $data['id']]);
				}, $this->errors);

				return $rst;
			} else {
				return false;
			}
		} catch (\Exception $e) {
			return false;
		}
	}
}