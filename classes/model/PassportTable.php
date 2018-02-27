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

use wulaphp\db\DatabaseConnection;
use wulaphp\db\Table;
use wulaphp\io\Request;

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
					$id = $data['id'];
					if (isset($data['email']) && !$this->updateOauth($id, 'email', $data['email'], $data['email'], $db)) {
						return false;
					}
					if (isset($data['phone']) && !$this->updateOauth($id, 'phone', $data['phone'], $data['phone'], $db)) {
						return false;
					}
					unset($data['email']);
					$data['update_time'] = time();
					if (isset($data['recom'])) {
						$recom = $data['recom'];
						unset($data['recom']);
						$data['parent'] = self::toId($recom);
						$data['spm']    = $this->getSpm($recom, $db);
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

	/**
	 * 新用户.
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	public function newAccount($data) {
		$rst = $this->trans(function ($db) use ($data) {
			if (isset($data['email'])) {
				$email = $data['email'];
				unset($data['email']);
			}
			$data['update_time'] = $data['create_time'] = time();
			if (isset($data['recom'])) {
				$recom = $data['recom'];
				unset($data['recom']);
				$data['parent'] = self::toId($recom);
				$data['spm']    = $this->getSpm($recom, $db);
			}
			$data['ip'] = Request::getIp();
			$id         = $this->insert($data);
			if ($id) {
				if (isset($email) && !$this->updateOauth($id, 'email', $email, $email, $db)) {
					return false;
				}
				if (isset($data['phone']) && !$this->updateOauth($id, 'phone', $data['phone'], $data['phone'], $db)) {
					return false;
				}
				//生成推荐码
				$up['rec_code'] = base_convert($id, 10, 36);
				if (!$this->update($up, $id)) {
					return false;
				}
			}

			return $id;
		}, $this->errors);

		return $rst;
	}

	/**
	 * 删除
	 *
	 * @param array $ids
	 *
	 * @return bool
	 */
	public function deleteAccount($ids) {
		return $this->trans(function (DatabaseConnection $db) use ($ids) {
			//删除用户
			if (!$db->delete()->from('{passport}')->where(['id IN' => $ids])->exec()) {
				return false;
			}
			//删除用户数据
			if (!$db->delete()->from('{passport_meta}')->where(['passport_id IN' => $ids])->exec()) {
				return false;
			}
			//删除授权属性
			$sql = $db->delete()->from('{oauth_meta} AS OM');
			$sql->left('{oauth} AS OA', 'OM.oauth_id', 'OA.id')->where(['OA.passport_id iN' => $ids]);
			if (!$sql->exec()) {
				return false;
			}
			//删除登录会话表
			$sql1 = $db->delete()->from('{oauth_session} AS OM');
			$sql1->left('{oauth} AS OA', 'OM.oauth_id', 'OA.id')->where(['OA.passport_id iN' => $ids]);
			if (!$sql1->exec()) {
				return false;
			}
			//删除授权表
			if (!$db->delete()->from('{oauth}')->where(['passport_id IN' => $ids])->exec()) {
				return false;
			}

			//通知第三方通行证删除啦.
			return apply_filter('passport\onDelete', true, $ids);
		}, $this->errors);
	}

	/**
	 * @param int                            $id
	 * @param string                         $type
	 * @param string                         $open_id
	 * @param string                         $union_id
	 * @param \wulaphp\db\DatabaseConnection $db
	 *
	 * @return bool
	 * @throws
	 */
	public function updateOauth($id, $type, $open_id, $union_id, $db = null) {
		$db                   = $db ? $db : $this->dbconnection;
		$where['passport_id'] = $id;
		$where['type']        = $type;
		if (empty($open_id)) {
			//删除授权属性
			$sql = $db->delete()->from('{oauth_meta} AS OM');
			$sql->left('{oauth} AS OA', 'OM.oauth_id', 'OA.id')->where(['OA.passport_id' => $id, 'OA.type' => $type]);
			if (!$sql->exec()) {
				return false;
			}
			//删除登录会话表
			$sql1 = $db->delete()->from('{oauth_session} AS OM');
			$sql1->left('{oauth} AS OA', 'OM.oauth_id', 'OA.id')->where(['OA.passport_id' => $id, 'OA.type' => $type]);
			if (!$sql1->exec()) {
				return false;
			}
			//删除授权表
			if (!$db->delete()->from('{oauth}')->where($where)->exec()) {
				return false;
			}
		} else {
			$data = ['open_id' => $open_id, 'union_id' => $union_id, 'update_time' => time()];
			if ($db->select('id')->from('{oauth}')->where($where)->exist('id')) {
				return $db->update('{oauth}')->set($data)->where($where)->exec();
			} else {
				$data['type']        = $type;
				$data['create_time'] = $data['update_time'];
				$data['device']      = '';
				$data['passport_id'] = $id;

				return $db->insert($data)->into('{oauth}')->exec();
			}
		}

		return true;
	}

	public function updateOauthMeta($id, $name, $value, $db = null) {

	}

	public function updateMeta($id, $name, $value, $db = null) {

	}

	/**
	 * @param                    $recom
	 * @param DatabaseConnection $db
	 *
	 * @return string
	 */
	private function getSpm($recom, $db = null) {
		if (!$recom) {
			return '';
		}
		//完成推荐关系
		$db  = $db ? $db : $this->db();
		$spm = $db->select('spm')->from('{passport}')->where(['rec_code' => $recom])->get('spm');
		if ($spm) {
			return $spm . $recom . '/';
		} else {
			return $recom . '/';
		}
	}

	/**
	 * @param int|string $id
	 *
	 * @return string
	 */
	public static function toRecCode($id) {
		return base_convert($id, 10, 36);
	}

	/**
	 * @param string $coce
	 *
	 * @return string
	 */
	public static function toId($coce) {
		return base_convert($coce, 36, 10);
	}
}