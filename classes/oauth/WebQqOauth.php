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
use passport\classes\form\QqSetForm;
use wulaphp\form\FormTable;

class WebQqOauth extends BaseOauth {
    public function check(array $data) {
        $app_id = $this->options['app_id'] ?? false;
        if (!$data['openid'] || !$data['meta']['accessToken'] || !$app_id) {
            log_error('openid or accessToken appid not find', 'oauth_qq');

            return false;
        }
        $qq_check = $this->checkQq($data['openid'], $data['meta']['accessToken'], $app_id);

        return $qq_check;
    }

    public function supports(): array {
        return ['ios' => 0, 'ipad' => 0, 'android' => 0, 'pad' => 0, 'web' => 1, 'pc' => 0, 'h5' => 0];
    }

    public function getName(): string {
        return '网页QQ';
    }

    public function getDesc(): string {
        return '网页QQ登录';
    }

    public function getForm(): ?FormTable {
        return new QqSetForm(true);
    }
}