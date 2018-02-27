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

class TableCols {
	/**
	 * passport.table的列。
	 *
	 * @param array $cols
	 *
	 * @return mixed
	 */
	public static function get_columns_of_passport_table($cols) {
		$cols['phone']    = ['name' => '手机', 'show' => true, 'width' => 120, 'order' => 20];
		$cols['email']    = ['name' => '邮箱', 'show' => true, 'width' => 180, 'order' => 30];
		$cols['spm']      = ['name' => '推荐人', 'show' => false, 'width' => 200, 'order' => 36];
		$cols['rec_code'] = ['name' => '推荐码', 'show' => true, 'width' => 120, 'order' => 38];
		$cols['channel']  = ['name' => '渠道', 'show' => true, 'sort' => 'PAS.channel', 'width' => 120, 'order' => 40];
		$cols['status']   = [
			'name'   => '激活',
			'show'   => true,
			'width'  => 80,
			'order'  => 100,
			'sort'   => 'PAS.status',
			'align'  => 'center',
			'render' => function ($v) {
				if ($v) {
					return '<span class="active"><i class="fa fa-check text-success text-active"></i></span>';
				} else {
					return '<span><i class="fa fa-times text-danger text"></i></span>';
				}
			}
		];

		return $cols;
	}
}