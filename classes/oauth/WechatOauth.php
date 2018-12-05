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
use passport\classes\form\WxSetForm;
use wulaphp\form\FormTable;

class WechatOauth extends BaseOauth {
    public function check(array $data) {
        if (!$data['openid'] || !$data['meta']['accessToken']) {
            log_error('openid or accessToken not find', 'oauth_wechat');

            return false;
        }
        $rtn = $this->checkWechat($data['openid'], $data['meta']['accessToken']);

        return $rtn;
    }

    public function supports(): array {
        return ['ios' => 1, 'ipad' => 1, 'android' => 1, 'pad' => 1, 'web' => 0, 'pc' => 0, 'h5' => 0];
    }

    public function getName(): string {
        return '微信';
    }

    public function getDesc(): string {
        return '手机端微信登录';
    }

    public function getForm(): ?FormTable {
        return new WxSetForm(true);
    }
}