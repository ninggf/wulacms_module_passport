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

abstract class BaseOauth implements IOauth {
	/**
	 * @return \wulaphp\form\FormTable
	 */
	public function getForm() {
		return null;
	}
}