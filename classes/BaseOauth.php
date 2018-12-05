<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace passport\classes;

use curlient\Curlient;
use wulaphp\form\FormTable;

abstract class BaseOauth implements IOauth {
    const device = [
        'web'     => '网站',
        'ios'     => '苹果',
        'ipad'    => 'iPad',
        'android' => '安卓',
        'pad'     => '平板',
        'h5'      => 'H5',
        'pc'      => 'PC'
    ];

    protected $options = [];

    public function supports(): array {
        return ['ios' => 1, 'ipad' => 1, 'android' => 1, 'pad' => 1, 'web' => 1, 'pc' => 1, 'h5' => 1];
    }

    /**
     * set options
     *
     * @param array $options
     */
    public function setOptions(array $options) {
        $this->options = $options;
    }

    /**
     * @return \wulaphp\form\FormTable
     */
    public function getForm(): ?FormTable {
        return null;
    }

    /**
     * @param array $meta
     *
     * @return array
     */
    public function getOauthData(?array $meta = null): array {
        $meta = is_array($meta) ? $meta : [];

        return $meta;
    }

    /**
     * 微信登录校验
     *
     * @param string $openid
     * @param string $access_token
     *
     * @return bool
     */
    public function checkWechat(string $openid, string $access_token): bool {
        $url         = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$openid}&lang=zh_CN";
        $Curl        = new Curlient();
        $wechat_data = $Curl->request($url)->json();
        if (!$wechat_data) {
            log_error('wechat_data not find', 'oauth_wechat');

            return false;
        }
        if (!isset($wechat_data['openid'])) {
            log_error('invalid wechat_data msg --->' . json_encode($wechat_data), 'oauth_wechat');

            return false;
        }

        return true;

    }

    /**
     * qq 校验
     *
     * @param string $openid
     * @param string $access_token
     *
     * @return bool
     */
    public function checkQq(string $openid, string $access_token, string $appid): bool {
        $url  = "https://graph.qq.com/user/get_user_info?access_token={$access_token}&openid={$openid}&oauth_consumer_key={$appid}";
        $Curl = new Curlient();
        $res  = $Curl->request($url)->json();
        if ($res['ret'] == 0) {
            return true;
        }
        log_error('invalid qq_data msg --->' . json_encode($res), 'oauth_qq');

        return false;
    }
}