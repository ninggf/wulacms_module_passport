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

interface IOauth {
	/**
	 * @param array $data
	 *
	 * @return bool
	 */
	public function check($data);

	/**
	 * @return string
	 */
	public function getName();

	/**
	 * @return string
	 */
	public function getDesc();

	/**
	 * @return \wulaphp\form\FormTable
	 */
	public function getForm();

	public function getOauthData(?array $meta = null): array;
}