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
use wulaphp\form\FormTable;

class WebWechatOauth extends BaseOauth {
    public function check(array $data) {
        if (!$data['openid'] || !$data['meta']['accessToken']) {
            log_error('openid or accessToken not find', 'oauth_wechat');

            return false;
        }
        $rtn = $this->checkWechat($data['openid'], $data['meta']['accessToken']);

        return $rtn;
    }

    public function supports(): array {
        return ['ios' => 0, 'ipad' => 0, 'android' => 0, 'pad' => 0, 'web' => 1, 'pc' => 0, 'h5' => 0];
    }

    public function getName(): string {
        return '网页微信';
    }

    public function getDesc(): string {
        return '网页微信登录';
    }

    public function getForm(): ?FormTable {
        return null;
    }
}