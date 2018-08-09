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

use wulaphp\form\FormTable;

abstract class BaseOauth implements IOauth {
	protected $options = [];

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
}