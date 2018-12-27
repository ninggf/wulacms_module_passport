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

    public function forceLogout($token, $web = false) {
        $where['token'] = $token;

        try {
            $rst = $this->dbconnection->update('{oauth_session}')->set(['expiration' => time()])->where($where)->exec();
            if (!$rst) {
                return false;
            }
            if ($web) {
                fire('passport\webForceLogout', [$token]);
            } else {
                fire('passport\onForceLogout', [$token]);
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 更新oauth meta数据.
     *
     * @param string|int                          $id
     * @param string                              $name
     * @param string                              $value
     * @param null|\wulaphp\db\DatabaseConnection $db
     *
     * @return bool
     */
    public function updateMeta($id, $name, $value, $db = null) {
        static $meta = null;
        if (!$meta) {
            $meta = new OauthMeta($db);
        }

        return $meta->setMeta($id, $name, $value);
    }

    /**
     * 获取meta值
     *
     * @param string|int $id
     * @param string     $name
     *
     * @return string
     */
    public function getMeta($id, $name) {
        static $meta = null;
        if (!$meta) {
            $meta = new OauthMeta();
        }

        return $meta->getMeta($id, $name);
    }
}