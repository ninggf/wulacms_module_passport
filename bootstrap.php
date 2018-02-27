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
				$system               = $ui->getMenu('system');
				$account              = $system->getMenu('passport');
				$account->name        = _tt('Passport@passport');
				$account->data['url'] = App::url('passport');
				$account->pos         = 2;
				$account->icon        = '&#xe630;';
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

	protected function bind() {
		bind('get_columns_of_passport.table', '&\passport\classes\TableCols');
	}
}

App::register(new PassportModule());