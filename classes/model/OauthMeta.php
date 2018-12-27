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

use wulaphp\db\MetaTable;
use wulaphp\db\Table;

class OauthMeta extends Table {
    use MetaTable;
    private $metaIdField   = 'oauth_id';
    private $metaNameField = 'name';
}