<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace passport\controllers;

use backend\classes\IFramePageController;
use backend\form\BootstrapFormRender;
use passport\classes\form\OauthForm;
use passport\classes\model\OauthApp;
use wulaphp\io\Ajax;
use wulaphp\io\Response;
use wulaphp\util\ArrayCompare;
use wulaphp\validator\JQueryValidatorController;
use wulaphp\validator\ValidateException;

/**
 * Class AppsController
 * @package passport\controllers
 * @acl     m:system/passport
 */
class AppsController extends IFramePageController {
    use JQueryValidatorController;

    public function index() {
        return $this->render();
    }

    public function data($sort) {
        $app          = new OauthApp();
        $data['rows'] = $app->apps();
        usort($data['rows'], ArrayCompare::compare($sort['name'], $sort['dir']));

        return view($data);
    }

    public function cfg($id) {
        $apps = OauthApp::getApps();
        if (!isset($apps[ $id ])) {
            Response::error('第三方登录' . $id . '不存在');
        }
        $table = new OauthApp();
        $cfg   = $table->get($id)->get();
        if (!$cfg) {
            $cfg['type'] = $id;
            $table->newApp($cfg);
        }
        $app      = $apps[ $id ];
        $form     = new OauthForm(true);
        $supports = $app->supports();
        foreach ($supports as $s => $v) {
            if (!$v) {
                $form->excludeFields($s);
            }
        }
        $form->inflateByData($cfg);

        $aform   = $app->getForm();
        $options = $cfg['options'] ? @json_decode($cfg['options'], true) : false;
        if ($aform) {
            if ($options) {
                $aform->inflateByData($options);
            }
            $data['aform'] = BootstrapFormRender::v($aform);
            $form->applyRules($aform);
        }

        $data['form']  = BootstrapFormRender::v($form);
        $data['rules'] = $form->encodeValidatorRule($this);

        return view($data);
    }

    public function save($type) {
        $apps = OauthApp::getApps();
        if (!isset($apps[ $type ])) {
            Response::error('第三方登录' . $type . '不存在');
        }
        $table = new OauthApp();
        $cfg   = $table->exist(['type' => $type]);
        if (!$cfg) {
            Response::error('第三方登录' . $type . '不可用');
        }
        $form = new OauthForm(true);
        $app  = $form->inflate();
        try {
            $form->validate();
            $appx  = $apps[ $type ];
            $aform = $appx->getForm();
            if ($aform) {
                $options = $aform->inflate();
                if ($options) {
                    $app['options'] = json_encode($options);
                } else {
                    $app['options'] = '';
                }
                $aform->validate();
            }
            $rst = $table->updateApp($app);
            if ($rst) {
                return Ajax::reload('#table', '配置完成');
            } else {
                return Ajax::error('配置失败');
            }
        } catch (ValidateException $e) {
            return Ajax::validate('AppEditForm', $e->getErrors());
        } catch (\Exception $e) {
            return Ajax::error('配置失败:' . $e->getMessage());
        }
    }
}