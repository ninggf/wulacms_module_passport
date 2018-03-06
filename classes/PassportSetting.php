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

use backend\classes\Setting;
use passport\classes\form\SettingForm;

class PassportSetting extends Setting {
	public function getForm($group = '') {
		return new SettingForm(true);
	}

	public function getName() {
		return '通行证配置';
	}
}