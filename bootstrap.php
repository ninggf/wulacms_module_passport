<?php

namespace passport;

use wula\cms\CmfModule;
use wulaphp\app\App;

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
		return 'wulacms team';
	}

	public function getVersionList() {
		$v['1.0.0'] = '初始化模块';

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

	protected function bind() {
		bind('get_columns_of_passport.table', '&\passport\classes\TableCols');
	}
}

App::register(new PassportModule());