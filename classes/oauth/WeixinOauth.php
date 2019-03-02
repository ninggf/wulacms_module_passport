<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace passport\classes\oauth;

use passport\classes\BaseOauth;

class WeixinOauth extends BaseOauth {
    public function check(array $data) {
        return true;
    }

    public function getName(): string {
        return '服务号授权';
    }

    public function getDesc(): string {
        return '微信服务号授权登录(含静默登录)';
    }

    public function supports(): array {
        return ['ios' => 1, 'ipad' => 1, 'android' => 1, 'pad' => 1, 'web' => 0, 'pc' => 0, 'h5' => 0];
    }
}