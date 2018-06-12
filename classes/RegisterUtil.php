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

use passport\classes\model\PassportTable;
use wulaphp\app\App;

class RegisterUtil {
	/**
	 * IP黑名单检测.
	 *
	 * @param string $ip
	 *
	 * @return bool
	 */
	public static function checkIp($ip) {
		if (!$ip) {
			return false;
		}
		$blackips = App::cfg('blackips@passport');
		if (empty($blackips)) {
			return true;
		}
		$ips = explode("\n", $blackips);

		return in_array($ip, $ips);
	}

	/**
	 * 注册限速检测.
	 *
	 * @param string $ip
	 *
	 * @return bool
	 */
	public static function limit($ip) {
		$max_count = App::icfg('max_count@passport');
		if ($max_count == 0) {
			return true;
		}
		$interval = App::icfg('interval@passport', 60);
		$time     = time() - $interval;
		try {
			$db    = App::db();
			$total = $db->select(imv('COUNT(id)', 'total'))->from('{passport}')->where([
				'ip'             => $ip,
				'create_time >=' => $time
			])->get();
			if ($total && $total['total'] >= $max_count) {
				return false;
			}
		} catch (\Exception $e) {
		}

		return true;
	}

	/**
	 * 推荐码是否有效.
	 *
	 * @param string $code
	 *
	 * @return bool
	 */
	public static function checkRecCode($code) {
		$needRecCode = App::bcfg('need_rec@passport');
		if (!$needRecCode) {
			return true;
		}
		if (empty($code)) {
			return false;
		}
		try {
			$db = App::db();
			if (!$db->select('id')->from('{passport}')->where(['rec_code' => $code])->exist('id')) {
				return false;
			}
			$max_rec = App::icfg('max_rec@passport');
			if ($max_rec) {
				$pid  = PassportTable::toId($code);
				$toal = $db->select(imv('COUNT(id)', 'total'))->from('{passport}')->where(['parent' => $pid])->get('total');
				if ($toal >= $max_rec) {
					return false;
				}
			}

			return true;
		} catch (\Exception $e) {

		}

		return false;
	}

	/**
	 * 手机是否有效.
	 *
	 * @param string $phone
	 *
	 * @return bool
	 */
	public static function checkPhone($phone) {
		if (empty($phone)) {
			return false;
		}
		try {
			$db = App::db();

			return !($db->select('id')->from('{oauth}')->where(['open_id' => $phone, 'type' => 'phone'])->exist('id'));
		} catch (\Exception $e) {

		}

		return false;
	}
}