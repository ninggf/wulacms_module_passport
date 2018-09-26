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

class QqSetForm extends FormTable {
	use JQueryValidator;
	public $table = null;
	/**
	 * appid
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @layout 100,col-xs-12
	 */
	public $app_id;
}