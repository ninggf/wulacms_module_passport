<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace passport\classes\form;

use wulaphp\form\FormTable;
use wulaphp\validator\JQueryValidator;

class OauthForm extends FormTable {
	use JQueryValidator;
	public $table = null;
	/**
	 * @var \backend\form\HiddenField
	 * @type string
	 */
	public $type;
	/**
	 * 是否启用
	 * @var \backend\form\CheckboxField
	 * @type bool
	 * @layout 1,col-xs-3
	 */
	public $status = 1;
	/**
	 * 启用苹果
	 * @var \backend\form\CheckboxField
	 * @type bool
	 * @layout 1,col-xs-3
	 */
	public $ios = 1;
	/**
	 * 启用安卓
	 * @var \backend\form\CheckboxField
	 * @type bool
	 * @layout 1,col-xs-3
	 */
	public $android = 1;
	/**
	 * 启用WEB
	 * @var \backend\form\CheckboxField
	 * @type bool
	 * @layout 1,col-xs-3
	 */
	public $web = 1;
}