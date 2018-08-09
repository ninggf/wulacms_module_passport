<?php

namespace passport;

use passport\api\v1\AccountApi;
use passport\classes\ExpireCheckJob;
use passport\classes\model\OauthApp;
use passport\classes\PassportSetting;
use passport\classes\VipPassport;
use wula\cms\CmfModule;
use wulaphp\app\App;
use wulaphp\auth\Passport;
use wulaphp\db\DatabaseConnection;

/**
 * Class PassportModule
 * @package passport
 * @group   core
 */
class PassportModule extends CmfModule {
	public function getName() {
		return _t('Passport@passport');
	}

	public function getDescription() {
		return '支持众多第三方登录的通行证模块';
	}

	public function getHomePageURL() {
		return 'https://www.wulacms.com/modules/passprt';
	}

	public function getAuthor() {
		return 'Leo Ning';
	}

	public function getVersionList() {
		$v['1.0.0'] = '初始化模块';
		$v['1.1.0'] = '优化数据库,添加索引';
		$v['1.2.0'] = '添加推荐等级';
		$v['1.2.1'] = '添加移动端不重复登录支持';
		$v['1.2.2'] = '添加ipad,与pad设备';
		$v['1.2.3'] = '添加pc,h5,wxapp,wxgame设备';

		return $v;
	}

	/**
	 * 注册导航菜单.
	 *
	 * @bind dashboard\initUI
	 *
	 * @param \backend\classes\DashboardUI $ui
	 */
	public static function initMenu($ui) {
		$passport = whoami('admin');
		if ($passport->cando('m:system')) {
			if ($passport->cando('m:system/passport')) {
				$system     = $ui->getMenu('system');
				$menu       = $system->getMenu('passport');
				$menu->name = _tt('Passport@passport');
				$menu->pos  = 2;
				$menu->icon = '&#xe630;';

				$pass              = $menu->getMenu('account', _tt('Passports@passport'), 1);
				$pass->data['url'] = App::url('passport');
				$pass->icon        = '&#xe630;';

				$oauth              = $menu->getMenu('oauth', _tt('Oauth Login@passport'), 2);
				$oauth->data['url'] = App::url('passport/oauth');
				$oauth->icon        = '&#xe681;';

				$apps              = $menu->getMenu('apps', _tt('Oauth Apps@passport'), 3);
				$apps->data['url'] = App::url('passport/apps');
				$apps->icon        = '&#xe642;';

				$log              = $menu->getMenu('logs', _tt('Login Log@passport'), 900);
				$log->data['url'] = App::url('passport/log');
				$log->icon        = '&#xe64a;';
			}
		}
	}

	/**
	 * 权限。
	 *
	 * @param \wulaphp\auth\AclResourceManager $manager
	 *
	 * @bind rbac\initAdminManager
	 */
	public static function aclRes($manager) {
		$res = $manager->getResource('system/passport', _tt('Passport@passport'), 'm');
		$res->addOperate('u', __('Edit'));
		$res->addOperate('d', __('Delete'));
	}

	/**
	 * @param array $logs
	 *
	 * @filter system\logs
	 * @return array
	 */
	public static function syslog($logs) {
		$logs['passport'] = '通行证日志';

		return $logs;
	}

	/**
	 * @param $settings
	 *
	 * @filter backend/settings
	 * @return array
	 */
	public static function setting($settings) {
		$settings['passport'] = new PassportSetting();

		return $settings;
	}

	/**
	 * @param array  $status
	 * @param string $device
	 *
	 * @return array
	 * @filter rest\onGetClientStatus
	 */
	public static function clientStatus($status, $device) {
		//需要推荐码
		$status['neeRecCode'] = App::bcfg('need_rec@passport');
		//可以验证码登录
		$status['codeLogin'] = App::bcfg('code_login@passport');
		//允许修改用户信息
		$status['updataMeta'] = App::bcfg('allow_update@passport', true);
		//允许注册
		$status['allowReg'] = App::bcfg('enabled@passport', true);
		if ($device) {
			$appTable = new OauthApp();
			$apps     = $appTable->apps();
			$qq       = $apps['qq'];
			$wechat   = $apps['wechat'];
			$phone    = $apps['phone'];
			$oauth    = [];
			if ($qq['status'] && $qq[ $device ]) {
				$oauth[] = 'qq';
			}
			if ($wechat['status'] && $wechat[ $device ]) {
				$oauth[] = 'wechat';
			}
			if ($phone['status'] && $phone[ $device ]) {
				$oauth[] = 'phone';
			}
			//允许的第三方登录
			$status['oauth'] = $oauth;
		}

		return $status;
	}

	/**
	 * 强制退出。
	 *
	 * @param array $tokens
	 *
	 * @bind passport\onForceLogout
	 */
	public static function forceLogout($tokens) {
		AccountApi::forceLogout($tokens);
	}

	/**
	 * 强制退出。
	 *
	 * @param array $tokens
	 *
	 * @bind passport\onWebForceLogout
	 */
	public static function webForceLogout($tokens) {
		AccountApi::forceLogout($tokens, true);
	}

	/**
	 * @param Passport $passport
	 *
	 * @filter passport\newVipPassport
	 *
	 * @return Passport
	 */
	public static function createPassport($passport) {
		if ($passport instanceof Passport) {
			$passport = new VipPassport();
		}

		return $passport;
	}

	/**
	 * 定时清空过期登录会话
	 *
	 * @param int $time
	 */
	public static function crontab($time) {
		$ck = new ExpireCheckJob();
		$ck->run();
	}

	protected function bind() {
		bind('get_columns_of_passport.table', '&\passport\classes\TableCols');
	}

	protected function upgradeTo1_2_0(DatabaseConnection $db) {
		return $db->cudx("UPDATE {passport} SET spl = (length(spm)-length(replace(spm,'/',''))) WHERE spm <>''");
	}
}

App::register(new PassportModule());